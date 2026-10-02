<?php

namespace App\Policies;

use App\Models\FoundItem;
use App\Models\User;

class FoundItemPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FoundItem $foundItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FoundItem $foundItem): bool
    {
        return $user->id === $foundItem->user_id;
    }

    /**
     * Once the item is at the office, the finder can no longer delete or
     * withdraw the report - the office is holding the physical item.
     */
    public function delete(User $user, FoundItem $foundItem): bool
    {
        return $user->id === $foundItem->user_id && ! $foundItem->isAtOffice();
    }

    /**
     * Only the finder and admins (handled by the before() hook) may see hidden_details.
     */
    public function viewHiddenDetails(User $user, FoundItem $foundItem): bool
    {
        return $user->id === $foundItem->user_id;
    }

    /**
     * Only the reporter (or an admin, via before()) may view AI matches.
     */
    public function viewMatches(User $user, FoundItem $foundItem): bool
    {
        return $user->id === $foundItem->user_id;
    }

    /**
     * Only admins (via before()) may manually re-run matching.
     */
    public function rerunMatching(User $user, FoundItem $foundItem): bool
    {
        return false;
    }
}
