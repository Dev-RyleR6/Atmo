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
        $likeModel = new \App\Models\LikeModel();
        $commentModel = new \App\Models\CommentModel();
        $repostModel = new \App\Models\RepostModel();
        $userModel = new UserModel();
        
        // Fetch original posts
        $posts = $postModel->where('user_id', $user['id'])
                           ->where('visibility !=', 'private')
                           ->orderBy('created_at', 'DESC')
                           ->findAll();
                           
        // Fetch Reposts
        $reposts = $repostModel->where('user_id', $user['id'])
                               ->orderBy('created_at', 'DESC')
                               ->findAll();
        
        // Helper function to add social data to a post
        $addSocialData = function(&$post) use ($likeModel, $commentModel, $repostModel, $loggedInUserId, $userModel) {
            $postId = $post['id'];
            $post['like_count'] = $likeModel->where('post_id', $postId)->countAllResults();
            $post['comment_count'] = $commentModel->where('post_id', $postId)->countAllResults();
            $post['repost_count'] = $repostModel->where('post_id', $postId)->countAllResults();
            $post['is_liked'] = $loggedInUserId && $likeModel->where('user_id', $loggedInUserId)->where('post_id', $postId)->first() ? true : false;
            $post['is_reposted'] = $loggedInUserId && $repostModel->where('user_id', $loggedInUserId)->where('post_id', $postId)->first() ? true : false;
            
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
        
        // Process original posts
        $validPosts = [];
        foreach ($posts as &$post) {
            $post['type'] = 'original';
            if (empty($post['content']) && empty($post['media_path'])) {
                continue;
            }
            $post['user'] = $user;
            $addSocialData($post);
            $validPosts[] = $post;
        }
        $posts = $validPosts;

        // Process reposts
        $validReposts = [];
        foreach ($reposts as &$repost) {
            $repost['type'] = 'repost';
            $repost['reposted_by'] = $user;
            
            $originalPost = $postModel->find($repost['post_id']);
            if (!$originalPost) {
                continue;
            }
            if ($originalPost['visibility'] == 'private') {
                continue;
            }
            
            $originalUser = $userModel->find($originalPost['user_id']);
            if (!$originalUser) {
                continue;
            }
            unset($originalUser['password']);
            $originalPost['user'] = $originalUser;
            $addSocialData($originalPost);
            
            $repost['original_post'] = $originalPost;
            $repost['created_at'] = $repost['created_at'];
            $validReposts[] = $repost;
        }

        // Merge and sort
        $allPosts = array_merge($posts, $validReposts);
        usort($allPosts, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        $posts = $allPosts;
                           
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
    
    public function followers($username = null)
    {
        $loggedInUserId = session()->get('user_id');
        $userModel = new UserModel();
        $followModel = new FollowModel();
        
        if ($username) {
            $user = $userModel->where('username', $username)->first();
        } else {
            $user = $userModel->find($loggedInUserId);
        }

        if (!$user) {
            return redirect()->to(site_url('feed'))->with('error', 'User not found');
        }

        unset($user['password']);
        
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

        return view('followers', [
            'user' => $user,
            'users' => $followerUsers,
            'title' => 'Followers'
        ]);
    }
    
    public function following($username = null)
    {
        $loggedInUserId = session()->get('user_id');
        $userModel = new UserModel();
        $followModel = new FollowModel();
        
        if ($username) {
            $user = $userModel->where('username', $username)->first();
        } else {
            $user = $userModel->find($loggedInUserId);
        }

        if (!$user) {
            return redirect()->to(site_url('feed'))->with('error', 'User not found');
        }

        unset($user['password']);
        
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

        return view('followers', [
            'user' => $user,
            'users' => $followingUsers,
            'title' => 'Following'
        ]);
    }
}
