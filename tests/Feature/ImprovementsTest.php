<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\UserRole;
use App\Jobs\RunItemMatching;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\User;
use App\Services\MatchingStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    // ---- Matching status ----

    public function test_report_shows_ai_is_checking_while_matching_is_queued(): void
    {
        Queue::fake();
        $lostItem = LostItem::factory()->create(); // queues RunItemMatching

        $this->assertSame(MatchingStatus::CHECKING, MatchingStatus::get($lostItem));
        $this->actingAs($lostItem->user)->get(route('lost-items.show', $lostItem))
            ->assertSee('AI is checking for matches');
    }

    public function test_status_clears_when_matching_finishes_and_shows_failure_after_retries(): void
    {
        $lostItem = LostItem::factory()->create(); // runs synchronously in tests
        $this->assertNull(MatchingStatus::get($lostItem));

        (new RunItemMatching($lostItem))->failed(new RuntimeException('AI down'));

        $this->actingAs($lostItem->user)->get(route('lost-items.show', $lostItem))
            ->assertSee('Matching couldn\'t finish right now', false);
    }

    // ---- System health ----

    public function test_dashboard_shows_failed_jobs_and_admin_can_retry_them(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) str()->uuid(), 'connection' => 'database', 'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\RunItemMatching', 'job' => 'x', 'data' => []]),
            'exception' => 'AI down', 'failed_at' => now(),
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee('Retry failed jobs');
        $this->actingAs($admin)->post(route('admin.system.retry-failed'))->assertSessionHas('success');

        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertDatabaseHas('admin_logs', ['admin_id' => $admin->id, 'action' => 'system.retried_failed_jobs']);
    }

    public function test_students_cannot_retry_jobs(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.system.retry-failed'))->assertForbidden();
    }

    // ---- Scheduler ----

    public function test_auto_close_is_scheduled_hourly(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command ?? '', 'app:auto-close-returned-items'));

        $this->assertNotNull($event);
        $this->assertSame('0 * * * *', $event->expression);
    }

    // ---- Privacy + account deletion ----

    public function test_privacy_notice_is_public(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Data Privacy Act of 2012');
    }

    public function test_user_can_delete_their_account_and_photos(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $user->id]);
        $path = UploadedFile::fake()->image('a.jpg')->store('item-images', 'public');
        $lostItem->images()->create(['path' => $path, 'original_name' => 'a.jpg']);

        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('lost_items', ['id' => $lostItem->id]);
        $this->assertDatabaseMissing('item_images', ['path' => $path]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_wrong_password_does_not_delete_the_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('profile.destroy'), ['delete_password' => 'wrong'])
            ->assertSessionHasErrors('delete_password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_deletion_is_blocked_while_someone_has_an_active_claim_on_their_item(): void
    {
        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Approved]);

        $this->actingAs($foundItem->user)->delete(route('profile.destroy'), ['delete_password' => 'password'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $foundItem->user_id]);
    }

    public function test_admins_cannot_delete_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('profile.destroy'), ['delete_password' => 'password'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    // ---- QR claim tag ----

    public function test_admin_can_print_a_claim_tag_without_private_details(): void
    {
        $foundItem = FoundItem::factory()->create(['hidden_details' => 'SecretInsideXYZ']);

        $this->actingAs($this->admin())->get(route('admin.office.tag', $foundItem))
            ->assertOk()
            ->assertSee('F-'.str_pad($foundItem->id, 4, '0', STR_PAD_LEFT))
            ->assertSee(json_encode(route('found-items.show', $foundItem)), false)
            ->assertDontSee('SecretInsideXYZ')
            ->assertDontSee($foundItem->user->email);
    }

    public function test_students_cannot_open_claim_tags(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.office.tag', FoundItem::factory()->create()))
            ->assertForbidden();
    }
}
