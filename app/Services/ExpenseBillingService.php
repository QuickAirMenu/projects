<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseBillingService
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function bill(int $projectId, string $from, string $to, int $taxPercentage, int $userId, ?string $dueDate = null): Invoice
    {
        return DB::transaction(function () use ($projectId, $from, $to, $taxPercentage, $userId, $dueDate) {
            $project = Project::with('client:id,name')->findOrFail($projectId);

            $expenses = Expense::query()
                ->where('project_id', $projectId)
                ->where('status', 'approved')
                ->where('billable', true)
                ->whereNull('invoiced_invoice_id')
                ->whereDate('expense_date', '>=', $from)
                ->whereDate('expense_date', '<=', $to)
                ->orderBy('expense_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($expenses->isEmpty()) {
                throw ValidationException::withMessages([
                    'from' => __('expenses.no_unbilled'),
                ]);
            }

            $items = $expenses->map(function (Expense $expense) {
                return [
                    'description' => $expense->expense_date->format('Y-m-d').' — '.$expense->vendor_name
                        .' · '.__('expenses.cat_'.$expense->category).' ('.$expense->expense_number.')',
                    'quantity' => 1,
                    'unit_price' => round((float) $expense->amount, 2),
                ];
            })->all();

            $invoice = $this->invoiceService->createInvoice([
                'project_id' => $project->id,
                'client_id' => $project->client_id,
                'invoice_type' => 'additional',
                'invoice_date' => now()->toDateString(),
                'due_date' => $dueDate ?: now()->addDays(15)->toDateString(),
                'tax_percentage' => $taxPercentage,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'notes' => __('expenses.billing_note', ['from' => $from, 'to' => $to, 'project' => $project->name]),
                'items' => $items,
                'created_by' => $userId,
            ]);

            Expense::whereIn('id', $expenses->pluck('id'))->update(['invoiced_invoice_id' => $invoice->id]);

            return $invoice;
        });
    }
}
