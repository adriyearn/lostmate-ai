<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Notifications\ClaimApproved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_notifications_go_to_both_the_app_and_email(): void
    {
        Notification::fake();

        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $this->actingAs($foundItem->user)->post(route('claims.approve', $claim), ['response' => 'Matches my notes.']);

        Notification::assertSentTo($claim->claimant, ClaimApproved::class,
            fn ($notification, array $channels) => $channels === ['database', 'mail']);
    }

    public function test_in_app_notification_is_saved_immediately_and_email_waits_in_the_queue(): void
    {
        // Behave like the real app: the queue worker, not the request, sends emails.
        config(['queue.default' => 'database']);

        $foundItem = FoundItem::factory()->create(['status' => ItemStatus::Matched]);
        $claim = Claim::factory()->create(['found_item_id' => $foundItem->id, 'status' => ClaimStatus::Pending]);

        $this->actingAs($foundItem->user)->post(route('claims.approve', $claim), ['response' => 'Matches my notes.']);

        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $claim->claimant_id)
            ->where('type', ClaimApproved::class)->count());
        // Only the email is queued (creating the found item also queues AI matching, so filter).
        $this->assertSame(1, DB::table('jobs')->where('payload', 'like', '%SendQueuedNotifications%')->count());
    }

    public function test_the_email_reuses_the_in_app_message_and_link(): void
    {
        $claim = Claim::factory()->create(['status' => ClaimStatus::Approved]);

        $mail = (new ClaimApproved($claim))->toMail($claim->claimant);

        $this->assertStringContainsString('Your claim was approved', $mail->subject);
        $this->assertSame(route('my-claims.index'), $mail->actionUrl);
    }
}
