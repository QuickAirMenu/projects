<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDeadlineApproaching extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'task_deadline',
            'title'   => __('notifications.task_deadline_approaching'),
            'message' => __('notifications.task_deadline_msg', [
                'task' => $this->task->title,
                'date' => $this->task->due_date?->format('Y-m-d'),
            ]),
            'url'     => route('tasks.show', $this->task->id),
            'icon'    => 'fa-clock',
            'color'   => 'orange',
        ];
    }
}
