<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\AiMatch;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use App\Notifications\ClaimApproved;
use App\Notifications\ClaimRejected;
use App\Notifications\ClaimSubmitted;
use App\Notifications\ItemReturned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_submit_a_claim_and_it_moves_the_item_to_matched(): void
    {
        $finder = User::factory()->create();
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id, 'status' => ItemStatus::Open]);

        $response = $this->actingAs($claimant)->post(route('claims.store', $foundItem), [
            'identifying_details' => 'It has a small scratch on the back.',
        ]);

        $response->assertRedirect(route('my-claims.index'));

        $this->assertDatabaseHas('claims', [
            'found_item_id' => $foundItem->id,
            'claimant_id' => $claimant->id,
            'status' => ClaimStatus::Pending->value,
        ]);

        $this->assertSame(ItemStatus::Matched, $foundItem->fresh()->status);
        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $finder->id)
            ->where('type', ClaimSubmitted::class)->count());
    }

    public function test_a_user_cannot_claim_their_own_found_item(): void
    {
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id]);

        $response = $this->actingAs($finder)->get(route('claims.create', $foundItem));
        $response->assertForbidden();

        $response = $this->actingAs($finder)->post(route('claims.store', $foundItem), [
            'identifying_details' => 'Mine.',
        ]);
        $response->assertForbidden();
    }

    public function test_a_claimant_cannot_submit_two_pending_claims_on_the_same_item(): void
    {
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create();

        $this->actingAs($claimant)->post(route('claims.store', $foundItem), [
            'identifying_details' => 'First attempt.',
        ]);

        $response = $this->actingAs($claimant)->post(route('claims.store', $foundItem), [
            'identifying_details' => 'Second attempt.',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Claim::where('claimant_id', $claimant->id)->count());
    }

    public function test_approving_a_claim_marks_item_claimed_and_auto_rejects_other_pending_claims(): void
    {
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id, 'status' => ItemStatus::Matched]);

        $claimA = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);
        $claimB = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $response = $this->actingAs($finder)->post(route('claims.approve', $claimA), [
            'response' => 'Looks right, come by the office.',
        ]);

        $response->assertRedirect();

        $this->assertSame(ClaimStatus::Approved, $claimA->fresh()->status);
        $this->assertSame(ClaimStatus::Rejected, $claimB->fresh()->status);
        $this->assertSame(ItemStatus::Claimed, $foundItem->fresh()->status);

        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $claimA->claimant_id)
            ->where('type', ClaimApproved::class)->count());
        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $claimB->claimant_id)
            ->where('type', ClaimRejected::class)->count());
    }

    public function test_rejecting_the_only_pending_claim_reverts_the_item_to_open(): void
    {
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id, 'status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $this->actingAs($finder)->post(route('claims.reject', $claim), [
            'response' => 'Details did not match.',
        ]);

        $this->assertSame(ClaimStatus::Rejected, $claim->fresh()->status);
        $this->assertSame(ItemStatus::Open, $foundItem->fresh()->status);
    }

    public function test_rejecting_one_of_several_pending_claims_does_not_reopen_the_item(): void
    {
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id, 'status' => ItemStatus::Matched]);
        $claimA = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);
        Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $this->actingAs($finder)->post(route('claims.reject', $claimA), [
            'response' => 'Not a match.',
        ]);

        $this->assertSame(ItemStatus::Matched, $foundItem->fresh()->status);
    }

    public function test_a_rejected_claims_linked_lost_item_reverts_to_open(): void
    {
        $finder = User::factory()->create();
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id, 'status' => ItemStatus::Matched]);
        $lostItem = LostItem::factory()->create(['user_id' => $claimant->id, 'status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create([
            'found_item_id' => $foundItem->id,
            'claimant_id' => $claimant->id,
            'lost_item_id' => $lostItem->id,
            'status' => ClaimStatus::Pending,
        ]);

        $this->actingAs($finder)->post(route('claims.reject', $claim), ['response' => 'No match.']);

        $this->assertSame(ItemStatus::Open, $lostItem->fresh()->status);
    }

    public function test_confirming_returned_completes_the_claim_and_returns_both_items(): void
    {
        $finder = User::factory()->create();
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id, 'status' => ItemStatus::Claimed]);
        $lostItem = LostItem::factory()->create(['user_id' => $claimant->id, 'status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create([
            'found_item_id' => $foundItem->id,
            'claimant_id' => $claimant->id,
            'lost_item_id' => $lostItem->id,
            'status' => ClaimStatus::Approved,
        ]);

        $response = $this->actingAs($finder)->post(route('claims.confirm-returned', $claim));

        $response->assertRedirect();
        $this->assertSame(ClaimStatus::Completed, $claim->fresh()->status);
        $this->assertNotNull($claim->fresh()->completed_at);
        $this->assertSame(ItemStatus::Returned, $foundItem->fresh()->status);
        $this->assertSame(ItemStatus::Returned, $lostItem->fresh()->status);

        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $claimant->id)
            ->where('type', ItemReturned::class)->count());
    }

    public function test_only_the_finder_can_approve_or_reject_a_claim(): void
    {
        $finder = User::factory()->create();
        $outsider = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id]);
        $claim = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $this->actingAs($outsider)->post(route('claims.approve', $claim), ['response' => 'x'])
            ->assertForbidden();
        $this->actingAs($outsider)->post(route('claims.reject', $claim), ['response' => 'x'])
            ->assertForbidden();
    }

    public function test_submitting_a_claim_confirms_the_linked_ai_match(): void
    {
        $claimant = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $claimant->id]);
        $foundItem = FoundItem::factory()->create();
        $aiMatch = AiMatch::factory()->create([
            'lost_item_id' => $lostItem->id,
            'found_item_id' => $foundItem->id,
        ]);

        $this->actingAs($claimant)->post(route('claims.store', $foundItem), [
            'identifying_details' => 'Matches the AI suggestion.',
            'lost_item_id' => $lostItem->id,
            'ai_match_id' => $aiMatch->id,
        ]);

        $this->assertSame('confirmed', $aiMatch->fresh()->status->value);
    }

    public function test_owner_can_withdraw_an_open_lost_item(): void
    {
        $owner = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id, 'status' => ItemStatus::Open]);

        $this->actingAs($owner)->post(route('lost-items.withdraw', $lostItem))->assertRedirect();

        $this->assertSame(ItemStatus::Closed, $lostItem->fresh()->status);
    }

    public function test_owner_can_close_a_returned_lost_item(): void
    {
        $owner = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id, 'status' => ItemStatus::Returned]);

        $this->actingAs($owner)->post(route('lost-items.received', $lostItem))->assertRedirect();

        $this->assertSame(ItemStatus::Closed, $lostItem->fresh()->status);
    }
}
