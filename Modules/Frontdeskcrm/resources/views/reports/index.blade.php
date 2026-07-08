@extends('layouts.master')

@section('title', 'Reports Dashboard')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-chart-pie me-2"></i>Reports Dashboard</h4>
            <p class="text-muted mb-0">Key performance indicators for {{ $currentMonthLabel }}</p>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">In House</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $inHouse }}</h3>
                            <small class="text-muted">/ {{ $totalRooms }} rooms ({{ $occupancyPercent }}%)</small>
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
                            <small class="text-muted text-uppercase fw-bold">Arrivals Today</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $arrivalsToday }}</h3>
                            <small class="text-muted">Expected check-ins</small>
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
                            <small class="text-muted text-uppercase fw-bold">Departures Today</small>
                            <h3 class="fw-bold mb-0 mt-1">{{ $departuresToday }}</h3>
                            <small class="text-muted">Scheduled check-outs</small>
                        </div>
                        <div class="fs-1 text-warning opacity-25"><i class="fas fa-sign-out-alt"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">MTD Revenue</small>
                            <h3 class="fw-bold mb-0 mt-1">₦{{ number_format($monthRevenue, 2) }}</h3>
                            <small class="text-muted">Payments collected</small>
                        </div>
                        <div class="fs-1 text-info opacity-25"><i class="fas fa-money-bill-wave"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary KPI Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <small class="text-muted text-uppercase fw-bold">Room Revenue</small>
                    <h4 class="fw-bold mb-0 text-primary">₦{{ number_format($roomRevenue, 2) }}</h4>
                    <small class="text-muted">{{ $currentMonthLabel }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <small class="text-muted text-uppercase fw-bold">ADR</small>
                    <h4 class="fw-bold mb-0 text-success">₦{{ number_format($adr, 2) }}</h4>
                    <small class="text-muted">Average Daily Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <small class="text-muted text-uppercase fw-bold">RevPAR</small>
                    <h4 class="fw-bold mb-0 text-info">₦{{ number_format($revpar, 2) }}</h4>
                    <small class="text-muted">Revenue Per Available Room</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Night Audits --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-moon me-2"></i>Recent Night Audits
        </div>
        <div class="card-body p-0">
            @if($recentAudits->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Occupancy</th>
                            <th>Room Revenue</th>
                            <th>Extra Charges</th>
                            <th>Tax</th>
                            <th>Total Revenue</th>
                            <th>Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAudits as $audit)
                        <tr>
                            <td class="fw-bold">{{ $audit->audit_date->format('D, M d') }}</td>
                            <td>{{ $audit->occupancy_count }} / {{ $audit->total_rooms }} ({{ $audit->occupancy_percentage }}%)</td>
                            <td>₦{{ number_format($audit->room_revenue, 2) }}</td>
                            <td>₦{{ number_format($audit->extra_revenue, 2) }}</td>
                            <td>₦{{ number_format($audit->tax_amount, 2) }}</td>
                            <td class="fw-bold">₦{{ number_format($audit->total_revenue, 2) }}</td>
                            <td>₦{{ number_format($audit->total_payments, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4">
                <i class="fas fa-moon fa-2x mb-2"></i>
                <p class="mb-0">No night audits have been run yet.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
