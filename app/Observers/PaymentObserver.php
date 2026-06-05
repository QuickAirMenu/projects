<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    public function creating(Payment $payment): void
    {
        if (empty($payment->received_by)) {
            $payment->received_by = auth()->id();
        }
    }
}
