<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Inappropriate = 'inappropriate';
    case Fraud = 'fraud';
    case FalseClaim = 'false_claim';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam',
            self::Inappropriate => 'Inappropriate content',
            self::Fraud => 'Fraud',
            self::FalseClaim => 'False claim',
            self::Other => 'Other',
        };
    }
}
