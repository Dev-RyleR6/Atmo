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
        if ($followerId == $followedId) {
            return $this->fail('You cannot follow yourself');
        }

        $followModel = new FollowModel();
        $existing = $followModel->where('follower_id', $followerId)->where('followed_id', $followedId)->first();

        if ($existing) {
            $followModel->where('follower_id', $followerId)->where('followed_id', $followedId)->delete();
            return $this->respond(['status' => 'success', 'message' => 'Unfollowed']);
        } else {
            $followModel->insert(['follower_id' => $followerId, 'followed_id' => $followedId]);
            
            // Send notification
            $notifModel = new NotificationModel();
            $notifModel->insert([
                'recipient_id' => $followedId,
                'sender_id' => $followerId,
                'type' => 'follow'
            ]);

            return $this->respond(['status' => 'success', 'message' => 'Followed']);
        }
    }

    public function toggleBlock($blockedId)
    {
        $blockerId = session()->get('user_id');
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
