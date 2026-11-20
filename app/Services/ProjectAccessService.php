<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;

class ProjectAccessService
{
    protected array $cache = [];

    public function bypass(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'finance']);
    }

    public function projectIds(User $user): array
    {
        $key = 'projects:'.$user->id;

        if (! isset($this->cache[$key])) {
            $this->cache[$key] = Project::withoutGlobalScope('project_access')
                ->where(function ($query) use ($user) {
                    $query->where('manager_id', $user->id)
                        ->orWhereHas('teamMembers', fn ($q) => $q->where('users.id', $user->id));
                })
                ->pluck('id')
                ->all();
        }

        return $this->cache[$key];
    }

    public function clientIds(User $user): array
    {
        $key = 'clients:'.$user->id;

        if (! isset($this->cache[$key])) {
            $this->cache[$key] = Project::withoutGlobalScope('project_access')
                ->whereIn('id', $this->projectIds($user))
                ->whereNotNull('client_id')
                ->distinct()
                ->pluck('client_id')
                ->all();
        }

        return $this->cache[$key];
    }
}
