<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class FeedbackCreated extends Mailable
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

        //$this->pathToImage = config('app.url')."/serviceImages/".$servicerequest->id.".jpg";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    // public function build()
    // {
    //     file_get_contents(asset('/exports/'.$this->servicerequest->id));
    //     return $this
    //     ->bcc(\Config('oly.developer_email'))
    //     //->view('emails.img_return')
    //     ->view('emails.feedback_recvd_new', ['id'=> $this->request_id])
    //     ->subject('Olympus My Voice | '.ucfirst($this->servicerequest->request_type).' | '.$this->servicerequest->cvm_id.' | *Feedback Received*')
    //     ->attach(storage_path().'/exports/ServiceRequests-'.$this->servicerequest->id.'.xls', [
    //         'as' => 'ServiceRequest-'.$this->servicerequest->id.'.xls',
    //         'mime' => 'application/vnd.ms-excel',
    //     ]);
    // }

    public function build()
    {
        $fileName = 'ServiceRequests-' . (int)$this->servicerequest->id . '.xls';
        $filePath = storage_path('exports/' . $fileName);

        if (!file_exists($filePath)) {
            throw new \Exception('Attachment file not found');
        }

        return $this
            ->bcc(config('oly.developer_email'))
            ->view('emails.feedback_recvd_new', [
                'id' => $this->request_id
            ])
            ->subject(
                'Olympus My Voice | ' .
                ucfirst($this->servicerequest->request_type) .
                ' | ' . $this->servicerequest->cvm_id .
                ' | *Feedback Received*'
            )
            ->attach($filePath, [
                'as'   => 'ServiceRequest-' . $this->servicerequest->id . '.xls',
                'mime' => 'application/vnd.ms-excel',
            ]);
    }
}
