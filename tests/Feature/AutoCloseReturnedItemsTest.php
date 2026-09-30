<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCloseReturnedItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_closes_items_returned_seven_or_more_days_ago(): void
    {
        $oldLost = LostItem::factory()->create(['status' => ItemStatus::Returned]);
        $oldLost->timestamps = false;
        $oldLost->updated_at = now()->subDays(8);
        $oldLost->saveQuietly();

        $oldFound = FoundItem::factory()->create(['status' => ItemStatus::Returned]);
        $oldFound->timestamps = false;
        $oldFound->updated_at = now()->subDays(10);
        $oldFound->saveQuietly();

        $this->artisan('app:auto-close-returned-items')->assertSuccessful();

        $this->assertSame(ItemStatus::Closed, $oldLost->fresh()->status);
        $this->assertSame(ItemStatus::Closed, $oldFound->fresh()->status);
        $this->assertNotNull($oldLost->fresh()->closed_at);
    }

    public function test_it_leaves_recently_returned_items_alone(): void
    {
        $recentLost = LostItem::factory()->create(['status' => ItemStatus::Returned]);
        $recentLost->timestamps = false;
        $recentLost->updated_at = now()->subDays(2);
        $recentLost->saveQuietly();

        $this->artisan('app:auto-close-returned-items')->assertSuccessful();

        $this->assertSame(ItemStatus::Returned, $recentLost->fresh()->status);
    }

    public function test_it_does_not_touch_items_in_other_statuses(): void
    {
        $openItem = LostItem::factory()->create(['status' => ItemStatus::Open]);
        $openItem->timestamps = false;
        $openItem->updated_at = now()->subDays(30);
        $openItem->saveQuietly();

        $this->artisan('app:auto-close-returned-items')->assertSuccessful();

        $this->assertSame(ItemStatus::Open, $openItem->fresh()->status);
    }
}
