<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimeEntryBillingService
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function bill(int $projectId, string $from, string $to, int $taxPercentage, int $userId, ?string $dueDate = null): Invoice
    {
        return DB::transaction(function () use ($projectId, $from, $to, $taxPercentage, $userId, $dueDate) {
            $project = Project::with('client:id,name')->findOrFail($projectId);

            $entries = TimeEntry::query()
                ->where('project_id', $projectId)
                ->where('billable', true)
                ->whereNull('invoiced_invoice_id')
                ->whereDate('work_date', '>=', $from)
                ->whereDate('work_date', '<=', $to)
                ->orderBy('work_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                throw ValidationException::withMessages([
                    'from' => __('time.no_unbilled'),
                ]);
            }

            $items = $entries->map(function (TimeEntry $entry) {
                $hours = rtrim(rtrim(number_format((float) $entry->hours, 2, '.', ''), '0'), '.');

                return [
                    'description' => $entry->work_date->format('Y-m-d').' — '.$entry->description
                        .' ('.$hours.'h × '.number_format((float) $entry->hourly_rate, 2).')',
                    'quantity' => 1,
                    'unit_price' => $entry->amount(),
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
                'notes' => __('time.billing_note', ['from' => $from, 'to' => $to, 'project' => $project->name]),
                'items' => $items,
                'created_by' => $userId,
            ]);

            TimeEntry::whereIn('id', $entries->pluck('id'))->update(['invoiced_invoice_id' => $invoice->id]);

            return $invoice;
        });
    }
}
