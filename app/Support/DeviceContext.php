<?php

namespace App\Support;

use App\Models\Device;
use App\Models\TelemetryLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DeviceContext
{
    /**
     * Resolve which device_id applies for the current request/user.
     */
    public static function activeDeviceId(?Request $request = null, ?User $user = null): ?string
    {
        $request = $request ?? request();
        $user = $user ?? ($request?->user() ?? auth()->user());

        if (!$user) {
            $deviceId = $request?->query('device_id') ?? $request?->input('device_id');

            return $deviceId ? Device::normalizeId($deviceId) : null;
        }

        if ($user->is_admin) {
            $deviceId = $request->query('device_id')
                ?? $request->input('device_id')
                ?? session('monitor_device_id');

            if ($deviceId) {
                $deviceId = Device::normalizeId($deviceId);
                session(['monitor_device_id' => $deviceId]);
            }

            return $deviceId ?: null;
        }

        return $user->device_id ? Device::normalizeId($user->device_id) : null;
    }

    /**
     * All known room device IDs (from users and telemetry).
     *
     * @return list<string>
     */
    public static function availableDeviceIds(): array
    {
        $fromRegistry = Device::pluck('device_id');

        $fromUsers = User::whereNotNull('device_id')
            ->where('device_id', '!=', '')
            ->pluck('device_id');

        $fromTelemetry = TelemetryLog::whereNotNull('device_id')
            ->where('device_id', '!=', '')
            ->distinct()
            ->pluck('device_id');

        return $fromRegistry->merge($fromUsers)->merge($fromTelemetry)->unique()->sort()->values()->all();
    }

  /**
     * Scope telemetry queries by role and selected/assigned device.
     */
    public static function scopeTelemetry(Builder $query, ?Request $request = null, ?User $user = null): Builder
    {
        $request = $request ?? request();
        $user = $user ?? ($request?->user() ?? auth()->user());

        if (!$user) {
            return $query;
        }

        if ($user->is_admin) {
            $deviceId = static::activeDeviceId($request, $user);
            if ($deviceId) {
                return $query
                    ->where('device_id', $deviceId)
                    ->whereNotNull('device_id');
            }

            // Admin must pick a room — never mix telemetry from multiple devices.
            return $query->whereRaw('1 = 0');
        }

        if ($user->device_id) {
            return $query
                ->where('device_id', Device::normalizeId($user->device_id))
                ->whereNotNull('device_id');
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Device ID used for actuator read/write (required for control actions).
     */
    public static function actuatorDeviceId(?Request $request = null, ?User $user = null): ?string
    {
        return static::activeDeviceId($request, $user);
    }
}
