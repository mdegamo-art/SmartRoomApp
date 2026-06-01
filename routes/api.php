<?php

use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceLinkController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Smart Room IoT
|--------------------------------------------------------------------------
|
| ESP32 uses:
|   POST /api/sensor-data          → store sensor reading
|   GET  /api/actuator-status      → poll LED/Buzzer state
|
| Mobile App uses:
|   GET  /api/time                 → server clock (real-time sync)
|   POST /api/login                → authenticate
|   GET  /api/device/status        → link status
|   GET  /api/device/linkable      → IDs available to link
|   POST /api/device/link          → user links device ID
|   POST /api/device/unlink        → user unlinks device
|   GET  /api/sensor-data/latest   → latest reading
|   GET  /api/sensor-data          → paginated history
|   POST /api/actuator-status      → update actuator
|
| Admin uses:
|   POST /api/users                → create user (admin only)
|   GET  /api/users                → list users (admin only)
|   GET  /api/rooms                → list available rooms (admin only)
|   GET  /api/devices              → registered device IDs (admin only)
|   POST /api/devices              → register device ID (admin only)
|
*/

// ─── PUBLIC ROUTES (no auth required — ESP32 uses these) ──────────────────────

// ESP32: Save sensor data
Route::post('/sensor-data', [SensorDataController::class, 'store']);

// ESP32: Get current actuator state (polling every 1-2 sec)
Route::get('/actuator-status', [ActuatorController::class, 'status']);

// Mobile / clients: server time (no auth)
Route::get('/time', [TimeController::class, 'show']);


// ─── AUTH ROUTES ──────────────────────────────────────────────────────────────

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = \App\Models\User::query()
        ->where(function ($q) use ($request) {
            $q->where('email', $request->username)
              ->orWhere('name', $request->username);
        })
        ->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    $token = $user->createToken('mobile-app')->plainTextToken;

    $requiresLink = !$user->is_admin && empty($user->device_id);

    return response()->json(array_merge([
        'token' => $token,
        'user'  => [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'is_admin'      => (bool) $user->is_admin,
            'device_id'     => $user->device_id,
            'device_linked' => !empty($user->device_id),
        ],
        'requires_device_link' => $requiresLink,
    ], TimeController::meta()));
});


// ─── PROTECTED ROUTES (mobile app — requires Bearer token) ────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Get latest sensor reading
    Route::get('/sensor-data/latest', [SensorDataController::class, 'latest']);

    // Get paginated sensor history
    Route::get('/sensor-data', [SensorDataController::class, 'index']);

    // Update actuator state from mobile
    Route::post('/actuator-status', [ActuatorController::class, 'update']);

    // Auth: get current user (includes live server time)
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json(array_merge(
            $user->toArray(),
            [
                'device_linked'        => (bool) $user->device_id,
                'requires_device_link' => !$user->is_admin && !$user->device_id,
            ],
            TimeController::meta()
        ));
    });

    // Auth: logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    });

    // Admin: create user
    Route::post('/users', [UserController::class, 'apiStore']);

    // Admin: list users
    Route::get('/users', [UserController::class, 'apiIndex']);

    // Admin: list available rooms (device IDs)
    Route::get('/rooms', [RoomController::class, 'index']);

    // Admin: device registry
    Route::get('/devices', [DeviceController::class, 'apiIndex']);
    Route::post('/devices', [DeviceController::class, 'apiStore']);

    // Mobile: self-service device linking
    Route::get('/device/status', [DeviceLinkController::class, 'status']);
    Route::get('/device/linkable', [DeviceLinkController::class, 'linkable']);
    Route::post('/device/link', [DeviceLinkController::class, 'link']);
    Route::post('/device/unlink', [DeviceLinkController::class, 'unlink']);
});
