<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\RecurringInvoice;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceService
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function generate(RecurringInvoice $recurring, bool $force = true): ?Invoice
    {
        return DB::transaction(function () use ($recurring, $force) {
            $locked = RecurringInvoice::whereKey($recurring->getKey())->lockForUpdate()->firstOrFail();

            if (! $force && $locked->next_run_date?->isAfter(now()->startOfDay())) {
                return null;
            }

            if (! $locked->project_id || ! $locked->project) {
                throw new \RuntimeException('Recurring invoice has no valid project.');
            }

            $invoice = $this->invoiceService->createInvoice([
                'project_id' => $locked->project_id,
                'client_id' => $locked->client_id,
                'invoice_type' => $locked->invoice_type,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'tax_percentage' => (float) $locked->tax_percentage,
                'discount_type' => $locked->discount_type ?: 'fixed',
                'discount_amount' => (float) $locked->discount_amount,
                'notes' => $locked->notes,
                'terms' => $locked->terms,
                'items' => $locked->items,
                'created_by' => $locked->created_by,
            ]);

            $locked->update([
                'last_run_at' => now(),
                'next_run_date' => RecurringInvoice::nextDate($locked->next_run_date, $locked->frequency),
            ]);

            return $invoice;
        });
    }

    public function processDue(): array
    {
        $generated = 0;
        $failed = 0;

        RecurringInvoice::due()->with('project')->orderBy('next_run_date')->each(function (RecurringInvoice $recurring) use (&$generated, &$failed) {
            try {
                $this->generate($recurring, false);
                $generated++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        });

        return [$generated, $failed];
    }
}
