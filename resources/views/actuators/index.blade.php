@extends('layouts.app')

@section('title', 'Actuator Controls')
@section('page-title', 'Actuator Controls')
@section('page-sub', 'LED and Buzzer states · synced with ESP32 via GET /api/actuator-status')

@section('content')

<div class="alert-bar success" style="margin-bottom:16px;">
    <i class="ti ti-check"></i>
    All actuator states are synced with ESP32. The device polls every 1–2 seconds via <code style="font-family:monospace;">GET /api/actuator-status</code>
</div>

{{-- Actuator Cards --}}
<div style="display:flex;flex-direction:column;gap:14px;margin-bottom:24px;">

    {{-- LED --}}
    <div class="panel" style="padding:20px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="actuator-icon ai-led" style="width:48px;height:48px;font-size:22px;"><i class="ti ti-bulb"></i></div>
                <div>
                    <div style="font-size:16px;font-weight:700;color:var(--text-1);">LED Module (Light)</div>
                    <div style="font-size:12px;color:var(--text-2);margin-top:2px;">Simulates room lighting · Visual indicator</div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:3px;">Controlled by: Dashboard · Mobile App · Optional automation</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <form method="POST" action="{{ route('actuator.web.update') }}" class="toggle-form">
                    @csrf
                    <input type="hidden" name="actuator" value="led">
                    <input type="hidden" name="state" value="{{ ($states['led']->state ?? 0) ? 0 : 1 }}">
                    <button type="submit" class="toggle-btn {{ ($states['led']->state ?? 0) ? 'on' : 'off' }}" title="Toggle LED"></button>
                </form>
                <span style="font-size:13px;font-weight:600;color:{{ ($states['led']->state ?? 0) ? 'var(--green-text)' : 'var(--text-3)' }};">
                    {{ ($states['led']->state ?? 0) ? 'ON' : 'OFF' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Buzzer --}}
    <div class="panel" style="padding:20px 24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="actuator-icon ai-buzz" style="width:48px;height:48px;font-size:22px;"><i class="ti ti-bell"></i></div>
                <div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="font-size:16px;font-weight:700;color:var(--text-1);">Active Buzzer (Alarm)</div>
                        <span class="badge badge-warn">Auto-trigger if temp &gt; 35°C</span>
                    </div>
                    <div style="font-size:12px;color:var(--text-2);margin-top:2px;">Warning alarm · Temperature safety alert</div>
                    <div style="font-size:11px;color:var(--text-3);margin-top:3px;">Controlled by: Dashboard · Mobile App · Automation rule</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
                <form method="POST" action="{{ route('actuator.web.update') }}" class="toggle-form">
                    @csrf
                    <input type="hidden" name="actuator" value="buzzer">
                    <input type="hidden" name="state" value="{{ ($states['buzzer']->state ?? 0) ? 0 : 1 }}">
                    <button type="submit" class="toggle-btn {{ ($states['buzzer']->state ?? 0) ? 'on' : 'off' }}" title="Toggle Buzzer"></button>
                </form>
                <span style="font-size:13px;font-weight:600;color:{{ ($states['buzzer']->state ?? 0) ? 'var(--red-text)' : 'var(--text-3)' }};">
                    {{ ($states['buzzer']->state ?? 0) ? 'ON' : 'OFF' }}
                </span>
            </div>
        </div>
    </div>

</div>

{{-- DB State Table --}}
<div class="panel">
    <div class="panel-title">Database State — <code style="font-size:13px;font-family:monospace;">actuator_states</code> table</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>actuator_name</th>
                <th>state</th>
                <th>updated_at</th>
            </tr>
        </thead>
        <tbody>
            @foreach($states as $name => $row)
                <tr>
                    <td style="color:var(--text-3);">{{ $row->id }}</td>
                    <td><code style="font-family:monospace;background:var(--bg);padding:2px 7px;border-radius:5px;">{{ $name }}</code></td>
                    <td>
                        <span class="badge {{ $row->state ? 'badge-ok' : 'badge-alert' }}">
                            {{ $row->state }} ({{ $row->state ? 'ON' : 'OFF' }})
                        </span>
                    </td>
                    <td style="font-family:monospace;font-size:12px;color:var(--text-2);">{{ $row->updated_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Automation Rule --}}
<div class="panel" style="margin-top:14px;background:var(--amber-light);border-color:#FAC775;">
    <div style="display:flex;gap:12px;align-items:flex-start;">
        <i class="ti ti-robot" style="font-size:22px;color:var(--amber-text);flex-shrink:0;margin-top:2px;"></i>
        <div>
            <div style="font-size:14px;font-weight:700;color:var(--amber-text);">Smart Automation Rule</div>
            <div style="font-size:13px;color:#633806;margin-top:4px;line-height:1.6;">
                If <code style="font-family:monospace;background:rgba(0,0,0,0.08);padding:1px 5px;border-radius:4px;">temperature &gt; 35°C</code>
                → Laravel automatically sets buzzer state to <strong>ON</strong> when it receives sensor data via <code style="font-family:monospace;font-size:12px;">POST /api/sensor-data</code>.
                The ESP32 then picks this up on its next poll and activates the buzzer physically.
            </div>
        </div>
    </div>
</div>

@endsection
