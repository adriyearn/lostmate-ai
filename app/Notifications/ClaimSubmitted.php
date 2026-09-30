<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ClaimSubmitted extends Notification
{
    public function __construct(public Claim $claim) {}

    public function via(User $notifiable): array
    {
        return ['database'];
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
