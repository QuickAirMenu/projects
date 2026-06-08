<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'comment'     => 'required|string|max:5000',
            'is_internal' => 'boolean',
        ]);

        $comment = $task->comments()->create([
            'user_id'     => Auth::id(),
            'comment'     => $request->comment,
            'is_internal' => $request->boolean('is_internal', true),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'comment' => $comment->load('user')]);
        }

        return redirect()->route('tasks.show', $task)->with('success', __('task.comment_added'));
    }

    public function destroy(TaskComment $comment)
    {
        $this->authorize('delete', $comment);
        $taskId = $comment->task_id;
        $comment->delete();

        return redirect()->route('tasks.show', $taskId)->with('success', __('task.comment_deleted'));
    }
}
