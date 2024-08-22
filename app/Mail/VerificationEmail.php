<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Buyer;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $buyer;

    public function __construct(Buyer $buyer)
    {
        $this->buyer = $buyer;
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->view('emails.verify-email');
    }
}
