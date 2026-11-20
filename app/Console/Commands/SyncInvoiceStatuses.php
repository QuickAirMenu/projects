<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class SyncInvoiceStatuses extends Command
{
    protected $signature = 'invoices:sync-statuses';

    protected $description = 'Recompute invoice payment statuses from paid amounts and due dates';

    public function handle(): int
    {
        $updated = 0;

        Invoice::withTrashed()->chunkById(200, function ($invoices) use (&$updated) {
            foreach ($invoices as $invoice) {
                $total = (float) $invoice->total;

                if ($total < 0) {
                    $invoice->forceFill(['subtotal' => 0, 'tax_amount' => 0, 'discount_amount' => 0, 'total' => 0])->save();
                    $invoice->refresh();
                    $total = 0;
                }

                $paid = (float) $invoice->paid_amount;
                $remaining = max(0, round($total - $paid, 2));

                $status = match (true) {
                    $remaining <= 0 => 'paid',
                    $invoice->due_date && $invoice->due_date->toDateString() < now()->toDateString() => 'overdue',
                    $paid > 0 => 'partially_paid',
                    default => 'unpaid',
                };

                if ($invoice->payment_status !== $status || abs((float) $invoice->remaining_amount - $remaining) > 0.01) {
                    $invoice->forceFill(['payment_status' => $status, 'remaining_amount' => $remaining])->save();
                    $updated++;
                }
            }
        });

        $this->info("Invoices updated: {$updated}");

        return self::SUCCESS;
    }
}
