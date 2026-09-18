<?php

namespace App\Enums;

enum ImageStatus: string
{
    case Active = 'active';
    case Review = 'review';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Review => 'Review',
            self::Deleted => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Review => 'yellow',
            self::Deleted => 'red',
        };
    }
}
