<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
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
            'type'       => 'task_assigned',
            'title'      => __('notifications.task_assigned'),
            'message'    => __('notifications.task_assigned_msg', ['task' => $this->task->title]),
            'url'        => route('tasks.show', $this->task->id),
            'icon'       => 'fa-tasks',
            'color'      => 'blue',
        ];
    }
}
