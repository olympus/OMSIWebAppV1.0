<?php

namespace App\Mail\Revamp;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SendKYCVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'KYC Verification Successful'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.revamp.kyc_verified',
            with: [
                'name' => $this->customer->first_name,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
