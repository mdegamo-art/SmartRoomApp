<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemEvent extends Model
{
    protected $fillable = [
        'event_type',
        'action',
        'device_id',
        'actor_type',
        'actor_id',
        'actor_name',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
