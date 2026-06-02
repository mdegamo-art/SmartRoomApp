@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-sub')
    Live <span data-live-clock-short>--:--:--</span>{{ $activeDeviceId ? ' · ' . $activeDeviceId : ' · Select a room' }}
    @if($deviceOnline && $latest)
        · Live · last POST <span data-timestamp="{{ $latest->created_at->timestamp }}" data-live-mode="ago">{{ $latest->created_at->diffForHumans() }}</span>
    @elseif($latest)
        · Offline · last DB row {{ $latest->created_at->format('M j, g:i:s A') }}
    @endif
    @if($lastPostDeviceId)
        · ESP32 last posted as <strong>{{ $lastPostDeviceId }}</strong>
    @endif
@endsection

@section('content')

@if(auth()->user()->is_admin && empty($activeDeviceId))
<div class="alert-bar error" style="margin-bottom:16px;">
    <i class="ti ti-alert-circle"></i>
    No rooms yet. Register device IDs under <strong>Devices</strong>, or wait for ESP32 telemetry.
</div>
@elseif($activeDeviceId && $lastPostDeviceId && $lastPostDeviceId !== $activeDeviceId && $deviceOnline === false)
<div class="alert-bar error" style="margin-bottom:16px;background:var(--amber-light);border-color:#FAC775;color:var(--amber-text);">
    <i class="ti ti-alert-circle"></i>
    Your ESP32 is posting as <strong>{{ $lastPostDeviceId }}</strong>, but you are monitoring <strong>{{ $activeDeviceId }}</strong>.
    Update firmware <code style="font-family:monospace;">deviceId</code> or switch the monitor room dropdown.
</div>
@elseif($activeDeviceId && !$deviceOnline)
<div class="alert-bar error" style="margin-bottom:16px;">
    <i class="ti ti-alert-circle"></i>
    <strong>{{ $activeDeviceId }}</strong> is offline — no ESP32 has sent data for this ID in the last {{ config('smartroom.device_stale_seconds') }} seconds.
    @if($latest)
        Last stored row: {{ $latest->created_at->format('M j, Y g:i:s A') }} (device {{ $latest->device_id }}).
    @else
        No telemetry has been received for this room yet. Check that firmware uses <code style="font-family:monospace;">deviceId = "{{ $activeDeviceId }}"</code>.
    @endif
</div>
@endif

