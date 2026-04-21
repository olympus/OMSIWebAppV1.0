<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class AssignRequest extends Mailable
{
    use Queueable, SerializesModels;
    public $servicerequest;
    public $customer;
    public $assign_request;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($servicerequest, $customer, $assign_request)
    {
        $this->servicerequest = $servicerequest;
        $this->customer = $customer;
        $this->assign_request = $assign_request;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->bcc(\Config('developer_email'))
        ->subject('Olympus My Voice | Assign New Request '.$this->servicerequest->id)
        ->view('emails.assign_request');
    }
}
