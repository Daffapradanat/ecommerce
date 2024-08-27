<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Order;
use App\Notifications\NewProductNotification;
use App\Notifications\OrderStatusChangedNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $buyer = Auth::user();

        $notifications = $buyer->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    // 'id' => $notification->id,
                    // 'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    // 'created_at' => $notification->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => __('notification.notification_marked_as_read')
        ]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => __('notification.all_notifications_marked_as_read')
        ]);
    }

    public function newProductNotifications()
    {
        $buyer = Auth::user();

        $newProductNotifications = $buyer->notifications()
            ->where('type', NewProductNotification::class)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    // 'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    // 'created_at' => $notification->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $newProductNotifications
        ]);
    }

    public function orderStatusChangedNotification(Order $order, $oldStatus)
    {
        $notification = new Notification([
            'type' => 'App\Notifications\OrderStatusChangedNotification',
            'data' => [
                'message' => __('notification.order_status_changed', [
                    'id' => $order->id,
                    'oldStatus' => $oldStatus,
                    'newStatus' => $order->status
                ]),
                'order_id' => $order->id,
            ]
        ]);

        Auth::user()->notifications()->save($notification);

        return response()->json([
            'success' => true,
            'message' => __('notification.order_status_change_notification_created')
        ]);
    }


    public function unreadNotifications()
    {
        $buyer = Auth::user();

        $unreadNotifications = $buyer->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($unreadNotifications->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => __('notification.no_unread_notifications'),
                'unread_notifications' => []
            ]);
        }

        $mappedNotifications = $unreadNotifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
            ];
        });

        return response()->json([
            'success' => true,
            'unread_notifications' => $mappedNotifications
        ]);
    }

    public function readNotifications()
    {
        $buyer = Auth::user();

        $readNotifications = $buyer->notifications()
            ->whereNotNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($readNotifications->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => __('notification.no_read_notifications'),
                'read_notifications' => []
            ]);
        }

        $mappedNotifications = $readNotifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
            ];
        });

        return response()->json([
            'success' => true,
            'read_notifications' => $mappedNotifications
        ]);
    }
}
