<?php

namespace App\Mail;

use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InstallmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Installment $installment) {}

    public function envelope(): Envelope
    {
        $due = $this->installment->due_date->format('d M Y');

        return new Envelope(
            subject: "Payment reminder · {$this->installment->label} due {$due} · " . $this->installment->booking->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.installment-reminder',
            with: [
                'installment' => $this->installment,
                'booking' => $this->installment->booking,
                'loginUrl' => route('login'),
            ],
        );
    }
}
