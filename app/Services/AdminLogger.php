<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminLogger
{
    public function log(User $admin, string $action, ?Model $target = null, ?string $description = null): void
    {
        AdminLog::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->id,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
