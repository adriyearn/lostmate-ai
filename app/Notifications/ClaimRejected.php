<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\Concerns\AlsoSendsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClaimRejected extends Notification implements ShouldQueue
{
    use AlsoSendsEmail;

    public function __construct(public Claim $claim) {}

    protected function mailSubject(): string
    {
        return 'Update on your claim';
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
