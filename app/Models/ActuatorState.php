<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActuatorState extends Model
{
    protected $fillable = [
        'actuator_name',
        'state',
    ];

    protected $casts = [
        'state' => 'integer',
    ];

    /**
     * Helper: get the state of a specific actuator.
     */
    public static function getState(string $name): int
    {
        $row = self::where('actuator_name', $name)->first();
        return $row ? $row->state : 0;
    }

    /**
     * Helper: set the state of a specific actuator.
     */
    public static function setState(string $name, int $state): void
    {
        self::updateOrCreate(
            ['actuator_name' => $name],
            ['state' => $state]
        );
    }
}
