<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteProposalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public ?string $pdfBinary = null
    ) {
        $this->quote->loadMissing(['items', 'assignedSeller']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Propuesta Comercial #%s - Import Hns San Luis', $this->quote->reference_code),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-proposal',
            with: [
                'quote' => $this->quote,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->pdfBinary) {
            return [];
        }

        return [
            Attachment::fromData(
                fn () => $this->pdfBinary,
                sprintf('Cotizacion-%s.pdf', $this->quote->reference_code)
            )->withMime('application/pdf'),
        ];
    }
}
