<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyEscalationReport extends Mailable
{
    use Queueable, SerializesModels;
    public $excelname;
    public $excelpath;
    public $daterange_from;
    public $daterange_to;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($excelname, $excelpath, $daterange_from, $daterange_to)
    {
        $this->excelname = $excelname;
        $this->excelpath = storage_path('/exports/').$excelpath.'.xls';
        $this->daterange_from = $daterange_from;
        $this->daterange_to = $daterange_to;
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
        ->view('emails.html_return')
        ->with(['html'=>'Dear All, <br><br>Please refer attached Escalation Weekly Report for last week. <br>Thank you very much. <br><br>Team My Voice App.'])
        ->from(env('MAIL_FROM_ADDRESS', 'no-reply@olympusmyvoice.com'),  env('MAIL_FROM_NAME', 'Olympus My Voice Admin'))
        ->subject("Escalation Weekly Report ".$this->daterange_from.' - '.$this->daterange_to)
        ->attach($this->excelpath, [
            'as' => $this->excelname,
            'mime' => 'application/excel',
        ]);
    }
}
