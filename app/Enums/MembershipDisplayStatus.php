<?php

namespace App\Enums;

enum MembershipDisplayStatus: string
{
    case Active = 'active';
    case ExpiringSoon = 'expiring_soon';
    case PaymentPending = 'payment_pending';
    case Expired = 'expired';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::ExpiringSoon => 'Expiring Soon',
            self::PaymentPending => 'Payment Pending',
            self::Expired => 'Expired',
            self::Inactive => 'Inactive',
        };
    }
}
