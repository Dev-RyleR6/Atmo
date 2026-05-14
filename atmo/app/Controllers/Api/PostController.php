<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\RepostModel;
use CodeIgniter\API\ResponseTrait;

class PostController extends BaseController
{
    use ResponseTrait;

    public function index($feedType = 'for_you')
    {
        $userId = session()->get('user_id');
        $db = \Config\Database::connect();
        
        $followingIdsQuery = $db->table('follows')
                               ->select('followed_id')
                               ->where('follower_id', $userId)
                               ->get()
                               ->getResultArray();
                               
        $followingIds = array_column($followingIdsQuery, 'followed_id');
        $followingIds[] = $userId; // Include self

        $postModel = new PostModel();
        $likeModel = new LikeModel();
        $commentModel = new CommentModel();
        $repostModel = new RepostModel();
        
        // Helper function to add social data to a post
        $addSocialData = function(&$post) use ($likeModel, $commentModel, $repostModel, $userId, $userModel) {
            $postId = $post['id'];
            $post['like_count'] = $likeModel->where('post_id', $postId)->countAllResults();
            $post['comment_count'] = $commentModel->where('post_id', $postId)->countAllResults();
            $post['repost_count'] = $repostModel->where('post_id', $postId)->countAllResults();
            $post['is_liked'] = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first() ? true : false;
            $post['is_reposted'] = $repostModel->where('user_id', $userId)->where('post_id', $postId)->first() ? true : false;
            
            // Fetch comments with user data
            $comments = $commentModel->where('post_id', $postId)
                                     ->orderBy('created_at', 'ASC')
                                     ->limit(10)
                                     ->findAll();
            foreach ($comments as &$comment) {
                $commentUser = $userModel->find($comment['user_id']);
                if ($commentUser) {
                    unset($commentUser['password']);
                    $comment['user'] = $commentUser;
                }
            }
            $post['comments'] = $comments;
        };
        
        // Fetch original posts
        if ($feedType === 'your_atmosphere') {
            // Your Atmosphere: only posts from followed users (including self)
            $posts = $postModel->whereIn('user_id', $followingIds)
                               ->where('visibility !=', 'private')
                               ->orderBy('created_at', 'DESC')
                               ->limit(50)
                               ->findAll();
                               
            $reposts = $repostModel->whereIn('user_id', $followingIds)
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
        } else {
            // For You: all public posts (or from followed if you have any)
            if (count($followingIds) > 1) {
                $posts = $postModel->whereIn('user_id', $followingIds)
                                   ->where('visibility !=', 'private')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
                                   
                $reposts = $repostModel->whereIn('user_id', $followingIds)
                                       ->orderBy('created_at', 'DESC')
                                       ->limit(50)
                                       ->findAll();
            } else {
                $posts = $postModel->where('visibility', 'public')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
                                   
                $reposts = $repostModel->where('user_id', $userId)
                                       ->orderBy('created_at', 'DESC')
                                       ->limit(50)
                                       ->findAll();
            }
        }

        $userModel = new \App\Models\UserModel();
        
        // Process original posts
        foreach ($posts as &$post) {
            $post['type'] = 'original';
            $user = $userModel->find($post['user_id']);
            if ($user) {
                unset($user['password']);
                $post['user'] = $user;
            }
            $addSocialData($post);
        }

        // Process reposts
        foreach ($reposts as &$repost) {
            $repost['type'] = 'repost';
            $author = $userModel->find($repost['user_id']);
            if ($author) {
                unset($author['password']);
                $repost['reposted_by'] = $author;
            }
            
            // Fetch original post details
            $originalPost = $postModel->find($repost['post_id']);
            if ($originalPost) {
                $originalAuthor = $userModel->find($originalPost['user_id']);
                if ($originalAuthor) {
                    unset($originalAuthor['password']);
                    $originalPost['user'] = $originalAuthor;
                }
                $addSocialData($originalPost);
                $repost['original_post'] = $originalPost;
            }
        }

        // Merge and sort
        $feed = array_merge($posts, $reposts);
        usort($feed, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $this->respond(array_slice($feed, 0, 50));
    }

    public function create()
    {
        $userId = session()->get('user_id');
        
        $rules = [
            'content'    => 'permit_empty|string',
            'visibility' => 'required|in_list[public,followers,private]',
        ];

        $file = $this->request->getFile('media');
        if ($file && $file->isValid()) {
            $rules['media'] = 'max_size[media,10240]|ext_in[media,png,jpg,jpeg,gif,mp4]';
        }

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $mediaPath = null;
        $mediaType = 'text';

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/posts', $newName);
            $mediaPath = 'uploads/posts/' . $newName;
            
            $mime = $file->getMimeType();
            $mediaType = strpos($mime, 'video') !== false ? 'video' : 'image';
        }

        $content = $this->request->getVar('content');
        if (empty($content) && empty($mediaPath)) {
            return $this->fail('Post cannot be completely empty.');
        }

        $postModel = new PostModel();

        $data = [
            'user_id'    => $userId,
            'content'    => $content,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'visibility' => $this->request->getVar('visibility'),
        ];

        if ($postModel->skipValidation(true)->insert($data)) {
            $data['id'] = $postModel->getInsertID();
            return $this->respondCreated(['status' => 'success', 'post' => $data]);
        }

        return $this->fail('Failed to create post');
    }

