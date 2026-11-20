<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Console\Command;

class TreasuryAudit extends Command
{
    protected $signature = 'treasury:audit';

    protected $description = 'Audit treasury integrity: FX base amounts, payment/expense conversions, transfers, duplicates';

    public function handle(): int
    {
        $issues = [];

        $baseMismatch = AccountTransaction::all()->filter(
            fn (AccountTransaction $tx) => abs((float) $tx->base_amount - round((float) $tx->amount * (float) $tx->exchange_rate, 2)) > 0.01
        );
        if ($baseMismatch->isNotEmpty()) {
            $issues[] = ['base_amount_mismatch', $baseMismatch->count()];
        }

        $paymentMismatch = 0;
        foreach (Payment::whereNotNull('account_id')->get() as $payment) {
            $tx = AccountTransaction::where('reference_type', Payment::class)->where('reference_id', $payment->id)->first();
            $expected = round((float) $payment->amount * (float) ($payment->exchange_rate ?: 1), 2);
            if (! $tx || abs((float) $tx->amount - $expected) > 0.01) {
                $paymentMismatch++;
            }
        }
        if ($paymentMismatch > 0) {
            $issues[] = ['payment_conversion_mismatch', $paymentMismatch];
        }

        $expenseMismatch = 0;
        foreach (Expense::whereNotNull('account_id')->whereNotNull('paid_at')->get() as $expense) {
            $tx = AccountTransaction::where('reference_type', Expense::class)->where('reference_id', $expense->id)->first();
            $expected = round((float) $expense->total * (float) ($expense->exchange_rate ?: 1), 2);
            if (! $tx || abs((float) $tx->amount - $expected) > 0.01) {
                $expenseMismatch++;
            }
        }
        if ($expenseMismatch > 0) {
            $issues[] = ['expense_conversion_mismatch', $expenseMismatch];
        }

        $incompleteTransfers = AccountTransaction::whereNotNull('transfer_group')->get()
            ->groupBy('transfer_group')
            ->filter(fn ($rows) => $rows->count() !== 2)
            ->count();
        if ($incompleteTransfers > 0) {
            $issues[] = ['incomplete_transfer_groups', $incompleteTransfers];
        }

        $duplicates = AccountTransaction::query()
            ->selectRaw('account_id, transaction_date, direction, amount, COUNT(*) c')
            ->groupBy('account_id', 'transaction_date', 'direction', 'amount')
            ->having('c', '>', 1)
            ->get()
            ->count();
        if ($duplicates > 0) {
            $issues[] = ['duplicate_suspects', $duplicates];
        }

        foreach (Account::all() as $account) {
            if ($account->nature() === 'asset' && $account->balance() < 0) {
                $issues[] = ['negative_asset_balance:'.$account->name, round($account->balance(), 2)];
            }
        }

        $this->table(['Check', 'Count/Value'], $issues ?: [['ok', 'no issues']]);

        return $issues === [] ? self::SUCCESS : self::FAILURE;
    }
}
