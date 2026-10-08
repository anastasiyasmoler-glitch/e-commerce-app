<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderCancelledMail extends Mailable
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order cancelled',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-cancelled',
        );
    }
}
