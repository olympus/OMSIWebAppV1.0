<?php

namespace App\Mail\Revamp;

use App\Models\RoiCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoiCalculatorInternalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RoiCalculator $roi)
    {
    }

    public function build()
    {
        return $this
            ->bcc('ritik.bansal@lyxelandflamingo.com')
            ->subject('Olympus India | ROI Calculator enquiry received')
            ->view('emails.revamp.roi_calculator_internal');
    }
}
