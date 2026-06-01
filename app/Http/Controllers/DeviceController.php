<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::orderBy('device_id')->get();
        $linkedByDevice = User::whereNotNull('device_id')
            ->get()
            ->keyBy('device_id');

        return view('devices.index', compact('devices', 'linkedByDevice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^SMARTROOM-[A-Z0-9-]+$/i',
                Rule::unique('devices', 'device_id'),
            ],
            'label' => 'nullable|string|max:100',
        ]);

        $validated['device_id'] = Device::normalizeId($validated['device_id']);

        Device::create($validated);

        return redirect()->route('devices')->with('success', 'Device ID registered. Mobile users can now link to ' . $validated['device_id'] . '.');
    }

    public function destroy(Device $device)
    {
        if (User::where('device_id', $device->device_id)->exists()) {
            return redirect()->route('devices')->with('error', 'Cannot delete: a user is linked to this device. Unlink them first.');
        }

        $device->delete();

        return redirect()->route('devices')->with('success', 'Device removed from registry.');
    }

    /**
     * GET /api/devices — admin: all registered devices with link status.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $devices = Device::orderBy('device_id')->get();
        $linkedByDevice = User::whereNotNull('device_id')->get()->keyBy('device_id');

        return response()->json([
            'devices' => $devices->map(fn (Device $d) => [
                'device_id' => $d->device_id,
                'label'     => $d->label,
                'linked_user' => ($u = $linkedByDevice->get($d->device_id)) ? [
                    'id'    => $u->id,
                    'name'  => $u->name,
                    'email' => $u->email,
                ] : null,
            ]),
        ]);
    }

    /**
     * POST /api/devices — admin: register a new device ID.
     */
    public function apiStore(Request $request): JsonResponse
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $validated = $request->validate([
            'device_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^SMARTROOM-[A-Z0-9-]+$/i',
                Rule::unique('devices', 'device_id'),
            ],
            'label' => 'nullable|string|max:100',
        ]);

        $validated['device_id'] = Device::normalizeId($validated['device_id']);
        $device = Device::create($validated);

        return response()->json([
            'message' => 'Device registered.',
            'device'  => $device,
        ], 201);
    }
}
