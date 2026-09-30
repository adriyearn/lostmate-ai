<?php

namespace App\Enums;

enum AiMatchStatus: string
{
    case Suggested = 'suggested';
    case Confirmed = 'confirmed';
    case Dismissed = 'dismissed';
}
