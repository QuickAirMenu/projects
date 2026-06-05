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

        $projectQuery = Project::query();
        $taskQuery    = Task::whereNull('parent_task_id');

        // Scope data per role
        if (!$user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            $projectQuery->where(fn($q) => $q
                ->where('manager_id', $user->id)
                ->orWhereHas('teamMembers', fn($t) => $t->where('users.id', $user->id))
            );
            $taskQuery->where('assigned_to', $user->id);
        }

        $totalProjects   = (clone $projectQuery)->count();
        $activeProjects  = (clone $projectQuery)->whereHas('status', fn($q) => $q->whereIn('slug', ['in-progress', 'sent-to-client', 'revisions']))->count();
        $overdueProjects = (clone $projectQuery)->where('deadline', '<', now())->whereHas('status', fn($q) => $q->whereNotIn('slug', ['completed', 'closed', 'cancelled']))->count();

        $totalTasks   = (clone $taskQuery)->count();
        $openTasks    = (clone $taskQuery)->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))->count();
        $overdueTasks = (clone $taskQuery)->where('due_date', '<', now())->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))->count();

        $financialStats = [];
        if ($user->hasAnyRole(['super-admin', 'admin', 'finance'])) {
            $financialStats = [
                'total_invoiced' => Invoice::sum('total'),
                'total_paid'     => Invoice::sum('paid_amount'),
                'total_unpaid'   => Invoice::where('payment_status', '!=', 'paid')->sum('remaining_amount'),
                'overdue_count'  => Invoice::where('payment_status', 'overdue')->count(),
            ];
        }

        $recentProjects = (clone $projectQuery)->with(['client', 'status'])->latest()->take(5)->get();
        $myTasks        = Task::with(['project', 'status'])
            ->whereNull('parent_task_id')
            ->where('assigned_to', $user->id)
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))
            ->orderBy('due_date')
            ->take(8)
            ->get();

        return compact(
            'totalProjects', 'activeProjects', 'overdueProjects',
            'totalTasks', 'openTasks', 'overdueTasks',
            'financialStats', 'recentProjects', 'myTasks'
        );
    }
}
