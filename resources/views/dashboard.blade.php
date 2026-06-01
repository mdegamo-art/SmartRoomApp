@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Smart Room Dashboard')
@section('page-sub', 'Real-time sensor data and actuator controls · ESP32 connected')

@push('styles')
<style>
    .realtime-clock {
        font-family: monospace;
        font-size: 14px;
        color: var(--text-2);
    }
</style>
@endpush

@section('content')

{{-- Real-time Clock --}}
<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <div class="realtime-clock" id="realtimeClock">Loading...</div>
</div>

{{-- Sensor Data Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:24px;">

    {{-- Temperature --}}
    <div class="panel" style="padding:24px;background:linear-gradient(135deg,#5C35C9,#7C3AED);">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,0.7);letter-spacing:1;margin-bottom:8px;">TEMPERATURE</div>
        <div style="font-size:48px;font-weight:700;color:#fff;line-height:1;">
            {{ $latest ? number_format($latest->temperature, 1) : '--' }}<span style="font-size:20px;font-weight:400;color:rgba(255,255,255,0.7);">°C</span>
        </div>
        <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:4px;">
            @if($latest && $latest->temperature > 35)
                <span style="color:#FEE2E2;">🔴 Too Hot</span>
            @elseif($latest && $latest->temperature > 30)
                <span style="color:#FEF3C7;">🟡 Warm</span>
            @else
                <span style="color:#DCFCE7;">🟢 Normal</span>
            @endif
            · DHT11 Sensor
        </div>
    </div>

    {{-- Humidity --}}
    <div class="panel" style="padding:24px;">
        <div style="font-size:11px;font-weight:700;color:var(--text-3);letter-spacing:1;margin-bottom:8px;">HUMIDITY</div>
        <div style="font-size:48px;font-weight:700;color:var(--text-1);line-height:1;">
            {{ $latest ? $latest->humidity : '--' }}<span style="font-size:20px;font-weight:400;color:var(--text-2);">%</span>
        </div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">💧 DHT11 Sensor</div>
    </div>

    {{-- Light Level --}}
    <div class="panel" style="padding:24px;">
        <div style="font-size:11px;font-weight:700;color:var(--text-3);letter-spacing:1;margin-bottom:8px;">LIGHT LEVEL</div>
        <div style="font-size:48px;font-weight:700;color:var(--text-1);line-height:1;">
            {{ $latest ? $latest->light_level : '--' }}<span style="font-size:20px;font-weight:400;color:var(--text-2);"> Lux</span>
        </div>
        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">☀️ LDR Sensor</div>
    </div>

</div>

{{-- Quick Actuator Controls --}}
<div class="panel" style="padding:20px;margin-bottom:24px;">
    <div style="font-size:14px;font-weight:700;color:var(--text-1);margin-bottom:16px;">Quick Actuator Controls</div>
    <div style="display:flex;gap:16px;flex-wrap:wrap;">
        
        {{-- LED Toggle --}}
        <div style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:12px;flex:1;min-width:200px;">
            <div style="width:40px;height:40px;background:#FEF3C7;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">💡</div>
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:600;color:var(--text-1);">LED</div>
                <div style="font-size:11px;color:var(--text-2);">{{ $ledState ? 'ON' : 'OFF' }}</div>
            </div>
            <form method="POST" action="{{ route('actuator.web.update') }}">
                @csrf
                <input type="hidden" name="actuator" value="led">
                <input type="hidden" name="state" value="{{ $ledState ? 0 : 1 }}">
                <button type="submit" style="padding:8px 16px;border-radius:8px;border:1px solid var(--border);background:var(--surface);font-size:13px;cursor:pointer;">
                    {{ $ledState ? 'Turn Off' : 'Turn On' }}
                </button>
            </form>
        </div>

        {{-- Buzzer Toggle --}}
        <div style="display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:12px;flex:1;min-width:200px;">
            <div style="width:40px;height:40px;background:#FEE2E2;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">🔔</div>
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:600;color:var(--text-1);">Buzzer</div>
                <div style="font-size:11px;color:var(--text-2);">{{ $buzzerState ? 'ON' : 'OFF' }}</div>
            </div>
            <form method="POST" action="{{ route('actuator.web.update') }}">
                @csrf
                <input type="hidden" name="actuator" value="buzzer">
                <input type="hidden" name="state" value="{{ $buzzerState ? 0 : 1 }}">
                <button type="submit" style="padding:8px 16px;border-radius:8px;border:1px solid var(--border);background:var(--surface);font-size:13px;cursor:pointer;">
                    {{ $buzzerState ? 'Turn Off' : 'Turn On' }}
                </button>
            </form>
        </div>

    </div>
</div>

{{-- Recent Logs --}}
<div class="panel" style="padding:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <div style="font-size:14px;font-weight:700;color:var(--text-1);">Recent Sensor Readings</div>
        <a href="{{ route('logs') }}" style="font-size:12px;color:var(--primary);text-decoration:none;">View All →</a>
    </div>
    
    @if($recentLogs->count() > 0)
        <table style="width:100%;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:10px;font-size:11px;color:var(--text-3);">Time</th>
                    <th style="text-align:left;padding:10px;font-size:11px;color:var(--text-3);">Temp</th>
                    <th style="text-align:left;padding:10px;font-size:11px;color:var(--text-3);">Humidity</th>
                    <th style="text-align:left;padding:10px;font-size:11px;color:var(--text-3);">Light</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentLogs as $log)
                    <tr>
                        <td style="padding:10px;font-family:monospace;font-size:12px;color:var(--text-2);">
                            <span class="log-time" data-timestamp="{{ $log->created_at->timestamp }}">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td style="padding:10px;font-weight:600;">{{ number_format($log->temperature, 1) }}°C</td>
                        <td style="padding:10px;">{{ $log->humidity }}%</td>
                        <td style="padding:10px;">{{ $log->light_level }} Lux</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align:center;padding:32px;color:var(--text-3);">No sensor data received yet. ESP32 should be sending data every 5 seconds.</div>
    @endif
</div>

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        document.getElementById('realtimeClock').textContent = now.toLocaleString('en-US', options);
    }

    function updateLogTimes() {
        const now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.log-time').forEach(element => {
            const timestamp = parseInt(element.getAttribute('data-timestamp'));
            const seconds = now - timestamp;
            
            let timeAgo;
            if (seconds < 60) {
                timeAgo = `${seconds}s ago`;
            } else if (seconds < 3600) {
                const minutes = Math.floor(seconds / 60);
                timeAgo = `${minutes}m ago`;
            } else {
                const hours = Math.floor(seconds / 3600);
                timeAgo = `${hours}h ago`;
            }
            
            element.textContent = timeAgo;
        });
    }

    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);

    // Update log times immediately and then every second
    updateLogTimes();
    setInterval(updateLogTimes, 1000);
</script>
@endpush

@endsection
