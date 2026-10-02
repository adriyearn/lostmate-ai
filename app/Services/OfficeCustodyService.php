<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * The lost & found office side of the system:
 *  - receiving items that finders turn in at the office (#7), and
 *  - the unclaimed item policy: closing items nobody claimed after a set
 *    number of days, e.g. donated or disposed (#8).
 */
class OfficeCustodyService
{
    public function __construct(
        protected ItemStatusService $statusService,
        protected AdminLogger $adminLogger,
    ) {}

    /**
     * Office staff record that the finder handed the item in at the office.
     */
    public function receive(FoundItem $foundItem, User $staff): void
    {
        if ($foundItem->isAtOffice()) {
            throw new InvalidStatusTransitionException('This item is already at the office.');
        }

        if (in_array($foundItem->status, [ItemStatus::Returned, ItemStatus::Closed], true)) {
            throw new InvalidStatusTransitionException('This item was already returned or closed.');
        }

        $foundItem->forceFill([
            'surrendered_at' => now(),
            'surrendered_to' => $staff->id,
            'current_location' => config('lostmate.office.name'),
        ])->save();

        $this->adminLogger->log($staff, 'found_item.received_at_office', $foundItem,
            "Received \"{$foundItem->item_name}\" at ".config('lostmate.office.name').'.');
    }

    /**
     * Found items nobody has claimed (still "open") for longer than the
     * policy allows, oldest first.
     */
    public function unclaimedQuery(): Builder
    {
        return FoundItem::query()
            ->where('status', ItemStatus::Open)
            ->whereDate('date_found', '<=', now()->subDays(config('lostmate.unclaimed_after_days')))
            ->orderBy('date_found');
    }

    /**
     * Close an unclaimed item as donated/disposed. The note (what happened
     * to the item) is required and saved in the admin log for the record.
     */
    public function closeUnclaimed(FoundItem $foundItem, User $staff, string $note): void
    {
        $isUnclaimed = $this->unclaimedQuery()->whereKey($foundItem->id)->exists();

        if (! $isUnclaimed) {
            throw new InvalidStatusTransitionException(
                'Only open items unclaimed for '.config('lostmate.unclaimed_after_days').'+ days can be closed this way.'
            );
        }

        $this->statusService->transition($foundItem, ItemStatus::Closed);

        $this->adminLogger->log($staff, 'found_item.unclaimed_closed', $foundItem,
            "Closed unclaimed \"{$foundItem->item_name}\": {$note}");
    }
}
