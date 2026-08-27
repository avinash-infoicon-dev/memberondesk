<?php

namespace App\Enums;

enum SaasSubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Trial',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Trial => 'info',
            self::Active => 'success',
            self::PastDue => 'warning',
            self::Cancelled => 'muted',
            self::Expired => 'danger',
        };
    }
}
