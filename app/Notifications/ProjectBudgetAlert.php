<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectBudgetAlert extends Notification
{
    use Queueable;

    public function __construct(public Project $project, public float $cost, public float $percentage) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'project_budget_alert',
            'title' => __('notifications.project_budget_alert'),
            'message' => __('notifications.project_budget_alert_msg', [
                'project' => $this->project->name,
                'percentage' => number_format($this->percentage, 1),
                'cost' => number_format($this->cost, 2),
                'budget' => number_format((float) $this->project->budget, 2),
            ]),
            'url' => route('projects.show', $this->project),
            'icon' => 'fa-triangle-exclamation',
            'color' => 'red',
        ];
    }
}
