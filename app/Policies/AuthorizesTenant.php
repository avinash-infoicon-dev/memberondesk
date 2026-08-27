<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesTenant
{
    protected function owns(User $user, Model $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $user->business_id === (int) $model->getAttribute('business_id');
    }
}
