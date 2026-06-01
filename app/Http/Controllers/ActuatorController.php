<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use App\Support\DeviceContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActuatorController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:50',
        ]);

        $deviceId = $validated['device_id'];

        return response()->json([
            'device_id' => $deviceId,
            'led'       => ActuatorState::getState('led', $deviceId),
            'buzzer'    => ActuatorState::getState('buzzer', $deviceId),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'actuator'  => 'required|in:led,buzzer',
            'state'     => 'required|in:0,1',
            'device_id' => 'nullable|string|max:50',
        ]);

        $deviceId = $user->is_admin
            ? ($validated['device_id'] ?? DeviceContext::activeDeviceId($request, $user))
            : $user->device_id;

        if (!$deviceId) {
            return response()->json([
                'message' => $user->is_admin
                    ? 'Select a room (device_id) to control actuators.'
                    : 'No device assigned. Contact your administrator.',
            ], 403);
        }

        if (!$user->is_admin && $deviceId !== $user->device_id) {
            return response()->json(['message' => 'Unauthorized for this device.'], 403);
        }

        ActuatorState::setState($validated['actuator'], (int) $validated['state'], $deviceId);

        return response()->json([
            'message'   => 'Actuator updated.',
            'device_id' => $deviceId,
            'actuator'  => $validated['actuator'],
            'state'     => (int) $validated['state'],
        ]);
    }

    public function webUpdate(Request $request)
    {
        $validated = $request->validate([
            'actuator'  => 'required|in:led,buzzer',
            'state'     => 'required|in:0,1',
            'device_id' => 'nullable|string|max:50',
        ]);

        $deviceId = DeviceContext::actuatorDeviceId($request);

        if (!$deviceId) {
            return redirect()->back()->with('error', 'Select a room to control actuators.');
        }

        ActuatorState::setState($validated['actuator'], (int) $validated['state'], $deviceId);

        return redirect()->back()->with('success', ucfirst($validated['actuator']) . ' updated successfully.');
    }
}
