<?php

namespace App\Policies;

use App\Models\AiMatch;
use App\Models\User;

class AiMatchPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Either reporter involved in the match may dismiss it or start a
     * conversation about it.
     */
    public function manage(User $user, AiMatch $aiMatch): bool
    {
        return $user->id === $aiMatch->lostItem->user_id || $user->id === $aiMatch->foundItem->user_id;
    }
}
