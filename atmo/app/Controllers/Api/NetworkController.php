<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\FollowModel;
use App\Models\BlockModel;
use App\Models\NotificationModel;
use CodeIgniter\API\ResponseTrait;

class NetworkController extends BaseController
{
    use ResponseTrait;

    public function toggleFollow($followedId)
    {
        $followerId = session()->get('user_id');
        
        if (!$followerId) {
            return $this->fail('Not authenticated', 401);
        }
        
        if ($followerId == $followedId) {
            return $this->fail('You cannot follow yourself', 400);
        }

        $followModel = new FollowModel();
        $userModel = new \App\Models\UserModel();
        
        // Verify user exists
        $user = $userModel->find($followedId);
        if (!$user) {
            return $this->fail('User not found', 404);
        }
        
        $existing = $followModel->where('follower_id', $followerId)->where('followed_id', $followedId)->first();

        if ($existing) {
            $followModel->where('follower_id', $followerId)->where('followed_id', $followedId)->delete();
            $followersCount = $followModel->where('followed_id', $followedId)->countAllResults();
            return $this->respond([
                'status' => 'success', 
                'message' => 'Unfollowed',
                'is_following' => false,
                'followers_count' => $followersCount
            ]);
        } else {
            $followModel->skipValidation(true)->insert(['follower_id' => $followerId, 'followed_id' => $followedId]);
            
            // Send notification
            $notifModel = new NotificationModel();
            $notifModel->skipValidation(true)->insert([
                'recipient_id' => $followedId,
                'sender_id' => $followerId,
                'type' => 'follow'
            ]);

            $followersCount = $followModel->where('followed_id', $followedId)->countAllResults();
            return $this->respond([
                'status' => 'success', 
                'message' => 'Followed',
                'is_following' => true,
                'followers_count' => $followersCount
            ]);
        }
    }

    public function toggleBlock($blockedId)
    {
        $blockerId = $this->request->user_id;
        if ($blockerId == $blockedId) {
            return $this->fail('You cannot block yourself');
        }

        $blockModel = new BlockModel();
        $existing = $blockModel->where('blocker_id', $blockerId)->where('blocked_id', $blockedId)->first();

        if ($existing) {
            $blockModel->where('blocker_id', $blockerId)->where('blocked_id', $blockedId)->delete();
            return $this->respond(['status' => 'success', 'message' => 'Unblocked']);
        } else {
            $blockModel->insert(['blocker_id' => $blockerId, 'blocked_id' => $blockedId]);
            
            // If they were following, unfollow them
            $followModel = new FollowModel();
            $followModel->where('follower_id', $blockerId)->where('followed_id', $blockedId)->delete();
            $followModel->where('follower_id', $blockedId)->where('followed_id', $blockerId)->delete();

            return $this->respond(['status' => 'success', 'message' => 'Blocked']);
        }
    }
}
