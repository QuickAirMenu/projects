<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    public function creating(Payment $payment): void
    {
        if (empty($payment->received_by)) {
            $payment->received_by = auth()->id();
        }
    }

    public function created(Payment $payment): void
    {
        // Recalculate the client's lifetime value from paid invoices
        $payment->invoice?->client?->recalculateLifetimeValue();

        // Invalidate the cached financial stats so the dashboard reflects new totals
        Cache::forget('dashboard_financial_stats');
    }
}
