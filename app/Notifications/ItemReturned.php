<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ItemReturned extends Notification
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
            'message' => "The finder confirmed \"{$this->claim->foundItem->item_name}\" was returned to you.",
            'link' => route('my-claims.index'),
        ];
    }
}
