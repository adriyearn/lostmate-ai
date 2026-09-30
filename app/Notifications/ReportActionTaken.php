<?php

namespace App\Notifications;

use App\Models\Report;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ReportActionTaken extends Notification
{
    public function __construct(public Report $report) {}

    public function via(User $notifiable): array
    {
        return ['database'];
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
