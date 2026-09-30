<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LostItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_report_a_lost_item_with_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/lost-items', [
            'category_id' => $category->id,
            'item_name' => 'Black Wallet',
            'color' => 'Black',
            'description' => 'A black leather wallet.',
            'location_lost' => 'Library',
            'date_lost' => now()->toDateString(),
            'images' => [
                UploadedFile::fake()->image('wallet1.jpg'),
                UploadedFile::fake()->image('wallet2.jpg'),
            ],
        ]);

        $lostItem = LostItem::first();

        $response->assertRedirect(route('lost-items.show', $lostItem));
        $this->assertSame($user->id, $lostItem->user_id);
        $this->assertCount(2, $lostItem->images);
        Storage::disk('public')->assertExists($lostItem->images->first()->path);
    }

    public function test_reporting_a_lost_item_rejects_more_than_three_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/lost-items', [
            'category_id' => $category->id,
            'item_name' => 'Black Wallet',
            'description' => 'A black leather wallet.',
            'location_lost' => 'Library',
            'date_lost' => now()->toDateString(),
            'images' => [
                UploadedFile::fake()->image('1.jpg'),
                UploadedFile::fake()->image('2.jpg'),
                UploadedFile::fake()->image('3.jpg'),
                UploadedFile::fake()->image('4.jpg'),
            ],
        ]);

        $response->assertSessionHasErrors('images');
        $this->assertSame(0, LostItem::count());
    }

    public function test_a_user_cannot_edit_another_users_lost_item(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('lost-items.edit', $lostItem));

        $response->assertForbidden();
    }

    public function test_a_user_cannot_delete_another_users_lost_item(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('lost-items.destroy', $lostItem));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($lostItem);
    }

    public function test_the_owner_can_delete_their_own_lost_item(): void
    {
        $owner = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)->delete(route('lost-items.destroy', $lostItem));

        $response->assertRedirect(route('my-reports.index'));
        $this->assertSoftDeleted($lostItem);
    }

    public function test_an_admin_can_edit_any_lost_item(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($admin)->get(route('lost-items.edit', $lostItem));

        $response->assertOk();
    }
}
