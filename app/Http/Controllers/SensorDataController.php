<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use App\Models\TelemetryLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SensorDataController extends Controller
{
    /**
     * ESP32 → POST /api/sensor-data
     * Receives sensor readings and stores them.
     * Also auto-triggers buzzer if temperature > 35°C.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temperature' => 'required|numeric',
            'humidity'    => 'required|numeric',
            'light_level' => 'required|integer',
        ]);

        // Save to telemetry_logs
        TelemetryLog::create($validated);

        // ─── SMART AUTOMATION ───────────────────────────────────────
        // Auto-trigger buzzer if temperature exceeds threshold
        if ($validated['temperature'] > 35) {
            ActuatorState::setState('buzzer', 1);
        }
        // ────────────────────────────────────────────────────────────

        return response()->json(['message' => 'Data saved successfully.'], 201);
    }

    /**
     * GET /api/sensor-data/latest
     * Returns the most recent sensor reading.
     */
    public function latest(): JsonResponse
    {
        $log = TelemetryLog::latest()->first();

        if (!$log) {
            return response()->json(['message' => 'No data yet.'], 404);
        }

        return response()->json($log);
    }

    /**
     * GET /api/sensor-data
     * Returns paginated telemetry history.
     * Query params: ?page=1&per_page=20
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 20);

        $logs = TelemetryLog::latest()
            ->paginate($perPage);

        return response()->json($logs);
    }
}
