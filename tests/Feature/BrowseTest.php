<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_search_matches_item_name_and_description(): void
    {
        $user = User::factory()->create();
        LostItem::factory()->create(['item_name' => 'Black Wallet']);
        LostItem::factory()->create(['item_name' => 'Blue Backpack']);

        $response = $this->actingAs($user)->get('/browse?tab=lost&q=Wallet');

        $response->assertOk();
        $response->assertSee('Black Wallet');
        $response->assertDontSee('Blue Backpack');
    }

    public function test_keyword_search_does_not_match_hidden_details(): void
    {
        $user = User::factory()->create();
        FoundItem::factory()->create([
            'item_name' => 'Mystery Bag',
            'hidden_details' => 'UniqueSecretPhrase123',
        ]);

        $response = $this->actingAs($user)->get('/browse?tab=found&q=UniqueSecretPhrase123');

        $response->assertOk();
        $response->assertDontSee('Mystery Bag');
    }

    public function test_hidden_details_are_never_rendered_on_the_browse_page(): void
    {
        $user = User::factory()->create();
        FoundItem::factory()->create([
            'item_name' => 'Mystery Bag',
            'hidden_details' => 'UniqueSecretPhrase123',
        ]);

        $response = $this->actingAs($user)->get('/browse?tab=found');

        $response->assertOk();
        $response->assertDontSee('UniqueSecretPhrase123');
    }

    public function test_category_filter_narrows_results(): void
    {
        $user = User::factory()->create();
        $wallets = Category::factory()->create(['name' => 'Wallets']);
        $bags = Category::factory()->create(['name' => 'Bags']);

        LostItem::factory()->create(['item_name' => 'Red Wallet', 'category_id' => $wallets->id]);
        LostItem::factory()->create(['item_name' => 'Green Bag', 'category_id' => $bags->id]);

        $response = $this->actingAs($user)->get('/browse?tab=lost&category_id='.$wallets->id);

        $response->assertSee('Red Wallet');
        $response->assertDontSee('Green Bag');
    }

    public function test_status_filter_narrows_results(): void
    {
        $user = User::factory()->create();
        LostItem::factory()->create(['item_name' => 'Open Item', 'status' => ItemStatus::Open]);
        LostItem::factory()->create(['item_name' => 'Closed Item', 'status' => ItemStatus::Closed]);

        $response = $this->actingAs($user)->get('/browse?tab=lost&status=closed');

        $response->assertSee('Closed Item');
        $response->assertDontSee('Open Item');
    }

    public function test_guests_cannot_browse(): void
    {
        $response = $this->get('/browse');

        $response->assertRedirect('/login');
    }
}
