<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\PhotoStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function useCloudinary(): void
    {
        config(['services.cloudinary.url' => 'cloudinary://123456:topsecret@democloud']);

        Http::fake([
            'api.cloudinary.com/v1_1/democloud/image/upload' => Http::response(['public_id' => 'lostmate/item-images/abc123']),
            'api.cloudinary.com/v1_1/democloud/image/destroy' => Http::response(['result' => 'ok']),
        ]);
    }

    public function test_without_cloudinary_photos_are_stored_locally_as_before(): void
    {
        Storage::fake('public');
        $photos = app(PhotoStorage::class);

        $path = $photos->store(UploadedFile::fake()->image('wallet.jpg'), 'item-images');

        $this->assertStringStartsWith('item-images/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame(asset('storage/'.$path), $photos->url($path));
    }

    public function test_with_cloudinary_photos_are_uploaded_signed_and_never_send_the_secret(): void
    {
        $this->useCloudinary();

        $path = app(PhotoStorage::class)->store(UploadedFile::fake()->image('wallet.jpg'), 'item-images');

        $this->assertSame('cloudinary:lostmate/item-images/abc123', $path);
        Http::assertSent(function (Request $request) {
            $fields = collect($request->data())->pluck('contents', 'name');

            return str_contains($request->url(), '/image/upload')
                && $fields['api_key'] === '123456'
                && $fields['folder'] === 'lostmate/item-images'
                && strlen($fields['signature']) === 40
                && ! str_contains($request->body(), 'topsecret');
        });
    }

    public function test_cloudinary_photo_urls_point_to_the_cdn(): void
    {
        $this->useCloudinary();

        $this->assertSame(
            'https://res.cloudinary.com/democloud/image/upload/f_auto,q_auto/lostmate/item-images/abc123',
            app(PhotoStorage::class)->url('cloudinary:lostmate/item-images/abc123')
        );
    }

    public function test_signature_matches_cloudinarys_documented_example(): void
    {
        // Example from Cloudinary's "Generating authentication signatures" docs.
        config(['services.cloudinary.url' => 'cloudinary://key:abcd@cloud']);

        $signed = (new class extends PhotoStorage
        {
            public function sign(array $params): array
            {
                return $this->signed($params);
            }
        })->sign([
            'timestamp' => 1315060510,
            'public_id' => 'sample_image',
            'eager' => 'w_400,h_300,c_pad|w_260,h_200,c_crop',
        ]);

        $this->assertSame('bfd09f95f331f558cbd1320e67aa8d488770583e', $signed['signature']);
    }

    public function test_deleting_a_cloudinary_photo_calls_destroy(): void
    {
        $this->useCloudinary();

        app(PhotoStorage::class)->delete('cloudinary:lostmate/item-images/abc123');

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/image/destroy')
            && $request['public_id'] === 'lostmate/item-images/abc123');
    }

    public function test_reporting_an_item_with_a_photo_uses_cloudinary_end_to_end(): void
    {
        $this->useCloudinary();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('lost-items.store'), [
            'category_id' => Category::factory()->create()->id,
            'item_name' => 'Black wallet',
            'description' => 'Leather wallet with a red logo',
            'location_lost' => 'Library',
            'date_lost' => now()->toDateString(),
            'images' => [UploadedFile::fake()->image('wallet.jpg')],
        ])->assertSessionHasNoErrors();

        $image = $user->lostItems()->first()->images()->first();
        $this->assertSame('cloudinary:lostmate/item-images/abc123', $image->path);
        $this->assertStringStartsWith('https://res.cloudinary.com/democloud/', $image->url);
    }
}
