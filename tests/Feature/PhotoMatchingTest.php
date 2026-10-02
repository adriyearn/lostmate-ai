<?php

namespace Tests\Feature;

use App\Enums\ItemStatus;
use App\Jobs\RunItemMatching;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected FoundItem $foundItem;

    protected LostItem $lostItem;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['matching.fake' => false, 'services.openai.api_key' => 'test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => '{"matches": []}']]]]),
        ]);

        $category = Category::factory()->create();
        $this->lostItem = LostItem::factory()->create(['category_id' => $category->id, 'date_lost' => now()]);
        $this->foundItem = FoundItem::factory()->create([
            'category_id' => $category->id,
            'status' => ItemStatus::Open,
            'date_found' => now(),
            'hidden_details' => 'TopSecretHiddenDetailXYZ',
        ]);

        $path = UploadedFile::fake()->image('wallet.jpg', 400, 300)->store('items', 'public');
        $this->foundItem->images()->create(['path' => $path, 'original_name' => 'wallet.jpg']);
    }

    public function test_photos_are_not_sent_by_default(): void
    {
        config(['matching.use_photos' => false]);

        RunItemMatching::dispatchSync($this->lostItem);

        Http::assertSent(fn ($request) => ! str_contains($request->body(), 'image_url')
            && is_string($request['messages'][1]['content']));
    }

    public function test_when_enabled_candidate_photos_are_sent_at_low_detail_with_their_id(): void
    {
        config(['matching.use_photos' => true]);

        RunItemMatching::dispatchSync($this->lostItem);

        Http::assertSent(function ($request) {
            $parts = $request['messages'][1]['content'];
            $images = collect($parts)->where('type', 'image_url');
            $labels = collect($parts)->where('type', 'text')->pluck('text')->implode(' ');

            return $images->count() === 1
                && str_starts_with($images->first()['image_url']['url'], 'data:image/jpeg;base64,')
                && $images->first()['image_url']['detail'] === 'low'
                && str_contains($labels, "candidate_id {$this->foundItem->id}")
                && str_contains($request['messages'][0]['content'], 'personal information visible in a photo')
                && ! str_contains($request->body(), 'TopSecretHiddenDetailXYZ'); // text rules still apply
        });
    }

    public function test_missing_or_oversized_photos_are_skipped(): void
    {
        config(['matching.use_photos' => true, 'matching.max_photo_bytes' => 10]); // every real photo is "too big"

        RunItemMatching::dispatchSync($this->lostItem);

        Http::assertSent(fn ($request) => is_string($request['messages'][1]['content']));
    }
}
