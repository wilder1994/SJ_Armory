<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RevistaTemporaryAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $loginUrl,
        public string $loginEmail,
        public string $accessCode,
        public Carbon $expiresAt,
        public string $appName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Acceso temporal Revista armas — :app', ['app' => $this->appName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.revista-temporary-access',
        );
    }
}
