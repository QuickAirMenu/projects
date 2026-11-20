<?php

namespace App\Models\Concerns;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quote;
use App\Models\RecurringExpense;
use App\Models\RecurringInvoice;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\ProjectAccessService;
use Illuminate\Database\Eloquent\Builder;

trait ScopedToAccessibleProjects
{
    public static function bootScopedToAccessibleProjects(): void
    {
        static::addGlobalScope('project_access', function (Builder $builder) {
            $user = auth()->user() ?: auth('sanctum')->user();

            if (! $user instanceof User) {
                return;
            }

            $access = app(ProjectAccessService::class);

            if ($access->bypass($user)) {
                return;
            }

            $model = $builder->getModel();
            $projectIds = $access->projectIds($user);

            if ($model instanceof Project) {
                $builder->whereIn($model->getQualifiedKeyName(), $projectIds);

                return;
            }

            if ($model instanceof Client) {
                $builder->whereIn($model->getQualifiedKeyName(), $access->clientIds($user));

                return;
            }

            if ($model instanceof Quote) {
                $builder->whereIn('client_id', $access->clientIds($user));

                return;
            }

            if ($model instanceof Payment) {
                $builder->whereIn('invoice_id', function ($query) use ($projectIds) {
                    $query->select('id')->from('invoices')->whereIn('project_id', $projectIds);
                });

                return;
            }

            if (in_array(get_class($model), [
                Invoice::class,
                Task::class,
                TimeEntry::class,
                Expense::class,
                RecurringInvoice::class,
                RecurringExpense::class,
                Contract::class,
            ], true)) {
                $builder->whereIn($model->qualifyColumn('project_id'), $projectIds);
            }
        });
    }
}
