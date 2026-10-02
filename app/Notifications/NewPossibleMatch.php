<?php

namespace App\Notifications;

use App\Models\AiMatch;
use App\Models\User;
use App\Notifications\Concerns\AlsoSendsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewPossibleMatch extends Notification implements ShouldQueue
{
    use AlsoSendsEmail;

    public function __construct(public AiMatch $aiMatch) {}

    protected function mailSubject(): string
    {
        return 'Possible match for your report';
    }

    public function toArray(User $notifiable): array
    {
        $this->aiMatch->loadMissing(['lostItem', 'foundItem']);

        $isLostReporter = $notifiable->id === $this->aiMatch->lostItem->user_id;

        return [
            'ai_match_id' => $this->aiMatch->id,
            'score' => $this->aiMatch->score,
            'message' => $isLostReporter
                ? "A possible match was found for your lost \"{$this->aiMatch->lostItem->item_name}\" report."
                : "A possible match was found for your found \"{$this->aiMatch->foundItem->item_name}\" report.",
            'link' => $isLostReporter
                ? route('lost-items.matches', $this->aiMatch->lostItem)
                : route('found-items.matches', $this->aiMatch->foundItem),
        ];
    }
}
