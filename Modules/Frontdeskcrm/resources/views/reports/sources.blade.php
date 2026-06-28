@extends('layouts.master')

@section('title', 'Source Analysis Report')

@push('page-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-globe me-2"></i>Source Analysis</h4>
            <p class="text-muted mb-0">Booking source and channel performance</p>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')
    @include('frontdeskcrm::reports.partials.date-filter')

    {{-- KPI Cards --}}
    <div class="row g-3 my-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold">Gross Revenue</small>
                <h4 class="fw-bold mb-0 text-primary">₦{{ number_format($grandTotalRevenue, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-danger border-4">
                <small class="text-muted text-uppercase fw-bold">Total Commission</small>
                <h4 class="fw-bold mb-0 text-danger">₦{{ number_format($grandTotalCommission, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold">Net Revenue</small>
                <h4 class="fw-bold mb-0 text-success">₦{{ number_format($grandNetRevenue, 2) }}</h4>
            </div>
        </div>
    </div>

    {{-- Source Table --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-plug me-2"></i>Revenue by Booking Source
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th class="text-end">Bookings</th>
                            <th class="text-end">Checked In</th>
                            <th class="text-end">Checked Out</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Commission Rate</th>
                            <th class="text-end">Commission Cost</th>
                            <th class="text-end">Net Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bySource as $source)
                        <tr>
                            <td class="fw-bold">{{ $source->name }}</td>
                            <td class="text-end">{{ $source->total_bookings }}</td>
                            <td class="text-end">{{ $source->checked_in }}</td>
                            <td class="text-end">{{ $source->checked_out }}</td>
                            <td class="text-end">₦{{ number_format($source->revenue, 2) }}</td>
                            <td class="text-end">{{ $source->commission_rate }}%</td>
                            <td class="text-end text-danger">₦{{ number_format($source->commission, 2) }}</td>
                            <td class="text-end fw-bold text-success">₦{{ number_format($source->net_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Channel Manager --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-globe me-2"></i>Channel Connections
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Channel</th>
                            <th>Provider</th>
                            <th>Mapped Rooms</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byChannel as $ch)
                        <tr>
                            <td class="fw-bold">{{ $ch->name }}</td>
                            <td>{{ $ch->provider }}</td>
                            <td class="text-end">{{ $ch->mapped_rooms }}</td>
                            <td>
                                @if($ch->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-globe fa-2x mb-2"></i>
                                <p class="mb-0">No channels configured yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
