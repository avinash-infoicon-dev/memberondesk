<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Business $business): bool
    {
        return $user->isSuperAdmin() || (int) $user->business_id === (int) $business->id;
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Business $business): bool
    {
        return $user->isSuperAdmin() || ((int) $user->business_id === (int) $business->id && $user->isBusinessOwner());
    }

    public function delete(User $user, Business $business): bool
    {
        return $user->isSuperAdmin();
    }

    public function manage(User $user, Business $business): bool
    {
        return $this->update($user, $business);
    }
}
