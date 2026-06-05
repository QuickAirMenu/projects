<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\NotificationService;
use App\Services\ProjectService;

class TaskObserver
{
    public function updated(Task $task): void
    {
        // Notify when task is assigned to someone new
        if ($task->isDirty('assigned_to') && $task->assigned_to) {
            app(NotificationService::class)->notifyTaskAssigned($task);
        }

        // Recalculate parent project progress when task status changes
        if ($task->isDirty('status_id') && $task->project_id) {
            app(ProjectService::class)->recalculateProgress($task->project);
        }
    }

    public function creating(Task $task): void
    {
        if (empty($task->created_by)) {
            $task->created_by = auth()->id();
        }
    }
}
