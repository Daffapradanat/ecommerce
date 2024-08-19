<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Order;
use App\Mail\NotificationEmail;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $oldStatus;

    public function __construct(Order $order, $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
    }

    public function via($notifiable)
    {
        return ['database','mail'];
    }

    public function toMail($notifiable)
    {
        return (new NotificationEmail($this))
                ->to($notifiable->email)
                ->with([
                    'notification' => $this,
                    'data' => $this->toArray($notifiable)
                ]);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->getMessage(),
            'order_id' => $this->order->order_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->order->payment_status,
            'url' => route('orders.show', $this->order->id),
        ];
    }

    protected function getMessage()
    {
        return "Order {$this->order->order_id} has changed status.";
    }

    // protected function getMessage()
    // {
    //     switch($this->order->payment_status) {
    //         case 'cancelled':
    //             return "Order {$this->order->order_id} has been cancelled.";
    //         case 'awaiting_payment':
    //             return "Order {$this->order->order_id} is now awaiting payment.";
    //         case 'pending':
    //             return "Order {$this->order->order_id} is pending.";
    //         case 'failed':
    //             return "Payment for Order {$this->order->order_id} has failed.";
    //         case 'paid':
    //             return "Payment for Order {$this->order->order_id} has been successful.";
    //         default:
    //             return "Order {$this->order->order_id} status has changed to {$this->order->payment_status}.";
    //     }
    // }
}
