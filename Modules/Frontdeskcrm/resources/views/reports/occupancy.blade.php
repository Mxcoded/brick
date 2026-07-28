@extends('layouts.master')

@section('title', 'Occupancy Report')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Occupancy Report</h4>
        <a href="{{ route('frontdesk.reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <form method="GET" action="{{ route('frontdesk.reports.occupancy') }}" class="row g-2 mb-4">
        <div class="col-auto">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $to->format('Y-m-d') }}">
        </div>
        <div class="col-auto d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Refresh</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6>Avg. Occupancy Rate</h6>
                    <h3 class="mb-0">{{ $avgOccupancy }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6>Period</h6>
                    <h5 class="mb-0">{{ $from->format('M d') }} &ndash; {{ $to->format('M d, Y') }}</h5>
                    <small>{{ $from->diffInDays($to) + 1 }} days</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6>Total Rooms</h6>
                    <h3 class="mb-0">{{ $totalRooms }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Daily Occupancy</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                    <th>Occupancy %</th>
                                    <th>Arrivals</th>
                                    <th>Departures</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyData as $day)
                                <tr class="{{ $day['occupancy_rate'] >= 80 ? 'table-success' : ($day['occupancy_rate'] >= 50 ? '' : 'table-warning') }}">
                                    <td>{{ $day['date']->format('M d') }}</td>
                                    <td>{{ $day['date']->format('D') }}</td>
                                    <td>{{ $day['occupied'] }}</td>
                                    <td>{{ $day['available'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height:8px;">
                                                <div class="progress-bar {{ $day['occupancy_rate'] >= 80 ? 'bg-success' : ($day['occupancy_rate'] >= 50 ? 'bg-info' : 'bg-warning') }}"
                                                     style="width:{{ $day['occupancy_rate'] }}%"></div>
                                            </div>
                                            <span class="small">{{ $day['occupancy_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td>{{ $day['arrivals'] }}</td>
                                    <td>{{ $day['departures'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">By Room Type</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Room Type</th><th class="text-end">Occupied</th></tr>
                        </thead>
                        <tbody>
                            @forelse($byRoomType as $rt)
                            <tr>
                                <td>{{ $rt->roomType?->name ?? 'Unknown' }}</td>
                                <td class="text-end">{{ $rt->count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalOccupied = array_sum(array_column($dailyData, 'occupied'));
                        $totalAvailable = array_sum(array_column($dailyData, 'available'));
                        $days = count($dailyData);
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Room-Nights Sold</span>
                        <span class="fw-bold">{{ $totalOccupied }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Room-Nights Available</span>
                        <span class="fw-bold">{{ $totalOccupied + $totalAvailable }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Period Avg. Occupancy</span>
                        <span class="fw-bold">{{ $avgOccupancy }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
