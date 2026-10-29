<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    /**
     * Display the Kanban board for all tasks.
     */
    public function index(Request $request)
    {
        $statuses = TaskStatus::where('is_active', true)->orderBy('sort_order')->get();

        $query = Task::with(['project', 'assignedUser', 'status'])
            ->whereNull('parent_task_id')
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->visibleTo(auth()->user());

        $tasks = $query->get()->groupBy('status_id');

        return view('tasks.kanban', compact('statuses', 'tasks'));
    }

    /**
     * Update the status of a task (drag-and-drop).
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status_id'  => 'required|exists:task_statuses,id',
            'sort_order' => 'nullable|integer',
        ]);

        $task->update([
            'status_id'  => $request->status_id,
            'sort_order' => $request->sort_order ?? $task->sort_order,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'task' => $task->fresh(['status'])]);
        }

        return redirect()->back()->with('success', __('task.status_updated'));
    }
}
