<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\Concerns\AlsoSendsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ItemReturned extends Notification implements ShouldQueue
{
    use AlsoSendsEmail;

    public function __construct(public Claim $claim) {}

    protected function mailSubject(): string
    {
        return 'Your item was returned';
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
