<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskChecklistController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate(['item' => 'required|string|max:500']);

        $item = $task->checklists()->create([
            'item'       => $request->item,
            'sort_order' => $task->checklists()->count(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()->route('tasks.show', $task)->with('success', __('task.checklist_item_added'));
    }

    public function update(Request $request, TaskChecklist $item)
    {
        $this->authorize('update', $item->task);

        $request->validate([
            'is_completed' => 'boolean',
            'item'         => 'nullable|string|max:500',
        ]);

        $data = [];
        if ($request->has('is_completed')) {
            $data['is_completed']  = $request->boolean('is_completed');
            $data['completed_by']  = $request->boolean('is_completed') ? Auth::id() : null;
            $data['completed_at']  = $request->boolean('is_completed') ? now() : null;
        }
        if ($request->filled('item')) {
            $data['item'] = $request->item;
        }

        $item->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item->fresh()]);
        }

        return redirect()->route('tasks.show', $item->task_id)->with('success', __('task.checklist_updated'));
    }

    public function destroy(TaskChecklist $item)
    {
        $this->authorize('update', $item->task);

        $taskId = $item->task_id;
        $item->delete();

        return redirect()->route('tasks.show', $taskId)->with('success', __('task.checklist_item_deleted'));
    }
}
