<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class RequestUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $servicerequest;
    public $customer;
    public $pathToImage;
    public $request_id;
    public $oldData_employee_code;



    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request_id, $oldData_employee_code, $servicerequest, $customer)
    {
        $this->servicerequest = $servicerequest;
        $this->customer = $customer;
        $this->request_id = $request_id;
        $this->oldData_employee_code = $oldData_employee_code;
        //$this->pathToImage = $pathToImage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        $file = storage_path('exports/ServiceRequests-' . $this->servicerequest->id . '.xls');

        $mail = $this->bcc(config('oly.developer_email'))
            ->view('emails.request_updated_new', [
                'id' => $this->request_id,
                'oldData_employee_code' => $this->oldData_employee_code,
            ])
            ->subject('Olympus My Voice | ' . ucfirst($this->servicerequest->request_type) . 
                      ' | ' . $this->servicerequest->cvm_id . 
                      ' | *' . $this->servicerequest->status . '*');

        if (file_exists($file)) {
            $mail->attach($file, [
                'as' => 'Request-' . $this->servicerequest->id . '.xls',
                'mime' => 'application/vnd.ms-excel',
            ]);
        }

        return $mail;

        /*
            file_get_contents(asset('/exports/'.$this->servicerequest->id));
            return $this
            ->bcc(\Config('oly.developer_email'))
            ->view('emails.request_updated_new', ['id'=> $this->request_id,'oldData_employee_code' => $this->oldData_employee_code])
            ->subject('Olympus My Voice | '.ucfirst($this->servicerequest->request_type).' | '.$this->servicerequest->cvm_id . ' | *'.$this->servicerequest->status.'*')
            ->attach(storage_path().'/exports/ServiceRequests-'.$this->servicerequest->id.'.xls', [
                'as' => 'Request-'.$this->servicerequest->id.'.xls',
                'mime' => 'application/vnd.ms-excel',
            ]);
        */
    }
}
