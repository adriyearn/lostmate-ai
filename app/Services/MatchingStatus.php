<?php

namespace App\Services;

use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Support\Facades\Cache;

/**
 * Remembers whether AI matching for a report is still running or failed,
 * so the report page can say "AI is checking..." instead of showing nothing.
 *
 * Stored in the cache (no database column needed). Possible states:
 *  - "checking": a matching job is queued or running
 *  - "failed":   the job gave up after all its retries
 *  - null:       finished (or never run) - show the matches as usual
 */
class MatchingStatus
{
    public const CHECKING = 'checking';

    public const FAILED = 'failed';

    public static function markChecking(LostItem|FoundItem $item): void
    {
        // Expires on its own, so a lost job can't leave "checking" forever.
        Cache::put(self::key($item), self::CHECKING, now()->addMinutes(30));
    }

    public static function markFailed(LostItem|FoundItem $item): void
    {
        Cache::put(self::key($item), self::FAILED, now()->addDays(7));
    }

    public static function clear(LostItem|FoundItem $item): void
    {
        Cache::forget(self::key($item));
    }

    public static function get(LostItem|FoundItem $item): ?string
    {
        return Cache::get(self::key($item));
    }

    protected static function key(LostItem|FoundItem $item): string
    {
        return 'matching-status:'.($item instanceof LostItem ? 'lost' : 'found').':'.$item->id;
    }
}
