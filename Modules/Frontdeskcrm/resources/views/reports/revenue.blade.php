@extends('layouts.master')

@section('title', 'Revenue Report')

@push('page-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-chart-line me-2"></i>Revenue Report</h4>
            <p class="text-muted mb-0">Revenue breakdown, trends, and sources</p>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')
    @include('frontdeskcrm::reports.partials.date-filter')

    {{-- KPI Cards --}}
    <div class="row g-3 my-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold">Room Revenue</small>
                <h4 class="fw-bold mb-0 text-primary">₦{{ number_format($roomRevenueTotal, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-warning border-4">
                <small class="text-muted text-uppercase fw-bold">Extra Charges</small>
                <h4 class="fw-bold mb-0 text-warning">₦{{ number_format($extraRevenueTotal, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-info border-4">
                <small class="text-muted text-uppercase fw-bold">Est. VAT ({{ app(\App\Services\PropertyService::class)->taxRate() }}%)</small>
                <h4 class="fw-bold mb-0 text-info">₦{{ number_format($taxEstimate, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold">Payments</small>
                <h4 class="fw-bold mb-0 text-success">₦{{ number_format($paymentsTotal, 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-chart-area me-2"></i>Daily Revenue Trend
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-chart-pie me-2"></i>Revenue by Payment Method
                </div>
                <div class="card-body">
                    <canvas id="paymentPieChart" height="200"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm mb-0">
                            @foreach($byPaymentMethod as $pm)
                            <tr>
                                <td>{{ $pm->payment_method }}</td>
                                <td class="text-end fw-bold">₦{{ number_format($pm->total, 2) }}</td>
                                <td class="text-end text-muted">{{ $pm->count }} txns</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- By Source --}}
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
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Commission</th>
                            <th class="text-end">Net Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bySource as $source)
                        <tr>
                            <td class="fw-bold">{{ $source->name }}</td>
                            <td class="text-end">₦{{ number_format($source->revenue, 2) }}</td>
                            <td class="text-end text-danger">- ₦{{ number_format($source->commission, 2) }}</td>
                            <td class="text-end fw-bold text-success">₦{{ number_format($source->net_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Daily Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-list me-2"></i>Daily Revenue Detail
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 420px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Room</th>
                            <th class="text-end">Extra Charges</th>
                            <th class="text-end">Total Revenue</th>
                            <th class="text-end">Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daily as $day)
                        <tr>
                            <td class="fw-bold">{{ $day['label'] }}</td>
                            <td class="text-end">₦{{ number_format($day['room_revenue'], 2) }}</td>
                            <td class="text-end">₦{{ number_format($day['extra_revenue'], 2) }}</td>
                            <td class="text-end fw-bold">₦{{ number_format($day['total'], 2) }}</td>
                            <td class="text-end text-success">₦{{ number_format($day['payments'], 2) }}</td>
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
    var ctx = document.getElementById('revenueChart').getContext('2d');
    var labels = {!! json_encode($daily->pluck('label')) !!};
    var roomRev = {!! json_encode($daily->pluck('room_revenue')) !!};
    var extraRev = {!! json_encode($daily->pluck('extra_revenue')) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Room Revenue', data: roomRev, backgroundColor: '#C8A165', borderRadius: 3 },
                { label: 'Extra Charges', data: extraRev, backgroundColor: '#17a2b8', borderRadius: 3 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 12 } } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '₦' + v.toLocaleString() } },
                x: { grid: { display: false } }
            }
        }
    });

    var pieCtx = document.getElementById('paymentPieChart').getContext('2d');
    var pmLabels = {!! json_encode($byPaymentMethod->pluck('payment_method')) !!};
    var pmData = {!! json_encode($byPaymentMethod->pluck('total')->map(fn($v) => (float) $v)) !!};

    if (pmLabels.length) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: pmLabels,
                datasets: [{ data: pmData, backgroundColor: ['#C8A165', '#28a745', '#17a2b8', '#dc3545', '#ffc107'] }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
            }
        });
    }
</script>
@endpush
