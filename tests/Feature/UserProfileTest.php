<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Enums\UserRole;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_another_users_public_profile(): void
    {
        $maria = User::factory()->create(['name' => 'Maria Santos']);
        $maria->profile->update(['department' => 'CCS', 'course_or_position' => 'BSIT', 'bio' => 'Hi, I help find things!']);
        LostItem::factory()->create(['user_id' => $maria->id, 'item_name' => 'Blue umbrella', 'status' => ItemStatus::Open]);
        FoundItem::factory()->create(['user_id' => $maria->id, 'item_name' => 'Old returned bag', 'status' => ItemStatus::Closed]);

        $this->actingAs(User::factory()->create())
            ->get(route('users.show', $maria))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('BSIT')
            ->assertSee('Hi, I help find things!')
            ->assertSee('Blue umbrella')
            ->assertDontSee('Old returned bag');
    }

    public function test_email_and_contact_number_are_never_shown(): void
    {
        $maria = User::factory()->create(['email' => 'maria.secret@school.test']);
        $maria->profile->update(['contact_number' => '09171234567']);

        $this->actingAs(User::factory()->create())
            ->get(route('users.show', $maria))
            ->assertOk()
            ->assertDontSee('maria.secret@school.test')
            ->assertDontSee('09171234567');
    }

    public function test_deactivated_profiles_are_hidden_except_from_admins(): void
    {
        $banned = User::factory()->create(['is_active' => false]);

        $this->actingAs(User::factory()->create())->get(route('users.show', $banned))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))->get(route('users.show', $banned))->assertOk();
    }

    public function test_guests_must_log_in_to_view_profiles(): void
    {
        $this->get(route('users.show', User::factory()->create()))->assertRedirect(route('login'));
    }

    public function test_item_pages_link_to_the_reporters_profile(): void
    {
        $foundItem = FoundItem::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('found-items.show', $foundItem))
            ->assertSee(route('users.show', $foundItem->user), false);
    }
}
