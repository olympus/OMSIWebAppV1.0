<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MonthlyCustomerList extends Mailable
{
    use Queueable, SerializesModels;

    public $regionname;
    public $excelname;
    public $excelpath;
    public $daterange_to;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($regionname, $excelname, $excelpath, $daterange_to)
    {
        if ($regionname == 'panindia') {
            $this->regionname = 'All India';
        }else{
            $this->regionname = $regionname;
        }
        $this->excelname = $excelname;
        $this->excelpath = storage_path('/exports/').$excelpath.'.xls';
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
        ->with(['html'=>'Dear All, <br><br>Please refer attached Olympus My Voice App Customer List as of '.$this->daterange_to . '.<br>Thank you very much. <br><br>Team My Voice App.'])
        ->from(env('MAIL_FROM_ADDRESS', 'no-reply@olympusmyvoice.com'),  env('MAIL_FROM_NAME', 'Olympus My Voice Admin'))
        ->subject("My Voice App Customer List (".ucfirst($this->regionname).") as of ".$this->daterange_to)
        ->attach($this->excelpath, [
            'as' => $this->excelname,
                'mime' => 'application/excel',
        ]);
    }
}
