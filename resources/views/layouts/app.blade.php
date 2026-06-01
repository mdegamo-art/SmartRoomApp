<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Room IoT — @yield('title', 'Dashboard')</title>

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple:      #5C35C9;
            --purple-dark: #4322A8;
            --purple-light:#EDE9FA;
            --green:       #4CAF50;
            --green-light: #EAF3DE;
            --green-text:  #3B6D11;
            --red:         #E53935;
            --red-light:   #FCEBEB;
            --red-text:    #A32D2D;
            --amber:       #F59E0B;
            --amber-light: #FAEEDA;
            --amber-text:  #854F0B;
            --blue:        #2563EB;
            --blue-light:  #E6F1FB;
            --blue-text:   #185FA5;
            --bg:          #F3F4F6;
            --surface:     #ffffff;
            --border:      #E5E7EB;
            --text-1:      #111827;
            --text-2:      #6B7280;
            --text-3:      #9CA3AF;
            --sidebar-w:   220px;
            --radius:      10px;
            --radius-lg:   14px;
        }

        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text-1); font-size: 14px; }

        /* ── SHELL ── */
        .shell { display: flex; height: 100vh; overflow: hidden; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w); min-width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
        }
        .sidebar-brand {
            padding: 18px 16px 14px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .brand-logo {
            width: 32px; height: 32px; border-radius: 9px;
            background: var(--purple);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .brand-logo i { font-size: 17px; color: #fff; }
        .brand-name  { font-size: 13px; font-weight: 600; color: var(--text-1); line-height: 1.3; }
        .brand-sub   { font-size: 11px; color: var(--text-3); }

        .sidebar-nav { flex: 1; padding: 10px 8px; display: flex; flex-direction: column; gap: 2px; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: var(--radius);
            color: var(--text-2); font-size: 13px; font-weight: 500;
            text-decoration: none; transition: background 0.15s, color 0.15s;
        }
        .nav-item i { font-size: 17px; }
        .nav-item:hover { background: var(--bg); color: var(--text-1); }
        .nav-item.active { background: var(--purple-light); color: var(--purple); }

        .sidebar-footer {
            padding: 10px 8px;
            border-top: 1px solid var(--border);
        }
        .user-row {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: var(--radius);
            cursor: pointer;
        }
        .user-row:hover { background: var(--bg); }
        .avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--purple-light); color: var(--purple);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text-1); }
        .user-role { font-size: 11px; color: var(--text-3); }

        /* ── MAIN ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

        /* ── TOPBAR ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            height: 56px; padding: 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-title { font-size: 16px; font-weight: 600; color: var(--text-1); }
        .topbar-sub   { font-size: 11px; color: var(--text-3); margin-top: 1px; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .status-pill  {
            display: flex; align-items: center; gap: 6px;
            background: var(--green-light); color: var(--green-text);
            font-size: 12px; font-weight: 600;
            padding: 4px 12px; border-radius: 99px;
        }
        .status-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); }
        .icon-btn {
            width: 34px; height: 34px; border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--border); background: var(--surface);
            color: var(--text-2); cursor: pointer; text-decoration: none;
            transition: background 0.15s;
        }
        .icon-btn:hover { background: var(--bg); }
        .icon-btn i { font-size: 17px; }

        /* ── CONTENT ── */
        .content { flex: 1; overflow-y: auto; padding: 20px 24px; }

        /* ── CARDS ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
        }
        .panel-title {
            font-size: 14px; font-weight: 600; color: var(--text-1);
            margin-bottom: 14px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .panel-title a { font-size: 12px; font-weight: 400; color: var(--blue); text-decoration: none; }

        /* ── METRIC CARDS ── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px; margin-bottom: 16px;
        }
        .metric-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 16px 18px;
        }
        .metric-icon-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .metric-icon {
            width: 38px; height: 38px; border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center; font-size: 19px;
        }
        .mi-temp   { background: #FAECE7; color: #993C1D; }
        .mi-hum    { background: var(--blue-light); color: var(--blue-text); }
        .mi-lux    { background: var(--amber-light); color: var(--amber-text); }
        .mi-status { background: var(--green-light); color: var(--green-text); }
        .metric-label { font-size: 12px; color: var(--text-2); font-weight: 500; }
        .metric-value { font-size: 28px; font-weight: 700; color: var(--text-1); line-height: 1.1; margin-top: 2px; }
        .metric-unit  { font-size: 14px; font-weight: 400; color: var(--text-2); }
        .metric-sub   { font-size: 11px; color: var(--text-3); margin-top: 4px; }

        /* ── TWO-COL ── */
        .two-col { display: grid; grid-template-columns: 1fr 300px; gap: 14px; margin-bottom: 14px; }

        /* ── ALERT BAR ── */
        .alert-bar {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: var(--radius);
            margin-bottom: 14px; font-size: 13px;
        }
        .alert-bar.success { background: var(--green-light); color: var(--green-text); border: 1px solid #C0DD97; }
        .alert-bar.error   { background: var(--red-light);   color: var(--red-text);   border: 1px solid #F7C1C1; }

        /* ── ACTUATOR TOGGLE ROW ── */
        .actuator-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px; border: 1px solid var(--border);
            border-radius: var(--radius); background: var(--bg);
            margin-bottom: 10px;
        }
        .actuator-left { display: flex; align-items: center; gap: 10px; }
        .actuator-icon {
            width: 38px; height: 38px; border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .ai-led  { background: var(--amber-light); color: var(--amber-text); }
        .ai-buzz { background: var(--red-light);   color: var(--red-text); }
        .actuator-name { font-size: 13px; font-weight: 600; color: var(--text-1); }
        .actuator-desc { font-size: 11px; color: var(--text-2); margin-top: 1px; }

        /* Toggle switch */
        .toggle-form { display: flex; align-items: center; }
        .toggle-btn {
            position: relative; width: 44px; height: 24px;
            border-radius: 99px; border: none; cursor: pointer;
            transition: background 0.2s;
        }
        .toggle-btn.on  { background: var(--green); }
        .toggle-btn.off { background: var(--border); }
        .toggle-btn::after {
            content: ''; position: absolute;
            width: 18px; height: 18px; border-radius: 50%;
            background: #fff; top: 3px;
            transition: left 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle-btn.on::after  { left: 23px; }
        .toggle-btn.off::after { left: 3px; }

        /* ── TABLE ── */
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th {
            text-align: left; padding: 9px 12px;
            color: var(--text-2); font-weight: 600;
            border-bottom: 1px solid var(--border); font-size: 12px;
        }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
        tbody tr:hover { background: var(--bg); }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 11px 12px; }

        /* Badge */
        .badge {
            display: inline-block;
            font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 99px;
        }
        .badge-ok    { background: var(--green-light); color: var(--green-text); }
        .badge-warn  { background: var(--amber-light); color: var(--amber-text); }
        .badge-alert { background: var(--red-light);   color: var(--red-text); }
        .badge-blue  { background: var(--blue-light);  color: var(--blue-text); }

        /* Pagination */
        .pagination { display: flex; gap: 6px; justify-content: flex-end; margin-top: 14px; }
        .page-btn {
            padding: 5px 10px; border-radius: 7px; border: 1px solid var(--border);
            background: var(--surface); color: var(--text-2); font-size: 12px;
            text-decoration: none; font-weight: 500;
        }
        .page-btn.active { background: var(--purple); color: #fff; border-color: var(--purple); }
        .page-btn:hover  { background: var(--bg); }

        /* Device info grid */
        .device-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .device-item .dk { font-size: 11px; color: var(--text-3); margin-bottom: 1px; }
        .device-item .dv { font-size: 13px; font-weight: 600; color: var(--text-1); }

        /* Chart legend */
        .chart-legend { display: flex; gap: 14px; margin-bottom: 10px; font-size: 12px; color: var(--text-2); }
        .legend-dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; margin-right: 4px; }

        /* Search input */
        .search-wrap {
            display: flex; align-items: center; gap: 8px;
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 6px 12px; background: var(--surface);
        }
        .search-wrap i { color: var(--text-3); font-size: 15px; }
        .search-wrap input {
            border: none; outline: none; background: transparent;
            font-size: 13px; color: var(--text-1); width: 200px;
        }

        /* Filter pills */
        .filter-row { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
        .filter-pill {
            padding: 5px 14px; border-radius: 99px;
            border: 1px solid var(--border); background: var(--surface);
            color: var(--text-2); font-size: 12px; font-weight: 500;
            text-decoration: none; cursor: pointer;
        }
        .filter-pill.active { background: var(--purple-light); color: var(--purple); border-color: #C4B5F8; }

        /* Settings input */
        .settings-group { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 20px; }
        .settings-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid var(--border); }
        .settings-row:last-child { border-bottom: none; }
        .settings-key { font-size: 13px; font-weight: 600; color: var(--text-1); }
        .settings-sub { font-size: 11px; color: var(--text-3); margin-top: 1px; }
        .settings-val { font-size: 13px; color: var(--text-2); }
        .settings-input {
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 6px 10px; font-size: 13px; color: var(--text-1);
            background: var(--bg); outline: none; font-family: monospace;
        }
        .settings-label-sec {
            font-size: 10px; font-weight: 700; color: var(--text-3);
            text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 8px;
        }

        .live-clock-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--surface);
            font-family: ui-monospace, monospace;
            font-size: 12px;
            color: var(--text-1);
            white-space: nowrap;
        }
        .live-clock-pill i { font-size: 14px; color: var(--text-2); }

        /* Responsive */
        @media (max-width: 900px) {
            .metrics-grid { grid-template-columns: 1fr 1fr; }
            .two-col { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="shell">

    {{-- ── SIDEBAR ── --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo"><i class="ti ti-cpu"></i></div>
            <div>
                <div class="brand-name">Smart Room IoT</div>
                <div class="brand-sub">ESP32 + Laravel</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}"  class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('logs') }}"       class="nav-item {{ request()->routeIs('logs') ? 'active' : '' }}">
                <i class="ti ti-file-text"></i> Logs
            </a>
            <a href="{{ route('actuators') }}"  class="nav-item {{ request()->routeIs('actuators') ? 'active' : '' }}">
                <i class="ti ti-toggle-right"></i> Actuators
            </a>
            @if(auth()->user()->is_admin)
            <a href="{{ route('devices') }}"    class="nav-item {{ request()->routeIs('devices*') ? 'active' : '' }}">
                <i class="ti ti-cpu"></i> Devices
            </a>
            <a href="{{ route('users') }}"      class="nav-item {{ request()->routeIs('users') ? 'active' : '' }}">
                <i class="ti ti-users"></i> Users
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-row">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="user-role">
                        @if(auth()->user()->is_admin)
                            Administrator
                        @else
                            {{ auth()->user()->device_id ?? 'Mobile user' }}
                        @endif
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:4px;">
                @csrf
                <button type="submit" style="width:100%;padding:8px 12px;border:none;background:none;text-align:left;color:var(--text-2);font-size:13px;cursor:pointer;border-radius:var(--radius);display:flex;align-items:center;gap:8px;">
                    <i class="ti ti-logout" style="font-size:16px;"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN ── --}}
    <div class="main">

        {{-- TOPBAR --}}
        <div class="topbar">
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-sub">@yield('page-sub', 'Smart Room Monitoring and Control System')</div>
            </div>
            <div class="topbar-right">
                <div class="live-clock-pill" title="Live time ({{ config('app.timezone') }})">
                    <i class="ti ti-clock"></i>
                    <span data-live-clock-short>--:--:--</span>
                </div>
                @if(auth()->user()->is_admin && !empty($monitorDeviceIds))
                <form method="GET" action="{{ url()->current() }}" style="display:flex;align-items:center;gap:8px;">
                    @foreach(request()->except('device_id') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label style="font-size:12px;color:var(--text-2);white-space:nowrap;">Monitor room</label>
                    <select name="device_id" onchange="this.form.submit()" style="padding:6px 10px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);font-size:13px;color:var(--text-1);">
                        @foreach($monitorDeviceIds as $roomId)
                            <option value="{{ $roomId }}" @selected($monitorDeviceId === $roomId)>{{ $roomId }}</option>
                        @endforeach
                    </select>
                </form>
                @elseif(!auth()->user()->is_admin && auth()->user()->device_id)
                <span style="font-size:12px;font-family:monospace;color:var(--text-2);padding:6px 10px;border:1px solid var(--border);border-radius:var(--radius);">{{ auth()->user()->device_id }}</span>
                @endif
                @php
                    $presenceOnline = $monitorDeviceOnline ?? false;
                    $presenceDevice = $monitorDeviceId ?? auth()->user()->device_id ?? 'No device';
                @endphp
                <div class="status-pill" style="{{ $presenceOnline ? '' : 'background:var(--red-light);color:var(--red-text);' }}">
                    <div class="status-dot" style="background:{{ $presenceOnline ? 'var(--green)' : 'var(--red)' }};"></div>
                    <span id="device-status-text">{{ $presenceOnline ? 'Online' : 'Offline' }} · {{ $presenceDevice }}</span>
                </div>
                <a href="{{ route('dashboard') }}" class="icon-btn" title="Refresh">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="content">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert-bar success">
                    <i class="ti ti-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-bar error">
                    <i class="ti ti-alert-circle"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

</div>

<script>window.SMARTROOM_TIMEZONE = @json(config('app.timezone'));</script>
<script src="{{ asset('js/smartroom-time.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
