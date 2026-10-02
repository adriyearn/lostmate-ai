<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\User;
use App\Services\ReportSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_the_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Someone probably')
            ->assertSee(route('register'));
    }

    public function test_logged_in_users_go_to_their_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_landing_page_never_shows_item_details(): void
    {
        FoundItem::factory()->create([
            'item_name' => 'Secret Purple Umbrella',
            'hidden_details' => 'Initials JD on handle',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Secret Purple Umbrella')
            ->assertDontSee('Initials JD on handle');
    }

    public function test_recovery_rate_counts_only_items_actually_handed_over(): void
    {
        // Handed over via a completed claim, then closed by the owner: counts.
        $returned = FoundItem::factory()->create(['status' => ItemStatus::Closed]);
        Claim::factory()->create([
            'found_item_id' => $returned->id,
            'status' => ClaimStatus::Completed,
            'completed_at' => now(),
        ]);

        // Withdrawn by the finder (also "closed") and still open: don't count.
        FoundItem::factory()->create(['status' => ItemStatus::Closed]);
        FoundItem::factory()->create(['status' => ItemStatus::Open]);
        FoundItem::factory()->create(['status' => ItemStatus::Open]);

        $this->assertSame(25, app(ReportSummaryService::class)->recoveryRate());
    }
}
