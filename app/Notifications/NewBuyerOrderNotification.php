<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\NotificationEmail;
use App\Models\Order;
use App\Models\Buyer;

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
        return ['database','mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->order->buyer->name . 'order has been placed successfully. ' . $this->order->order_id,
            'order_id' => $this->order->order_id,
            'total_price' => $this->order->total_price,
            'url' => '/orders/' . $this->order->order_id
        ];
    }
}
