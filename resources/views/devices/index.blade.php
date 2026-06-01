@extends('layouts.app')

@section('title', 'Devices')
@section('page-title', 'Device registry')
@section('page-sub', 'Register room IDs here. Mobile users link their account to one ID in the app.')

@section('content')

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <button onclick="document.getElementById('addDeviceModal').showModal()" style="display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);font-size:13px;font-weight:500;color:var(--text-1);cursor:pointer;">
        <i class="ti ti-plus" style="font-size:15px;"></i> Register Device ID
    </button>
</div>

<dialog id="addDeviceModal" style="border:none;border-radius:var(--radius);padding:0;max-width:400px;">
    <div style="padding:24px;background:var(--surface);">
        <h3 style="margin:0 0 16px 0;font-size:16px;font-weight:600;color:var(--text-1);">Register Device ID</h3>
        <form method="POST" action="{{ route('devices.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:var(--text-2);margin-bottom:6px;">Device ID *</label>
                <input type="text" name="device_id" required placeholder="SMARTROOM-001" pattern="SMARTROOM-.+" style="width:100%;padding:8px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--background);font-size:13px;color:var(--text-1);font-family:monospace;">
                <p style="font-size:11px;color:var(--text-3);margin:6px 0 0;">Must match the ID in ESP32 firmware. Mobile users type this in the app to link.</p>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:var(--text-2);margin-bottom:6px;">Label (optional)</label>
                <input type="text" name="label" placeholder="Room 101" style="width:100%;padding:8px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--background);font-size:13px;color:var(--text-1);">
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
                <button type="button" onclick="document.getElementById('addDeviceModal').close()" style="padding:8px 16px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);font-size:13px;cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px;border-radius:var(--radius);border:none;background:#5C35C9;font-size:13px;font-weight:500;color:white;cursor:pointer;">Register</button>
            </div>
        </form>
    </div>
</dialog>

<div class="panel" style="padding:0;overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th style="padding:12px 16px;">Device ID</th>
                <th style="padding:12px 16px;">Label</th>
                <th style="padding:12px 16px;">Linked user</th>
                <th style="padding:12px 16px;">Registered</th>
                <th style="padding:12px 16px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
                @php $linked = $linkedByDevice->get($device->device_id); @endphp
                <tr>
                    <td style="padding:12px 16px;font-family:monospace;font-weight:600;font-size:13px;">{{ $device->device_id }}</td>
                    <td style="padding:12px 16px;color:var(--text-2);font-size:13px;">{{ $device->label ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:13px;">
                        @if($linked)
                            <span class="badge badge-ok">{{ $linked->name }}</span>
                        @else
                            <span style="color:var(--text-3);">Available to link</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:12px;color:var(--text-3);">{{ $device->created_at->format('Y-m-d') }}</td>
                    <td style="padding:12px 16px;">
                        @if(!$linked)
                        <form method="POST" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm('Remove {{ $device->device_id }} from registry?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn" title="Delete" style="color:var(--red-text);cursor:pointer;background:none;border:none;padding:0;"><i class="ti ti-trash" style="font-size:15px;"></i></button>
                        </form>
                        @else
                        <span style="font-size:11px;color:var(--text-3);">Unlink user first</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px;color:var(--text-3);">
                        No devices registered. Add SMARTROOM-001, SMARTROOM-002, etc. before users link in the mobile app.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
