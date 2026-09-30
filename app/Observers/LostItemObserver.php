<?php

namespace App\Observers;

use App\Jobs\RunItemMatching;
use App\Models\LostItem;

class LostItemObserver
{
    /**
     * Fields that, when changed, should trigger AI matching to re-run.
     */
    protected const MATCHING_FIELDS = [
        'item_name', 'category_id', 'color', 'brand', 'description', 'location_lost', 'date_lost',
    ];

    public function created(LostItem $lostItem): void
    {
        RunItemMatching::dispatch($lostItem);
    }

    public function updated(LostItem $lostItem): void
    {
        if ($lostItem->wasChanged(self::MATCHING_FIELDS)) {
            RunItemMatching::dispatch($lostItem);
        }
    }
}
