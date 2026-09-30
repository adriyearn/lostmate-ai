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

    public function delete(User $user, FoundItem $foundItem): bool
    {
        return $user->id === $foundItem->user_id;
    }

    /**
     * Only the finder and admins (handled by the before() hook) may see hidden_details.
     */
    public function viewHiddenDetails(User $user, FoundItem $foundItem): bool
    {
        return $user->id === $foundItem->user_id;
    }
}
