<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->created_by)) {
            $invoice->created_by = auth()->id();
        }
    }

    public function updated(Invoice $invoice): void
    {
        // Sync payment_status back to the project
        if ($invoice->isDirty('payment_status') && $invoice->project_id) {
            $project = $invoice->project;
            if ($project) {
                $allPaid = $project->invoices()->where('payment_status', '!=', 'paid')->doesntExist();
                $anyPartial = $project->invoices()->where('payment_status', 'partially_paid')->exists();

                $projectPaymentStatus = $allPaid ? 'paid' : ($anyPartial ? 'partially_paid' : 'unpaid');
                $project->update(['payment_status' => $projectPaymentStatus]);
            }
        }
    }
}
