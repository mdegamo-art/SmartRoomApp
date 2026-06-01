<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActuatorState extends Model
{
    protected $fillable = [
        'device_id',
        'actuator_name',
        'state',
    ];

    protected $casts = [
        'state' => 'integer',
    ];

    public static function getState(string $name, string $deviceId): int
    {
        $row = self::where('device_id', $deviceId)
            ->where('actuator_name', $name)
            ->first();

        return $row ? $row->state : 0;
    }

    public static function setState(string $name, int $state, string $deviceId): void
    {
        self::updateOrCreate(
            ['device_id' => $deviceId, 'actuator_name' => $name],
            ['state' => $state]
        );
    }
}
