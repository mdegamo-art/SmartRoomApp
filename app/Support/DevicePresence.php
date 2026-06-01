<?php

namespace App\Support;

use App\Models\Device;
use App\Models\TelemetryLog;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class DevicePresence
{
    public const LAST_INGEST_DEVICE_KEY = 'smartroom:last_ingest_device_id';
    public const LAST_INGEST_AT_KEY = 'smartroom:last_ingest_at';

    public static function staleAfterSeconds(): int
    {
        return max(5, (int) config('smartroom.device_stale_seconds', 25));
    }

    public static function cacheKey(string $deviceId): string
    {
        return 'smartroom:presence:' . Device::normalizeId($deviceId);
    }

    /**
     * Call on every successful ESP32 POST /api/sensor-data.
     */
    public static function markSeen(string $deviceId): void
    {
        $deviceId = Device::normalizeId($deviceId);
        $ttl = static::staleAfterSeconds() * 4;

        Cache::put(static::cacheKey($deviceId), now()->getTimestamp(), $ttl);
        Cache::put(static::LAST_INGEST_DEVICE_KEY, $deviceId, 3600);
        Cache::put(static::LAST_INGEST_AT_KEY, now()->toIso8601String(), 3600);
    }

    /**
     * Online only when this device_id received a POST within the stale window.
     */
    public static function isOnline(?TelemetryLog $latest, ?string $deviceId = null): bool
    {
        $deviceId = $deviceId ?? $latest?->device_id;
        if ($deviceId) {
            $cached = Cache::get(static::cacheKey($deviceId));
            if ($cached !== null) {
                $age = now()->getTimestamp() - (int) $cached;

                return $age >= 0 && $age <= static::staleAfterSeconds();
            }
        }

        if (!$latest?->created_at) {
            return false;
        }

        return static::ageSeconds($latest->created_at) <= static::staleAfterSeconds();
    }

    public static function isOnlineForDevice(?string $deviceId): bool
    {
        if (!$deviceId) {
            return false;
        }

        $deviceId = Device::normalizeId($deviceId);

        return static::isOnline(static::latestForDevice($deviceId), $deviceId);
    }

    public static function ageSeconds(CarbonInterface $at): int
    {
        return max(0, now()->getTimestamp() - $at->getTimestamp());
    }

    public static function latestForDevice(?string $deviceId): ?TelemetryLog
    {
        if (!$deviceId) {
            return null;
        }

        return TelemetryLog::query()
            ->where('device_id', Device::normalizeId($deviceId))
            ->whereNotNull('device_id')
            ->latest('created_at')
            ->first();
    }

    public static function lastIngestDeviceId(): ?string
    {
        $id = Cache::get(static::LAST_INGEST_DEVICE_KEY);

        return $id ? Device::normalizeId($id) : null;
    }

    public static function lastIngestAt(): ?string
    {
        return Cache::get(static::LAST_INGEST_AT_KEY);
    }

    /**
     * @return array{online: bool, last_seen_at: ?string, reading_age_seconds: ?int, stale_after_seconds: int, last_post_device_id: ?string, last_post_at: ?string}
     */
    public static function meta(?TelemetryLog $latest, ?string $deviceId = null): array
    {
        $staleAfter = static::staleAfterSeconds();
        $deviceId = $deviceId ?? $latest?->device_id;
        $online = static::isOnline($latest, $deviceId);

        if (!$latest) {
            return [
                'online'                => false,
                'last_seen_at'          => null,
                'reading_age_seconds'   => null,
                'stale_after_seconds'   => $staleAfter,
                'last_post_device_id'   => static::lastIngestDeviceId(),
                'last_post_at'          => static::lastIngestAt(),
            ];
        }

        return [
            'online'                => $online,
            'last_seen_at'          => $latest->created_at->toIso8601String(),
            'reading_age_seconds'   => static::ageSeconds($latest->created_at),
            'stale_after_seconds'   => $staleAfter,
            'last_post_device_id'   => static::lastIngestDeviceId(),
            'last_post_at'          => static::lastIngestAt(),
        ];
    }
}
