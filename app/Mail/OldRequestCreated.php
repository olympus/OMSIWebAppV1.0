<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class OldRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $pathToImage;
    public $servicerequest;
    public $customer;
    public $assign_request;
    public $request_id;



    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request_id, $servicerequest, $customer, $assign_request)
    {
        $this->servicerequest = $servicerequest;
        $this->customer = $customer;
        $this->assign_request = $assign_request;
        $this->request_id = $request_id;

        //$this->pathToImage = config('app.url')."/serviceImages/".$servicerequest->id.".jpg";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        file_get_contents(asset('/exports/'.$this->servicerequest->id));
        return $this
        ->bcc(\Config('oly.developer_email'))
        ->subject('Olympus My Voice | '.ucfirst($this->servicerequest->request_type).' | '.$this->servicerequest->cvm_id)
        //->view('emails.img_return')
        ->view('emails.request_created_new', ['id'=> $this->request_id])
        ->attach(storage_path().'/exports/ServiceRequests-'.$this->servicerequest->id.'.xls', [
            'as' => 'Request-'.$this->servicerequest->id.'.xls',
            'mime' => 'application/vnd.ms-excel',
        ]);;
    }
}
