<?php

namespace App\Mail;

use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Installment $installment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment received · ' . $this->installment->booking->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.installment-paid',
            with: [
                'installment' => $this->installment,
                'booking' => $this->installment->booking,
                'loginUrl' => route('login'),
            ],
        );
    }
}
