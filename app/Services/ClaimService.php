<?php

namespace App\Services;

use App\Enums\AiMatchStatus;
use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\User;
use App\Notifications\ClaimApproved;
use App\Notifications\ClaimRejected;
use App\Notifications\ClaimSubmitted;
use App\Notifications\ItemReturned;

/**
 * Orchestrates the claim/recovery workflow described in CLAUDE.md. Every
 * status change goes through ItemStatusService; this class handles the
 * surrounding business rules (one pending claim per claimant, auto-rejecting
 * competing claims, notifications).
 */
class ClaimService
{
    public function __construct(protected ItemStatusService $statusService) {}

    /**
     * Rules: one pending claim per claimant per found item; a claimant
     * cannot claim their own found item; only one claim per item can be
     * approved.
     */
    public function submit(User $claimant, FoundItem $foundItem, array $data): Claim
    {
        if ($claimant->id === $foundItem->user_id) {
            throw new InvalidStatusTransitionException("You can't claim your own found item report.");
        }

        $hasPendingClaim = $foundItem->claims()
            ->where('claimant_id', $claimant->id)
            ->where('status', ClaimStatus::Pending)
            ->exists();

        if ($hasPendingClaim) {
            throw new InvalidStatusTransitionException('You already have a pending claim on this item.');
        }

        $claim = $foundItem->claims()->create([
            'claimant_id' => $claimant->id,
            'lost_item_id' => $data['lost_item_id'] ?? null,
            'ai_match_id' => $data['ai_match_id'] ?? null,
            'identifying_details' => $data['identifying_details'],
            'proof_image_path' => $data['proof_image_path'] ?? null,
            'status' => ClaimStatus::Pending,
        ]);

        $this->statusService->transitionIfPossible($foundItem, ItemStatus::Matched);

        if ($claim->lost_item_id) {
            $claim->loadMissing('lostItem');
            $this->statusService->transitionIfPossible($claim->lostItem, ItemStatus::Matched);
        }

        if ($claim->ai_match_id) {
            $claim->aiMatch()->update(['status' => AiMatchStatus::Confirmed]);
        }

        $foundItem->user->notify(new ClaimSubmitted($claim));

        // Items held at the office are reviewed by the staff member who received them.
        if ($foundItem->isAtOffice() && $foundItem->surrenderedTo) {
            $foundItem->surrenderedTo->notify(new ClaimSubmitted($claim));
        }

        return $claim;
    }

    /**
     * Approving a claim: claim -> approved, found item -> claimed, other
     * pending claims on the item -> rejected automatically.
     */
    public function approve(Claim $claim, ?string $response): void
    {
        $this->guardPending($claim);

        $claim->update([
            'status' => ClaimStatus::Approved,
            'finder_response' => $response,
            'reviewed_at' => now(),
        ]);

        $claim->loadMissing('foundItem', 'claimant');

        $this->statusService->transition($claim->foundItem, ItemStatus::Claimed);

        $claim->foundItem->claims()
            ->where('id', '!=', $claim->id)
            ->where('status', ClaimStatus::Pending)
            ->get()
            ->each(function (Claim $otherClaim) {
                $this->rejectCompetingClaim($otherClaim);
            });

        $claim->claimant->notify(new ClaimApproved($claim));
    }

    /**
     * Finder rejects a claim with a required response message.
     */
    public function reject(Claim $claim, string $response): void
    {
        $this->guardPending($claim);

        $claim->update([
            'status' => ClaimStatus::Rejected,
            'finder_response' => $response,
            'reviewed_at' => now(),
        ]);

        $this->revertItemsIfNoActiveClaims($claim);

        $claim->loadMissing('claimant');
        $claim->claimant->notify(new ClaimRejected($claim));
    }

    /**
     * The claimant withdraws their own pending claim (e.g. they claimed the
     * wrong item). Items go back to "open" the same way as a rejection, but
     * nobody is notified - the finder simply sees the claim as cancelled.
     */
    public function cancel(Claim $claim): void
    {
        if ($claim->status !== ClaimStatus::Pending) {
            throw new InvalidStatusTransitionException('Only a pending claim can be cancelled.');
        }

        $claim->update(['status' => ClaimStatus::Cancelled]);

        $this->revertItemsIfNoActiveClaims($claim);
    }

    /**
     * Auto-rejection of a competing claim when another one is approved -
     * same as reject() but without a finder-authored response message.
     */
    protected function rejectCompetingClaim(Claim $claim): void
    {
        $claim->update([
            'status' => ClaimStatus::Rejected,
            'finder_response' => 'Automatically rejected - another claim on this item was approved.',
            'reviewed_at' => now(),
        ]);

        $this->revertLinkedLostItemIfNoActiveClaims($claim);

        $claim->loadMissing('claimant');
        $claim->claimant->notify(new ClaimRejected($claim));
    }

    /**
     * Finder confirms the item was handed over: claim -> completed, found
     * item and linked lost item -> returned.
     */
    public function confirmReturned(Claim $claim): void
    {
        if ($claim->status !== ClaimStatus::Approved) {
            throw new InvalidStatusTransitionException('Only an approved claim can be confirmed as returned.');
        }

        $claim->update([
            'status' => ClaimStatus::Completed,
            'completed_at' => now(),
        ]);

        $claim->loadMissing('foundItem', 'lostItem', 'claimant');

        $this->statusService->transition($claim->foundItem, ItemStatus::Returned);

        if ($claim->lostItem) {
            $this->statusService->transitionIfPossible($claim->lostItem, ItemStatus::Returned);
        }

        $claim->claimant->notify(new ItemReturned($claim));
    }

    protected function guardPending(Claim $claim): void
    {
        if ($claim->status !== ClaimStatus::Pending) {
            throw new InvalidStatusTransitionException('Only a pending claim can be approved or rejected.');
        }
    }

    /**
     * After a rejection, put the found item back to "open" only if it has
     * no other pending claims left - other claimants are still waiting.
     */
    protected function revertItemsIfNoActiveClaims(Claim $claim): void
    {
        $claim->loadMissing('foundItem');

        $stillHasActiveClaims = $claim->foundItem->claims()
            ->where('status', ClaimStatus::Pending)
            ->exists();

        if (! $stillHasActiveClaims) {
            $this->statusService->transitionIfPossible($claim->foundItem, ItemStatus::Open);
        }

        $this->revertLinkedLostItemIfNoActiveClaims($claim);
    }

    /**
     * Revert a rejected claim's linked lost item back to "open", but only
     * if no other pending/approved claim still references it.
     */
    protected function revertLinkedLostItemIfNoActiveClaims(Claim $claim): void
    {
        if (! $claim->lost_item_id) {
            return;
        }

        $claim->loadMissing('lostItem');

        $stillReferenced = Claim::where('lost_item_id', $claim->lost_item_id)
            ->whereIn('status', [ClaimStatus::Pending, ClaimStatus::Approved])
            ->exists();

        if (! $stillReferenced) {
            $this->statusService->transitionIfPossible($claim->lostItem, ItemStatus::Open);
        }
    }
}
