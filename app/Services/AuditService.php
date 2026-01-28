<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AuditService
{
    /**
     * Log a system event.
     */
    public function log(?User $actor, string $event, Model $target, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor ? $actor->id : null,
            'event' => $event,
            'auditable_type' => get_class($target),
            'auditable_id' => $target->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
