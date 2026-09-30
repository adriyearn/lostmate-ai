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
     * Approve, reject, or confirm-returned: only the finder (the found
     * item's reporter).
     */
    public function review(User $user, Claim $claim): bool
    {
        return $user->id === $claim->foundItem->user_id;
    }
}
