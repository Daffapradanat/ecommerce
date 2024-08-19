<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Mail\NotificationEmail;
use App\Models\Product;

class NewProductNotification extends Notification
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_price' => $this->product->price,
            'message' => 'New Product has arrived: ' . $this->product->name,
        ];
    }
}
