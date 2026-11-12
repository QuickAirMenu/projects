<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function envelope(): Envelope
    {
        $approved = $this->payment->status === 'approved';

        return new Envelope(
            subject: $approved
                ? __('portal.payment_approved_subject', ['invoice' => $this->payment->invoice?->invoice_number])
                : __('portal.payment_rejected_subject', ['invoice' => $this->payment->invoice?->invoice_number])
        );
    }

    public function content(): Content
    {
        $settings = Setting::whereIn('key', [
            'company_name', 'company_name_ar', 'company_logo',
            'company_email', 'company_phone', 'company_address', 'currency_symbol',
        ])->pluck('value', 'key')->toArray();

        return new Content(view: 'emails.payment-reviewed', with: [
            'payment' => $this->payment,
            'approved' => $this->payment->status === 'approved',
            'currency' => $settings['currency_symbol'] ?? 'ر.س',
            'companyName' => $settings['company_name_ar'] ?? $settings['company_name'] ?? config('app.name'),
        ]);
    }
}
