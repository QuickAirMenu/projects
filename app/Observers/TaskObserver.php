<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\NotificationService;
use App\Services\ProjectService;

class TaskObserver
{
    public function creating(Task $task): void
    {
        if (empty($task->created_by)) {
            $task->created_by = auth()->id();
        }
    }

    public function created(Task $task): void
    {
        // Recalculate parent project progress when a task is created
        if ($task->project_id) {
            app(ProjectService::class)->recalculateProgress($task->project);
        }
    }

    public function updated(Task $task): void
    {
        // Notify when task is assigned to someone new
        if ($task->isDirty('assigned_to') && $task->assigned_to) {
            app(NotificationService::class)->notifyTaskAssigned($task);
        }

        // Recalculate parent project progress when task changes
        if ($task->project_id) {
            app(ProjectService::class)->recalculateProgress($task->project);
        }
    }

    public function deleted(Task $task): void
    {
        // Recalculate parent project progress when a task is deleted
        if ($task->project_id) {
            app(ProjectService::class)->recalculateProgress($task->project);
        }
    }

    public function restored(Task $task): void
    {
        // Recalculate parent project progress when a task is restored
        if ($task->project_id) {
            app(ProjectService::class)->recalculateProgress($task->project);
        }
    }
}
