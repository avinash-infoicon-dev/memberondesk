<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Upi = 'upi';
    case Online = 'online';
    case Gateway = 'gateway';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Upi => 'UPI',
            self::Online => 'Online',
            self::Gateway => 'Payment Gateway',
        };
    }
}
