<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'hours'       => 'required|numeric|min:0.25|max:24',
            'logged_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:500',
            'billable'    => 'boolean',
        ]);

        $log = $task->timeLogs()->create([
            'user_id'     => Auth::id(),
            'hours'       => $request->hours,
            'logged_date' => $request->logged_date,
            'description' => $request->description,
            'billable'    => $request->boolean('billable', true),
        ]);

        // Update actual_hours on the task
        $task->update(['actual_hours' => $task->timeLogs()->sum('hours')]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'log' => $log->load('user')]);
        }

        return redirect()->route('tasks.show', $task)->with('success', __('task.time_logged'));
    }

    public function destroy(TimeLog $log)
    {
        $task   = $log->task;
        $taskId = $log->task_id;
        $log->delete();

        // Recalculate actual_hours
        $task->update(['actual_hours' => $task->timeLogs()->sum('hours')]);

        return redirect()->route('tasks.show', $taskId)->with('success', __('task.time_log_deleted'));
    }
}
