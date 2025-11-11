<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArchiveCustomerDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fileName;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function build()
    {
        return $this->view('emails.archive_customer_data')
                    ->subject('Archive Customer Data')
                    ->attach(storage_path('app/public/' . $this->fileName));
    }
}
