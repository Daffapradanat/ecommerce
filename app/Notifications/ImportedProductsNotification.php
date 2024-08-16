<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportedProductsNotification extends Notification
{
    use Queueable;

    protected $importCount;

    public function __construct($importCount)
    {
        $this->importCount = $importCount;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->importCount . ' new products have been imported.',
        ];
    }
}
