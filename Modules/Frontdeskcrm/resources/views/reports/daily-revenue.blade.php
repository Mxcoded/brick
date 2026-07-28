@extends('layouts.master')

@section('title', 'Daily Revenue Report')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Daily Revenue Report</h4>
        <div>
            <a href="{{ route('frontdesk.reports.daily-revenue', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
               class="btn btn-outline-secondary btn-sm me-1">&larr; Previous</a>
            <span class="mx-2 fw-semibold">{{ $date->format('l, M d, Y') }}</span>
            <a href="{{ route('frontdesk.reports.daily-revenue', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
               class="btn btn-outline-secondary btn-sm ms-1">Next &rarr;</a>
        </div>
    </div>

    <form method="GET" action="{{ route('frontdesk.reports.daily-revenue') }}" class="row g-2 mb-4">
        <div class="col-auto">
            <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Go</button>
        </div>
    </form>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6>Occupancy</h6>
                    <h3 class="mb-0">{{ $occupancyRate }}%</h3>
                    <small>{{ $checkedIn }} / {{ $totalRooms }} rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6>ADR</h6>
                    <h3 class="mb-0">{{ number_format($adr, 2) }}</h3>
                    <small>Avg. Daily Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6>RevPAR</h6>
                    <h3 class="mb-0">{{ number_format($revpar, 2) }}</h3>
                    <small>Revenue per Available Room</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h6>Room Revenue</h6>
                    <h3 class="mb-0">{{ number_format($roomRevenue->total ?? 0, 2) }}</h3>
                    <small>{{ $roomRevenue->count ?? 0 }} charges</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Revenue Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Room Revenue</td>
                            <td class="text-end fw-bold">{{ number_format($roomRevenue->total ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Breakfast Revenue</td>
                            <td class="text-end">{{ number_format($breakfastRevenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Extension Revenue</td>
                            <td class="text-end">{{ number_format($extensionRevenue, 2) }}</td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>Total Revenue</strong></td>
                            <td class="text-end fw-bold">
                                {{ number_format(($roomRevenue->total ?? 0) + $breakfastRevenue + $extensionRevenue, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Methods</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Method</th><th class="text-end">Count</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $pmt)
                            <tr>
                                <td>{{ ucfirst($pmt->payment_method) }}</td>
                                <td class="text-end">{{ $pmt->count }}</td>
                                <td class="text-end">{{ number_format($pmt->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">No payments today.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Arrivals &amp; Departures</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Arrivals Today</span>
                        <span class="fw-bold text-success">{{ $arrivals }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Departures Today</span>
                        <span class="fw-bold text-warning">{{ $departures }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Checked Out</span>
                        <span class="fw-bold text-secondary">{{ $checkedOut }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Booking Sources (In-House)</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        @forelse($bySource as $source)
                        <tr>
                            <td>{{ $source->bookingSource?->name ?? 'Unknown' }}</td>
                            <td class="text-end">{{ $source->count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-2">No data</td></tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Room Inventory</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Rooms</span>
                        <span class="fw-bold">{{ $totalRooms }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Occupied</span>
                        <span class="fw-bold text-primary">{{ $checkedIn }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Available</span>
                        <span class="fw-bold text-success">{{ $availableRooms }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
