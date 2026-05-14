<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PostModel;
use App\Models\FollowModel;
use CodeIgniter\API\ResponseTrait;

class UserController extends BaseController
{
    use ResponseTrait;

    public function show($usernameOrId)
    {
        $userModel = new UserModel();
        
        $user = $userModel->where('username', $usernameOrId)->orWhere('id', $usernameOrId)->first();

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password']);

        // Fetch user's posts
        $postModel = new PostModel();
        $posts = $postModel->where('user_id', $user['id'])
                           ->orderBy('created_at', 'DESC')
                           ->findAll();
        
        $user['posts'] = $posts;

        return $this->respond($user);
    }

    public function search()
    {
        $query = $this->request->getVar('q');
        if (empty($query)) {
            return $this->respond([]);
        }

        $userModel = new UserModel();
        $users = $userModel->like('username', $query)
                           ->orLike('first_name', $query)
                           ->orLike('last_name', $query)
                           ->limit(10)
                           ->findAll();

        foreach ($users as &$user) {
            unset($user['password']);
        }

        return $this->respond($users);
    }
    
    public function followers($username)
    {
        $loggedInUserId = session()->get('user_id');
        $userModel = new UserModel();
        $followModel = new FollowModel();
        
        $user = $userModel->where('username', $username)->first();
        
        if (!$user) {
            return $this->failNotFound('User not found');
        }
        
        // Get followers
        $followers = $followModel->where('followed_id', $user['id'])->findAll();
        $followerIds = array_column($followers, 'follower_id');
        
        $followerUsers = [];
        if (!empty($followerIds)) {
            $followerUsers = $userModel->whereIn('id', $followerIds)->findAll();
            foreach ($followerUsers as &$follower) {
                unset($follower['password']);
                // Check if logged in user follows this follower
                $follower['is_following'] = $loggedInUserId && $followModel->where('follower_id', $loggedInUserId)->where('followed_id', $follower['id'])->first() ? true : false;
            }
        }
        
        return $this->respond($followerUsers);
    }
    
    public function following($username)
    {
        $loggedInUserId = session()->get('user_id');
        $userModel = new UserModel();
        $followModel = new FollowModel();
        
        $user = $userModel->where('username', $username)->first();
        
        if (!$user) {
            return $this->failNotFound('User not found');
        }
        
        // Get following
        $following = $followModel->where('follower_id', $user['id'])->findAll();
        $followingIds = array_column($following, 'followed_id');
        
        $followingUsers = [];
        if (!empty($followingIds)) {
            $followingUsers = $userModel->whereIn('id', $followingIds)->findAll();
            foreach ($followingUsers as &$followed) {
                unset($followed['password']);
                // Check if logged in user follows this user
                $followed['is_following'] = $loggedInUserId && $followModel->where('follower_id', $loggedInUserId)->where('followed_id', $followed['id'])->first() ? true : false;
            }
        }
        
        return $this->respond($followingUsers);
    }

    public function updateProfile()
    {
        $userId = $this->request->user_id;
        $userModel = new UserModel();

        $rules = [
            'bio'         => 'permit_empty|string|max_length[500]',
            'profile_pic' => 'uploaded[profile_pic]|max_size[profile_pic,2048]|is_image[profile_pic]|permit_empty',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = [];
        if ($this->request->getVar('bio') !== null) {
            $data['bio'] = $this->request->getVar('bio');
        }

        $file = $this->request->getFile('profile_pic');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/profiles', $newName);
            $data['profile_pic'] = 'uploads/profiles/' . $newName;
        }

        if (empty($data)) {
            return $this->respond(['status' => 'success', 'message' => 'Nothing to update']);
        }

        if ($userModel->update($userId, $data)) {
            return $this->respond(['status' => 'success', 'message' => 'Profile updated', 'data' => $data]);
        }

        return $this->fail('Failed to update profile');
    }

    public function getStats($userId)
    {
        $followModel = new \App\Models\FollowModel();
        
        $followers = $followModel->where('followed_id', $userId)->countAllResults();
        $following = $followModel->where('follower_id', $userId)->countAllResults();

        return $this->respond([
            'status' => 'success',
            'user_id' => $userId,
            'followers' => $followers,
            'following' => $following
        ]);
    }

    public function suggested()
    {
        $loggedInUserId = session()->get('user_id');
        $userModel = new UserModel();
        $followModel = new \App\Models\FollowModel();
        
        // Get IDs of users already followed
        $following = $followModel->where('follower_id', $loggedInUserId)->findAll();
        $followedIds = array_column($following, 'followed_id');
        $followedIds[] = $loggedInUserId; // Don't suggest self
        
        $suggestedUsers = $userModel->whereNotIn('id', $followedIds)
                                     ->limit(5)
                                     ->findAll();
                                     
        foreach ($suggestedUsers as &$user) {
            unset($user['password']);
        }
        
        return $this->respond($suggestedUsers);
    }
}
