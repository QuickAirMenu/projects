<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $performance = User::where('is_active', true)
            ->withCount([
                'assignedTasks as total_tasks',
                'assignedTasks as completed_tasks' => fn($q) => $q->whereHas('status', fn($s) => $s->where('slug', 'done')),
                'assignedTasks as overdue_tasks'   => fn($q) => $q->where('due_date', '<', now())->whereHas('status', fn($s) => $s->whereNotIn('slug', ['done'])),
            ])
            ->get()
            ->map(function ($user) use ($from, $to) {
                $user->logged_hours = TimeLog::where('user_id', $user->id)
                    ->whereBetween('logged_date', [$from, $to])
                    ->sum('hours');
                return $user;
            });

        return view('reports.performance', compact('performance', 'users', 'from', 'to'));
    }
}
