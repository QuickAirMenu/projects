<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->toDateString();

        $performance = User::where('is_active', true)
            ->with('roles')
            ->withCount([
                'assignedTasks as total_tasks',
                'assignedTasks as completed_tasks' => fn ($q) => $q->whereHas('status', fn ($s) => $s->where('slug', 'done')),
                'assignedTasks as overdue_tasks' => fn ($q) => $q->where('due_date', '<', now())->whereHas('status', fn ($s) => $s->whereNotIn('slug', ['done'])),
            ])
            ->withSum(['timeLogs as logged_hours' => fn ($q) => $q->whereBetween('logged_date', [$from, $to])], 'hours')
            ->get();

        return view('reports.performance', compact('performance', 'from', 'to'));
    }
}
