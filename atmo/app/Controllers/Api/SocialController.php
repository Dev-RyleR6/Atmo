<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\PostModel;
use App\Models\RepostModel;
use App\Services\NotificationService;
use CodeIgniter\API\ResponseTrait;

class SocialController extends BaseController
{
    use ResponseTrait;

    public function toggleLike($postId)
    {
        $userId = session()->get('user_id');
        $likeModel = new LikeModel();
        
        $existing = $likeModel->where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $likeModel->where('user_id', $userId)->where('post_id', $postId)->delete();
            return $this->respond(['status' => 'success', 'message' => 'Unliked']);
        } else {
            $likeModel->insert(['user_id' => $userId, 'post_id' => $postId]);
            
            // Send notification
            $postModel = new PostModel();
            $post = $postModel->find($postId);
            if ($post) {
                NotificationService::notify($post['user_id'], $userId, 'like', $postId);
            }
            
            return $this->respond(['status' => 'success', 'message' => 'Liked']);
        }
    }

    public function addComment($postId)
    {
        $userId = session()->get('user_id');
        $text = $this->request->getVar('comment_text');

        if (empty($text)) {
            return $this->fail('Comment text is required');
        }

        $commentModel = new CommentModel();
        $data = [
            'post_id' => $postId,
            'user_id' => $userId,
            'comment_text' => $text
        ];

        if ($commentModel->insert($data)) {
             // Send notification
            $postModel = new PostModel();
            $post = $postModel->find($postId);
            if ($post) {
                NotificationService::notify($post['user_id'], $userId, 'comment', $postId);
            }
            return $this->respondCreated(['status' => 'success', 'message' => 'Comment added']);
        }

        return $this->fail('Failed to add comment');
    }

    public function repost($postId)
    {
        $userId = session()->get('user_id');
        $repostText = $this->request->getVar('repost_text');

        $repostModel = new RepostModel();
        $data = [
            'user_id' => $userId,
            'post_id' => $postId,
            'repost_text' => $repostText
        ];

        if ($repostModel->insert($data)) {
            // Send notification
            $postModel = new PostModel();
            $post = $postModel->find($postId);
            if ($post) {
                NotificationService::notify($post['user_id'], $userId, 'repost', $postId);
            }
            return $this->respondCreated(['status' => 'success', 'message' => 'Reposted successfully']);
        }

        return $this->fail('Failed to repost');
    }
}
