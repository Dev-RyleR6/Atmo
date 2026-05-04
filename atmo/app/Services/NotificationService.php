<?php

namespace App\Services;

use App\Models\NotificationModel;

class NotificationService
{
    /**
     * Create a notification.
     *
     * @param int $recipientId
     * @param int $senderId
     * @param string $type ('like', 'comment', 'follow', 'repost')
     * @param int|null $postId
     * @return bool
     */
    public static function notify(int $recipientId, int $senderId, string $type, ?int $postId = null): bool
    {
        // Don't notify yourself
        if ($recipientId === $senderId) {
            return false;
        }

        $model = new NotificationModel();
        return (bool) $model->insert([
            'recipient_id' => $recipientId,
            'sender_id'    => $senderId,
            'type'         => $type,
            'post_id'      => $postId,
            'is_read'      => 0
        ]);
    }
}
