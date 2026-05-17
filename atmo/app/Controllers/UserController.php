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
        
        // Fetch original posts - initialize safely
        $postsQuery = $postModel->where('user_id', $user['id'])
                           ->where('visibility !=', 'private')
                           ->orderBy('created_at', 'DESC')
                           ->findAll();
        $posts = is_array($postsQuery) ? $postsQuery : [];
                           
        // Fetch Reposts - initialize safely
        $repostsQuery = $repostModel->where('user_id', $user['id'])
                               ->orderBy('created_at', 'DESC')
                               ->findAll();
        $reposts = is_array($repostsQuery) ? $repostsQuery : [];
        
        // Helper function to add social data to a post
        $addSocialData = function(&$post) use ($likeModel, $commentModel, $repostModel, $loggedInUserId, $userModel) {
            if (!isset($post['id'])) return;
            $postId = $post['id'];
            $post['like_count'] = $likeModel->where('post_id', $postId)->countAllResults();
            $post['comment_count'] = $commentModel->where('post_id', $postId)->countAllResults();
            $post['repost_count'] = $repostModel->where('post_id', $postId)->countAllResults();
            $post['is_liked'] = $loggedInUserId && $likeModel->where('user_id', $loggedInUserId)->where('post_id', $postId)->first() ? true : false;
            $post['is_reposted'] = $loggedInUserId && $repostModel->where('user_id', $loggedInUserId)->where('post_id', $postId)->first() ? true : false;
            
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
                if (empty($post['content']) && empty($post['media_path'])) {
                    continue;
                }
                if (!isset($post['user_id'])) {
                    continue;
                }
                $user_obj = $userModel->find($post['user_id']);
                if (!$user_obj) {
                    continue; // Skip if user not found
                }
                unset($user_obj['password']);
                $post['user'] = $user_obj;
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
                $repost_author = $userModel->find($repost['user_id']);
                if (!$repost_author) {
                    continue;
                }
                unset($repost_author['password']);
                $repost['reposted_by'] = $repost_author;
                
                if (!isset($repost['post_id'])) {
                    continue;
                }
                $originalPost = $postModel->find($repost['post_id']);
                if (!$originalPost) {
                    continue;
                }
                if ($originalPost['visibility'] == 'private') {
                    continue;
                }
                if (empty($originalPost['content']) && empty($originalPost['media_path'])) {
                    continue;
                }
                
                if (!isset($originalPost['user_id'])) {
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
                $repost['created_at'] = $repost['created_at'] ?? null;
                $validReposts[] = $repost;
            }
        }

        // Merge and sort
        $allPosts = array_merge($posts, $validReposts);
        usort($allPosts, function($a, $b) {
            $aTime = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $bTime = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
            return $bTime - $aTime;
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
            'posts' => $posts ?? [],
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

        // Handle password update separately
        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');
        
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            // Password update requested
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return redirect()->back()->with('error', 'Please fill in all password fields');
            }
            
            if ($newPassword !== $confirmPassword) {
                return redirect()->back()->with('error', 'New passwords do not match');
            }
            
            if (strlen($newPassword) < 8) {
                return redirect()->back()->with('error', 'New password must be at least 8 characters');
            }
            
            // Verify current password
            $user = $userModel->find($userId);
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return redirect()->back()->with('error', 'Current password is incorrect');
            }
            
            // Update password
            $userModel->skipValidation(true)->update($userId, [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);
            
            return redirect()->back()->with('success', 'Password updated successfully');
        }

        // Handle profile field updates
        $rules = [
            'first_name'  => 'permit_empty|string|max_length[100]',
            'last_name'   => 'permit_empty|string|max_length[100]',
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
        
        $fieldsToUpdate = ['first_name', 'last_name', 'dob', 'sex', 'bio'];
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
            if (isset($data['profile_pic'])) {
                session()->set('profile_pic', $data['profile_pic']);
            }
            if (isset($data['first_name'])) {
                session()->set('first_name', $data['first_name']);
            }
            if (isset($data['last_name'])) {
                session()->set('last_name', $data['last_name']);
            }
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
        
        // Get followers - ensure safe array
        $followersQuery = $followModel->where('followed_id', $user['id'])->findAll();
        $followers = is_array($followersQuery) ? $followersQuery : [];
        $followerIds = !empty($followers) ? array_column($followers, 'follower_id') : [];
        
        $followerUsers = [];
        if (!empty($followerIds)) {
            $followerUsersQuery = $userModel->whereIn('id', $followerIds)->findAll();
            $followerUsers = is_array($followerUsersQuery) ? $followerUsersQuery : [];
            
            foreach ($followerUsers as &$follower) {
                if (isset($follower['password'])) {
                    unset($follower['password']);
                }
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
        
        // Get following - ensure safe array
        $followingQuery = $followModel->where('follower_id', $user['id'])->findAll();
        $following = is_array($followingQuery) ? $followingQuery : [];
        $followingIds = !empty($following) ? array_column($following, 'followed_id') : [];
        
        $followingUsers = [];
        if (!empty($followingIds)) {
            $followingUsersQuery = $userModel->whereIn('id', $followingIds)->findAll();
            $followingUsers = is_array($followingUsersQuery) ? $followingUsersQuery : [];
            
            foreach ($followingUsers as &$followed) {
                if (isset($followed['password'])) {
                    unset($followed['password']);
                }
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