    public function show($id)
    {
        $postModel = new PostModel();
        $post = $postModel->find($id);

        if (!$post) {
            return $this->failNotFound('Post not found');
        }

        return $this->respond($post);
    }

    public function delete($id)
    {
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        
        $post = $postModel->find($id);
        if (!$post) {
            return $this->failNotFound('Post not found');
        }

        if ($post['user_id'] != $userId) {
            return $this->failForbidden('You can only delete your own posts');
        }

        if ($postModel->delete($id)) {
            return $this->respondDeleted(['status' => 'success', 'message' => 'Post deleted']);
        }

        return $this->fail('Failed to delete post');
    }

    public function toggleLike($postId)
    {
        $userId = session()->get('user_id');
        $likeModel = new LikeModel();
        
        $existing = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $likeModel->where('user_id', $userId)->where('post_id', $postId)->delete();
            $isLiked = false;
        } else {
            $likeModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            $isLiked = true;
        }

        $likeCount = $likeModel->where('post_id', $postId)->countAllResults();
        
        return $this->respond([
            'status' => 'success',
            'is_liked' => $isLiked,
            'like_count' => $likeCount
        ]);
    }

    public function toggleRepost($postId)
    {
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        $repostModel = new RepostModel();
        
        $originalPost = $postModel->find($postId);
        if (!$originalPost) {
            return $this->failNotFound('Post not found');
        }

        $existing = $repostModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $repostModel->where('user_id', $userId)->where('post_id', $postId)->delete();
            $isReposted = false;
        } else {
            $repostModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            $isReposted = true;
        }

        $repostCount = $repostModel->where('post_id', $postId)->countAllResults();
        
        return $this->respond([
            'status' => 'success',
            'is_reposted' => $isReposted,
            'repost_count' => $repostCount
        ]);
    }

    public function addComment($postId)
    {
        $userId = session()->get('user_id');
        $text = $this->request->getPost('comment_text');

        if (empty($text)) {
            return $this->fail('Comment text cannot be empty');
        }

        $postModel = new PostModel();
        $post = $postModel->find($postId);
        if (!$post) {
            return $this->failNotFound('Post not found');
        }

        $commentModel = new CommentModel();
        $commentId = $commentModel->insert([
            'post_id' => $postId,
            'user_id' => $userId,
            'comment_text' => $text
        ]);

        $comment = $commentModel->find($commentId);

        $userModel = new \App\Models\UserModel();
        $commentUser = $userModel->find($userId);
        if ($commentUser) {
            unset($commentUser['password']);
            $comment['user'] = $commentUser;
        }

        $commentCount = $commentModel->where('post_id', $postId)->countAllResults();

        return $this->respond([
            'status' => 'success',
            'comment' => $comment,
            'comment_count' => $commentCount
        ]);
    }
}
