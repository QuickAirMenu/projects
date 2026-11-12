<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReviewed extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $approved = $this->payment->status === 'approved';
        $invoice = $this->payment->invoice;

        return [
            'type' => $approved ? 'payment_approved' : 'payment_rejected',
            'title' => $approved
                ? __('portal.payment_approved_title')
                : __('portal.payment_rejected_title'),
            'message' => $approved
                ? __('portal.payment_approved_msg', [
                    'amount' => number_format((float) $this->payment->amount, 2),
                    'invoice' => $invoice?->invoice_number,
                ])
                : __('portal.payment_rejected_msg', [
                    'amount' => number_format((float) $this->payment->amount, 2),
                    'invoice' => $invoice?->invoice_number,
                    'reason' => $this->payment->rejection_reason ?: '-',
                ]),
            'url' => $invoice ? route('portal.invoices.show', $invoice->id) : route('portal.payments'),
            'icon' => $approved ? 'fa-circle-check' : 'fa-circle-xmark',
            'color' => $approved ? 'emerald' : 'red',
        ];
    }
}
