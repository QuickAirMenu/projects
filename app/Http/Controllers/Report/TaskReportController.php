<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;

class TaskReportController extends Controller
{
    public function index(Request $request)
    {
        $statuses = TaskStatus::where('is_active', true)->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $query = Task::with(['project', 'assignedUser', 'status'])->whereNull('parent_task_id');

        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->from) {
            $query->whereDate('due_date', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('due_date', '<=', $request->to);
        }

        $tasks = $query->latest()->paginate(20);

        $stats = [
            'total' => Task::whereNull('parent_task_id')->count(),
            'overdue' => Task::whereNull('parent_task_id')->where('due_date', '<', now())->whereHas('status', fn ($q) => $q->whereNotIn('slug', ['done']))->count(),
            'done' => Task::whereNull('parent_task_id')->whereHas('status', fn ($q) => $q->where('slug', 'done'))->count(),
            'blocked' => Task::whereNull('parent_task_id')->where('is_blocked', true)->count(),
        ];

        return view('reports.tasks', compact('tasks', 'statuses', 'users', 'stats'));
    }
}
