<?php

namespace Tests\Feature;

use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_missing_item_shows_the_custom_404_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/lost-items/99999');

        $response->assertStatus(404);
        $response->assertSee('Page not found');
    }

    public function test_a_forbidden_action_shows_the_custom_403_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('lost-items.edit', $lostItem));

        $response->assertStatus(403);
        $response->assertSee("You don't have access to this");
    }
}
