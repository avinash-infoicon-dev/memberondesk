<?php

namespace App\Enums;

enum NotificationType: string
{
    case PaymentPending = 'payment_pending';
    case SubscriptionExpiring = 'subscription_expiring';
    case SubscriptionExpired = 'subscription_expired';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::PaymentPending => 'Payment Pending',
            self::SubscriptionExpiring => 'Subscription Expiring',
            self::SubscriptionExpired => 'Subscription Expired',
            self::Custom => 'Custom',
        };
    }
}
