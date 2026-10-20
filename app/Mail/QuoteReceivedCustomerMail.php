<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReceivedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote)
    {
        $this->quote->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Confirmación de Solicitud de Cotización #%s - Import Hns San Luis', $this->quote->reference_code),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote-received-customer',
            with: [
                'quote' => $this->quote,
            ],
        );
    }
}
