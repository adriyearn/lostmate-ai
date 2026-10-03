<?php

namespace App\Jobs;

use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\MatchingService;
use App\Services\MatchingStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class RunItemMatching implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public LostItem|FoundItem $item)
    {
        // Runs when the job is queued, so the report page shows
        // "AI is checking for matches" right away.
        MatchingStatus::markChecking($item);
    }

    public function handle(MatchingService $matchingService): void
    {
        $matchingService->run($this->item);

        MatchingStatus::clear($this->item);
    }

    /**
     * Called by Laravel after the last retry fails (e.g. the AI service was
     * down the whole time). The report itself is already saved; admins can
     * retry from the dashboard's System health card.
     */
    public function failed(?Throwable $exception): void
    {
        MatchingStatus::markFailed($this->item);
    }
}
