<?php

namespace App\Controllers;

use App\Models\FollowModel;
use App\Models\NotificationModel;

class NetworkController extends BaseController
{
    public function toggleFollow($followedId)
    {
        $followerId = session()->get('user_id');
        if ($followerId == $followedId) {
            return redirect()->back()->with('error', 'You cannot follow yourself');
        }

        $followModel = new FollowModel();
        $existing = $followModel->where('follower_id', $followerId)->where('followed_id', $followedId)->first();

        if ($existing) {
            $followModel->where('follower_id', $followerId)->where('followed_id', $followedId)->delete();
            return redirect()->back()->with('success', 'Unfollowed');
        } else {
            $followModel->insert(['follower_id' => $followerId, 'followed_id' => $followedId]);
            
            // Send notification
            $notifModel = new NotificationModel();
            $notifModel->insert([
                'recipient_id' => $followedId,
                'sender_id' => $followerId,
                'type' => 'follow'
            ]);

            return redirect()->back()->with('success', 'Followed');
        }
    }
}
