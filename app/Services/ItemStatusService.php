<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\FoundItem;
use App\Models\LostItem;

/**
 * The single place status changes happen for lost/found items, per
 * CLAUDE.md's "Item statuses and allowed transitions" rules. Every status
 * write in the app - claims, matches, reports being closed - goes through
 * here so an invalid transition can never slip in through a controller.
 */
class ItemStatusService
{
    /**
     * Allowed from => [to, ...] transitions. "matched => returned" covers
     * the linked-lost-item cascade when a found item is confirmed returned
     * (lost items don't have their own "claimed" step).
     */
    protected const TRANSITIONS = [
        'open' => ['matched', 'closed'],
        'matched' => ['open', 'claimed', 'returned'],
        'claimed' => ['returned'],
        'returned' => ['closed'],
        'closed' => [],
    ];

    public function canTransition(LostItem|FoundItem $item, ItemStatus $to): bool
    {
        return in_array($to->value, self::TRANSITIONS[$item->status->value] ?? [], true);
    }

    /**
     * Apply a status transition. Throws if the transition isn't allowed.
     * A no-op if the item is already in the target status.
     */
    public function transition(LostItem|FoundItem $item, ItemStatus $to): void
    {
        if ($item->status === $to) {
            return;
        }

        if (! $this->canTransition($item, $to)) {
            throw new InvalidStatusTransitionException(sprintf(
                'Cannot transition %s #%d from "%s" to "%s".',
                $item::class,
                $item->id,
                $item->status->value,
                $to->value,
            ));
        }

        $attributes = ['status' => $to];

        if ($to === ItemStatus::Closed) {
            $attributes['closed_at'] = now();
        }

        $item->update($attributes);
    }

    /**
     * Apply a transition only if it's currently allowed; silently does
     * nothing otherwise. Used for cascades (e.g. reverting a linked lost
     * item to "open" after a claim is rejected) where the item may already
     * be in some other valid state and that's fine.
     */
    public function transitionIfPossible(LostItem|FoundItem $item, ItemStatus $to): void
    {
        if ($item->status === $to || $this->canTransition($item, $to)) {
            $this->transition($item, $to);
        }
    }
}
