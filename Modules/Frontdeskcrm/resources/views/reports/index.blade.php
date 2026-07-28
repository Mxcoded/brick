@extends('layouts.master')

@section('title', 'Reports')
@section('page-content')

<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">Reports Dashboard</h4>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h5>Daily Revenue Report</h5>
                    <p class="text-muted">View room revenue, payment breakdown, occupancy, ADR, and RevPAR for any date.</p>
                    <a href="{{ route('frontdesk.reports.daily-revenue') }}" class="btn btn-gold mt-2">
                        <i class="fas fa-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                    <h5>Arrivals &amp; Departures</h5>
                    <p class="text-muted">Forecast guest arrivals and departures for the next 7 days or custom range.</p>
                    <a href="{{ route('frontdesk.reports.arrivals-departures') }}" class="btn btn-gold mt-2">
                        <i class="fas fa-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-5">
                    <i class="fas fa-bed fa-3x text-warning mb-3"></i>
                    <h5>Occupancy Report</h5>
                    <p class="text-muted">Daily occupancy rates, arrivals, departures by room type over any date range.</p>
                    <a href="{{ route('frontdesk.reports.occupancy') }}" class="btn btn-gold mt-2">
                        <i class="fas fa-eye me-2"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
