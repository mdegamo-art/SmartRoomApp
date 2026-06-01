<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use App\Models\TelemetryLog;
use App\Support\DeviceContext;
use Illuminate\Http\Request;

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

        $telemetryQuery = DeviceContext::scopeTelemetry(TelemetryLog::query(), $request);
        $latest = (clone $telemetryQuery)->latest()->first();
        $recentLogs = (clone $telemetryQuery)->latest()->take(5)->get();

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

        return view('dashboard.index', compact(
            'latest',
            'recentLogs',
            'ledState',
            'buzzerState',
            'chartData',
            'activeDeviceId',
            'deviceIds'
        ));
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
