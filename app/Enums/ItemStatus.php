<?php

namespace App\Enums;

enum ItemStatus: string
{
    case Open = 'open';
    case Matched = 'matched';
    case Claimed = 'claimed';
    case Returned = 'returned';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Matched => 'Matched',
            self::Claimed => 'Claimed',
            self::Returned => 'Returned',
            self::Closed => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'text-bg-primary',
            self::Matched => 'text-bg-info',
            self::Claimed => 'text-bg-warning',
            self::Returned => 'text-bg-success',
            self::Closed => 'text-bg-secondary',
        };
    }
}
