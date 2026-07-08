@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-charcoal mb-1"><i class="fas fa-chart-line me-2 text-gold"></i>Readings Report</h3>
        <p class="text-muted mb-0">Historical daily readings for generators, diesel, water, and cold rooms</p>
    </div>
    <a href="{{ route('maintenance.readings.create') }}" class="btn btn-gold btn-sm"><i class="fas fa-plus me-1"></i> New Reading</a>
</div>

{{-- Filters --}}
<form method="GET" class="row g-2 mb-4">
    <div class="col-auto">
        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From">
    </div>
    <div class="col-auto">
        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To">
    </div>
    <div class="col-auto">
        <select name="reading_type" class="form-select form-select-sm">
            <option value="">All Types</option>
            @foreach(\Modules\Maintenance\Models\MaintenanceReading::TYPES as $val => $label)
            <option value="{{ $val }}" {{ request('reading_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
        <a href="{{ route('maintenance.readings.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
</form>

{{-- Export Forms (outside filter form to avoid nested form bug) --}}
@if($readings->count())
<div class="d-flex gap-2 mb-4">
    <form action="{{ route('maintenance.readings.export') }}" method="POST">
        @csrf
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="to" value="{{ request('to') }}">
        <input type="hidden" name="reading_type" value="{{ request('reading_type') }}">
        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-pdf me-1"></i> Export PDF</button>
    </form>
    <form action="{{ route('maintenance.readings.export-excel') }}" method="POST">
        @csrf
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="to" value="{{ request('to') }}">
        <input type="hidden" name="reading_type" value="{{ request('reading_type') }}">
        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</button>
    </form>
</div>
@endif

{{-- Readings Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small">Date</th>
                        <th class="small">Type</th>
                        <th class="small">Category</th>
                        <th class="small text-end">Reading</th>
                        <th class="small text-end">Capacity</th>
                        <th class="small text-end">Calculated</th>
                        <th class="small">Notes</th>
                        <th class="small">Recorded By</th>
                        <th class="small">At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readings as $r)
                    <tr>
                        <td><a href="{{ route('maintenance.readings.show', $r->reading_date->toDateString()) }}" class="text-decoration-none">{{ $r->reading_date->format('M d, Y') }}</a></td>
                        <td><span class="badge bg-{{ $r->reading_type === 'generator' ? 'warning' : ($r->reading_type === 'diesel_reservoir' ? 'secondary' : ($r->reading_type === 'water_tank' ? 'info' : 'primary')) }}">{{ \Modules\Maintenance\Models\MaintenanceReading::TYPES[$r->reading_type] ?? $r->reading_type }}</span></td>
                        <td class="small">{{ $r->category ? ucfirst(str_replace('_', ' ', $r->category)) : '—' }}</td>
                        <td class="small text-end fw-bold">
                            @if($r->reading_type === 'cold_room')
                                {{ number_format($r->reading_value, 1) }}&deg;C
                            @elseif($r->reading_type === 'diesel_reservoir')
                                {{ number_format($r->reading_value, 0) }}L
                            @else
                                {{ number_format($r->reading_value, 1) }}%
                            @endif
                        </td>
                        <td class="small text-end text-muted">{{ $r->capacity ? number_format($r->capacity) : '—' }}</td>
                        <td class="small text-end fw-bold">{{ $r->reading_type === 'diesel_reservoir' ? '—' : ($r->calculated_value ? number_format($r->calculated_value, $r->reading_type === 'generator' ? 2 : 0) : '—') }}</td>
                        <td class="small text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $r->notes ?: '—' }}</td>
                        <td class="small">{{ $r->recorder?->name ?: '—' }}</td>
                        <td class="small text-muted">{{ $r->created_at->format('h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4 small">No readings found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($readings->hasPages())
    <div class="card-footer bg-white">
        {{ $readings->links() }}
    </div>
    @endif
</div>
<style>
    .x-small { font-size: 0.7rem; }
    .card { border-radius: 10px; }
    .card-header { border-radius: 10px 10px 0 0 !important; border-bottom: 2px solid #f0f0f0; }
</style>
@endsection
