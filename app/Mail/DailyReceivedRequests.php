<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailyReceivedRequests extends Mailable
{
    use Queueable, SerializesModels;
    public $excelname;
    public $excelpath;
    public $date_today;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($excelname, $excelpath, $date_today)
    {
        $this->excelname = $excelname;
        $this->excelpath = storage_path('exports/').$excelpath.'.xls';
        $this->date_today = $date_today;
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
        ->with(['html'=>'Dear All, <br><br>Please refer attached Olympus My Voice App Pending Requests Summary as of '.date('d M Y',strtotime('today', time())) . '.<br>Thank you very much. <br><br>Team My Voice App.'])
        ->from(env('MAIL_FROM_ADDRESS', 'no-reply@olympusmyvoice.com'),  env('MAIL_FROM_NAME', 'Olympus My Voice Admin'))
        ->subject("Pending Requests Summary (".date('d M Y',strtotime('today', time())) .")" )
        ->attach($this->excelpath, [
            'as' => $this->excelname,
            'mime' => 'application/excel',
        ]);
    }
}
