<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Models\User;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * A user's public profile: photo, name, school info, bio, and their
     * open reports. Email and contact number are never shown here.
     */
    public function show(User $user): View
    {
        $this->authorize('viewProfile', $user);

        $user->load('profile');

        // Only reports other people can still act on (not returned/closed).
        $active = [ItemStatus::Open, ItemStatus::Matched];

        return view('users.show', [
            'user' => $user,
            'lostItems' => $user->lostItems()->whereIn('status', $active)
                ->with(['category', 'images'])->latest()->take(6)->get(),
            'foundItems' => $user->foundItems()->whereIn('status', $active)
                ->with(['category', 'images'])->latest()->take(6)->get(),
            'returnedCount' => $user->foundItems()->whereIn('status', [ItemStatus::Returned, ItemStatus::Closed])
                ->whereHas('claims', fn ($q) => $q->whereNotNull('completed_at'))->count(),
        ]);
    }
}
