<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Any signed-in user may view another user's public profile, unless
     * that account was deactivated (then only admins can still see it).
     */
    public function viewProfile(User $viewer, User $user): bool
    {
        return $user->is_active || $viewer->isAdmin();
    }
}
