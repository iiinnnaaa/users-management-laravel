<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Verify your email').' — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-verification-otp',
        );
    }
}
