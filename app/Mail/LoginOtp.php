<?php
namespace App\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
class LoginOtp extends Mailable
{
    // Synchronous delivery only; never persist a queued plaintext code.
    public function __construct(public string $code) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Verify your HomeServices email address'); }
    public function content(): Content { return new Content(view: 'emails.login-otp', text: 'emails.login-otp-text'); }
}
