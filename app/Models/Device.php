<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_id',
        'label',
    ];

    public static function normalizeId(string $deviceId): string
    {
        return strtoupper(trim($deviceId));
    }

    /**
     * Device may be linked if admin registered it or ESP32 has sent telemetry.
     */
    public static function isLinkable(string $deviceId): bool
    {
        $deviceId = static::normalizeId($deviceId);

        if (static::where('device_id', $deviceId)->exists()) {
            return true;
        }

        return TelemetryLog::where('device_id', $deviceId)->exists();
    }

    public function linkedUser()
    {
        return $this->hasOne(User::class, 'device_id', 'device_id');
    }
}
