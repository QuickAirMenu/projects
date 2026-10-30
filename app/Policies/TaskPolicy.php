<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'project-manager']) ||
               $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create-tasks');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('edit-tasks') ||
               $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('delete-tasks');
    }
}
