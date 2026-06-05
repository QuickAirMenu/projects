<?php

namespace App\Mail;

use App\Models\Quote;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "عرض سعر #{$this->quote->quote_number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-sent', with: ['quote' => $this->quote]);
    }

    public function attachments(): array
    {
        $settings = Setting::whereIn('key', ['company_name','company_name_ar','company_logo','company_email','company_phone','company_address','currency_symbol'])
            ->pluck('value', 'key')
            ->toArray();

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => Pdf::loadView('pdf.quote', ['quote' => $this->quote, 'settings' => $settings])->output(),
                "quote-{$this->quote->quote_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
