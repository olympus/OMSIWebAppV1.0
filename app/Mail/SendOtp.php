<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtp extends Mailable
{
    use Queueable, SerializesModels;
    public $customer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Olympus My Voice | OTP')
        ->view('emails.otp');
    }
}
