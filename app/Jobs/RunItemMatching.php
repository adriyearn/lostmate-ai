<?php

namespace App\Jobs;

use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\MatchingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunItemMatching implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public LostItem|FoundItem $item) {}

    public function handle(MatchingService $matchingService): void
    {
        $matchingService->run($this->item);
    }
}
