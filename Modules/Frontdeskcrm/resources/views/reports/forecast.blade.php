@extends('layouts.master')

@section('title', 'Forecast Report')

@push('page-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-chart-bar me-2"></i>Forecast Report</h4>
            <p class="text-muted mb-0">Projected occupancy, arrivals, and departures</p>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')

    {{-- Date filter --}}
    <form method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label small mb-1">Forecast Until</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to->format('Y-m-d') }}" min="{{ $from->format('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
        </div>
        @if(request('date_to'))
        <div class="col-auto">
            <a href="{{ route('frontdesk.reports.forecast') }}" class="btn btn-sm btn-light">30 Days</a>
        </div>
        @endif
    </form>

    {{-- KPI Cards --}}
    <div class="row g-3 my-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold">Total Arrivals</small>
                <h3 class="fw-bold mb-0 text-success">{{ $totalArrivals }}</h3>
                <small class="text-muted">{{ $from->format('M d') }} - {{ $to->format('M d') }}</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-danger border-4">
                <small class="text-muted text-uppercase fw-bold">Total Departures</small>
                <h3 class="fw-bold mb-0 text-danger">{{ $totalDepartures }}</h3>
                <small class="text-muted">Scheduled check-outs</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold">Avg Projected Occ.</small>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($avgProjectedOccupancy, 1) }}%</h3>
                <small class="text-muted">Over forecast period</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-info border-4">
                <small class="text-muted text-uppercase fw-bold">Total Rooms</small>
                <h3 class="fw-bold mb-0 text-info">{{ $totalRooms }}</h3>
                <small class="text-muted">Available inventory</small>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-chart-bar me-2"></i>Forecast Overview
        </div>
        <div class="card-body">
            <canvas id="forecastChart" height="280"></canvas>
        </div>
    </div>

    {{-- Daily Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-list me-2"></i>Daily Forecast
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Arrivals</th>
                            <th class="text-end">Departures</th>
                            <th class="text-end">In House</th>
                            <th class="text-end">Reserved</th>
                            <th class="text-end">Projected</th>
                            <th class="text-end">Available</th>
                            <th class="text-end">Occupancy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daily as $day)
                        <tr>
                            <td class="fw-bold">{{ $day['label'] }}</td>
                            <td class="text-end">{{ $day['arrivals'] }}</td>
                            <td class="text-end">{{ $day['departures'] }}</td>
                            <td class="text-end">{{ $day['in_house'] }}</td>
                            <td class="text-end">{{ $day['reserved'] }}</td>
                            <td class="text-end fw-bold">{{ $day['projected'] }}</td>
                            <td class="text-end">{{ $day['available'] }}</td>
                            <td class="text-end">
                                <span class="fw-bold {{ $day['occupancy_pct'] >= 70 ? 'text-success' : ($day['occupancy_pct'] >= 40 ? 'text-warning' : 'text-muted') }}">
                                    {{ $day['occupancy_pct'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    var ctx = document.getElementById('forecastChart').getContext('2d');
    var labels = {!! json_encode($daily->pluck('label')) !!};
    var projected = {!! json_encode($daily->pluck('projected')) !!};
    var arrivals = {!! json_encode($daily->pluck('arrivals')) !!};
    var departures = {!! json_encode($daily->pluck('departures')) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Projected Occupancy', data: projected, backgroundColor: '#C8A165', borderRadius: 3 },
                { label: 'Arrivals', data: arrivals, backgroundColor: '#28a745', borderRadius: 3 },
                { label: 'Departures', data: departures, backgroundColor: '#dc3545', borderRadius: 3 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
