<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    public function viewAny(User $user): bool  { return $user->can('view-quotes'); }
    public function view(User $user, Quote $quote): bool  { return $user->can('view-quotes'); }
    public function create(User $user): bool   { return $user->can('create-quotes'); }
    public function update(User $user, Quote $quote): bool { return $user->can('edit-quotes'); }
    public function delete(User $user, Quote $quote): bool { return $user->can('delete-quotes'); }
}
