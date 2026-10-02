<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_sends_a_verification_email(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'New Student',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Notification::assertSentTo(User::where('email', 'new@example.com')->first(), QueuedVerifyEmail::class);
    }

    public function test_unverified_users_are_sent_to_the_verify_page(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('browse.index'))->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('verification.notice'))->assertOk()->assertSee('Check your inbox');
    }

    public function test_the_signed_link_verifies_the_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_a_link_with_the_wrong_hash_does_not_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('someone-else@example.com'),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_only_school_emails_can_register_when_a_domain_is_configured(): void
    {
        config(['lostmate.school_email_domains' => ['school.edu.ph']]);

        $this->post('/register', [
            'name' => 'Outsider',
            'email' => 'outsider@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/register', [
            'name' => 'Student',
            'email' => 'student@school.edu.ph',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_verification_can_be_switched_off_until_email_sending_works(): void
    {
        config(['lostmate.require_email_verification' => false]);
        Notification::fake();

        // Existing unverified users can use the app...
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        // ...and new registrations aren't sent a link they could never receive.
        $this->post('/register', [
            'name' => 'New Student',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        Notification::assertNothingSent();
    }
}
