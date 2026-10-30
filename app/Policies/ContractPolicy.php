<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-contracts');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->can('view-contracts');
    }

    public function create(User $user): bool
    {
        return $user->can('create-contracts');
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->can('edit-contracts');
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->can('delete-contracts');
    }
}
