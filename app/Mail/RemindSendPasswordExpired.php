<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RemindSendPasswordExpired extends Mailable
{
    use Queueable, SerializesModels;
    public $customer;
    public $day;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer,$type, $day)
    {
        if($type == 1){
            $this->customer = $customer->first_name;
            $this->day = $day;
        }else{
            $this->customer = $customer->name;
        }
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
        ->subject('Olympus My Voice | Password Expired Reminder')
        ->view('emails.reminder_send_password_expired');
    }
}
