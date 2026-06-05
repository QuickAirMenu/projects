<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'payment_received',
            'title'   => __('notifications.payment_received'),
            'message' => __('notifications.payment_received_msg', ['amount' => number_format($this->payment->amount, 2)]),
            'url'     => route('invoices.show', $this->payment->invoice_id),
            'icon'    => 'fa-money-bill-wave',
            'color'   => 'green',
        ];
    }
}
