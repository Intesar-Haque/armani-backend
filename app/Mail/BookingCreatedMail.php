<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public ?string $plainPassword = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your booking is confirmed · ' . $this->booking->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-created',
            with: [
                'booking' => $this->booking,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => route('login'),
            ],
        );
    }
}
