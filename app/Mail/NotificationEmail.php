<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    // public $notification;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    // public function __construct($notification)
    // {
    //     $this->notification = $notification;
    // }

    public function build()
    {
        $pdf = PDF::loadView('emails.invoice', ['order' => $this->order]);

        return $this->markdown('emails.new_order')
                    ->subject('Pesanan Baru - ' . $this->order->order_id)
                    ->with([
                        'order' => $this->order,
                    ])
                    ->attachData($pdf->output(), 'invoice-'.$this->order->order_id.'.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }

    // public function build()
    // {
    //     $subject = $this->getSubject();
    //     return $this->view('emails.notification')
    //                 ->subject($subject)
    //                 ->with([
    //                     'subject' => $subject,
    //                     'notification' => $this->notification
    //                 ]);
    // }

    // private function getSubject()
    // {
    //     $notificationType = class_basename($this->notification);
    //     switch ($notificationType) {
    //         case 'NewProductNotification':
    //             return 'New Product Arrival';
    //         case 'OrderCancelledNotification':
    //             return 'Order Cancelled';
    //         case 'OrderStatusChangedNotification':
    //             return 'Order Status Updated';
    //         case 'NewBuyerOrderNotification':
    //         case 'NewOrderNotification':
    //             return 'New Order Placed';
    //         case 'ImportedProductsNotification':
    //             return 'Products Imported Successfully';
    //         case 'NewBuyer':
    //             return 'New Buyer Registration';
    //         default:
    //             return 'New Notification';
    //     }
    // }
}
