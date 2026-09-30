<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ClaimRejected extends Notification
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
            'message' => "Your claim on \"{$this->claim->foundItem->item_name}\" was rejected.".
                ($this->claim->finder_response ? " Reason: {$this->claim->finder_response}" : ''),
            'link' => route('my-claims.index'),
        ];
    }
}
