<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class TimeController extends Controller
{
    /**
     * GET /api/time — server clock for mobile apps (sync & display).
     */
    public function show(): JsonResponse
    {
        $now = now();

        return response()->json([
            'server_time'        => $now->toIso8601String(),
            'unix'               => $now->timestamp,
            'timezone'           => config('app.timezone'),
            'formatted'          => $now->format('M j, Y g:i:s A'),
            'formatted_24h'      => $now->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Build time metadata to attach to API payloads (mobile).
     *
     * @return array<string, mixed>
     */
    public static function meta(): array
    {
        $now = now();

        return [
            'server_time' => $now->toIso8601String(),
            'unix'        => $now->timestamp,
            'timezone'    => config('app.timezone'),
        ];
    }
}
