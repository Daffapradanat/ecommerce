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
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
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
                'message' => "Order #{$order->id} status has been changed from {$oldStatus} to {$order->status}.",
                'order_id' => $order->id,
            ]
        ]);

        Auth::user()->notifications()->save($notification);

        return response()->json([
            'success' => true,
            'message' => 'Order status change notification created'
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
                'message' => 'No unread notifications',
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
                'message' => 'No read notifications',
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
