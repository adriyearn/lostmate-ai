<?php

namespace App\Services;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Lets a user permanently delete their own account and personal data
 * (Data Privacy Act of 2012: the right to have personal data erased).
 *
 * The database removes the user's reports, claims, messages, and profile
 * automatically (cascade delete). This service adds the parts the
 * database can't do: safety checks, and deleting the uploaded photos.
 */
class AccountDeletionService
{
    public function __construct(protected PhotoStorage $photos) {}

    /**
     * Why this account can't be deleted right now, or null if it can.
     */
    public function blockedReason(User $user): ?string
    {
        if ($user->isAdmin()) {
            return 'Admin accounts can\'t be deleted here (it would also erase the admin audit log). Ask another admin to change your role first.';
        }

        $active = [ClaimStatus::Pending, ClaimStatus::Approved];

        if ($user->claims()->whereIn('status', $active)->exists()) {
            return 'You have a claim that is still pending or approved. Cancel it or finish the handover first.';
        }

        $claimsOnTheirItems = Claim::whereIn('status', $active)
            ->whereIn('found_item_id', $user->foundItems()->withTrashed()->select('id'))
            ->exists();

        if ($claimsOnTheirItems) {
            return 'Someone has a pending or approved claim on an item you found. Resolve it first so they aren\'t left waiting.';
        }

        if ($user->foundItems()->whereNotNull('surrendered_at')->whereNotIn('status', ['returned', 'closed'])->exists()) {
            return 'An item you found is being held at the office. Wait until it is returned or closed.';
        }

        return null;
    }

    public function delete(User $user): void
    {
        if ($reason = $this->blockedReason($user)) {
            throw new RuntimeException($reason);
        }

        // Collect every photo path first; the rows disappear with the user.
        $paths = collect()
            ->merge($user->lostItems()->withTrashed()->with('images')->get()->flatMap->images->pluck('path'))
            ->merge($user->foundItems()->withTrashed()->with('images')->get()->flatMap->images->pluck('path'))
            ->merge($user->claims()->pluck('proof_image_path'))
            ->merge(Claim::whereIn('found_item_id', $user->foundItems()->withTrashed()->select('id'))->pluck('proof_image_path'))
            ->push($user->profile?->avatar_path)
            ->filter()
            ->unique();

        DB::transaction(function () use ($user) {
            // item_images uses a polymorphic link (no foreign key), so it
            // isn't removed by the cascade - delete those rows ourselves.
            foreach ([$user->lostItems()->withTrashed()->get(), $user->foundItems()->withTrashed()->get()] as $items) {
                foreach ($items as $item) {
                    $item->images()->delete();
                }
            }

            $user->delete();
        });

        // Files last: if the database step failed, nothing is lost.
        $paths->each(fn ($path) => $this->photos->delete($path));
    }
}
