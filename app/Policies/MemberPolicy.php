<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    use AuthorizesTenant;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function view(User $user, Member $member): bool
    {
        return $this->owns($user, $member);
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessAccess();
    }

    public function update(User $user, Member $member): bool
    {
        return $this->owns($user, $member) && $user->hasBusinessAccess();
    }

    public function delete(User $user, Member $member): bool
    {
        return $this->owns($user, $member) && $user->isBusinessOwner();
    }
}
