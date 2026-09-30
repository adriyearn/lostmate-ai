<?php

namespace App\Policies;

use App\Models\LostItem;
use App\Models\User;

class LostItemPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LostItem $lostItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, LostItem $lostItem): bool
    {
        return $user->id === $lostItem->user_id;
    }

    public function delete(User $user, LostItem $lostItem): bool
    {
        return $user->id === $lostItem->user_id;
    }

    /**
     * Only the reporter (or an admin, via before()) may view AI matches.
     */
    public function viewMatches(User $user, LostItem $lostItem): bool
    {
        return $user->id === $lostItem->user_id;
    }

    /**
     * Only admins (via before()) may manually re-run matching.
     */
    public function rerunMatching(User $user, LostItem $lostItem): bool
    {
        return false;
    }
}
