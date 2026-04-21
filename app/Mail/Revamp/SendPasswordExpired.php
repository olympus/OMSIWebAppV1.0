<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPasswordExpired extends Mailable
{
    use Queueable, SerializesModels;
    public $customer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer,$type)
    {
        if($type == 1){
            $this->customer = $customer->first_name;
        }else{
            $this->customer = $customer->name;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Olympus My Voice | Password Expired')
        ->view('emails.send_password_expired');
    }
}
