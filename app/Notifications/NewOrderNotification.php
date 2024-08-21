<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\NewOrderMail;
use App\Models\Order;
use App\Models\Buyer;

class NewOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new NewOrderMail($this->order))
                ->to($notifiable->email);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->order->buyer->name . ' order has been placed successfully. ' . $this->order->order_id,
            'order_id' => $this->order->order_id,
            'buyer_name' => $this->order->buyer->name,
            'total_price' => $this->order->total_price,
            'url' => route('order.show', ['order' => $this->order->order_id])
            // 'url' => '/orders/' . $this->order->order_id
        ];
    }
}
