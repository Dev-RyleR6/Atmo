<?php

namespace App\Controllers;

use App\Models\FollowModel;
use App\Services\NotificationService;

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
            return redirect()->back()->with('success', 'Unfollowed User');
        } else {
            $followModel->skipValidation(true)->insert(['follower_id' => $followerId, 'followed_id' => $followedId]);
            
            NotificationService::notify($followedId, $followerId, 'follow');

            return redirect()->back()->with('success', 'Followed User');
        }
    }
}
