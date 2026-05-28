<?php

use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\SensorDataController;
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
|   POST /api/login                → authenticate
|   GET  /api/sensor-data/latest   → latest reading
|   GET  /api/sensor-data          → paginated history
|   POST /api/actuator-status      → update actuator
|
| Admin uses:
|   POST /api/users                → create user (admin only)
|   GET  /api/users                → list users (admin only)
|
*/

// ─── PUBLIC ROUTES (no auth required — ESP32 uses these) ──────────────────────

// ESP32: Save sensor data
Route::post('/sensor-data', [SensorDataController::class, 'store']);

// ESP32: Get current actuator state (polling every 1-2 sec)
Route::get('/actuator-status', [ActuatorController::class, 'status']);


// ─── AUTH ROUTES ──────────────────────────────────────────────────────────────

Route::post('/login', function (Request $request) {
    // Support login by username (name) or email
    $user = \App\Models\User::where('email', $request->username)
        ->orWhere('name', $request->username)
        ->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
    ]);
});


// ─── PROTECTED ROUTES (mobile app — requires Bearer token) ────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Get latest sensor reading
    Route::get('/sensor-data/latest', [SensorDataController::class, 'latest']);

    // Get paginated sensor history
    Route::get('/sensor-data', [SensorDataController::class, 'index']);

    // Update actuator state from mobile
    Route::post('/actuator-status', [ActuatorController::class, 'update']);

    // Auth: get current user
    Route::get('/user', function (Request $request) {
        return $request->user();
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
});
