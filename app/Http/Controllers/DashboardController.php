<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use App\Models\TelemetryLog;
use App\Models\Device;
use App\Support\DeviceContext;
use App\Support\DevicePresence;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeDeviceId = DeviceContext::activeDeviceId($request);
        $deviceIds = DeviceContext::availableDeviceIds();

        if (auth()->user()->is_admin && !$activeDeviceId && count($deviceIds) > 0) {
            $activeDeviceId = $deviceIds[0];
            session(['monitor_device_id' => $activeDeviceId]);
        }

        if ($activeDeviceId) {
            $activeDeviceId = Device::normalizeId($activeDeviceId);
            session(['monitor_device_id' => $activeDeviceId]);
        }

        $latest = $activeDeviceId ? DevicePresence::latestForDevice($activeDeviceId) : null;

        $telemetryQuery = DeviceContext::scopeTelemetry(TelemetryLog::query(), $request);
        $recentLogs = (clone $telemetryQuery)->latest('created_at')->take(5)->get();

        $ledState = 0;
        $buzzerState = 0;
        if ($activeDeviceId) {
            $ledState = ActuatorState::getState('led', $activeDeviceId);
            $buzzerState = ActuatorState::getState('buzzer', $activeDeviceId);
        }

        $chartQuery = DeviceContext::scopeTelemetry(
            TelemetryLog::where('created_at', '>=', now()->subHour()),
            $request
        );
        $chartData = $chartQuery->orderBy('created_at')
            ->get(['temperature', 'humidity', 'light_level', 'created_at']);

        $deviceOnline = DevicePresence::isOnline($latest, $activeDeviceId);
        $lastPostDeviceId = DevicePresence::lastIngestDeviceId();
        $lastPostAt = DevicePresence::lastIngestAt();

        return view('dashboard.index', compact(
            'latest',
            'recentLogs',
            'ledState',
            'buzzerState',
            'chartData',
            'activeDeviceId',
            'deviceIds',
            'deviceOnline',
            'lastPostDeviceId',
            'lastPostAt'
        ));
    }

    /**
     * JSON status for dashboard polling (session auth).
     */
    public function presence(Request $request): JsonResponse
    {
        $deviceId = DeviceContext::activeDeviceId($request);
        if (!$deviceId) {
            return response()->json([
                'device_id'           => null,
                'device_online'       => false,
                'last_post_device_id' => DevicePresence::lastIngestDeviceId(),
                'last_post_at'        => DevicePresence::lastIngestAt(),
            ]);
        }

        $deviceId = Device::normalizeId($deviceId);
        $latest = DevicePresence::latestForDevice($deviceId);

        return response()->json(array_merge([
            'device_id'     => $deviceId,
            'device_online' => DevicePresence::isOnline($latest, $deviceId),
            'temperature'   => $latest?->temperature,
            'humidity'      => $latest?->humidity,
            'light_level'   => $latest?->light_level,
            'reading_at'    => $latest?->created_at?->toIso8601String(),
        ], DevicePresence::meta($latest, $deviceId)));
    }

    public function logs(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $search = $request->query('search', '');
        $activeDeviceId = DeviceContext::activeDeviceId($request);
        $deviceIds = DeviceContext::availableDeviceIds();

        $query = DeviceContext::scopeTelemetry(TelemetryLog::query(), $request);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('temperature', 'like', "%{$search}%")
                  ->orWhere('humidity', 'like', "%{$search}%")
                  ->orWhere('light_level', 'like', "%{$search}%");
            });
        }

        if ($filter === 'alert') {
            $query->where('temperature', '>', 35);
        } elseif ($filter === 'warning') {
            $query->where(function ($q) {
                $q->whereBetween('temperature', [30, 35])
                  ->orWhere('light_level', '>', 400);
            });
        } elseif ($filter === 'normal') {
            $query->where('temperature', '<=', 30)
                  ->where('light_level', '<=', 400);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('logs.index', compact('logs', 'filter', 'search', 'activeDeviceId', 'deviceIds'));
    }

    public function actuators(Request $request)
    {
        $activeDeviceId = DeviceContext::activeDeviceId($request);
        $deviceIds = DeviceContext::availableDeviceIds();

        if (auth()->user()->is_admin && !$activeDeviceId && count($deviceIds) > 0) {
            $activeDeviceId = $deviceIds[0];
            session(['monitor_device_id' => $activeDeviceId]);
        }

        $states = collect();
        if ($activeDeviceId) {
            $states = ActuatorState::where('device_id', $activeDeviceId)
                ->get()
                ->keyBy('actuator_name');
        }

        return view('actuators.index', compact('states', 'activeDeviceId', 'deviceIds'));
    }
}
