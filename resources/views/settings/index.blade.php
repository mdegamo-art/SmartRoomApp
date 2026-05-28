@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-sub', 'API server configuration, automation rules, and system info')

@section('content')

<div style="max-width:700px;">

    {{-- SERVER CONFIG --}}
    <div class="settings-label-sec">Server Configuration</div>
    <div class="settings-group">
        <div class="settings-row">
            <div>
                <div class="settings-key">API Base URL</div>
                <div class="settings-sub">ESP32 sends data to this address</div>
            </div>
            <code class="settings-input" style="background:var(--bg);">{{ url('/api') }}</code>
        </div>
        <div class="settings-row">
            <div>
                <div class="settings-key">Sensor Data Endpoint</div>
                <div class="settings-sub">ESP32 POSTs here every ~2 seconds</div>
            </div>
            <code class="settings-input" style="background:var(--bg);">POST /api/sensor-data</code>
        </div>
        <div class="settings-row">
            <div>
                <div class="settings-key">Actuator Status Endpoint</div>
                <div class="settings-sub">ESP32 GETs this to know what to do</div>
            </div>
            <code class="settings-input" style="background:var(--bg);">GET /api/actuator-status</code>
        </div>
        <div class="settings-row">
            <div class="settings-key">Dashboard Auto-Refresh</div>
            <span class="badge badge-ok">Every 5 seconds</span>
        </div>
    </div>

    {{-- AUTOMATION --}}
    <div class="settings-label-sec">Automation Rules</div>
    <div class="settings-group">
        <div class="settings-row">
            <div>
                <div class="settings-key">Temperature Alert Threshold</div>
                <div class="settings-sub">Auto-triggers buzzer if exceeded</div>
            </div>
            <span class="badge badge-alert">35°C</span>
        </div>
        <div class="settings-row">
            <div>
                <div class="settings-key">Auto Buzzer on High Temp</div>
                <div class="settings-sub">Triggered via SensorDataController</div>
            </div>
            <span class="badge badge-ok">Enabled</span>
        </div>
    </div>

    {{-- SYSTEM FLOW --}}
    <div class="settings-label-sec">System Flow</div>
    <div class="settings-group">
        @foreach([
            ['Step 1', 'Sensor Reading', 'ESP32 reads DHT11 (temp/humidity) and LDR (light)'],
            ['Step 2', 'Data Transmission', 'ESP32 POSTs JSON to Laravel → saved in telemetry_logs'],
            ['Step 3', 'Dashboard Monitoring', 'Blade views display live data, auto-refresh every 5s'],
            ['Step 4', 'User Controls Actuators', 'Dashboard or mobile app toggles LED/Buzzer'],
            ['Step 5', 'Laravel Updates State', 'actuator_states table updated via POST /api/actuator-status'],
            ['Step 6', 'ESP32 Polls State', 'ESP32 checks GET /api/actuator-status every 1-2 seconds'],
            ['Step 7', 'Physical Action', 'ESP32 turns LED/Buzzer ON or OFF based on response'],
        ] as [$num, $title, $desc])
        <div class="settings-row">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div style="width:24px;height:24px;border-radius:50%;background:var(--purple-light);color:var(--purple);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;margin-top:1px;">
                    {{ substr($num, -1) }}
                </div>
                <div>
                    <div class="settings-key">{{ $title }}</div>
                    <div class="settings-sub">{{ $desc }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ABOUT --}}
    <div class="settings-label-sec">About</div>
    <div class="settings-group">
        <div class="settings-row">
            <div class="settings-key">Project Title</div>
            <div class="settings-val" style="max-width:55%;text-align:right;">Smart Room Monitoring and Control System</div>
        </div>
        <div class="settings-row">
            <div class="settings-key">Tech Stack</div>
            <div class="settings-val">ESP32 · Laravel · MySQL · React Native</div>
        </div>
        <div class="settings-row">
            <div class="settings-key">Laravel Version</div>
            <div class="settings-val">{{ app()->version() }}</div>
        </div>
        <div class="settings-row">
            <div class="settings-key">PHP Version</div>
            <div class="settings-val">{{ phpversion() }}</div>
        </div>
        <div class="settings-row">
            <div class="settings-key">App Version</div>
            <div class="settings-val">1.0.0</div>
        </div>
    </div>

</div>
@endsection
