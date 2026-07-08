@extends('layouts.master')

@section('title', 'Occupancy Report')

@push('page-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-bed me-2"></i>Occupancy Report</h4>
            <p class="text-muted mb-0">Daily occupancy, arrivals and departures</p>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')
    @include('frontdeskcrm::reports.partials.date-filter')

    {{-- KPI Cards --}}
    <div class="row g-3 my-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <small class="text-muted text-uppercase fw-bold">Avg Occupancy</small>
                <h3 class="fw-bold mb-0 text-primary">{{ number_format($avgOccupancy, 1) }}%</h3>
                <small class="text-muted">{{ $from->format('M d') }} - {{ $to->format('M d, Y') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <small class="text-muted text-uppercase fw-bold">Total Rooms</small>
                <h3 class="fw-bold mb-0 text-success">{{ $totalRooms }}</h3>
                <small class="text-muted">Available inventory</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <small class="text-muted text-uppercase fw-bold">Days Reported</small>
                <h3 class="fw-bold mb-0 text-info">{{ $daily->count() }}</h3>
                <small class="text-muted">Date range</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-chart-bar me-2"></i>Daily Occupancy
                </div>
                <div class="card-body">
                    <canvas id="occupancyChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-door-open me-2"></i>By Room Type
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="text-end">Rooms</th>
                                <th class="text-end">Occupied</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byRoomType as $rt)
                            <tr>
                                <td>{{ $rt->name }}</td>
                                <td class="text-end">{{ $rt->total_rooms }}</td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ $rt->checked_in_count }}</span>
                                    <small class="text-muted">({{ $rt->total_rooms > 0 ? round(($rt->checked_in_count / $rt->total_rooms) * 100) : 0 }}%)</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-list me-2"></i>Daily Breakdown
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Arrivals</th>
                            <th class="text-end">Departures</th>
                            <th class="text-end">Occupied</th>
                            <th class="text-end">Available</th>
                            <th class="text-end">Occupancy %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daily as $day)
                        <tr>
                            <td class="fw-bold">{{ $day['label'] }}</td>
                            <td class="text-end">{{ $day['arrivals'] }}</td>
                            <td class="text-end">{{ $day['departures'] }}</td>
                            <td class="text-end">{{ $day['occupied'] }}</td>
                            <td class="text-end">{{ $day['available'] }}</td>
                            <td class="text-end">
                                <span class="fw-bold {{ $day['occupancy_pct'] >= 70 ? 'text-success' : ($day['occupancy_pct'] >= 40 ? 'text-warning' : 'text-danger') }}">
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
    var ctx = document.getElementById('occupancyChart').getContext('2d');
    var labels = {!! json_encode($daily->pluck('label')) !!};
    var occupied = {!! json_encode($daily->pluck('occupied')) !!};
    var arrivals = {!! json_encode($daily->pluck('arrivals')) !!};
    var departures = {!! json_encode($daily->pluck('departures')) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Occupied', data: occupied, backgroundColor: '#C8A165', borderRadius: 3 },
                { label: 'Arrivals', data: arrivals, backgroundColor: '#28a745', borderRadius: 3 },
                { label: 'Departures', data: departures, backgroundColor: '#dc3545', borderRadius: 3 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
