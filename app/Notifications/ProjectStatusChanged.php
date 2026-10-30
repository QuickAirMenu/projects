<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Project $project, public mixed $oldStatusId) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_status_changed',
            'title' => __('notifications.project_status_changed'),
            'message' => __('notifications.project_status_msg', ['project' => $this->project->name]),
            'url' => route('projects.show', $this->project->id),
            'icon' => 'fa-folder',
            'color' => 'purple',
        ];
    }
}
