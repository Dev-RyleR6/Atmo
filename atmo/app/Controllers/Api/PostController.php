<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PostModel;
use CodeIgniter\API\ResponseTrait;

class PostController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $userId = $this->request->user_id;
        $db = \Config\Database::connect();
        
        // Fetch posts from users I follow + my own posts
        // This includes original posts and reposts via a UNION or combined query
        // For simplicity and high performance, we use a subquery for followed IDs
        
        $followingIdsQuery = $db->table('follows')
                               ->select('followed_id')
                               ->where('follower_id', $userId)
                               ->get()
                               ->getResultArray();
                               
        $followingIds = array_column($followingIdsQuery, 'followed_id');
        $followingIds[] = $userId; // Include self

        $postModel = new PostModel();
        
        // Fetch original posts
        $posts = $postModel->whereIn('user_id', $followingIds)
                           ->where('visibility !=', 'private')
                           ->orderBy('created_at', 'DESC')
                           ->limit(50)
                           ->findAll();
                           
        // Fetch Reposts and merge
        $repostModel = new \App\Models\RepostModel();
        $reposts = $repostModel->whereIn('user_id', $followingIds)
                               ->orderBy('created_at', 'DESC')
                               ->limit(50)
                               ->findAll();

        $userModel = new \App\Models\UserModel();
        
        // Process original posts
        foreach ($posts as &$post) {
            $post['type'] = 'original';
            $user = $userModel->find($post['user_id']);
            if ($user) {
                unset($user['password']);
                $post['user'] = $user;
            }
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
        $userId = $this->request->user_id;
        
        $rules = [
            'content'    => 'permit_empty|string',
            'visibility' => 'required|in_list[public,followers,private]',
            'media'      => 'uploaded[media]|max_size[media,10240]|ext_in[media,png,jpg,jpeg,gif,mp4]|permit_empty',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $mediaPath = null;
        $mediaType = 'text';

        $file = $this->request->getFile('media');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/posts', $newName);
            $mediaPath = 'uploads/posts/' . $newName;
            
            $mime = $file->getMimeType();
            $mediaType = str_contains($mime, 'video') ? 'video' : 'image';
        }

        $postModel = new PostModel();

        $data = [
            'user_id'    => $userId,
            'content'    => $this->request->getVar('content'),
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'visibility' => $this->request->getVar('visibility'),
        ];

        if ($postModel->insert($data)) {
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
        $userId = $this->request->user_id;
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
}
