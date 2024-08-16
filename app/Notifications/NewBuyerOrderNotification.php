<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewBuyerOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your order has been placed successfully. ' . $this->order->order_id,
            'order_id' => $this->order->order_id,
            'total_price' => $this->order->total_price,
            'url' => '/orders/' . $this->order->order_id
        ];
    }
}
