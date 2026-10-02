<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\UserRole;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\User;
use App\Notifications\ClaimSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OfficeCustodyTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_can_mark_an_item_received_at_the_office(): void
    {
        $admin = $this->admin();
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Open]);

        $this->actingAs($admin)->post(route('admin.office.receive', $foundItem))->assertSessionHas('success');

        $foundItem->refresh();
        $this->assertTrue($foundItem->isAtOffice());
        $this->assertSame($admin->id, $foundItem->surrendered_to);
        $this->assertSame(config('lostmate.office.name'), $foundItem->current_location);
        $this->assertDatabaseHas('admin_logs', ['admin_id' => $admin->id, 'action' => 'found_item.received_at_office']);
    }

    public function test_students_cannot_mark_items_received(): void
    {
        $foundItem = FoundItem::factory()->create();

        $this->actingAs($foundItem->user)->post(route('admin.office.receive', $foundItem))->assertForbidden();
        $this->assertFalse($foundItem->fresh()->isAtOffice());
    }

    public function test_custody_fields_cannot_be_set_through_the_edit_form(): void
    {
        $foundItem = FoundItem::factory()->create();

        $foundItem->fill(['surrendered_at' => now(), 'surrendered_to' => $foundItem->user_id])->save();

        $this->assertFalse($foundItem->fresh()->isAtOffice());
    }

    public function test_after_drop_off_the_finder_can_no_longer_review_claims_or_delete(): void
    {
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);
        app(\App\Services\OfficeCustodyService::class)->receive($foundItem, $this->admin());

        $finder = $foundItem->user;
        $this->actingAs($finder)->post(route('claims.approve', $claim), ['response' => 'ok'])->assertForbidden();
        $this->actingAs($finder)->delete(route('found-items.destroy', $foundItem))->assertForbidden();
        $this->actingAs($finder)->post(route('found-items.withdraw', $foundItem))->assertForbidden();
    }

    public function test_office_staff_handle_claims_on_items_at_the_office(): void
    {
        $admin = $this->admin();
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);
        app(\App\Services\OfficeCustodyService::class)->receive($foundItem, $admin);

        $this->actingAs($admin)->post(route('claims.approve', $claim), ['response' => 'Verified at the desk.']);

        $this->assertSame(ClaimStatus::Approved, $claim->fresh()->status);
    }

    public function test_new_claims_on_office_items_notify_the_staff_member(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Open]);
        app(\App\Services\OfficeCustodyService::class)->receive($foundItem, $admin);

        $this->actingAs(User::factory()->create())->post(route('claims.store', $foundItem), [
            'identifying_details' => 'Has my initials inside.',
        ]);

        Notification::assertSentTo($admin, ClaimSubmitted::class);
        Notification::assertSentTo($foundItem->user, ClaimSubmitted::class);
    }

    public function test_unclaimed_list_only_shows_old_open_items(): void
    {
        config(['lostmate.unclaimed_after_days' => 60]);
        FoundItem::factory()->create(['item_name' => 'Old umbrella', 'status' => ItemStatus::Open, 'date_found' => now()->subDays(90)]);
        FoundItem::factory()->create(['item_name' => 'Fresh tumbler', 'status' => ItemStatus::Open, 'date_found' => now()->subDays(5)]);
        FoundItem::factory()->create(['item_name' => 'Claimed laptop', 'status' => ItemStatus::Matched, 'date_found' => now()->subDays(90)]);

        $this->actingAs($this->admin())->get(route('admin.office.index', ['tab' => 'unclaimed']))
            ->assertOk()
            ->assertSee('Old umbrella')
            ->assertDontSee('Fresh tumbler')
            ->assertDontSee('Claimed laptop');
    }

    public function test_admin_can_close_an_unclaimed_item_with_a_note(): void
    {
        $admin = $this->admin();
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Open, 'date_found' => now()->subDays(90)]);

        $this->actingAs($admin)
            ->post(route('admin.office.close-unclaimed', $foundItem), ['note' => 'Donated to the school clinic'])
            ->assertSessionHas('success');

        $this->assertSame(ItemStatus::Closed, $foundItem->fresh()->status);
        $this->assertDatabaseHas('admin_logs', [
            'action' => 'found_item.unclaimed_closed',
            'target_id' => $foundItem->id,
        ]);
    }

    public function test_recent_items_cannot_be_closed_as_unclaimed(): void
    {
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Open, 'date_found' => now()->subDays(3)]);

        $this->actingAs($this->admin())
            ->post(route('admin.office.close-unclaimed', $foundItem), ['note' => 'Too early'])
            ->assertSessionHas('error');

        $this->assertSame(ItemStatus::Open, $foundItem->fresh()->status);
    }

    public function test_a_note_is_required_to_close_an_unclaimed_item(): void
    {
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Open, 'date_found' => now()->subDays(90)]);

        $this->actingAs($this->admin())
            ->post(route('admin.office.close-unclaimed', $foundItem), ['note' => ''])
            ->assertSessionHasErrors('note');
    }
}
