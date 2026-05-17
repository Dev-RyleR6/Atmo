<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\RepostModel;
use App\Models\UserModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Services\NotificationService;

/**
 * PostController - Handles all post-related operations
 * 
 * Manages:
 * - Feed generation (For You, Your Atmosphere)
 * - Post CRUD operations
 * - Interactions (likes, reposts, comments)
 * - Social data aggregation
 */
class PostController extends BaseController
{
    /**
     * Generates the feed based on feed type with posts and reposts
     * 
     * @param string $feedType 'for_you' or 'your_atmosphere'
     * @return array Combined sorted feed of original posts and reposts
     */
    private function getFeed($feedType = 'for_you')
    {
        $userId = session()->get('user_id');
        $db = \Config\Database::connect();
        
        // Safely get following IDs
        $followingIdsQuery = $db->table('follows')
                               ->select('followed_id')
                               ->where('follower_id', $userId)
                               ->get()
                               ->getResultArray();
                               
        $followingIds = array_column($followingIdsQuery, 'followed_id');
        $followingIds[] = $userId; // Include self
        
        // Ensure followingIds is never empty
        if (empty($followingIds)) {
            $followingIds = [$userId];
        }

        $postModel = new PostModel();
        $likeModel = new LikeModel();
        $commentModel = new CommentModel();
        $repostModel = new RepostModel();
        $userModel = new UserModel();
        
        // Initialize posts and reposts arrays safely
        $posts = [];
        $reposts = [];
        
        // Fetch original posts
        if ($feedType === 'your_atmosphere') {
            // Your Atmosphere: only posts from followed users (including self)
            $postsQuery = $postModel->whereIn('user_id', $followingIds)
                               ->where('visibility !=', 'private')
                               ->orderBy('created_at', 'DESC')
                               ->limit(50)
                               ->findAll();
            $posts = is_array($postsQuery) ? $postsQuery : [];
                               
            $repostsQuery = $repostModel->whereIn('user_id', $followingIds)
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
            $reposts = is_array($repostsQuery) ? $repostsQuery : [];
        } else {
            // For You: all public posts (or from followed if you have any)
            if (count($followingIds) > 1) {
                $postsQuery = $postModel->whereIn('user_id', $followingIds)
                                   ->where('visibility !=', 'private')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
                $posts = is_array($postsQuery) ? $postsQuery : [];
                                   
                $repostsQuery = $repostModel->whereIn('user_id', $followingIds)
                                       ->orderBy('created_at', 'DESC')
                                       ->limit(50)
                                       ->findAll();
                $reposts = is_array($repostsQuery) ? $repostsQuery : [];
            } else {
                $postsQuery = $postModel->where('visibility', 'public')
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(50)
                                   ->findAll();
                $posts = is_array($postsQuery) ? $postsQuery : [];
                                   
                $repostsQuery = $repostModel->where('user_id', $userId)
                                       ->orderBy('created_at', 'DESC')
                                       ->limit(50)
                                       ->findAll();
                $reposts = is_array($repostsQuery) ? $repostsQuery : [];
            }
        }
        
        // Helper function to add social data to a post
        $addSocialData = function(&$post) use ($likeModel, $commentModel, $repostModel, $userId, $userModel) {
            $postId = isset($post['id']) ? $post['id'] : null;
            if (!$postId) return;
            
            $post['like_count'] = $likeModel->where('post_id', $postId)->countAllResults();
            $post['comment_count'] = $commentModel->where('post_id', $postId)->countAllResults();
            $post['repost_count'] = $repostModel->where('post_id', $postId)->countAllResults();
            $post['is_liked'] = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first() ? true : false;
            $post['is_reposted'] = $repostModel->where('user_id', $userId)->where('post_id', $postId)->first() ? true : false;
            
            // Fetch comments with user data - always initialize as array
            $commentsQuery = $commentModel->where('post_id', $postId)
                                     ->orderBy('created_at', 'ASC')
                                     ->limit(10)
                                     ->findAll();
            $comments = is_array($commentsQuery) ? $commentsQuery : [];
            
            foreach ($comments as &$comment) {
                if (isset($comment['user_id'])) {
                    $commentUser = $userModel->find($comment['user_id']);
                    if ($commentUser) {
                        unset($commentUser['password']);
                        $comment['user'] = $commentUser;
                    } else {
                        $comment['user'] = [];
                    }
                }
            }
            $post['comments'] = $comments;
        };
        
        // Process original posts
        $validPosts = [];
        if (is_array($posts)) {
            foreach ($posts as &$post) {
                $post['type'] = 'original';
                // Skip posts without content and media
                if (empty($post['content']) && empty($post['media_path'])) {
                    continue;
                }
                if (!isset($post['user_id'])) {
                    continue;
                }
                $user = $userModel->find($post['user_id']);
                if (!$user) {
                    continue; // Skip if user not found
                }
                unset($user['password']);
                $post['user'] = $user;
                $addSocialData($post);
                $validPosts[] = $post;
            }
        }
        $posts = $validPosts;

        // Process reposts
        $validReposts = [];
        if (is_array($reposts)) {
            foreach ($reposts as &$repost) {
                $repost['type'] = 'repost';
                if (!isset($repost['user_id'])) {
                    continue;
                }
                $author = $userModel->find($repost['user_id']);
                if (!$author) {
                    continue; // Skip if repost author not found
                }
                unset($author['password']);
                $repost['reposted_by'] = $author;
                
                if (!isset($repost['post_id'])) {
                    continue;
                }
                $originalPost = $postModel->find($repost['post_id']);
                if (!$originalPost) {
                    continue; // Skip if original post not found
                }
                // Skip reposts without content and media
                if (empty($originalPost['content']) && empty($originalPost['media_path'])) {
                    continue;
                }
                if (!isset($originalPost['user_id'])) {
                    continue;
                }
                $originalAuthor = $userModel->find($originalPost['user_id']);
                if (!$originalAuthor) {
                    continue; // Skip if original post author not found
                }
                unset($originalAuthor['password']);
                $originalPost['user'] = $originalAuthor;
                $addSocialData($originalPost);
                $repost['original_post'] = $originalPost;
                $validReposts[] = $repost;
            }
        }
        $reposts = $validReposts;

        // Merge and sort
        $feed = array_merge($posts, $reposts);
        usort($feed, function($a, $b) {
            $aTime = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $bTime = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
            return $bTime - $aTime;
        });
        
        return array_slice($feed, 0, 50);
    }

