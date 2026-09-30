<?php

namespace Tests\Feature;

use App\Enums\AiMatchStatus;
use App\Enums\ItemStatus;
use App\Jobs\RunItemMatching;
use App\Models\AiMatch;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use App\Notifications\NewPossibleMatch;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidates_are_filtered_by_category_status_and_date_window(): void
    {
        $wallets = Category::factory()->create(['name' => 'Wallets']);
        $bags = Category::factory()->create(['name' => 'Bags']);
        Category::factory()->create(['name' => 'Others']);

        $lostItem = LostItem::factory()->create([
            'category_id' => $wallets->id,
            'date_lost' => now()->subDays(5),
        ]);

        $sameCategoryInWindow = FoundItem::factory()->create([
            'category_id' => $wallets->id,
            'status' => ItemStatus::Open,
            'date_found' => now()->subDays(3),
        ]);

        // Wrong category (and not "Others")
        FoundItem::factory()->create([
            'category_id' => $bags->id,
            'status' => ItemStatus::Open,
            'date_found' => now()->subDays(3),
        ]);

        // Right category, but closed
        FoundItem::factory()->create([
            'category_id' => $wallets->id,
            'status' => ItemStatus::Closed,
            'date_found' => now()->subDays(3),
        ]);

        // Right category, but outside the 30-day window
        FoundItem::factory()->create([
            'category_id' => $wallets->id,
            'status' => ItemStatus::Open,
            'date_found' => now()->subDays(60),
        ]);

        $candidates = app(MatchingService::class)->findCandidates($lostItem);

        $this->assertCount(1, $candidates);
        $this->assertSame($sameCategoryInWindow->id, $candidates->first()->id);
    }

    public function test_candidates_are_capped_at_fifteen(): void
    {
        $category = Category::factory()->create();
        $lostItem = LostItem::factory()->create(['category_id' => $category->id, 'date_lost' => now()]);

        FoundItem::factory()->count(20)->create([
            'category_id' => $category->id,
            'status' => ItemStatus::Open,
            'date_found' => now(),
        ]);

        $candidates = app(MatchingService::class)->findCandidates($lostItem);

        $this->assertCount(15, $candidates);
    }

    public function test_fake_matching_saves_a_match_and_notifies_both_reporters_above_threshold(): void
    {
        config(['matching.fake' => true]);

        $category = Category::factory()->create();
        $lostReporter = User::factory()->create();
        $finder = User::factory()->create();

        $lostItem = LostItem::factory()->create([
            'user_id' => $lostReporter->id,
            'category_id' => $category->id,
            'item_name' => 'Black Leather Wallet',
            'color' => 'Black',
            'brand' => 'Generic',
            'description' => 'A black leather wallet with a red logo on it',
            'date_lost' => now(),
        ]);

        $foundItem = FoundItem::factory()->create([
            'user_id' => $finder->id,
            'category_id' => $category->id,
            'item_name' => 'Black Leather Wallet',
            'color' => 'Black',
            'brand' => 'Generic',
            'description' => 'A black leather wallet with a red logo on it',
            'hidden_details' => 'Contains a school ID',
            'status' => ItemStatus::Open,
            'date_found' => now(),
        ]);

        RunItemMatching::dispatchSync($lostItem);

        $aiMatch = AiMatch::where('lost_item_id', $lostItem->id)->where('found_item_id', $foundItem->id)->first();

        $this->assertNotNull($aiMatch);
        $this->assertSame(AiMatchStatus::Suggested, $aiMatch->status);
        $this->assertGreaterThanOrEqual(70, $aiMatch->score);

        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $lostReporter->id)->count());
        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $finder->id)->count());
    }

    public function test_matches_below_the_minimum_score_are_not_saved(): void
    {
        config(['matching.fake' => true]);

        $category = Category::factory()->create();

        $lostItem = LostItem::factory()->create([
            'category_id' => $category->id,
            'item_name' => 'Completely Unrelated Thing Alpha',
            'color' => null,
            'brand' => null,
            'description' => 'Nothing in common whatsoever zzz',
            'date_lost' => now(),
        ]);

        FoundItem::factory()->create([
            'category_id' => $category->id,
            'item_name' => 'Totally Different Object Beta',
            'color' => null,
            'brand' => null,
            'description' => 'Shares no words at all yyy',
            'status' => ItemStatus::Open,
            'date_found' => now(),
        ]);

        RunItemMatching::dispatchSync($lostItem);

        $this->assertSame(0, AiMatch::count());
    }

    public function test_dismissed_matches_are_not_overwritten_by_a_rerun(): void
    {
        config(['matching.fake' => true]);

        $category = Category::factory()->create();

        $lostItem = LostItem::factory()->create([
            'category_id' => $category->id,
            'item_name' => 'Black Leather Wallet',
            'color' => 'Black',
            'brand' => 'Generic',
            'description' => 'A black leather wallet with a red logo',
            'date_lost' => now(),
        ]);

        $foundItem = FoundItem::factory()->create([
            'category_id' => $category->id,
            'item_name' => 'Black Leather Wallet',
            'color' => 'Black',
            'brand' => 'Generic',
            'description' => 'A black leather wallet with a red logo',
            'status' => ItemStatus::Open,
            'date_found' => now(),
        ]);

        RunItemMatching::dispatchSync($lostItem);

        $aiMatch = AiMatch::where('lost_item_id', $lostItem->id)->where('found_item_id', $foundItem->id)->first();
        $aiMatch->update(['status' => AiMatchStatus::Dismissed]);

        RunItemMatching::dispatchSync($lostItem);

        $this->assertSame(AiMatchStatus::Dismissed, $aiMatch->fresh()->status);
    }

    public function test_hidden_details_and_user_identity_are_never_sent_to_the_ai(): void
    {
        config(['matching.fake' => false, 'services.openai.api_key' => 'test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"matches": []}']],
                ],
            ], 200),
        ]);

        $category = Category::factory()->create();
        $finder = User::factory()->create(['name' => 'Super Secret Finder Name']);

        $lostItem = LostItem::factory()->create(['category_id' => $category->id, 'date_lost' => now()]);
        FoundItem::factory()->create([
            'user_id' => $finder->id,
            'category_id' => $category->id,
            'status' => ItemStatus::Open,
            'date_found' => now(),
            'hidden_details' => 'TopSecretHiddenDetailXYZ',
        ]);

        RunItemMatching::dispatchSync($lostItem);

        Http::assertSent(function ($request) {
            $body = $request->body();

            return ! str_contains($body, 'TopSecretHiddenDetailXYZ')
                && ! str_contains($body, 'Super Secret Finder Name');
        });
    }

    public function test_only_the_reporter_or_admin_can_view_the_matches_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser)->get(route('lost-items.matches', $lostItem))->assertForbidden();
        $this->actingAs($owner)->get(route('lost-items.matches', $lostItem))->assertOk();
        $this->actingAs($admin)->get(route('lost-items.matches', $lostItem))->assertOk();
    }

    public function test_only_an_admin_can_rerun_matching(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->post(route('lost-items.rerun-matching', $lostItem))->assertForbidden();
        $this->actingAs($admin)->post(route('lost-items.rerun-matching', $lostItem))->assertRedirect();
    }

    public function test_a_reporter_can_dismiss_a_match(): void
    {
        $lostReporter = User::factory()->create();
        $finder = User::factory()->create();
        $unrelatedUser = User::factory()->create();

        $lostItem = LostItem::factory()->create(['user_id' => $lostReporter->id]);
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id]);
        $aiMatch = AiMatch::factory()->create([
            'lost_item_id' => $lostItem->id,
            'found_item_id' => $foundItem->id,
        ]);

        $this->actingAs($unrelatedUser)->post(route('ai-matches.dismiss', $aiMatch))->assertForbidden();

        $this->actingAs($lostReporter)->post(route('ai-matches.dismiss', $aiMatch))->assertRedirect();

        $this->assertSame(AiMatchStatus::Dismissed, $aiMatch->fresh()->status);
    }
}
