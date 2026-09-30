<?php

namespace App\Observers;

use App\Jobs\RunItemMatching;
use App\Models\FoundItem;

class FoundItemObserver
{
    /**
     * Fields that, when changed, should trigger AI matching to re-run.
     * hidden_details and current_location are intentionally excluded -
     * hidden_details is never sent to the AI, and current_location doesn't
     * affect whether two reports describe the same item.
     */
    protected const MATCHING_FIELDS = [
        'item_name', 'category_id', 'color', 'brand', 'description', 'location_found', 'date_found',
    ];

    public function created(FoundItem $foundItem): void
    {
        RunItemMatching::dispatch($foundItem);
    }

    public function updated(FoundItem $foundItem): void
    {
        if ($foundItem->wasChanged(self::MATCHING_FIELDS)) {
            RunItemMatching::dispatch($foundItem);
        }
    }
}
