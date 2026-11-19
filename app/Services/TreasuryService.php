<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TreasuryService
{
    public function baseCurrency(): string
    {
        return (string) (Setting::query()->where('key', 'currency')->value('value') ?: 'SAR');
    }

    public function record(Account $account, array $data): AccountTransaction
    {
        $rate = (float) ($data['exchange_rate'] ?? 1);
        $amount = round((float) $data['amount'], 2);

        return AccountTransaction::create([
            'account_id' => $account->id,
            'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
            'direction' => $data['direction'],
            'amount' => $amount,
            'currency' => $account->currency,
            'exchange_rate' => $rate > 0 ? $rate : 1,
            'base_amount' => round($amount * ($rate > 0 ? $rate : 1), 2),
            'category' => $data['category'] ?? 'other',
            'description' => $data['description'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'transfer_group' => $data['transfer_group'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    public function transfer(Account $from, Account $to, float $amountOut, float $amountIn, string $date, ?string $description = null): void
    {
        DB::transaction(function () use ($from, $to, $amountOut, $amountIn, $date, $description) {
            $group = (string) Str::uuid();

            $this->record($from, [
                'direction' => 'out', 'amount' => $amountOut, 'transaction_date' => $date,
                'exchange_rate' => $from->exchange_rate_to_base,
                'category' => 'transfer', 'description' => $description ?: __('accounts.transfer_to', ['account' => $to->name]),
                'transfer_group' => $group,
            ]);

            $this->record($to, [
                'direction' => 'in', 'amount' => $amountIn, 'transaction_date' => $date,
                'exchange_rate' => $to->exchange_rate_to_base,
                'category' => 'transfer', 'description' => $description ?: __('accounts.transfer_from', ['account' => $from->name]),
                'transfer_group' => $group,
            ]);
        });
    }

    public function recordClientPayment(Payment $payment): void
    {
        if (! $payment->account_id || $payment->status !== 'approved') {
            return;
        }

        $exists = AccountTransaction::query()
            ->where('reference_type', Payment::class)
            ->where('reference_id', $payment->id)
            ->exists();

        if ($exists) {
            return;
        }

        $account = Account::find($payment->account_id);

        if (! $account) {
            return;
        }

        $this->record($account, [
            'direction' => 'in',
            'amount' => (float) $payment->amount,
            'transaction_date' => $payment->payment_date?->toDateString() ?? now()->toDateString(),
            'exchange_rate' => (float) ($payment->exchange_rate ?: 1),
            'category' => 'client_payment',
            'description' => __('accounts.client_payment_desc', ['number' => $payment->invoice?->invoice_number]),
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
        ]);
    }

    public function recordTaskPayout(Task $task, Account $account, float $amount, float $rate, string $date): void
    {
        $this->record($account, [
            'direction' => 'out',
            'amount' => $amount,
            'transaction_date' => $date,
            'exchange_rate' => $rate,
            'category' => 'team_payout',
            'description' => __('accounts.team_payout_desc', ['task' => $task->title]),
            'reference_type' => Task::class,
            'reference_id' => $task->id,
        ]);

        $task->forceFill([
            'cost_paid_at' => now(),
            'paid_account_id' => $account->id,
            'cost_currency' => $task->cost_currency ?: $account->currency,
        ])->save();
    }

    public function reverseTaskPayout(Task $task): void
    {
        AccountTransaction::query()
            ->where('reference_type', Task::class)
            ->where('reference_id', $task->id)
            ->where('category', 'team_payout')
            ->delete();

        $task->forceFill(['cost_paid_at' => null, 'paid_account_id' => null])->save();
    }

    public function recordExpensePayment(Expense $expense): void
    {
        if (! $expense->account_id || ! $expense->paid_at) {
            return;
        }

        $exists = AccountTransaction::query()
            ->where('reference_type', Expense::class)
            ->where('reference_id', $expense->id)
            ->exists();

        if ($exists) {
            return;
        }

        $account = Account::find($expense->account_id);

        if (! $account) {
            return;
        }

        $this->record($account, [
            'direction' => 'out',
            'amount' => (float) $expense->total,
            'transaction_date' => $expense->paid_at->toDateString(),
            'category' => 'expense',
            'description' => __('accounts.expense_desc', ['number' => $expense->expense_number]),
            'reference_type' => Expense::class,
            'reference_id' => $expense->id,
        ]);
    }
}
