<?php

namespace App\Http\Controllers;

use App\Models\TelemetryLog;
use App\Models\User;
use App\Support\DeviceContext;
use App\Support\DevicePresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * GET /api/rooms — list rooms (device IDs) for admin monitoring.
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $deviceIds = DeviceContext::availableDeviceIds();
        $usersByDevice = User::whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $rooms = array_map(function (string $deviceId) use ($usersByDevice) {
            $user = $usersByDevice->get($deviceId);
            $latest = TelemetryLog::where('device_id', $deviceId)->latest()->first();

            return array_merge([
                'device_id'       => $deviceId,
                'assigned_user'   => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ] : null,
                'last_reading_at' => $latest?->created_at?->toIso8601String(),
            ], DevicePresence::meta($latest));
        }, $deviceIds);

        return response()->json([
            'rooms' => $rooms,
            'count' => count($rooms),
        ]);
    }
}
