<?php

namespace App\Controllers;

use App\Models\PostModel;
use App\Models\RepostModel;
use App\Models\UserModel;

class PostController extends BaseController
{
    public function index()
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
        
        // Fetch original posts
        $posts = $postModel->whereIn('user_id', $followingIds)
                           ->where('visibility !=', 'private')
                           ->orderBy('created_at', 'DESC')
                           ->limit(50)
                           ->findAll();
                           
        // Fetch Reposts and merge
        $repostModel = new RepostModel();
        $reposts = $repostModel->whereIn('user_id', $followingIds)
                               ->orderBy('created_at', 'DESC')
                               ->limit(50)
                               ->findAll();

        $userModel = new UserModel();
        
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
        
        $feed = array_slice($feed, 0, 50);

        return view('feed', ['posts' => $feed]);
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
}
