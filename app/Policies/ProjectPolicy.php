<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool { return $user->can('view-projects'); }

    public function view(User $user, Project $project): bool
    {
        return $user->can('view-projects') &&
            ($user->hasAnyRole(['super-admin', 'admin', 'finance']) ||
             $project->manager_id === $user->id ||
             $project->teamMembers->contains($user->id));
    }

    public function create(User $user): bool { return $user->can('create-projects'); }

    public function update(User $user, Project $project): bool
    {
        return $user->can('edit-projects') &&
            ($user->hasAnyRole(['super-admin', 'admin']) || $project->manager_id === $user->id);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('delete-projects');
    }
}
