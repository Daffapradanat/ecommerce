<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBuyer extends Notification
{
    use Queueable;

    protected $buyers;

    public function __construct($buyer)
    {
        $this->buyer = $buyer;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'A new buyer has successfully registered ' . $this->buyer->name,
            'url' => route('buyer.index'),
        ];
    }
}
