<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_update_their_profile(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'department' => 'College of Engineering',
            'course_or_position' => 'BSIT',
            'year_level' => '3rd Year',
            'contact_number' => '09171234567',
            'bio' => 'Hello there.',
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertRedirect(route('profile.edit'));

        $profile = $user->profile->fresh();
        $this->assertSame('College of Engineering', $profile->department);
        Storage::disk('public')->assertExists($profile->avatar_path);
    }

    public function test_contact_number_is_never_shown_on_a_public_item_page(): void
    {
        $user = User::factory()->create();
        $user->profile->update(['contact_number' => '09171234567']);

        $lostItem = \App\Models\LostItem::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs(User::factory()->create())->get(route('lost-items.show', $lostItem));

        $response->assertDontSee('09171234567');
    }
}
