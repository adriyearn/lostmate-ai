<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';
    case ActionTaken = 'action_taken';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Reviewed => 'Reviewed',
            self::ActionTaken => 'Action taken',
            self::Dismissed => 'Dismissed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Reviewed => 'text-bg-info',
            self::ActionTaken => 'text-bg-success',
            self::Dismissed => 'text-bg-secondary',
        };
    }
}
