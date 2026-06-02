<?php

namespace App\Support;

use App\Models\SystemEvent;
use App\Models\User;

class SystemEventLogger
{
    /**
     * Persist a system event entry for audit trail.
     *
     * @param  array<string,mixed>  $meta
     */
    public static function log(
        string $eventType,
        string $action,
        ?string $deviceId = null,
        ?User $actor = null,
        array $meta = []
    ): void {
        SystemEvent::create([
            'event_type' => $eventType,
            'action' => $action,
            'device_id' => $deviceId,
            'actor_type' => $actor ? ($actor->is_admin ? 'admin' : 'user') : 'system',
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'meta' => empty($meta) ? null : $meta,
        ]);
    }
}
