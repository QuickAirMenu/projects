<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_submitted',
            'title' => __('notifications.payment_submitted'),
            'message' => __('notifications.payment_submitted_msg', [
                'amount' => number_format((float) $this->payment->amount, 2),
                'invoice' => $this->payment->invoice?->invoice_number,
            ]),
            'url' => route('payments.index'),
            'icon' => 'fa-hourglass-half',
            'color' => 'amber',
        ];
    }
}
