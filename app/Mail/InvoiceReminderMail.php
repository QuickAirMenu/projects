<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.invoice_reminder_subject', ['number' => $this->invoice->invoice_number])
        );
    }

    public function content(): Content
    {
        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo',
            'company_email', 'company_phone', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

        return new Content(view: 'emails.invoice-reminder', with: [
            'invoice' => $this->invoice,
            'currency' => $settings['currency_symbol'] ?? 'ر.س',
            'companyName' => $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name'),
        ]);
    }
}
