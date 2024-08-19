<?php

namespace App\Notifications;

use App\Models\Order;
use App\Mail\NotificationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderCancelledNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast','mail'];
    }

    public function toMail($notifiable)
    {
        return (new NotificationEmail($this))
                ->to($notifiable->email);
    }
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //         ->subject('Order Cancelled')
    //         ->line('We regret to inform you that your order has been cancelled.')
    //         ->line('Order ID: ' . $this->order->order_id)
    //         ->line('Total Price: $' . number_format($this->order->total_price, 2))
    //         ->action('View Order', url('/orders/' . $this->order->id))
    //         ->line('Thank you for using our application!');
    // }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->order->buyer->name . ' order with ID ' . $this->order->order_id . ' has been cancelled.',
            'order_id' => $this->order->order_id,
            'total_price' => $this->order->total_price,
            'url' => '/orders/' . $this->order->order_id
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->order->buyer->name . ' order with ID ' . $this->order->order_id . ' has been cancelled.',
            'order_id' => $this->order->order_id,
            'total_price' => $this->order->total_price,
            'url' => '/orders/' . $this->order->order_id
        ]);
    }
}
