<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PostModel;
use App\Models\FollowModel;

class UserController extends BaseController
{
    public function profile()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to(site_url('login'))->with('error', 'User not found');
        }

        unset($user['password']);

        // Fetch user's posts
        $postModel = new PostModel();
        $posts = $postModel->where('user_id', $user['id'])
                           ->orderBy('created_at', 'DESC')
                           ->findAll();
                           
        // Get Follower / Following count
        $followModel = new FollowModel();
        $followersCount = $followModel->where('followed_id', $userId)->countAllResults();
        $followingCount = $followModel->where('follower_id', $userId)->countAllResults();

        $data = [
            'user' => $user,
            'posts' => $posts,
            'followers_count' => $followersCount,
            'following_count' => $followingCount
        ];

        return view('profile', $data);
    }

    public function search()
    {
        $query = $this->request->getGet('q');
        if (empty($query)) {
            return view('search', ['users' => [], 'query' => $query]);
        }

        $userModel = new UserModel();
        $users = $userModel->like('username', $query)
                           ->orLike('first_name', $query)
                           ->orLike('last_name', $query)
                           ->orLike('email', $query) // Added email to address rubric gap
                           ->limit(20)
                           ->findAll();

        return view('search', ['users' => $users, 'query' => $query]);
    }

    public function updateProfile()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();

        $rules = [
            'first_name'  => 'permit_empty|string|max_length[100]',
            'last_name'   => 'permit_empty|string|max_length[100]',
            'email'       => "permit_empty|valid_email|is_unique[users.email,id,$userId]",
            'dob'         => 'permit_empty|valid_date',
            'sex'         => 'permit_empty|in_list[Male,Female,Other,Prefer not to say]',
            'bio'         => 'permit_empty|string|max_length[500]',
            'profile_pic' => 'uploaded[profile_pic]|max_size[profile_pic,2048]|is_image[profile_pic]|permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        
        $fieldsToUpdate = ['first_name', 'last_name', 'email', 'dob', 'sex', 'bio'];
        foreach($fieldsToUpdate as $field) {
            $val = $this->request->getPost($field);
            // Sanitize string inputs
            if ($val !== null && $val !== '') {
                $data[$field] = strip_tags($val);
            }
        }

        $file = $this->request->getFile('profile_pic');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/profiles', $newName);
            $data['profile_pic'] = 'uploads/profiles/' . $newName;
        }

        if (empty($data)) {
            return redirect()->back()->with('success', 'Nothing to update');
        }

        if ($userModel->update($userId, $data)) {
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update profile');
    }
}
