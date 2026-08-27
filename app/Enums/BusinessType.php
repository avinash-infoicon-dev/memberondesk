<?php

namespace App\Enums;

enum BusinessType: string
{
    case Gym = 'gym';
    case Library = 'library';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Gym => 'Gym',
            self::Library => 'Library',
            self::Both => 'Gym & Library',
        };
    }
}
