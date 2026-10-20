<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientEmail,
        public ?string $triggeredByName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prueba de Configuración de Correo Corporativo - Import Hns San Luis',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.smtp-test',
            with: [
                'recipientEmail' => $this->recipientEmail,
                'triggeredByName' => $this->triggeredByName,
                'testedAt' => now()->format('d/m/Y H:i:s'),
            ],
        );
    }
}
