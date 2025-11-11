<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MisPANIndia extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfname;
    public $pdfpath;
    public $daterange;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pdfname, $pdfpath, $daterange)
    {
        $this->pdfname = $pdfname;
        $this->pdfpath = $pdfpath;
        $this->daterange = $daterange;
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
        ->view('mismail')
        ->from(env('MAIL_FROM_ADDRESS', 'no-reply@olympusmyvoice.com'),  env('MAIL_FROM_NAME', 'Olympus My Voice Admin'))
        ->subject("My Voice App Monthly Report (All India)_15 Aug 2018".' - '.date('d M Y',strtotime('last day of last month', time())))
        ->attach($this->pdfpath, [
            'as' => $this->pdfname,
            'mime' => 'application/pdf',
        ]);
    }
}
