@extends('layouts.master')

@section('title', 'Daily Operations Report')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-file-alt me-2"></i>Daily Operations Report</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('frontdesk.reports.index') }}">Frontdesk</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontdesk.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Daily Report</li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge bg-primary fs-6 py-2 px-3">{{ $date->format('l, d F Y') }}</span>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')

    {{-- Date Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 px-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Select Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
                    <a href="{{ route('frontdesk.reports.daily') }}" class="btn btn-sm btn-light">Today</a>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Row 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Occupancy</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $occupiedRooms }} / {{ $totalRooms }}</h3>
                            <small class="text-muted">{{ $occupancyRate }}% occupied</small>
                        </div>
                        <div class="fs-1 text-primary opacity-25"><i class="fas fa-bed"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Today's Revenue</small>
                            <h3 class="fw-bold mb-0 mt-1">₦{{ number_format($totalRevenue, 2) }}</h3>
                            <small class="text-muted">Room + Restaurant</small>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="fas fa-money-bill-wave"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Today's Payments</small>
                            <h3 class="fw-bold mb-0 mt-1">₦{{ number_format($totalPayments, 2) }}</h3>
                            <small class="text-muted">Payments received</small>
                        </div>
                        <div class="fs-1 text-info opacity-25"><i class="fas fa-credit-card"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Outstanding Balance</small>
                            <h3 class="fw-bold mb-0 mt-1">₦{{ number_format($outstandingBalance, 2) }}</h3>
                            <small class="text-muted">Unpaid charges</small>
                        </div>
                        <div class="fs-1 text-warning opacity-25"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Row 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Check-ins Today</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $checkinsToday }}</h3>
                            <small class="text-muted">New arrivals</small>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="fas fa-sign-in-alt"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Check-outs Today</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $checkoutsToday }}</h3>
                            <small class="text-muted">Departures</small>
                        </div>
                        <div class="fs-1 text-warning opacity-25"><i class="fas fa-sign-out-alt"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active Registrations</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $activeRegistrations }}</h3>
                            <small class="text-muted">Currently in-house</small>
                        </div>
                        <div class="fs-1 text-primary opacity-25"><i class="fas fa-clipboard-list"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Restaurant Orders</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $restaurantOrders }}</h3>
                            <small class="text-muted">Orders today</small>
                        </div>
                        <div class="fs-1 text-danger opacity-25"><i class="fas fa-utensils"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Occupancy Summary --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 3px solid #C8A165;">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-bed me-2" style="color: #C8A165;"></i>Occupancy Summary
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Rooms Occupied</span>
                        <span class="fw-bold">{{ $occupiedRooms }} / {{ $totalRooms }}</span>
                    </div>
                    <div class="progress mb-3" style="height: 12px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ $occupancyRate }}%"
                             aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded-3 py-2">
                                <small class="text-muted d-block">Occupied</small>
                                <strong class="text-primary">{{ $occupiedRooms }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 py-2">
                                <small class="text-muted d-block">Available</small>
                                <strong class="text-success">{{ $totalRooms - $occupiedRooms }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded-3 py-2">
                                <small class="text-muted d-block">Rate</small>
                                <strong class="text-info">{{ $occupancyRate }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Breakdown --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 3px solid #C8A165;">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-chart-pie me-2" style="color: #C8A165;"></i>Revenue Breakdown
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-bed text-primary me-2"></i>Room Revenue</td>
                                    <td class="text-end fw-bold">₦{{ number_format($roomRevenue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-utensils text-danger me-2"></i>Restaurant Revenue</td>
                                    <td class="text-end fw-bold">₦{{ number_format($restaurantRevenue, 2) }}</td>
                                </tr>
                                <tr class="table-light">
                                    <td class="fw-bold">Total Revenue</td>
                                    <td class="text-end fw-bold" style="color: #C8A165;">₦{{ number_format($totalRevenue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-credit-card text-info me-2"></i>Payments Received</td>
                                    <td class="text-end fw-bold text-success">₦{{ number_format($totalPayments, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Registrations Today --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="border-left: 3px solid #C8A165;">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-clipboard-list me-2" style="color: #C8A165;"></i>Recent Registrations Today
            <span class="badge bg-primary float-end">{{ $registrations->count() }}</span>
        </div>
        <div class="card-body p-0">
            @if($registrations->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Rate</th>
                            <th>Nights</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrations as $reg)
                        <tr>
                            <td class="fw-bold">{{ $reg->guest->full_name ?? $reg->full_name ?? 'N/A' }}</td>
                            <td>{{ $reg->roomUnit->unit_number ?? $reg->room_allocation ?? 'N/A' }}</td>
                            <td>
                                @if($reg->stay_status === 'checked_in')
                                    <span class="badge bg-success">Checked In</span>
                                @elseif($reg->stay_status === 'reserved')
                                    <span class="badge bg-warning text-dark">Reserved</span>
                                @elseif($reg->stay_status === 'checked_out')
                                    <span class="badge bg-secondary">Checked Out</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ ucfirst($reg->stay_status) }}</span>
                                @endif
                            </td>
                            <td>₦{{ number_format($reg->room_rate ?? 0, 2) }}</td>
                            <td>{{ $reg->no_of_nights ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                <p class="mb-0">No check-ins recorded for this date.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Outstanding Balances --}}
    @if($unpaidCharges->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-3" style="border-left: 3px solid #C8A165;">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-exclamation-triangle me-2" style="color: #C8A165;"></i>Outstanding Balances
            <span class="badge bg-warning text-dark float-end">{{ $unpaidCharges->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Guest Name</th>
                            <th>Total Charges</th>
                            <th>Total Paid</th>
                            <th>Balance Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unpaidCharges as $charge)
                        <tr>
                            <td class="fw-bold">{{ $charge->full_name }}</td>
                            <td>₦{{ number_format($charge->total_charges, 2) }}</td>
                            <td>₦{{ number_format($charge->total_paid, 2) }}</td>
                            <td class="fw-bold text-danger">₦{{ number_format($charge->balance, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('page-styles')
<style>
    .card {
        transition: transform 0.15s ease-in-out;
    }
    .card:hover {
        transform: translateY(-1px);
    }
    .table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    .progress {
        border-radius: 10px;
    }
    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Daily Operations Report loaded for {{ $date->format("Y-m-d") }}');
    });
</script>
@endpush
