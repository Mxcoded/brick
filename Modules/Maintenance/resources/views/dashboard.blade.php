@extends('layouts.master')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-tachometer-alt me-2" style="color: var(--luxury-gold);"></i>Maintenance Dashboard</h2>
            <p class="text-muted mb-0">Overview of all maintenance issues</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i> All Logs
            </a>
            <a href="{{ route('maintenance.create') }}" class="btn" style="background-color: var(--luxury-gold); color: #fff;">
                <i class="fas fa-plus me-1"></i> New Log
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-primary">{{ $totalLogs }}</div>
                    <div class="text-muted small text-uppercase">Total Logs</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-warning">{{ $openLogs }}</div>
                    <div class="text-muted small text-uppercase">Open / In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-success">{{ $completedLogs }}</div>
                    <div class="text-muted small text-uppercase">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-danger">{{ $cancelledLogs }}</div>
                    <div class="text-muted small text-uppercase">Cancelled</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-info">{{ $thisMonth }}</div>
                    <div class="text-muted small text-uppercase">This Month</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold" style="color: var(--luxury-gold);">{{ $avgCompletionDays ? number_format($avgCompletionDays, 1) : '--' }}</div>
                    <div class="text-muted small text-uppercase">Avg Days to Complete</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Department Breakdown --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-building me-2" style="color: var(--luxury-gold);"></i>Issues by Department
                </div>
                <div class="card-body">
                    @if ($departmentStats->count())
                        @foreach ($departmentStats as $stat)
                            @php
                                $pct = $totalLogs > 0 ? round(($stat->count / $totalLogs) * 100) : 0;
                                $colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997'];
                                $color = $colors[$loop->index % count($colors)];
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium">{{ $stat->department }}</span>
                                    <span class="text-muted">{{ $stat->count }} ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height: 10px; background-color: #e9ecef;">
                                    <div class="progress-bar rounded" role="progressbar"
                                        style="width: {{ $pct }}%; background-color: {{ $color }};"
                                        aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0 text-center py-4">No logs yet.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Distribution --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="fas fa-chart-pie me-2" style="color: var(--luxury-gold);"></i>Status Distribution
                </div>
                <div class="card-body">
                    @if ($statusStats->count())
                        @php
                            $statusLabels = ['new' => 'New', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
                            $statusColors = ['new' => '#ffc107', 'in_progress' => '#0d6efd', 'completed' => '#198754', 'cancelled' => '#dc3545'];
                        @endphp
                        @foreach ($statusStats as $stat)
                            @php
                                $pct = $totalLogs > 0 ? round(($stat->count / $totalLogs) * 100) : 0;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium">
                                        <span class="badge rounded-pill" style="background-color: {{ $statusColors[$stat->status] ?? '#6c757d' }};">
                                            {{ $statusLabels[$stat->status] ?? ucfirst($stat->status) }}
                                        </span>
                                    </span>
                                    <span class="text-muted">{{ $stat->count }} ({{ $pct }}%)</span>
                                </div>
                                <div class="progress" style="height: 10px; background-color: #e9ecef;">
                                    <div class="progress-bar rounded" role="progressbar"
                                        style="width: {{ $pct }}%; background-color: {{ $statusColors[$stat->status] ?? '#6c757d' }};"
                                        aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0 text-center py-4">No logs yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════ Daily Readings ══════ --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="fas fa-clipboard-list me-2" style="color: var(--luxury-gold);"></i>Daily Readings</span>
            <div class="d-flex gap-2">
                <a href="{{ route('maintenance.readings.create') }}" class="btn btn-sm" style="background-color: var(--luxury-gold); color: #fff;"><i class="fas fa-plus me-1"></i> New</a>
                <a href="{{ route('maintenance.readings.index') }}" class="btn btn-sm btn-outline-secondary">View Report</a>
            </div>
        </div>
        <div class="card-body">
            @if($lastReadingDate)
            <p class="small text-muted mb-3">Last reading: {{ \Carbon\Carbon::parse($lastReadingDate)->format('M d, Y') }} &middot; {{ $readingsThisWeek }} readings this week</p>
            @endif
            <div class="row g-3">
                {{-- Generators --}}
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded" style="background: #f8f9fa;" title="Latest generator screen readings from {{ $todayGen->count() > 0 ? $todayGen->first()->reading_date->format('M d') : 'recent entries' }}">
                        <h6 class="fw-bold small mb-2"><i class="fas fa-bolt me-1 text-warning"></i>Generators</h6>
                        @if($todayGen->count())
                        @foreach($todayGen as $gen)
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ str_replace('_', ' ', ucfirst($gen->category)) }}</span>
                            <span class="fw-bold">{{ $gen->reading_value }}%</span>
                        </div>
                        @endforeach
                        @elseif($recentReadings->where('reading_type', 'generator')->count())
                        @php $recentGen = $recentReadings->where('reading_type', 'generator'); @endphp
                        @foreach($recentGen as $gen)
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ str_replace('_', ' ', ucfirst($gen->category)) }}</span>
                            <span class="fw-bold">{{ $gen->reading_value }}%</span>
                        </div>
                        @endforeach
                        <small class="text-muted">{{ $recentGen->first()->reading_date->format('M d') }}</small>
                        @else
                        <p class="small text-muted mb-0">No readings yet</p>
                        @endif
                    </div>
                </div>
                {{-- Diesel Reservoir --}}
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded" style="background: #f8f9fa;">
                        <h6 class="fw-bold small mb-2"><i class="fas fa-oil-can me-1 text-secondary"></i>Diesel Reservoir</h6>
                        @if($todayDiesel)
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Level</span>
                            <span class="fw-bold">{{ number_format($todayDiesel->reading_value) }}L</span>
                        </div>
                        @if($todayDiesel->capacity)
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Capacity</span>
                            <span>{{ number_format($todayDiesel->capacity) }}L</span>
                        </div>
                        @endif
                        @else
                        <p class="small text-muted mb-0">No reading today</p>
                        @endif
                    </div>
                </div>
                {{-- Water Tank --}}
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded" style="background: #f8f9fa;">
                        <h6 class="fw-bold small mb-2"><i class="fas fa-water me-1 text-info"></i>Water Tank</h6>
                        @if($todayWater)
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Level</span>
                            <span class="fw-bold">{{ $todayWater->reading_value }}%</span>
                        </div>
                        @else
                        <p class="small text-muted mb-0">No reading today</p>
                        @endif
                    </div>
                </div>
                {{-- Cold Room --}}
                <div class="col-md-3 col-6">
                    <div class="p-3 rounded" style="background: #f8f9fa;">
                        <h6 class="fw-bold small mb-2"><i class="fas fa-snowflake me-1 text-primary"></i>Cold Room</h6>
                        @if($todayColdRoom->count())
                        @foreach($todayColdRoom as $cr)
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">{{ ucfirst($cr->category) }}</span>
                            <span class="fw-bold">{{ number_format($cr->reading_value, 1) }}&deg;C</span>
                        </div>
                        @endforeach
                        @else
                        <p class="small text-muted mb-0">No reading today</p>
                        @endif
                    </div>
                </div>
            </div>
            {{-- Recent Readings History --}}
            @if($recentReadings->count())
            <hr class="my-3">
            <h6 class="fw-semibold small mb-2">Recent Entries</h6>
            <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0 small">
                    <thead>
                        <tr class="text-muted">
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-end">Reading</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReadings as $r)
                        <tr>
                            <td>{{ $r->reading_date->format('M d') }}</td>
                            <td>
                                @php
                                $icons = ['generator' => 'bolt', 'diesel_reservoir' => 'oil-can', 'water_tank' => 'water', 'cold_room' => 'snowflake'];
                                @endphp
                                <i class="fas fa-{{ $icons[$r->reading_type] ?? 'circle' }} me-1" style="font-size: 8px;"></i>
                                {{ \Modules\Maintenance\Models\MaintenanceReading::TYPES[$r->reading_type] ?? $r->reading_type }}
                                @if($r->category)
                                <small class="text-muted">({{ str_replace('_', ' ', $r->category) }})</small>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                @if($r->reading_type === 'cold_room') {{ number_format($r->reading_value, 1) }}&deg;C
                                @elseif($r->reading_type === 'diesel_reservoir') {{ number_format($r->reading_value, 0) }}L
                                @else {{ number_format($r->reading_value, 1) }}%
                                @endif
                            </td>
                            <td class="text-muted">{{ $r->recorder?->name ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Recent Logs --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="fas fa-clock me-2" style="color: var(--luxury-gold);"></i>Recent Activity</span>
            <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
        <div class="card-body p-0">
            @if ($recentLogs->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Log</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentLogs as $log)
                                <tr>
                                    <td>{{ $log->location }}</td>
                                    <td><span class="badge bg-secondary">{{ $log->department }}</span></td>
                                    <td>
                                        <a href="{{ route('maintenance.show', $log->id) }}" class="text-decoration-none">
                                            {{ Str::limit($log->nature_of_complaint, 50) }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $badge = ['new' => 'warning', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $badge[$log->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $log->complaint_datetime->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0 text-center py-4">No maintenance logs yet. <a href="{{ route('maintenance.create') }}">Create the first one</a>.</p>
            @endif
        </div>
    </div>
@endsection

@section('styles')
<style>
    .card { border-radius: 10px; }
    .card-header { border-radius: 10px 10px 0 0 !important; border-bottom: 2px solid #f0f0f0; }
    .progress { border-radius: 10px; }
    .progress-bar { transition: width 0.6s ease; }
    .fs-1 { font-size: 2.2rem !important; }
</style>
@endsection
