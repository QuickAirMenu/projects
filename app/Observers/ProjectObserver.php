<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\NotificationService;

class ProjectObserver
{
    public function updated(Project $project): void
    {
        // Notify when status changes
        if ($project->isDirty('status_id')) {
            $oldStatusId = $project->getOriginal('status_id');
            app(NotificationService::class)->notifyProjectStatusChanged($project, $oldStatusId);
        }
    }

    public function creating(Project $project): void
    {
        if (empty($project->created_by)) {
            $project->created_by = auth()->id();
        }
    }
}
