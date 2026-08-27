<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    use AuthorizesTenant;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $this->owns($user, $attendance);
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessAccess();
    }
}
