<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\Concerns\AlsoSendsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClaimSubmitted extends Notification implements ShouldQueue
{
    use AlsoSendsEmail;

    public function __construct(public Claim $claim) {}

    protected function mailSubject(): string
    {
        return 'New claim on your found item';
    }

    public function toArray(User $notifiable): array
    {
        $this->claim->loadMissing('foundItem');

        return [
            'claim_id' => $this->claim->id,
            'message' => "Someone submitted a claim on your found \"{$this->claim->foundItem->item_name}\" report.",
            'link' => route('found-items.claims', $this->claim->foundItem),
        ];
    }
}