{{-- ── METRICS ── --}}
<div class="metrics-grid">

    {{-- Temperature --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-temp"><i class="ti ti-temperature"></i></div>
            @if($latest && $latest->temperature > 35)
                <span id="metric-temp-badge" class="badge badge-alert">High</span>
            @elseif($latest && $latest->temperature > 30)
                <span id="metric-temp-badge" class="badge badge-warn">Warm</span>
            @else
                <span id="metric-temp-badge" class="badge badge-ok">Normal</span>
            @endif
        </div>
        <div class="metric-label">Temperature</div>
        <div class="metric-value" id="metric-temp-value">
            {{ $latest ? number_format($latest->temperature, 1) : '--' }}<span class="metric-unit">°C</span>
        </div>
        <div class="metric-sub">DHT11 · Room sensor</div>
    </div>

    {{-- Humidity --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-hum"><i class="ti ti-droplet"></i></div>
            <span id="metric-hum-badge" class="badge badge-ok">Normal</span>
        </div>
        <div class="metric-label">Humidity</div>
        <div class="metric-value" id="metric-hum-value">
            {{ $latest ? $latest->humidity : '--' }}<span class="metric-unit">%</span>
        </div>
        <div class="metric-sub">DHT11 · Room sensor</div>
    </div>

    {{-- Light --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-lux"><i class="ti ti-sun"></i></div>
            @if($latest && $latest->light_level > 400)
                <span id="metric-light-badge" class="badge badge-warn">Bright</span>
            @elseif($latest && $latest->light_level < 100)
                <span id="metric-light-badge" class="badge badge-blue">Dark</span>
            @else
                <span id="metric-light-badge" class="badge badge-ok">Normal</span>
            @endif
        </div>
        <div class="metric-label">Light Intensity</div>
        <div class="metric-value" id="metric-light-value">
            {{ $latest ? $latest->light_level : '--' }}<span class="metric-unit"> Lux</span>
        </div>
        <div class="metric-sub">LDR · Light sensor</div>
    </div>

    {{-- Device Status --}}
    <div class="metric-card">
        <div class="metric-icon-row">
            <div class="metric-icon mi-status"><i class="ti ti-wifi"></i></div>
            @if($deviceOnline)
                <span id="metric-device-badge" class="badge badge-ok">Live</span>
            @else
                <span id="metric-device-badge" class="badge badge-alert">Offline</span>
            @endif
        </div>
        <div class="metric-label">Device Status</div>
        <div id="metric-device-value" class="metric-value" style="font-size:20px;color:{{ $deviceOnline ? 'var(--green-text)' : 'var(--red-text)' }};">
            {{ $deviceOnline ? 'Online' : 'Offline' }}
        </div>
        <div id="metric-device-sub" class="metric-sub">
            @if($activeDeviceId)
                {{ $activeDeviceId }}
                @if($deviceOnline && $latest)
                    · reporting now
                @elseif($latest)
                    · last seen <span data-timestamp="{{ $latest->created_at->timestamp }}" data-live-mode="ago">{{ $latest->created_at->diffForHumans() }}</span>
                @else
                    · no ESP32 data for this ID
                @endif
            @else
                Select a room to monitor
            @endif
        </div>
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
                <div class="device-item"><div class="dk">Monitoring</div><div class="dv" id="presence-monitor-id">{{ $activeDeviceId ?? '—' }}</div></div>
                <div class="device-item"><div class="dk">Status</div><div class="dv" id="presence-status-label" style="color:{{ $deviceOnline ? 'var(--green-text)' : 'var(--red-text)' }};">{{ $deviceOnline ? 'Online' : 'Offline' }}</div></div>
                <div class="device-item"><div class="dk">Last ESP32 POST</div><div class="dv" id="presence-last-post" style="font-size:11px;font-family:monospace;">{{ $lastPostDeviceId ?? '—' }}{{ $lastPostAt ? ' · ' . \Carbon\Carbon::parse($lastPostAt)->format('g:i:s A') : '' }}</div></div>
                <div class="device-item"><div class="dk">Stale after</div><div class="dv">{{ config('smartroom.device_stale_seconds') }}s without POST</div></div>
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
                <th>Device ID</th>
                <th>Temperature</th>
                <th>Humidity</th>
                <th>Light</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="recent-telemetry-body">
            @forelse($recentLogs as $log)
                <tr>
                    <td style="color:var(--text-2);font-family:monospace;font-size:12px;">
                        <span data-timestamp="{{ $log->created_at->timestamp }}" data-live-mode="absolute" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">{{ $log->created_at->format('h:i:s A') }}</span>
                    </td>
                    <td style="font-family:monospace;font-size:12px;">{{ $log->device_id ?? '—' }}</td>
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
                <tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:24px;">No data yet for this room.</td></tr>
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
    const trendChart = new Chart(ctx, {
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

    const presenceUrl = @json(route('dashboard.presence'));
    const chartDataUrl = @json(route('dashboard.chart-data'));
    const liveDataUrl = @json(route('dashboard.live-data'));
    const staleAfter = {{ (int) config('smartroom.device_stale_seconds', 25) }};

    async function pollPresence() {
        try {
            const res = await fetch(presenceUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            const online = data.device_online === true;
            const statusEl = document.getElementById('device-status-text');
            const statusLabel = document.getElementById('presence-status-label');
            const lastPostEl = document.getElementById('presence-last-post');
            const pill = statusEl?.closest('.status-pill');

            if (statusEl && data.device_id) {
                statusEl.textContent = (online ? 'Online' : 'Offline') + ' · ' + data.device_id;
            }
            if (pill) {
                pill.style.background = online ? '' : 'var(--red-light)';
                pill.style.color = online ? '' : 'var(--red-text)';
                const dot = pill.querySelector('.status-dot');
                if (dot) dot.style.background = online ? 'var(--green)' : 'var(--red)';
            }
            if (statusLabel) {
                statusLabel.textContent = online ? 'Online' : 'Offline';
                statusLabel.style.color = online ? 'var(--green-text)' : 'var(--red-text)';
            }
            if (lastPostEl && data.last_post_device_id) {
                lastPostEl.textContent = data.last_post_device_id + (data.last_post_at ? ' · ' + new Date(data.last_post_at).toLocaleTimeString() : '');
            }
            // Do not reload the whole page here.
            // Presence polling should only update UI widgets, not reset session idle timers.
        } catch (e) {
            console.warn('presence poll failed', e);
        }
    }

    async function pollChartData() {
        try {
            const res = await fetch(chartDataUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const payload = await res.json();
            const rows = Array.isArray(payload.data) ? payload.data : [];

            const nextLabels = rows.map(d => {
                const date = new Date(d.created_at);
                return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            });
            const nextTemps = rows.map(d => d.temperature);
            const nextHums  = rows.map(d => d.humidity);
            const nextLuxs  = rows.map(d => Math.round(d.light_level / 10));

            trendChart.data.labels = nextLabels;
            trendChart.data.datasets[0].data = nextTemps;
            trendChart.data.datasets[1].data = nextHums;
            trendChart.data.datasets[2].data = nextLuxs;
            trendChart.update('none');
        } catch (e) {
            console.warn('chart poll failed', e);
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function setBadgeClass(el, variant) {
        if (!el) return;
        el.classList.remove('badge-ok', 'badge-warn', 'badge-alert', 'badge-blue');
        el.classList.add(variant);
    }

    async function pollLiveData() {
        try {
            const res = await fetch(liveDataUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const payload = await res.json();
            const latest = payload.latest;
            const online = payload.device_online === true;
            const deviceId = payload.device_id || '—';

            const tempBadge = document.getElementById('metric-temp-badge');
            const tempValue = document.getElementById('metric-temp-value');
            const humValue = document.getElementById('metric-hum-value');
            const lightBadge = document.getElementById('metric-light-badge');
            const lightValue = document.getElementById('metric-light-value');
            const deviceBadge = document.getElementById('metric-device-badge');
            const deviceValue = document.getElementById('metric-device-value');
            const deviceSub = document.getElementById('metric-device-sub');
            const recentBody = document.getElementById('recent-telemetry-body');

            const t = latest?.temperature;
            const h = latest?.humidity;
            const l = latest?.light_level;

            if (tempValue) tempValue.innerHTML = `${t != null ? Number(t).toFixed(1) : '--'}<span class="metric-unit">°C</span>`;
            if (humValue) humValue.innerHTML = `${h != null ? h : '--'}<span class="metric-unit">%</span>`;
            if (lightValue) lightValue.innerHTML = `${l != null ? l : '--'}<span class="metric-unit"> Lux</span>`;

            if (tempBadge) {
                if (t != null && t > 35) {
                    tempBadge.textContent = 'High';
                    setBadgeClass(tempBadge, 'badge-alert');
                } else if (t != null && t > 30) {
                    tempBadge.textContent = 'Warm';
                    setBadgeClass(tempBadge, 'badge-warn');
                } else {
                    tempBadge.textContent = 'Normal';
                    setBadgeClass(tempBadge, 'badge-ok');
                }
            }

            if (lightBadge) {
                if (l != null && l > 400) {
                    lightBadge.textContent = 'Bright';
                    setBadgeClass(lightBadge, 'badge-warn');
                } else if (l != null && l < 100) {
                    lightBadge.textContent = 'Dark';
                    setBadgeClass(lightBadge, 'badge-blue');
                } else {
                    lightBadge.textContent = 'Normal';
                    setBadgeClass(lightBadge, 'badge-ok');
                }
            }

            if (deviceBadge) {
                deviceBadge.textContent = online ? 'Live' : 'Offline';
                setBadgeClass(deviceBadge, online ? 'badge-ok' : 'badge-alert');
            }
            if (deviceValue) {
                deviceValue.textContent = online ? 'Online' : 'Offline';
                deviceValue.style.color = online ? 'var(--green-text)' : 'var(--red-text)';
            }
            if (deviceSub) {
                deviceSub.textContent = `${deviceId} · ${online ? 'reporting now' : 'no recent telemetry'}`;
            }

            if (recentBody) {
                const rows = Array.isArray(payload.recent_logs) ? payload.recent_logs : [];
                if (rows.length === 0) {
                    recentBody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:24px;">No data yet for this room.</td></tr>';
                } else {
                    recentBody.innerHTML = rows.map((log) => {
                        const status = log.status === 'alert'
                            ? '<span class="badge badge-alert">Alert</span>'
                            : log.status === 'warning'
                                ? '<span class="badge badge-warn">Warning</span>'
                                : '<span class="badge badge-ok">Normal</span>';
                        const ts = log.created_at
                            ? new Date(log.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                            : '--';
                        const tempVal = log.temperature != null ? Number(log.temperature).toFixed(1) : '--';
                        return `
                            <tr>
                                <td style="color:var(--text-2);font-family:monospace;font-size:12px;">${escapeHtml(ts)}</td>
                                <td style="font-family:monospace;font-size:12px;">${escapeHtml(log.device_id ?? '—')}</td>
                                <td>${escapeHtml(tempVal)} °C</td>
                                <td>${escapeHtml(log.humidity)} %</td>
                                <td>${escapeHtml(log.light_level)} Lux</td>
                                <td>${status}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }
        } catch (e) {
            console.warn('live-data poll failed', e);
        }
    }

    setInterval(pollPresence, 5000);
    pollPresence();
    setInterval(pollChartData, 3000);
    pollChartData();
    setInterval(pollLiveData, 3000);
    pollLiveData();
</script>
@endpush
