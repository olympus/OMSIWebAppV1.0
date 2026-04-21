<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestEscalated extends Mailable
{
    use Queueable, SerializesModels;

    public $servicerequest;
    public $customer;
    public $pathToImage;
    public $request_id;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request_id, $servicerequest, $customer)
    {
        $this->servicerequest = $servicerequest;
        $this->customer = $customer;
        $this->request_id = $request_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->bcc(\Config('oly.developer_email'))
        //->view('emails.img_return')
        ->view('emails.request_escalated_new', ['id'=> $this->request_id])
        ->subject('Olympus My Voice | Escalation ('.ucfirst($this->servicerequest->request_type).') | '.$this->servicerequest->cvm_id);
    }
}
