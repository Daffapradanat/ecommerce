<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $pdf = Pdf::loadView('emails.invoice', ['order' => $this->order]);
        $pdfContent = $pdf->output();

        return $this->markdown('emails.new_order')
            ->subject('Pesanan Baru - ' . $this->order->order_id)
            ->with([
                'order' => $this->order,
            ])
            ->attach(
                $pdfContent,
                [
                    'as' => 'invoice-'.$this->order->order_id.'.pdf',
                    'mime' => 'application/pdf',
                ]
            );
    }
}
