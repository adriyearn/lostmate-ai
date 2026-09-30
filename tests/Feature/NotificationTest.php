<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_polling_returns_only_messages_after_the_given_id_and_marks_them_read(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $conversation = Conversation::factory()->create([
            'user_one_id' => $userA->id,
            'user_two_id' => $userB->id,
        ]);

        $first = Message::factory()->create(['conversation_id' => $conversation->id, 'sender_id' => $userB->id]);
        $second = Message::factory()->create(['conversation_id' => $conversation->id, 'sender_id' => $userB->id]);

        $response = $this->actingAs($userA)->getJson(
            route('conversations.poll', $conversation).'?after='.$first->id
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'messages');
        $response->assertJsonPath('messages.0.id', $second->id);
        $this->assertNotNull($second->fresh()->read_at);
    }

    public function test_mark_all_read_clears_unread_notifications(): void
    {
        $user = User::factory()->create();
        $user->notify(new \App\Notifications\NewMessage(
            Message::factory()->create(['sender_id' => $user->id])
        ));

        $this->assertSame(1, $user->unreadNotifications()->count());

        $this->actingAs($user)->post(route('notifications.mark-all-read'));

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_notifications_index_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
    }
}
