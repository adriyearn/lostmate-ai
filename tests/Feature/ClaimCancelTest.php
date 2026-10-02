<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_claimant_can_cancel_a_pending_claim_and_items_reopen(): void
    {
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        $lostItem = LostItem::factory()->create(['user_id' => $claimant->id, 'status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create([
            'found_item_id' => $foundItem->id,
            'claimant_id' => $claimant->id,
            'lost_item_id' => $lostItem->id,
            'status' => ClaimStatus::Pending,
        ]);

        $this->actingAs($claimant)->post(route('claims.cancel', $claim))->assertRedirect();

        $this->assertSame(ClaimStatus::Cancelled, $claim->fresh()->status);
        $this->assertSame(ItemStatus::Open, $foundItem->fresh()->status);
        $this->assertSame(ItemStatus::Open, $lostItem->fresh()->status);
    }

    public function test_cancelling_keeps_the_item_matched_while_other_claims_are_pending(): void
    {
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        $mine = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);
        Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $this->actingAs($mine->claimant)->post(route('claims.cancel', $mine));

        $this->assertSame(ItemStatus::Matched, $foundItem->fresh()->status);
    }

    public function test_only_the_claimant_can_cancel(): void
    {
        $claim = Claim::factory()->create(['status' => ClaimStatus::Pending]);

        $this->actingAs(User::factory()->create())
            ->post(route('claims.cancel', $claim))
            ->assertForbidden();

        $this->assertSame(ClaimStatus::Pending, $claim->fresh()->status);
    }

    public function test_an_approved_claim_cannot_be_cancelled(): void
    {
        $claim = Claim::factory()->create(['status' => ClaimStatus::Approved]);

        $this->actingAs($claim->claimant)
            ->post(route('claims.cancel', $claim))
            ->assertSessionHas('error');

        $this->assertSame(ClaimStatus::Approved, $claim->fresh()->status);
    }
}
