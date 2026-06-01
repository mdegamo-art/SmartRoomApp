@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-sub')
    Live <span data-live-clock-short>--:--:--</span>{{ $activeDeviceId ? ' · ' . $activeDeviceId : ' · Select a room' }}@if($latest) · Last reading <span data-timestamp="{{ $latest->created_at->timestamp }}" data-live-mode="ago">{{ $latest->created_at->diffForHumans() }}</span>@endif
@endsection

@section('content')

@if(auth()->user()->is_admin && empty($activeDeviceId))
<div class="alert-bar error" style="margin-bottom:16px;">
    <i class="ti ti-alert-circle"></i>
    No rooms yet. Register device IDs under <strong>Devices</strong>, or wait for ESP32 telemetry.
</div>
@endif

{{-- ── METRICS ── --}}
<div class="metrics-grid">

    {{-- Temperature --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-temp"><i class="ti ti-temperature"></i></div>
            @if($latest && $latest->temperature > 35)
                <span class="badge badge-alert">High</span>
            @elseif($latest && $latest->temperature > 30)
                <span class="badge badge-warn">Warm</span>
            @else
                <span class="badge badge-ok">Normal</span>
            @endif
        </div>
        <div class="metric-label">Temperature</div>
        <div class="metric-value">
            {{ $latest ? number_format($latest->temperature, 1) : '--' }}<span class="metric-unit">°C</span>
        </div>
        <div class="metric-sub">DHT11 · Room sensor</div>
    </div>

    {{-- Humidity --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-hum"><i class="ti ti-droplet"></i></div>
            <span class="badge badge-ok">Normal</span>
        </div>
        <div class="metric-label">Humidity</div>
        <div class="metric-value">
            {{ $latest ? $latest->humidity : '--' }}<span class="metric-unit">%</span>
        </div>
        <div class="metric-sub">DHT11 · Room sensor</div>
    </div>

    {{-- Light --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-lux"><i class="ti ti-sun"></i></div>
            @if($latest && $latest->light_level > 400)
                <span class="badge badge-warn">Bright</span>
            @elseif($latest && $latest->light_level < 100)
                <span class="badge badge-blue">Dark</span>
            @else
                <span class="badge badge-ok">Normal</span>
            @endif
        </div>
        <div class="metric-label">Light Intensity</div>
        <div class="metric-value">
            {{ $latest ? $latest->light_level : '--' }}<span class="metric-unit"> Lux</span>
        </div>
        <div class="metric-sub">LDR · Light sensor</div>
    </div>

    {{-- Device Status --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-status"><i class="ti ti-wifi"></i></div>
        </div>
        <div class="metric-label">Device Status</div>
        <div class="metric-value" style="font-size:20px;color:var(--green-text);">Online</div>
        <div class="metric-sub">{{ $activeDeviceId ?? 'No room selected' }} · polling</div>
    </div>

</div>

{{-- ── CHART + ACTUATORS ── --}}
<div class="two-col">

    {{-- Chart --}}
    <div class="panel">
        <div class="panel-title">Data Trends — Last Hour</div>
        <div class="chart-legend">
            <span><span class="legend-dot" style="background:#D85A30;"></span>Temperature (°C)</span>
            <span><span class="legend-dot" style="background:#378ADD;"></span>Humidity (%)</span>
            <span><span class="legend-dot" style="background:#BA7517;"></span>Light (÷10 Lux)</span>
        </div>
        <div style="position:relative;height:210px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- Actuators + Device Info --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        <div class="panel">
            <div class="panel-title">Actuator Controls</div>

            {{-- LED --}}
            <div class="actuator-row">
                <div class="actuator-left">
                    <div class="actuator-icon ai-led"><i class="ti ti-bulb"></i></div>
                    <div>
                        <div class="actuator-name">LED (Light)</div>
                        <div class="actuator-desc">State: {{ $ledState ? 'ON' : 'OFF' }} · via dashboard</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('actuator.web.update') }}" class="toggle-form">
                    @csrf
                    @if($activeDeviceId)<input type="hidden" name="device_id" value="{{ $activeDeviceId }}">@endif
                    <input type="hidden" name="actuator" value="led">
                    <input type="hidden" name="state" value="{{ $ledState ? 0 : 1 }}">
                    <button type="submit" class="toggle-btn {{ $ledState ? 'on' : 'off' }}" title="Toggle LED"></button>
                </form>
            </div>

            {{-- Buzzer --}}
            <div class="actuator-row" style="margin-bottom:0;">
                <div class="actuator-left">
                    <div class="actuator-icon ai-buzz"><i class="ti ti-bell"></i></div>
                    <div>
                        <div class="actuator-name">Buzzer (Alarm)</div>
                        <div class="actuator-desc">State: {{ $buzzerState ? 'ON' : 'OFF' }} · via dashboard</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('actuator.web.update') }}" class="toggle-form">
                    @csrf
                    @if($activeDeviceId)<input type="hidden" name="device_id" value="{{ $activeDeviceId }}">@endif
                    <input type="hidden" name="actuator" value="buzzer">
                    <input type="hidden" name="state" value="{{ $buzzerState ? 0 : 1 }}">
                    <button type="submit" class="toggle-btn {{ $buzzerState ? 'on' : 'off' }}" title="Toggle Buzzer"></button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title">Device Info</div>
            <div class="device-grid">
                <div class="device-item"><div class="dk">Device ID</div><div class="dv">{{ $activeDeviceId ?? '—' }}</div></div>
                <div class="device-item"><div class="dk">Poll interval</div><div class="dv">1–2 sec</div></div>
                <div class="device-item"><div class="dk">Sensor POST</div><div class="dv" style="font-size:11px;font-family:monospace;">/api/sensor-data</div></div>
                <div class="device-item"><div class="dk">Actuator GET</div><div class="dv" style="font-size:11px;font-family:monospace;">/api/actuator-status</div></div>
            </div>
        </div>

    </div>
</div>

{{-- ── RECENT LOGS ── --}}
<div class="panel">
    <div class="panel-title">
        Recent Telemetry
        <a href="{{ route('logs') }}">View all logs →</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Temperature</th>
                <th>Humidity</th>
                <th>Light</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentLogs as $log)
                <tr>
                    <td style="color:var(--text-2);font-family:monospace;font-size:12px;">
                        <span data-timestamp="{{ $log->created_at->timestamp }}" data-live-mode="absolute" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">{{ $log->created_at->format('h:i:s A') }}</span>
                        <span style="color:var(--text-3);font-size:11px;"> (<span data-timestamp="{{ $log->created_at->timestamp }}" data-live-mode="ago">—</span>)</span>
                    </td>
                    <td>{{ number_format($log->temperature, 1) }} °C</td>
                    <td>{{ $log->humidity }} %</td>
                    <td>{{ $log->light_level }} Lux</td>
                    <td>
                        @if($log->status === 'alert')
                            <span class="badge badge-alert">Alert</span>
                        @elseif($log->status === 'warning')
                            <span class="badge badge-warn">Warning</span>
                        @else
                            <span class="badge badge-ok">Normal</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:24px;">No data yet. Waiting for ESP32…</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
    // Chart data from Laravel (passed as JSON)
    const chartData = @json($chartData);

    const labels = chartData.map(d => {
        const date = new Date(d.created_at);
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    });

    const temps = chartData.map(d => d.temperature);
    const hums  = chartData.map(d => d.humidity);
    const luxs  = chartData.map(d => Math.round(d.light_level / 10));

    const ctx = document.getElementById('trendChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: temps,
                    borderColor: '#D85A30',
                    backgroundColor: 'rgba(216,90,48,0.07)',
                    borderWidth: 2, pointRadius: 2, tension: 0.35,
                },
                {
                    label: 'Humidity (%)',
                    data: hums,
                    borderColor: '#378ADD',
                    backgroundColor: 'rgba(55,138,221,0.07)',
                    borderWidth: 2, pointRadius: 2, tension: 0.35,
                    borderDash: [4, 3],
                },
                {
                    label: 'Light (÷10)',
                    data: luxs,
                    borderColor: '#BA7517',
                    backgroundColor: 'rgba(186,117,23,0.07)',
                    borderWidth: 2, pointRadius: 2, tension: 0.35,
                    borderDash: [2, 3],
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { font: { size: 11 }, color: '#888', maxTicksLimit: 8 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                y: { ticks: { font: { size: 11 }, color: '#888' }, grid: { color: 'rgba(0,0,0,0.04)' } },
            }
        }
    });

    // Refresh sensor data every 5s (clock updates every 1s via smartroom-time.js)
    setInterval(() => location.reload(), 5000);
</script>
@endpush
