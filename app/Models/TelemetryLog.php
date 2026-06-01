<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetryLog extends Model
{
    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'light_level',
    ];

    protected $casts = [
        'temperature' => 'float',
        'humidity'    => 'float',
        'light_level' => 'integer',
    ];

    /**
     * Determine the status of this reading.
     */
    public function getStatusAttribute(): string
    {
        if ($this->temperature > 35) return 'alert';
        if ($this->temperature > 30 || $this->light_level > 400) return 'warning';
        return 'normal';
    }
}
