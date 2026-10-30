<?php

namespace App\Mail;

use App\Models\Quote;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mpdf\Mpdf;

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
        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo',
            'company_email', 'company_phone', 'company_address', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

        $currency = $settings['currency_symbol'] ?? 'ر.س';

        return new Content(view: 'emails.quote-sent', with: [
            'quote' => $this->quote,
            'currency' => $currency,
        ]);
    }

    public function attachments(): array
    {
        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo',
            'company_email', 'company_phone', 'company_address', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $html = view('pdf.quote', ['quote' => $this->quote, 'settings' => $settings])->render();
        $mpdf->WriteHTML($html);

        return [
            Attachment::fromData(
                fn () => $mpdf->Output("quote-{$this->quote->quote_number}.pdf", 'S'),
                "quote-{$this->quote->quote_number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
