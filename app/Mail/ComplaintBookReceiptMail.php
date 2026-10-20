<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComplaintBookReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $record,
        public string $sheetCode,
        public string $claimType,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Constancia de Hoja de Reclamación Virtual %s - IMPORT HNOS SAN LUIS S.A.C.', $this->sheetCode),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.complaint-book-receipt',
            with: [
                'record' => $this->record,
                'sheetCode' => $this->sheetCode,
                'claimType' => $this->claimType,
            ],
        );
    }
}
