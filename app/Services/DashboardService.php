<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

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

        $stats = [
            'total_projects' => (clone $projectQuery)->count(),
            'active_projects' => (clone $projectQuery)->whereHas('status', fn($q) => $q->whereIn('slug', ['in-progress', 'sent-to-client', 'revisions']))->count(),
            'my_tasks' => (clone $taskQuery)->where('assigned_to', $user->id)->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))->count(),
            'overdue_tasks' => (clone $taskQuery)->where('assigned_to', $user->id)->where('due_date', '<', now())->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))->count(),
        ];

        $financialStats = $canViewFinancialStats ? [
            'total_clients' => Client::count(),
            'active_clients' => Client::active()->count(),
            'unpaid_invoices' => Invoice::unpaid()->sum('remaining_amount'),
        ] : [];

        $recentProjects = (clone $projectQuery)->with(['client', 'status', 'manager'])->latest()->take(5)->get();
        $myTasks = Task::with(['project', 'status'])
            ->whereNull('parent_task_id')
            ->where('assigned_to', $user->id)
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))
            ->orderBy('due_date')
            ->take(10)
            ->get();
        $overdueProjects = (clone $projectQuery)
            ->with(['client', 'status', 'manager'])
            ->where('deadline', '<', now())
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['completed', 'closed', 'cancelled']))
            ->orderBy('deadline')
            ->take(5)
            ->get();

        return compact('stats', 'financialStats', 'canViewFinancialStats', 'recentProjects', 'myTasks', 'overdueProjects');
    }
}
