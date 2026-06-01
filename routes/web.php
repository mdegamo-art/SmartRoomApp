<?php

use App\Http\Controllers\ActuatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Smart Room IoT Admin Dashboard
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ─── AUTH ──────────────────────────────────────────────────────────────────
Route::get('/login',  [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
Route::post('/logout',[\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ─── PROTECTED DASHBOARD ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard',           [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/logs',      [DashboardController::class, 'logs'])->name('logs');
    Route::get('/dashboard/actuators', [DashboardController::class, 'actuators'])->name('actuators');

    // User management (admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/devices',            [DeviceController::class, 'index'])->name('devices');
        Route::post('/devices',           [DeviceController::class, 'store'])->name('devices.store');
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

        Route::get('/users',              [UserController::class, 'index'])->name('users');
        Route::post('/users',             [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}',       [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/device', [UserController::class, 'updateDevice'])->name('users.update.device');
        Route::delete('/users/{user}',    [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Actuator toggle from web dashboard
    Route::post('/dashboard/actuator', [ActuatorController::class, 'webUpdate'])->name('actuator.web.update');
});
