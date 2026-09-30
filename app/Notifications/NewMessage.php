<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification
{
    public function __construct(public Message $message) {}

    public function via(User $notifiable): array
    {
        return ['database'];
    }

    public function toArray(User $notifiable): array
    {
        $this->message->loadMissing('sender');

        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'message' => "New message from {$this->message->sender->name}.",
            'link' => route('conversations.show', $this->message->conversation_id),
        ];
    }
}
