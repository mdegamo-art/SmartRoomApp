<?php

namespace App\Http\Controllers;

use App\Models\ActuatorState;
use App\Models\TelemetryLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Web Admin Dashboard — main page.
     */
    public function index()
    {
        $latest      = TelemetryLog::latest()->first();
        $recentLogs  = TelemetryLog::latest()->take(5)->get();
        $ledState    = ActuatorState::getState('led');
        $buzzerState = ActuatorState::getState('buzzer');

        // Last-hour chart data (every 5 min intervals)
        $chartData = TelemetryLog::where('created_at', '>=', now()->subHour())
            ->orderBy('created_at')
            ->get(['temperature', 'humidity', 'light_level', 'created_at']);

        return view('dashboard.index', compact(
            'latest',
            'recentLogs',
            'ledState',
            'buzzerState',
            'chartData'
        ));
    }

    /**
     * Logs page — paginated telemetry history.
     */
    public function logs(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $search = $request->query('search', '');

        $query = TelemetryLog::latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('temperature', 'like', "%{$search}%")
                  ->orWhere('humidity', 'like', "%{$search}%")
                  ->orWhere('light_level', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('logs.index', compact('logs', 'filter', 'search'));
    }

    /**
     * Actuators management page.
     */
    public function actuators()
    {
        $states = ActuatorState::all()->keyBy('actuator_name');
        return view('actuators.index', compact('states'));
    }

    /**
     * Users management page.
     */
    public function users()
    {
        $users = \App\Models\User::all();
        return view('users.index', compact('users'));
    }

    /**
     * Settings page.
     */
    public function settings()
    {
        return view('settings.index');
    }
}
