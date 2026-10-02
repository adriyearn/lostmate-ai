<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickupCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function approvedClaim(): Claim
    {
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Claimed]);

        return Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Approved]);
    }

    public function test_pickup_code_is_six_digits_and_stable_per_claim(): void
    {
        $claim = $this->approvedClaim();
        $other = $this->approvedClaim();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $claim->pickupCode());
        $this->assertSame($claim->pickupCode(), $claim->fresh()->pickupCode());
        $this->assertNotSame($claim->pickupCode(), $other->pickupCode());
    }

    public function test_wrong_code_does_not_complete_the_handover(): void
    {
        $claim = $this->approvedClaim();
        $wrong = $claim->pickupCode() === '000000' ? '111111' : '000000';

        $this->actingAs($claim->foundItem->user)
            ->post(route('claims.confirm-returned', $claim), ['pickup_code' => $wrong])
            ->assertSessionHasErrors('pickup_code');

        $this->assertSame(ClaimStatus::Approved, $claim->fresh()->status);
        $this->assertSame(ItemStatus::Claimed, $claim->foundItem->fresh()->status);
    }

    public function test_missing_code_is_rejected(): void
    {
        $claim = $this->approvedClaim();

        $this->actingAs($claim->foundItem->user)
            ->post(route('claims.confirm-returned', $claim))
            ->assertSessionHasErrors('pickup_code');

        $this->assertSame(ClaimStatus::Approved, $claim->fresh()->status);
    }

    public function test_correct_code_completes_the_handover(): void
    {
        $claim = $this->approvedClaim();

        $this->actingAs($claim->foundItem->user)
            ->post(route('claims.confirm-returned', $claim), ['pickup_code' => $claim->pickupCode()])
            ->assertSessionHasNoErrors();

        $this->assertSame(ClaimStatus::Completed, $claim->fresh()->status);
        $this->assertSame(ItemStatus::Returned, $claim->foundItem->fresh()->status);
    }

    public function test_only_the_claimant_sees_the_code(): void
    {
        $claim = $this->approvedClaim();

        $this->actingAs($claim->claimant)->get(route('my-claims.index'))
            ->assertSee($claim->pickupCode());

        $this->actingAs($claim->foundItem->user)->get(route('found-items.claims', $claim->foundItem))
            ->assertOk()
            ->assertDontSee($claim->pickupCode());
    }

    public function test_someone_other_than_the_finder_cannot_confirm_even_with_the_code(): void
    {
        $claim = $this->approvedClaim();

        $this->actingAs(User::factory()->create())
            ->post(route('claims.confirm-returned', $claim), ['pickup_code' => $claim->pickupCode()])
            ->assertForbidden();
    }
}
