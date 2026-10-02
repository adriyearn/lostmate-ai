<?php

namespace App\Notifications;

use App\Models\Claim;
use App\Models\User;
use App\Notifications\Concerns\AlsoSendsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClaimApproved extends Notification implements ShouldQueue
{
    use AlsoSendsEmail;

    public function __construct(public Claim $claim) {}

    protected function mailSubject(): string
    {
        return 'Your claim was approved';
    }

    public function toArray(User $notifiable): array
    {
        $this->claim->loadMissing('foundItem');

        return [
            'claim_id' => $this->claim->id,
            'message' => "Your claim on \"{$this->claim->foundItem->item_name}\" was approved. Open My Claims to see your pickup code, then message the finder to arrange the handover.",
            'link' => route('my-claims.index'),
        ];
    }
}
