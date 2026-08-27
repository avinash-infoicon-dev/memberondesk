<?php

namespace App\Policies;

use App\Models\MembershipPlan;
use App\Models\User;

class MembershipPlanPolicy
{
    use AuthorizesTenant;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function view(User $user, MembershipPlan $membershipPlan): bool
    {
        return $this->owns($user, $membershipPlan);
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessAccess();
    }

    public function update(User $user, MembershipPlan $membershipPlan): bool
    {
        return $this->owns($user, $membershipPlan) && $user->hasBusinessAccess();
    }

    public function delete(User $user, MembershipPlan $membershipPlan): bool
    {
        return $this->owns($user, $membershipPlan) && $user->isBusinessOwner();
    }
}
