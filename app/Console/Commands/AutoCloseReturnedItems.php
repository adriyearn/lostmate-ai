<?php

namespace App\Console\Commands;

use App\Enums\ItemStatus;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\ItemStatusService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:auto-close-returned-items')]
#[Description('Closes lost/found reports that have been in "returned" status for 7+ days.')]
class AutoCloseReturnedItems extends Command
{
    public function handle(ItemStatusService $statusService): int
    {
        $cutoff = now()->subDays(7);
        $closed = 0;

        foreach ([LostItem::class, FoundItem::class] as $model) {
            $items = $model::where('status', ItemStatus::Returned)
                ->where('updated_at', '<=', $cutoff)
                ->get();

            foreach ($items as $item) {
                $statusService->transition($item, ItemStatus::Closed);
                $closed++;
            }
        }

        $this->info("Auto-closed {$closed} report(s) that had been returned for 7+ days.");

        return self::SUCCESS;
    }
}
