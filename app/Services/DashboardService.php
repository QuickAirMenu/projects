<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getStats(): array
    {
        $user = Auth::user();

        $projectQuery = Project::active();
        $taskQuery    = Task::whereNull('parent_task_id');
        $canViewFinancialStats = $user->hasAnyRole(['super-admin', 'admin', 'finance']);

        // Scope data per role
        if (!$user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            $projectQuery->where(fn($q) => $q
                ->where('manager_id', $user->id)
                ->orWhereHas('teamMembers', fn($t) => $t->where('users.id', $user->id))
            );
            $taskQuery->where('assigned_to', $user->id);
        }

        // Single-query stats with subquery selects
        $projectStats = (clone $projectQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN EXISTS (SELECT 1 FROM project_statuses WHERE project_statuses.id = projects.status_id AND project_statuses.slug IN (?, ?, ?)) THEN 1 ELSE 0 END) as active', ['in-progress', 'sent-to-client', 'revisions'])
            ->selectRaw('SUM(CASE WHEN EXISTS (SELECT 1 FROM project_statuses WHERE project_statuses.id = projects.status_id AND project_statuses.slug IN (?, ?)) THEN 1 ELSE 0 END) as completed', ['completed', 'closed'])
            ->selectRaw('SUM(CASE WHEN deadline < ? AND EXISTS (SELECT 1 FROM project_statuses WHERE project_statuses.id = projects.status_id AND project_statuses.slug NOT IN (?, ?, ?)) THEN 1 ELSE 0 END) as overdue', [now(), 'completed', 'closed', 'cancelled'])
            ->first();

        $taskStats = (clone $taskQuery)
            ->where('assigned_to', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN due_date < ? AND EXISTS (SELECT 1 FROM task_statuses WHERE task_statuses.id = tasks.status_id AND task_statuses.slug NOT IN (?)) THEN 1 ELSE 0 END) as overdue', [now(), 'done'])
            ->first();

        $stats = [
            'total_projects'  => (int) $projectStats->total,
            'active_projects' => (int) $projectStats->active,
            'completed_projects' => (int) $projectStats->completed,
            'overdue_projects_count' => (int) $projectStats->overdue,
            'my_tasks'       => (int) $taskStats->total,
            'overdue_tasks'  => (int) $taskStats->overdue,
        ];

        // Cached financial stats (60s) - these are global totals shared across all users
        $financialStats = $canViewFinancialStats
            ? Cache::remember('dashboard_financial_stats', 60, fn() => [
                'total_clients'   => Client::count(),
                'active_clients'  => Client::where('status', 'active')->count(),
                'unpaid_invoices' => Invoice::where('payment_status', '!=', 'paid')->sum('remaining_amount'),
                'total_invoiced'  => Invoice::sum('total'),
                'total_collected' => Invoice::sum('paid_amount'),
            ])
            : [];

        // Eager-load for the lists (avoids N+1 in templates)
        $recentProjects = (clone $projectQuery)->with(['client', 'status', 'manager'])->latest()->take(5)->get();
        $myTasks = Task::with(['project', 'status'])
            ->whereNull('parent_task_id')
            ->where('assigned_to', $user->id)
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))
            ->orderBy('due_date')
            ->take(6)
            ->get();
        $overdueProjects = (clone $projectQuery)
            ->with(['client', 'status', 'manager'])
            ->where('deadline', '<', now())
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['completed', 'closed', 'cancelled']))
            ->orderBy('deadline')
            ->take(3)
            ->get();

        return compact('stats', 'financialStats', 'canViewFinancialStats', 'recentProjects', 'myTasks', 'overdueProjects');
    }
}
