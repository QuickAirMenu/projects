<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use App\Notifications\ProjectBudgetAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendBudgetAlerts extends Command
{
    protected $signature = 'projects:budget-alerts {--threshold=90 : Alert when cost reaches this % of budget} {--days=7 : Minimum days between alerts per project}';

    protected $description = 'Notify project managers when project cost approaches or exceeds the budget';

    public function handle(): int
    {
        $threshold = max(1, min(100, (int) $this->option('threshold')));
        $days = max(1, (int) $this->option('days'));

        $expenses = Expense::query()
            ->where('status', 'approved')
            ->selectRaw('project_id, SUM(total) as total')
            ->groupBy('project_id')
            ->pluck('total', 'project_id');

        $labor = TimeEntry::query()
            ->selectRaw('project_id, SUM(hours * hourly_rate) as labor')
            ->groupBy('project_id')
            ->pluck('labor', 'project_id');

        $projects = Project::with(['manager', 'client:id,name'])
            ->where('budget', '>', 0)
            ->where('is_archived', false)
            ->get();

        $sent = 0;

        foreach ($projects as $project) {
            $cost = (float) ($expenses[$project->id] ?? 0) + (float) ($labor[$project->id] ?? 0);
            $budget = (float) $project->budget;
            $percentage = $budget > 0 ? ($cost / $budget) * 100 : 0;

            if ($percentage < $threshold) {
                continue;
            }

            if ($project->budget_alert_sent_at && $project->budget_alert_sent_at->gt(now()->subDays($days))) {
                continue;
            }

            $recipients = $project->manager
                ? collect([$project->manager])
                : User::role('super-admin')->get();

            if ($recipients->isEmpty()) {
                continue;
            }

            Notification::send($recipients, new ProjectBudgetAlert($project, $cost, $percentage));

            $project->forceFill(['budget_alert_sent_at' => now()])->save();
            $sent++;
        }

        $this->info("Budget alerts sent: {$sent}");

        return self::SUCCESS;
    }
}
