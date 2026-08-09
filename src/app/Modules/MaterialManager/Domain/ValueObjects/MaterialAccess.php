<?php

namespace App\Modules\MaterialManager\Domain\ValueObjects;

enum MaterialAccess: string
{
    case PUBLIC = 'public';
    case REGISTERED = 'registered';
    case SPECIAL = 'special';

    public function label(): string
    {
        return match($this) {
            self::PUBLIC => 'Public',
            self::REGISTERED => 'Registered',
            self::SPECIAL => 'Special',
        };
    }
}
