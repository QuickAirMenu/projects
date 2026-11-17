<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\RecurringInvoice;

class RecurringInvoiceService
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function generate(RecurringInvoice $recurring): Invoice
    {
        if (! $recurring->project_id || ! $recurring->project) {
            throw new \RuntimeException('Recurring invoice has no valid project.');
        }

        $invoice = $this->invoiceService->createInvoice([
            'project_id' => $recurring->project_id,
            'client_id' => $recurring->client_id,
            'invoice_type' => $recurring->invoice_type,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'tax_percentage' => (float) $recurring->tax_percentage,
            'discount_type' => $recurring->discount_type ?: 'fixed',
            'discount_amount' => (float) $recurring->discount_amount,
            'notes' => $recurring->notes,
            'terms' => $recurring->terms,
            'items' => $recurring->items,
            'created_by' => $recurring->created_by,
        ]);

        $recurring->update([
            'last_run_at' => now(),
            'next_run_date' => RecurringInvoice::nextDate($recurring->next_run_date, $recurring->frequency),
        ]);

        return $invoice;
    }

    public function processDue(): array
    {
        $generated = 0;
        $failed = 0;

        RecurringInvoice::due()->with('project')->orderBy('next_run_date')->each(function (RecurringInvoice $recurring) use (&$generated, &$failed) {
            try {
                $this->generate($recurring);
                $generated++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        });

        return [$generated, $failed];
    }
}
