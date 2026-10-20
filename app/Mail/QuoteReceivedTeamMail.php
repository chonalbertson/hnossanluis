<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReceivedTeamMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote)
    {
        $this->quote->loadMissing(['items', 'customer']);
    }

    public function envelope(): Envelope
    {
        $company = $this->quote->company_name ?: $this->quote->contact_name;

        return new Envelope(
            subject: sprintf('[Nueva Cotización %s] %s (%d ítems)', $this->quote->reference_code, $company, $this->quote->items->count()),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-received-team',
            with: [
                'quote' => $this->quote,
            ],
        );
    }
}
