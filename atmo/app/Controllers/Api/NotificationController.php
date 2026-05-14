<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class NotificationController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $userId = session()->get('user_id');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 20;
        
        $notificationModel = new NotificationModel();
        $userModel = new UserModel();

        $notifications = $notificationModel->where('recipient_id', $userId)
                                             ->orderBy('created_at', 'DESC')
                                             ->paginate($perPage, 'default', $page);

        foreach ($notifications as &$notification) {
            $sender = $userModel->find($notification['sender_id']);
            if ($sender) {
                unset($sender['password']);
                $notification['sender'] = $sender;
            }
        }

        $pager = $notificationModel->pager;
        
        return $this->respond([
            'notifications' => $notifications,
            'currentPage' => $pager->getCurrentPage(),
            'totalPages' => $pager->getPageCount(),
            'total' => $pager->getTotal()
        ]);
    }

    public function unreadCount()
    {
        $userId = session()->get('user_id');
        $notificationModel = new NotificationModel();

        $count = $notificationModel->where('recipient_id', $userId)
                                    ->where('is_read', 0)
                                    ->countAllResults();

        return $this->respond(['count' => $count]);
    }

    public function markAsRead($id = null)
    {
        $userId = session()->get('user_id');
        $notificationModel = new NotificationModel();

        if ($id) {
            $notification = $notificationModel->where('id', $id)
                                              ->where('recipient_id', $userId)
                                              ->first();
            if ($notification) {
                $notificationModel->update($id, ['is_read' => 1]);
            }
        } else {
            $notificationModel->where('recipient_id', $userId)
                              ->set('is_read', 1)
                              ->update();
        }

        return $this->respond(['status' => 'success']);
    }
}
