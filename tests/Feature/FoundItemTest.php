<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FoundItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_report_a_found_item_with_hidden_details(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/found-items', [
            'category_id' => $category->id,
            'item_name' => 'Black Wallet',
            'description' => 'A black wallet found near the library.',
            'hidden_details' => 'Contains a school ID and a jeepney card',
            'location_found' => 'Library entrance',
            'date_found' => now()->toDateString(),
            'images' => [UploadedFile::fake()->image('wallet.jpg')],
        ]);

        $foundItem = FoundItem::first();

        $response->assertRedirect(route('found-items.show', $foundItem));
        $this->assertSame('Contains a school ID and a jeepney card', $foundItem->hidden_details);
    }

    public function test_hidden_details_are_not_visible_to_other_users(): void
    {
        $finder = User::factory()->create();
        $otherUser = User::factory()->create();
        $foundItem = FoundItem::factory()->create([
            'user_id' => $finder->id,
            'hidden_details' => 'Secret identifying mark',
        ]);

        $response = $this->actingAs($otherUser)->get(route('found-items.show', $foundItem));

        $response->assertOk();
        $response->assertDontSee('Secret identifying mark');
    }

    public function test_hidden_details_are_visible_to_the_finder(): void
    {
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create([
            'user_id' => $finder->id,
            'hidden_details' => 'Secret identifying mark',
        ]);

        $response = $this->actingAs($finder)->get(route('found-items.show', $foundItem));

        $response->assertOk();
        $response->assertSee('Secret identifying mark');
    }

    public function test_hidden_details_are_visible_to_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create([
            'user_id' => $finder->id,
            'hidden_details' => 'Secret identifying mark',
        ]);

        $response = $this->actingAs($admin)->get(route('found-items.show', $foundItem));

        $response->assertOk();
        $response->assertSee('Secret identifying mark');
    }

    public function test_a_user_cannot_delete_another_users_found_item(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('found-items.destroy', $foundItem));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($foundItem);
    }
}
