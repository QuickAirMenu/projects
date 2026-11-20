<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeamPayoutService
{
    public function __construct(private TreasuryService $treasury) {}

    /**
     * Pay selected tasks into an account. Creates one outbound transaction per member,
     * converting each task cost to the account currency using its stored FX rate.
     */
    public function batchPay(Collection $taskIds, Account $account, string $date, ?string $note = null): array
    {
        $tasks = Task::with('assignedUser:id,name')
            ->whereIn('id', $taskIds)
            ->whereNotNull('assigned_to')
            ->whereNotNull('cost_amount')
            ->where('cost_amount', '>', 0)
            ->get();

        if ($tasks->isEmpty()) {
            throw ValidationException::withMessages(['tasks' => __('reports.payout_no_tasks')]);
        }

        if ($tasks->contains(fn (Task $task) => $task->cost_paid_at !== null)) {
            throw ValidationException::withMessages(['tasks' => __('reports.payout_already_paid')]);
        }

        if ((float) $account->exchange_rate_to_base <= 0) {
            throw ValidationException::withMessages(['account_id' => __('reports.payout_bad_account')]);
        }

        $group = (string) Str::uuid();
        $results = [];

        DB::transaction(function () use ($tasks, $account, $date, $note, $group, &$results) {
            foreach ($tasks->groupBy('assigned_to') as $memberId => $memberTasks) {
                $baseTotal = $memberTasks->sum(function (Task $task) {
                    return (float) $task->cost_amount * (float) ($task->cost_exchange_rate ?: 1);
                });

                $amount = round($baseTotal / (float) $account->exchange_rate_to_base, 2);

                $tx = $this->treasury->record($account, [
                    'direction' => 'out',
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'exchange_rate' => (float) $account->exchange_rate_to_base,
                    'category' => 'team_payout',
                    'description' => __('reports.payout_batch_desc', [
                        'member' => $memberTasks->first()->assignedUser?->name ?? '-',
                        'count' => $memberTasks->count(),
                        'note' => $note ? ' — '.$note : '',
                    ]),
                    'reference_type' => null,
                    'reference_id' => null,
                    'transfer_group' => null,
                ]);

                AccountTransaction::whereKey($tx->id)->update(['payout_group' => $group]);

                foreach ($memberTasks as $task) {
                    $task->forceFill([
                        'cost_paid_at' => now(),
                        'paid_account_id' => $account->id,
                        'cost_currency' => $task->cost_currency ?: $account->currency,
                        'payout_group' => $group,
                    ])->save();
                }

                $results[] = [
                    'member' => $memberTasks->first()->assignedUser?->name ?? '-',
                    'tasks' => $memberTasks->count(),
                    'amount' => $amount,
                ];
            }
        });

        return ['group' => $group, 'members' => $results, 'tasks' => $tasks->count()];
    }
}
