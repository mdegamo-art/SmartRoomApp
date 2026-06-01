<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceLinkController extends Controller
{
    /**
     * GET /api/device/status — whether the mobile user has linked a device.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(array_merge([
            'device_linked' => (bool) $user->device_id,
            'device_id'     => $user->device_id,
            'is_admin'      => (bool) $user->is_admin,
            'can_link'      => !$user->is_admin,
        ], TimeController::meta()));
    }

    /**
     * GET /api/device/linkable — device IDs the user may link (registered, not taken).
     */
    public function linkable(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            return response()->json(['message' => 'Admins do not link devices. Use room monitor instead.'], 403);
        }

        $registered = Device::orderBy('device_id')->pluck('device_id');
        $taken = User::whereNotNull('device_id')
            ->where('id', '!=', $user->id)
            ->pluck('device_id');

        $available = $registered
            ->diff($taken)
            ->values()
            ->all();

        return response()->json([
            'devices' => $available,
            'hint'    => 'Enter the same Device ID shown in your ESP32 firmware (e.g. SMARTROOM-001).',
        ]);
    }

    /**
     * POST /api/device/link — mobile user links their account to a device ID.
     */
    public function link(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            return response()->json(['message' => 'Admins monitor all rooms; linking is not required.'], 403);
        }

        if ($user->device_id) {
            return response()->json([
                'message'   => 'Already linked to ' . $user->device_id . '. Unlink first to switch rooms.',
                'device_id' => $user->device_id,
            ], 409);
        }

        $validated = $request->validate([
            'device_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^SMARTROOM-[A-Z0-9-]+$/i',
            ],
        ]);

        $deviceId = Device::normalizeId($validated['device_id']);

        if (!Device::isLinkable($deviceId)) {
            return response()->json([
                'message' => 'Device ID not recognized. It must be registered by your administrator (Devices page) or your ESP32 must have sent data at least once.',
            ], 404);
        }

        $existing = User::where('device_id', $deviceId)->where('id', '!=', $user->id)->first();
        if ($existing) {
            return response()->json([
                'message' => 'This device is already linked to another account.',
            ], 409);
        }

        $user->update(['device_id' => $deviceId]);
        $user->refresh();

        return response()->json([
            'message'       => 'Device linked successfully.',
            'device_linked' => true,
            'device_id'     => $user->device_id,
            'user'          => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'device_id' => $user->device_id,
            ],
        ]);
    }

    /**
     * POST /api/device/unlink — mobile user removes device link.
     */
    public function unlink(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            return response()->json(['message' => 'Admins do not have a linked device.'], 403);
        }

        if (!$user->device_id) {
            return response()->json(['message' => 'No device linked.'], 422);
        }

        $user->update(['device_id' => null]);

        return response()->json([
            'message'       => 'Device unlinked. You can link a different room from the app.',
            'device_linked' => false,
            'device_id'     => null,
        ]);
    }
}
