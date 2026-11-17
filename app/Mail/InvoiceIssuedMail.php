<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mpdf\Mpdf;

class InvoiceIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('messages.invoice_issued_subject', ['number' => $this->invoice->invoice_number]));
    }

    public function content(): Content
    {
        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo',
            'company_email', 'company_phone', 'company_address', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

        return new Content(view: 'emails.invoice-issued', with: [
            'invoice' => $this->invoice,
            'currency' => $settings['currency_symbol'] ?? 'ر.س',
            'companyName' => $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name'),
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
        $mpdf->WriteHTML(view('pdf.invoice', ['invoice' => $this->invoice->loadMissing('client', 'items'), 'settings' => $settings])->render());

        return [
            Attachment::fromData(
                fn () => $mpdf->Output('invoice-'.$this->invoice->invoice_number.'.pdf', 'S'),
                'invoice-'.$this->invoice->invoice_number.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
