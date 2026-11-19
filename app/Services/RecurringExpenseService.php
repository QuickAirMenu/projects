<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Project;
use App\Models\RecurringExpense;
use Illuminate\Support\Facades\DB;

class RecurringExpenseService
{
    public function generate(RecurringExpense $recurring, bool $force = true): ?Expense
    {
        return DB::transaction(function () use ($recurring, $force) {
            $locked = RecurringExpense::whereKey($recurring->getKey())->lockForUpdate()->firstOrFail();

            if (! $force && $locked->next_run_date?->isAfter(now()->startOfDay())) {
                return null;
            }

            if (! $force && ! $locked->is_active) {
                return null;
            }

            $clientId = $locked->client_id
                ?: ($locked->project_id ? Project::whereKey($locked->project_id)->value('client_id') : null);

            $expense = Expense::create([
                'expense_number' => Expense::nextNumber(),
                'vendor_name' => $locked->vendor_name,
                'category' => $locked->category,
                'expense_date' => now()->toDateString(),
                'amount' => (float) $locked->amount,
                'tax_amount' => (float) $locked->tax_amount,
                'total' => $locked->total(),
                'status' => 'pending',
                'payment_method' => $locked->payment_method,
                'reference' => $locked->reference,
                'notes' => $locked->notes,
                'project_id' => $locked->project_id,
                'client_id' => $clientId,
                'billable' => $locked->billable,
                'recurring_expense_id' => $locked->id,
                'created_by' => $locked->created_by,
            ]);

            $locked->update([
                'last_run_at' => now(),
                'next_run_date' => RecurringExpense::nextDate($locked->next_run_date, $locked->frequency),
            ]);

            return $expense;
        });
    }

    public function processDue(): array
    {
        $generated = 0;
        $failed = 0;

        RecurringExpense::due()->orderBy('next_run_date')->each(function (RecurringExpense $recurring) use (&$generated, &$failed) {
            try {
                if ($this->generate($recurring, false) !== null) {
                    $generated++;
                }
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        });

        return [$generated, $failed];
    }
}
