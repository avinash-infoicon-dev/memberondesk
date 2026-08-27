<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    use AuthorizesTenant;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription);
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessAccess();
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription) && $user->hasBusinessAccess();
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $this->owns($user, $subscription) && $user->isBusinessOwner();
    }
}
