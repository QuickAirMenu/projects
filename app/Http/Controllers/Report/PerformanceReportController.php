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
            ->with('roles')
            ->withCount([
                'assignedTasks as total_tasks',
                'assignedTasks as completed_tasks' => fn($q) => $q->whereHas('status', fn($s) => $s->where('slug', 'done')),
                'assignedTasks as overdue_tasks'   => fn($q) => $q->where('due_date', '<', now())->whereHas('status', fn($s) => $s->whereNotIn('slug', ['done'])),
            ])
            ->get()
            ->map(function ($user) use ($from, $to) {
                $user->logged_hours = 0;
                return $user;
            });

        $hoursMap = TimeLog::whereBetween('logged_date', [$from, $to])
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(hours) as total_hours')
            ->pluck('total_hours', 'user_id');

        $performance->each(function ($user) use ($hoursMap) {
            $user->logged_hours = $hoursMap[$user->id] ?? 0;
        });

        return view('reports.performance', compact('performance', 'users', 'from', 'to'));
    }
}
