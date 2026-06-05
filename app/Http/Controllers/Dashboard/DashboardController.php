<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Statistics
        $stats = [
            'total_projects' => Project::active()->count(),
            'active_projects' => Project::active()->whereHas('status', function($q) {
                $q->where('slug', 'in-progress');
            })->count(),
            'total_tasks' => Task::count(),
            'my_tasks' => Task::where('assigned_to', $user->id)
                ->whereHas('status', function($q) {
                    $q->where('slug', '!=', 'done');
                })->count(),
            'overdue_tasks' => Task::overdue()->count(),
            'total_clients' => Client::count(),
            'active_clients' => Client::active()->count(),
            'unpaid_invoices' => Invoice::unpaid()->sum('remaining_amount'),
        ];

        // Recent Projects
        $recentProjects = Project::with(['client', 'status', 'manager'])
            ->latest()
            ->take(5)
            ->get();

        // My Tasks
        $myTasks = Task::with(['project', 'status'])
            ->where('assigned_to', $user->id)
            ->whereHas('status', function($q) {
                $q->where('slug', '!=', 'done');
            })
            ->orderBy('due_date')
            ->take(10)
            ->get();

        // Overdue Projects
        $overdueProjects = Project::overdue()
            ->with(['client', 'status', 'manager'])
            ->orderBy('deadline')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'recentProjects',
            'myTasks',
            'overdueProjects'
        ));
    }
}
