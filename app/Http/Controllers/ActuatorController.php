<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActuatorController extends Controller
{
    /**
     * ESP32 → GET /api/actuator-status
     * ESP32 polls this every 1-2 seconds to know what to do physically.
     *
     * Response: { "led": 1, "buzzer": 0 }
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'led'    => ActuatorState::getState('led'),
            'buzzer' => ActuatorState::getState('buzzer'),
        ]);
    }

    /**
     * Dashboard/Mobile → POST /api/actuator-status
     * User presses ON/OFF in app or web dashboard.
     *
     * Body: { "actuator": "led", "state": 1 }
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actuator' => 'required|in:led,buzzer',
            'state'    => 'required|in:0,1',
        ]);

        ActuatorState::setState($validated['actuator'], (int) $validated['state']);

        return response()->json([
            'message'  => 'Actuator updated.',
            'actuator' => $validated['actuator'],
            'state'    => (int) $validated['state'],
        ]);
    }

    /**
     * Web Dashboard (Blade) → POST /dashboard/actuator
     * Called by the web admin toggle buttons.
     */
    public function webUpdate(Request $request)
    {
        $validated = $request->validate([
            'actuator' => 'required|in:led,buzzer',
            'state'    => 'required|in:0,1',
        ]);

        ActuatorState::setState($validated['actuator'], (int) $validated['state']);

        return redirect()->back()->with('success', ucfirst($validated['actuator']) . ' updated successfully.');
    }
}
