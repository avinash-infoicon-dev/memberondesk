<?php

namespace App\Enums;

enum AttendanceSource: string
{
    case Qr = 'qr';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Qr => 'QR Scan',
            self::Manual => 'Manual',
        };
    }
}
