<?php

namespace App\Notifications;

use App\Models\Report;
use App\Models\User;
use App\Notifications\Concerns\AlsoSendsEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportActionTaken extends Notification implements ShouldQueue
{
    use AlsoSendsEmail;

    public function __construct(public Report $report) {}

    protected function mailSubject(): string
    {
        return 'Your report was reviewed';
    }

    public function toArray(User $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'message' => "An administrator reviewed your report and marked it as \"{$this->report->status->label()}\".".
                ($this->report->admin_notes ? " Note: {$this->report->admin_notes}" : ''),
            'link' => route('notifications.index'),
        ];
    }
}
