<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use App\Models\TelemetryLog;
use App\Support\DeviceContext;
use App\Support\DevicePresence;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SensorDataController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id'   => 'required|string|max:50',
            'temperature' => 'required|numeric',
            'humidity'    => 'required|numeric',
            'light_level' => 'required|integer',
        ]);

        $deviceId = \App\Models\Device::normalizeId($validated['device_id']);
        $validated['device_id'] = $deviceId;
        $log = TelemetryLog::create($validated);

        DevicePresence::markSeen($deviceId);

        if ($validated['temperature'] > 33) {
            ActuatorState::setState('buzzer', 1, $deviceId);
        } elseif ($validated['temperature'] < 33) {
            ActuatorState::setState('buzzer', 0, $deviceId);
        }

        if ($validated['light_level'] < 40) {
            ActuatorState::setState('led', 1, $deviceId);
        } elseif ($validated['light_level'] > 60) {
            ActuatorState::setState('led', 0, $deviceId);
        }

        return response()->json([
            'message'    => 'Data saved successfully.',
            'device_id'  => $deviceId,
            'log_id'     => $log->id,
            'saved_at'   => $log->created_at->toIso8601String(),
        ], 201);
    }

    public function latest(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_admin && !$user->device_id) {
            return response()->json([
                'message'              => 'Link your device in the app first (Device ID from your ESP32).',
                'requires_device_link' => true,
            ], 403);
        }

        $deviceId = DeviceContext::activeDeviceId($request, $user);
        $log = $deviceId ? DevicePresence::latestForDevice($deviceId) : null;

        if (!$log) {
            return response()->json(array_merge(
                [
                    'message'        => 'No data yet for this room.',
                    'device_online'  => false,
                ],
                DevicePresence::meta(null, $deviceId),
                TimeController::meta()
            ), 404);
        }

        $payload = $log->toArray();
        $payload = array_merge($payload, TimeController::meta(), DevicePresence::meta($log, $deviceId));
        $payload['device_online'] = DevicePresence::isOnline($log, $deviceId);
        unset($payload['online']);
        $payload['reading_time_formatted'] = $log->created_at->format('M j, Y g:i:s A');

        return response()->json($payload);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->is_admin && !$user->device_id) {
            return response()->json([
                'message'              => 'Link your device in the app first (Device ID from your ESP32).',
                'requires_device_link' => true,
            ], 403);
        }

        $perPage = $request->query('per_page', 20);
        $query = DeviceContext::scopeTelemetry(TelemetryLog::query(), $request, $user);
        $logs = $query->latest()->paginate($perPage);

        $response = $logs->toArray();
        $response = array_merge($response, TimeController::meta());

        return response()->json($response);
    }
}
