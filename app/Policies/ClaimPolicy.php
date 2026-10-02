<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\User;

class ClaimPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Anyone except the found item's own reporter may submit a claim on it.
     */
    public function create(User $user, FoundItem $foundItem): bool
    {
        return $user->id !== $foundItem->user_id;
    }

    /**
     * Only the claimant can cancel their own claim.
     */
    public function cancel(User $user, Claim $claim): bool
    {
        return $user->id === $claim->claimant_id;
    }

    /**
     * Approve, reject, or confirm-returned: the finder (the found item's
     * reporter) - unless the item was turned in at the office, in which
     * case only office staff (admins, via before()) handle it.
     */
    public function review(User $user, Claim $claim): bool
    {
        return $user->id === $claim->foundItem->user_id && ! $claim->foundItem->isAtOffice();
    }
}
