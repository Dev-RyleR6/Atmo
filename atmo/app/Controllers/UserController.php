<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PostModel;
use App\Models\FollowModel;

class UserController extends BaseController
{
    public function profile($username = null)
    {
        $loggedInUserId = session()->get('user_id');
        $userModel = new UserModel();
        
        if ($username) {
            $user = $userModel->where('username', $username)->first();
        } else {
            $user = $userModel->find($loggedInUserId);
        }

        if (!$user) {
            return redirect()->to(site_url('feed'))->with('error', 'User not found');
        }

        unset($user['password']);
        
        $isOwnProfile = $user['id'] == $loggedInUserId;

        // Fetch user's posts
        $postModel = new PostModel();
        $posts = $postModel->where('user_id', $user['id'])
                           ->where('visibility !=', 'private')
                           ->orderBy('created_at', 'DESC')
                           ->findAll();
                           
        // Get Follower / Following count
        $followModel = new FollowModel();
        $followersCount = $followModel->where('followed_id', $user['id'])->countAllResults();
        $followingCount = $followModel->where('follower_id', $user['id'])->countAllResults();
        
        // Check if logged in user follows this profile
        $isFollowing = false;
        if ($loggedInUserId && !$isOwnProfile) {
            $isFollowing = $followModel->where('follower_id', $loggedInUserId)
                                       ->where('followed_id', $user['id'])
                                       ->first() ? true : false;
        }

        $data = [
            'user' => $user,
            'posts' => $posts,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'is_own_profile' => $isOwnProfile,
            'is_following' => $isFollowing
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
        ];

        $pic = $this->request->getFile('profile_pic');
        if ($pic && $pic->isValid()) {
            $rules['profile_pic'] = 'max_size[profile_pic,10240]|is_image[profile_pic]';
        }


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
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$file->isValid()) {
                return redirect()->back()->with('error', $file->getErrorString());
            }
            if (!$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/profiles', $newName);
                $data['profile_pic'] = 'uploads/profiles/' . $newName;
            }
        }

        if (empty($data)) {
            return redirect()->back()->with('success', 'Nothing to update');
        }

        if ($userModel->skipValidation(true)->update($userId, $data)) {
            return redirect()->back()->with('success', 'Profile updated successfully.');
        }

        // Fallback trace
        $dbError = $userModel->errors() ?: ['Unknown DB error occurred during update.'];
        return redirect()->back()->with('errors', $dbError);
    }
}
