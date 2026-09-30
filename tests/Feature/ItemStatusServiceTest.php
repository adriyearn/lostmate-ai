<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\LostItem;
use App\Services\ItemStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ItemStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ItemStatusService::class);
    }

    public function test_allowed_transitions_succeed(): void
    {
        $item = LostItem::factory()->create(['status' => ItemStatus::Open]);

        $this->service->transition($item, ItemStatus::Matched);
        $this->assertSame(ItemStatus::Matched, $item->fresh()->status);

        $this->service->transition($item, ItemStatus::Claimed);
        $this->assertSame(ItemStatus::Claimed, $item->fresh()->status);

        $this->service->transition($item, ItemStatus::Returned);
        $this->assertSame(ItemStatus::Returned, $item->fresh()->status);

        $this->service->transition($item, ItemStatus::Closed);
        $this->assertSame(ItemStatus::Closed, $item->fresh()->status);
        $this->assertNotNull($item->fresh()->closed_at);
    }

    public function test_disallowed_transitions_throw(): void
    {
        $item = LostItem::factory()->create(['status' => ItemStatus::Open]);

        $this->expectException(InvalidStatusTransitionException::class);

        $this->service->transition($item, ItemStatus::Claimed);
    }

    public function test_closed_items_cannot_transition_anywhere(): void
    {
        $item = LostItem::factory()->create(['status' => ItemStatus::Closed]);

        $this->assertFalse($this->service->canTransition($item, ItemStatus::Open));
        $this->assertFalse($this->service->canTransition($item, ItemStatus::Matched));
    }

    public function test_transitioning_to_the_current_status_is_a_no_op(): void
    {
        $item = LostItem::factory()->create(['status' => ItemStatus::Open]);

        $this->service->transition($item, ItemStatus::Open);

        $this->assertSame(ItemStatus::Open, $item->fresh()->status);
    }

    public function test_transition_if_possible_silently_skips_invalid_transitions(): void
    {
        $item = LostItem::factory()->create(['status' => ItemStatus::Open]);

        $this->service->transitionIfPossible($item, ItemStatus::Claimed);

        $this->assertSame(ItemStatus::Open, $item->fresh()->status);
    }
}
