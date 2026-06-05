<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\ProjectStatusChanged;
use App\Notifications\TaskDeadlineApproaching;

class NotificationService
{
    /**
     * Notify the assigned user when a task is assigned.
     */
    public function notifyTaskAssigned(Task $task): void
    {
        if ($task->assignedUser && $task->assignedUser->id !== auth()->id()) {
            $task->assignedUser->notify(new TaskAssigned($task));
        }
    }

    /**
     * Notify the project manager when project status changes.
     */
    public function notifyProjectStatusChanged(Project $project, string $oldStatus): void
    {
        if ($project->manager) {
            $project->manager->notify(new ProjectStatusChanged($project, $oldStatus));
        }
    }

    /**
     * Notify team members of tasks approaching their deadline (run via scheduler).
     */
    public function notifyUpcomingDeadlines(): void
    {
        $tasks = Task::with('assignedUser')
            ->whereNotNull('assigned_to')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(2)])
            ->whereHas('status', fn($q) => $q->whereNotIn('slug', ['done']))
            ->get();

        foreach ($tasks as $task) {
            if ($task->assignedUser) {
                $task->assignedUser->notify(new TaskDeadlineApproaching($task));
            }
        }
    }
}
