<?php

namespace Tests\Feature;

use App\Enums\AiMatchStatus;
use App\Models\AiMatch;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMatchesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_lists_ai_matches_for_the_users_own_reports(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id, 'category_id' => $category->id, 'item_name' => 'My Lost Wallet']);
        $foundItem = FoundItem::factory()->create(['category_id' => $category->id, 'item_name' => 'Someone Found Wallet']);

        AiMatch::factory()->create([
            'lost_item_id' => $lostItem->id,
            'found_item_id' => $foundItem->id,
            'score' => 88,
            'reason' => 'both black wallets',
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Someone Found Wallet');
        $response->assertSee('88%');
        $response->assertSee('both black wallets');
    }

    public function test_other_peoples_matches_and_dismissed_matches_are_not_listed(): void
    {
        $viewer = User::factory()->create();
        $category = Category::factory()->create();

        AiMatch::factory()->create([
            'lost_item_id' => LostItem::factory()->create(['category_id' => $category->id])->id,
            'found_item_id' => FoundItem::factory()->create(['category_id' => $category->id, 'item_name' => 'Unrelated Found Thing'])->id,
        ]);

        $myLost = LostItem::factory()->create(['user_id' => $viewer->id, 'category_id' => $category->id]);
        AiMatch::factory()->create([
            'lost_item_id' => $myLost->id,
            'found_item_id' => FoundItem::factory()->create(['category_id' => $category->id, 'item_name' => 'Dismissed Found Thing'])->id,
            'status' => AiMatchStatus::Dismissed,
        ]);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        // Both found items still appear under "Recently found"; what matters
        // is that the matches section has nothing for this user.
        $response->assertOk();
        $response->assertSee('No possible matches yet.');
        $response->assertDontSee('may match:');
    }

    public function test_message_sending_is_rate_limited(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $conversation = \App\Models\Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);

        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($userA)->post(route('conversations.store-message', $conversation), ['body' => "msg {$i}"]);
        }

        $this->actingAs($userA)
            ->post(route('conversations.store-message', $conversation), ['body' => 'one too many'])
            ->assertStatus(429);
    }
}
