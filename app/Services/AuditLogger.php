<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogger
{
    public function record(?User $user, string $action, ?string $target = null, ?string $detail = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistem',
            'role' => $user?->role?->label(),
            'action' => $action,
            'target' => $target,
            'detail' => $detail,
        ]);
    }
}
