<?php

namespace App\Enums;

enum BusinessType: string
{
    case Gym = 'gym';
    case Library = 'library';
    case Tutor = 'tutor';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Gym => 'Gym',
            self::Library => 'Library',
            self::Tutor => 'Tutor',
            self::Both => 'Gym & Library',
        };
    }

    public function supports(self $loginType): bool
    {
        return $this === $loginType || $this === self::Both;
    }
}
