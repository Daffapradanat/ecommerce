<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

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
}
