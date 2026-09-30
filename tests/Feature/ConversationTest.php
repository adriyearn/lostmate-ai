<?php

namespace Tests\Feature;

use App\Enums\ReportReason;
use App\Models\Conversation;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_start_a_conversation_from_a_found_item(): void
    {
        $finder = User::factory()->create();
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id]);

        $response = $this->actingAs($claimant)->post(route('found-items.contact', $foundItem));

        $conversation = Conversation::first();

        $response->assertRedirect(route('conversations.show', $conversation));
        $this->assertTrue($conversation->hasParticipant($finder));
        $this->assertTrue($conversation->hasParticipant($claimant));
        $this->assertSame($foundItem->id, $conversation->found_item_id);
    }

    public function test_starting_a_conversation_twice_reuses_the_existing_one(): void
    {
        $finder = User::factory()->create();
        $claimant = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id]);

        $this->actingAs($claimant)->post(route('found-items.contact', $foundItem));
        $this->actingAs($claimant)->post(route('found-items.contact', $foundItem));

        $this->assertSame(1, Conversation::count());
    }

    public function test_a_user_cannot_contact_themselves(): void
    {
        $finder = User::factory()->create();
        $foundItem = FoundItem::factory()->create(['user_id' => $finder->id]);

        $response = $this->actingAs($finder)->post(route('found-items.contact', $foundItem));

        $response->assertForbidden();
    }

    public function test_only_participants_can_view_a_conversation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $outsider = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $conversation = Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);

        $this->actingAs($outsider)->get(route('conversations.show', $conversation))->assertForbidden();
        $this->actingAs($userA)->get(route('conversations.show', $conversation))->assertOk();
        $this->actingAs($userB)->get(route('conversations.show', $conversation))->assertOk();
        $this->actingAs($admin)->get(route('conversations.show', $conversation))->assertOk();
    }

    public function test_sending_a_message_notifies_the_other_participant(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);

        $response = $this->actingAs($userA)->post(route('conversations.store-message', $conversation), [
            'body' => 'Hello, is this yours?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $userA->id,
            'body' => 'Hello, is this yours?',
        ]);

        $this->assertSame(1, DatabaseNotification::where('notifiable_id', $userB->id)
            ->where('type', NewMessage::class)
            ->count());
    }

    public function test_opening_a_conversation_marks_the_other_users_messages_as_read(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userB->id,
        ]);

        $this->assertSame(1, $conversation->unreadCountFor($userA));

        $this->actingAs($userA)->get(route('conversations.show', $conversation));

        $this->assertSame(0, $conversation->fresh()->unreadCountFor($userA));
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_a_participant_can_report_a_message(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userB->id,
        ]);

        $response = $this->actingAs($userA)->post(route('messages.report', $message), [
            'reason' => ReportReason::Inappropriate->value,
            'details' => 'This was rude.',
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Report::where('reportable_type', Message::class)
            ->where('reportable_id', $message->id)
            ->where('reporter_id', $userA->id)
            ->count());
    }

    public function test_a_non_participant_cannot_report_a_message(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);
        $message = Message::factory()->create(['conversation_id' => $conversation->id, 'sender_id' => $userB->id]);

        $response = $this->actingAs($outsider)->post(route('messages.report', $message), [
            'reason' => ReportReason::Spam->value,
        ]);

        $response->assertForbidden();
    }

    public function test_lost_item_owner_does_not_see_contact_button_on_their_own_report(): void
    {
        $owner = User::factory()->create();
        $lostItem = LostItem::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)->get(route('lost-items.show', $lostItem));

        $response->assertDontSee('Contact owner');
    }
}