    public function index()
    {
        $feedType = session()->get('current_feed_type') ?? 'for_you';
        
        // Check if feed type is passed via query parameter
        if ($this->request->getGet('feed')) {
            $feedType = $this->request->getGet('feed');
            session()->set('current_feed_type', $feedType);
        }
        
        return view('feed', ['posts' => $this->getFeed($feedType), 'current_feed_type' => $feedType]);
    }
    
    public function feed($feedType = 'for_you')
    {
        return $this->respond($this->getFeed($feedType));
    }
    
    public function toggleLike($postId)
    {
        $userId = session()->get('user_id');
        $likeModel = new LikeModel();
        $postModel = new PostModel();
        
        $existing = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $likeModel->where('user_id', $userId)->where('post_id', $postId)->delete();
        } else {
            $likeModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            
            $post = $postModel->find($postId);
            if ($post) {
                NotificationService::notify($post['user_id'], $userId, 'like', $postId);
            }
        }
        
        return redirect()->back();
    }
    
    public function addComment($postId)
    {
        $userId = session()->get('user_id');
        $text = $this->request->getPost('comment_text');
        $postModel = new PostModel();

        if (!empty($text)) {
            $commentModel = new CommentModel();
            $commentModel->insert([
                'post_id' => $postId,
                'user_id' => $userId,
                'comment_text' => $text
            ]);
            
            $post = $postModel->find($postId);
            if ($post) {
                NotificationService::notify($post['user_id'], $userId, 'comment', $postId);
            }
        }
        
        return redirect()->back();
    }
    
    public function toggleRepost($postId)
    {
        /**
         * Toggle repost status for a post
         * If user has already reposted, this DELETES the repost (permanently removes from DB)
         * If user hasn't reposted, this CREATES a new repost record
         * 
         * @param int $postId Original post ID to repost/unrepost
         */
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        $repostModel = new RepostModel();
        
        $originalPost = $postModel->find($postId);
        if (!$originalPost) {
            return redirect()->back()->with('error', 'Post not found');
        }

        // Check if this user has already reposted this post
        $existingRepost = $repostModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existingRepost) {
            // DELETE: Permanently remove the repost relationship from database
            $repostModel->where('user_id', $userId)->where('post_id', $postId)->delete();
        } else {
            // CREATE: Add new repost and send notification to original poster
            $repostModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            
            NotificationService::notify($originalPost['user_id'], $userId, 'repost', $postId);
        }
        
        return redirect()->back();
    }

    public function create()
    {
        $userId = session()->get('user_id');
        
        $rules = [
            'content'    => 'permit_empty|string|max_length[1000]',
            'visibility' => 'required|in_list[public,followers,private]',
        ];

        $media = $this->request->getFile('media');
        if ($media && $media->isValid()) {
            $rules['media'] = 'max_size[media,10240]|ext_in[media,png,jpg,jpeg,gif,mp4]';
        }


        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mediaPath = null;
        $mediaType = 'text';

        $file = $this->request->getFile('media');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$file->isValid()) {
                return redirect()->back()->withInput()->with('error', $file->getErrorString());
            }
            if (!$file->hasMoved()) {
                $mime = $file->getMimeType();
                $mediaType = str_contains($mime, 'video') ? 'video' : 'image';

                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/posts', $newName);
                $mediaPath = 'uploads/posts/' . $newName;
            }
        }

        $content = strip_tags($this->request->getPost('content'));

        if (empty($content) && empty($mediaPath)) {
            return redirect()->back()->with('error', 'Post cannot be completely empty.');
        }

        $postModel = new PostModel();
        $data = [
            'user_id'    => $userId,
            'content'    => $content,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'visibility' => $this->request->getPost('visibility') ?? 'public',
        ];

        if ($postModel->skipValidation(true)->insert($data)) {
            return redirect()->to(site_url('feed'))->with('success', 'Post created successfully!');
        }

        $dbError = $postModel->errors() ?: ['Upload failed or Database error in post creation.'];
        return redirect()->back()->with('errors', $dbError);
    }

    public function delete($id)
    {
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        
        $post = $postModel->find($id);

        if (!$post) {
            return redirect()->back()->with('error', 'Post not found');
        }

        if ($post['user_id'] != $userId) {
            return redirect()->back()->with('error', 'Unauthorized to delete this post');
        }

        if ($postModel->delete($id)) {
            return redirect()->back()->with('success', 'Post deleted successfully');
        }

        return redirect()->back()->with('error', 'Failed to delete post');
    }

    public function edit($id)
    {
        $userId = session()->get('user_id');
        $postModel = new PostModel();
        
        $post = $postModel->find($id);

        if (!$post) {
            return redirect()->back()->with('error', 'Post not found');
        }

        if ($post['user_id'] != $userId) {
            return redirect()->back()->with('error', 'Unauthorized to edit this post');
        }

        $rules = [
            'content' => 'permit_empty|string|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $content = strip_tags($this->request->getPost('content'));
        
        if (empty($content) && empty($post['media_path'])) {
            return redirect()->back()->with('error', 'Post cannot be completely empty.');
        }

        $postModel->skipValidation(true)->update($id, ['content' => $content]);
        return redirect()->back()->with('success', 'Post updated successfully');
    }

    public function editComment($commentId)
    {
        $userId = session()->get('user_id');
        $commentModel = new CommentModel();
        
        $comment = $commentModel->find($commentId);

        if (!$comment) {
            return redirect()->back()->with('error', 'Comment not found');
        }

        if ($comment['user_id'] != $userId) {
            return redirect()->back()->with('error', 'Unauthorized to edit this comment');
        }

        $rules = [
            'comment_text' => 'required|min_length[1]|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $text = strip_tags($this->request->getPost('comment_text'));
        
        $commentModel->skipValidation(true)->update($commentId, ['comment_text' => $text]);
        return redirect()->back()->with('success', 'Comment updated successfully');
    }

    public function deleteComment($commentId)
    {
        $userId = session()->get('user_id');
        $commentModel = new CommentModel();
        
        $comment = $commentModel->find($commentId);

        if (!$comment) {
            return redirect()->back()->with('error', 'Comment not found');
        }

        if ($comment['user_id'] != $userId) {
            return redirect()->back()->with('error', 'Unauthorized to delete this comment');
        }

        if ($commentModel->delete($commentId)) {
            return redirect()->back()->with('success', 'Comment deleted successfully');
        }

        return redirect()->back()->with('error', 'Failed to delete comment');
    }
}
