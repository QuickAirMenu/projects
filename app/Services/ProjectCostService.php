<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Collection;

class ProjectCostService
{
    /**
     * Labor/team cost per project: sum of task costs when present,
     * otherwise fall back to timesheet value (hours × rate).
     */
    public function laborByProject(): Collection
    {
        $taskCosts = Task::query()
            ->whereNotNull('cost_amount')
            ->selectRaw('project_id, SUM(cost_amount) as cost')
            ->groupBy('project_id')
            ->pluck('cost', 'project_id');

        $timesheet = TimeEntry::query()
            ->selectRaw('project_id, SUM(hours * hourly_rate) as labor')
            ->groupBy('project_id')
            ->pluck('labor', 'project_id');

        return $taskCosts->keys()
            ->merge($timesheet->keys())
            ->unique()
            ->mapWithKeys(function ($projectId) use ($taskCosts, $timesheet) {
                $task = (float) ($taskCosts[$projectId] ?? 0);

                return [$projectId => $task > 0 ? $task : (float) ($timesheet[$projectId] ?? 0)];
            });
    }

    public function expensesByProject(): Collection
    {
        return Expense::query()
            ->where('status', 'approved')
            ->selectRaw('project_id, SUM(total) as total')
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');
    }
}
