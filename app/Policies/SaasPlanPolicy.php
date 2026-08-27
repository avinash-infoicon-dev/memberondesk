<?php

namespace App\Policies;

use App\Models\SaasPlan;
use App\Models\User;

class SaasPlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function view(User $user, SaasPlan $saasPlan): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, SaasPlan $saasPlan): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, SaasPlan $saasPlan): bool
    {
        return $user->isSuperAdmin();
    }
}
