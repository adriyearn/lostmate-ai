<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression: a report saved with a time is stored as "12:30:00". Editing
 * it used to fail validation (only "12:30" was accepted), so photos added
 * while editing were silently rejected.
 */
class ItemTimeEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_lost_item_with_a_time_can_be_edited_and_get_a_photo(): void
    {
        Storage::fake('public');
        $lostItem = LostItem::factory()->create(['time_lost' => '12:30']);

        $this->actingAs($lostItem->user)->get(route('lost-items.edit', $lostItem))
            ->assertSee('value="12:30"', false);

        $this->actingAs($lostItem->user)->put(route('lost-items.update', $lostItem), [
            'category_id' => $lostItem->category_id,
            'item_name' => $lostItem->item_name,
            'description' => $lostItem->description,
            'location_lost' => $lostItem->location_lost,
            'date_lost' => $lostItem->date_lost->toDateString(),
            'time_lost' => '12:30:00', // what some browsers send back
            'images' => [UploadedFile::fake()->image('phone.jpg')],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $lostItem->images()->count());
    }

    public function test_a_found_item_with_a_time_can_be_edited(): void
    {
        $foundItem = FoundItem::factory()->create(['time_found' => '08:15']);

        $this->actingAs($foundItem->user)->put(route('found-items.update', $foundItem), [
            'category_id' => $foundItem->category_id,
            'item_name' => $foundItem->item_name,
            'description' => $foundItem->description,
            'location_found' => $foundItem->location_found,
            'date_found' => $foundItem->date_found->toDateString(),
            'time_found' => '08:15:00',
        ])->assertSessionHasNoErrors();
    }

    public function test_form_errors_are_announced_at_the_top(): void
    {
        $lostItem = LostItem::factory()->create();

        $this->actingAs($lostItem->user)
            ->from(route('lost-items.edit', $lostItem))
            ->followingRedirects()
            ->put(route('lost-items.update', $lostItem), ['item_name' => ''])
            ->assertSee("Your report wasn't saved yet", false);
    }
}
