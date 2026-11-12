<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerificationCodeMail extends Mailable
{
    public function __construct(public User $user, public string $code, public int $minutes = 10) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify Your Email - Equipment Borrowing System');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verification-code', text: 'emails.verification-code-text');
    }
}
