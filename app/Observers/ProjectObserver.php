<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\NotificationService;

class ProjectObserver
{
    public function saved(Project $project): void
    {
        $project->client?->recalculateLifetimeValue();
    }

    public function deleted(Project $project): void
    {
        if ($project->client) {
            $project->client->recalculateLifetimeValue();
        }
    }

    public function restored(Project $project): void
    {
        $project->client?->recalculateLifetimeValue();
    }

    public function forceDeleted(Project $project): void
    {
        if ($project->client) {
            $project->client->recalculateLifetimeValue();
        }
    }

    public function created(Project $project): void
    {
        // handled by saved()
    }

    public function updated(Project $project): void
    {
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
