@extends('layouts.app')

@section('title', 'Telemetry Logs')
@section('page-title', 'Telemetry Logs')
@section('page-sub', 'Full sensor history from MySQL · telemetry_logs table')

@section('content')

{{-- Search + Export --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:12px;flex-wrap:wrap;">
    <form method="GET" action="{{ route('logs') }}" style="display:flex;align-items:center;gap:10px;">
        <div class="search-wrap">
            <i class="ti ti-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search temperature, humidity, light…">
        </div>
        <button type="submit" style="padding:7px 14px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);font-size:13px;cursor:pointer;">
            Search
        </button>
        @if($search)
            <a href="{{ route('logs') }}" style="font-size:13px;color:var(--text-2);text-decoration:none;">Clear</a>
        @endif
    </form>

    <div style="display:flex;gap:8px;">
        <a href="{{ route('logs') }}?export=csv" class="icon-btn" title="Export CSV">
            <i class="ti ti-download"></i>
        </a>
        <a href="{{ route('logs') }}" class="icon-btn" title="Refresh">
            <i class="ti ti-refresh"></i>
        </a>
    </div>
</div>

{{-- Filter pills --}}
<div class="filter-row">
    <a href="{{ route('logs') }}" class="filter-pill {{ !request('filter') || request('filter') === 'all' ? 'active' : '' }}">All</a>
    <a href="{{ route('logs', ['filter' => 'normal']) }}"  class="filter-pill {{ request('filter') === 'normal'  ? 'active' : '' }}">🟢 Normal</a>
    <a href="{{ route('logs', ['filter' => 'warning']) }}" class="filter-pill {{ request('filter') === 'warning' ? 'active' : '' }}">🟡 Warning</a>
    <a href="{{ route('logs', ['filter' => 'alert']) }}"   class="filter-pill {{ request('filter') === 'alert'   ? 'active' : '' }}">🔴 Alert</a>
</div>

{{-- Count --}}
<div style="font-size:12px;color:var(--text-3);margin-bottom:10px;">
    {{ $logs->total() }} total records
    @if($search) · filtered by "{{ $search }}" @endif
</div>

{{-- Table --}}
<div class="panel" style="padding:0;overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th style="padding:12px 16px;">#</th>
                @if(auth()->user()->is_admin)
                <th style="padding:12px 16px;">Device</th>
                @endif
                <th style="padding:12px 16px;">Timestamp</th>
                <th style="padding:12px 16px;">Temperature</th>
                <th style="padding:12px 16px;">Humidity</th>
                <th style="padding:12px 16px;">Light (Lux)</th>
                <th style="padding:12px 16px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="padding:11px 16px;color:var(--text-3);">{{ $log->id }}</td>
                    @if(auth()->user()->is_admin)
                    <td style="padding:11px 16px;font-family:monospace;font-size:12px;">{{ $log->device_id ?? '—' }}</td>
                    @endif
                    <td style="padding:11px 16px;font-family:monospace;font-size:12px;color:var(--text-2);">
                        <span data-timestamp="{{ $log->created_at->timestamp }}" data-live-mode="absolute">{{ $log->created_at->format('Y-m-d h:i:s A') }}</span>
                        <span style="display:block;font-size:11px;color:var(--text-3);"><span data-timestamp="{{ $log->created_at->timestamp }}" data-live-mode="ago">—</span></span>
                    </td>
                    <td style="padding:11px 16px;font-weight:600;">
                        {{ number_format($log->temperature, 1) }} °C
                    </td>
                    <td style="padding:11px 16px;">{{ $log->humidity }} %</td>
                    <td style="padding:11px 16px;">{{ $log->light_level }} Lux</td>
                    <td style="padding:11px 16px;">
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
                <tr>
                    <td colspan="{{ auth()->user()->is_admin ? 7 : 6 }}" style="text-align:center;padding:32px;color:var(--text-3);">
                        No records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
    <div class="pagination">
        @if($logs->onFirstPage())
            <span class="page-btn" style="opacity:0.4;">← Prev</span>
        @else
            <a href="{{ $logs->previousPageUrl() }}" class="page-btn">← Prev</a>
        @endif

        @foreach($logs->getUrlRange(max(1, $logs->currentPage()-2), min($logs->lastPage(), $logs->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="page-btn {{ $page === $logs->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach

        @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="page-btn">Next →</a>
        @else
            <span class="page-btn" style="opacity:0.4;">Next →</span>
        @endif
    </div>
@endif

{{-- System modification audit trail --}}
<div class="panel" style="margin-top:14px;padding:0;overflow:hidden;">
    <div class="panel-title" style="padding:14px 16px;margin:0;border-bottom:1px solid var(--border);">
        System modifications audit trail
        <span style="font-size:11px;color:var(--text-3);font-weight:500;">Newest first</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="padding:12px 16px;">Timestamp</th>
                <th style="padding:12px 16px;">Device</th>
                <th style="padding:12px 16px;">Event</th>
                <th style="padding:12px 16px;">Action</th>
                <th style="padding:12px 16px;">Actor</th>
                <th style="padding:12px 16px;">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td style="padding:11px 16px;font-family:monospace;font-size:12px;color:var(--text-2);">
                        <span data-timestamp="{{ $event->created_at->timestamp }}" data-live-mode="absolute">{{ $event->created_at->format('Y-m-d h:i:s A') }}</span>
                    </td>
                    <td style="padding:11px 16px;font-family:monospace;font-size:12px;">{{ $event->device_id ?? '—' }}</td>
                    <td style="padding:11px 16px;">
                        <span class="badge {{ $event->event_type === 'automation_change' ? 'badge-warn' : 'badge-blue' }}">
                            {{ str_replace('_', ' ', $event->event_type) }}
                        </span>
                    </td>
                    <td style="padding:11px 16px;">{{ $event->action }}</td>
                    <td style="padding:11px 16px;">
                        {{ $event->actor_name ?? 'System' }}
                        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;">{{ $event->actor_type ?? 'system' }}</div>
                    </td>
                    <td style="padding:11px 16px;font-family:monospace;font-size:11px;color:var(--text-2);">
                        @if(is_array($event->meta))
                            @php
                                $parts = [];
                                foreach ($event->meta as $k => $v) {
                                    if (is_scalar($v) || is_null($v)) {
                                        $parts[] = $k . '=' . ($v === null ? 'null' : $v);
                                    }
                                }
                            @endphp
                            {{ implode(' · ', $parts) ?: '—' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:24px;color:var(--text-3);">
                        No system modification events yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
