<?php

namespace App\Policies;

use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestPolicy
{
    use AuthorizesTenant;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasBusinessAccess();
    }

    public function view(User $user, PaymentRequest $paymentRequest): bool
    {
        return $this->owns($user, $paymentRequest);
    }

    public function create(User $user): bool
    {
        return $user->hasBusinessAccess();
    }

    public function update(User $user, PaymentRequest $paymentRequest): bool
    {
        return $this->owns($user, $paymentRequest) && $user->hasBusinessAccess();
    }
}
