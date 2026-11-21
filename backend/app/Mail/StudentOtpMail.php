<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->code} is your CareerPathAI verification code",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-otp',
            with: ['code' => $this->code],
        );
    }
}
