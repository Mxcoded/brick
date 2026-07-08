@extends('layouts.master')

@section('title', 'Guest Demographics Report')

@push('page-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-users me-2"></i>Guest Demographics</h4>
            <p class="text-muted mb-0">Guest profiles, nationalities, and segmentation</p>
        </div>
    </div>

    @include('frontdeskcrm::reports.partials.report-nav')
    @include('frontdeskcrm::reports.partials.date-filter')

    {{-- KPI --}}
    <div class="row g-3 my-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold">Total Guests</small>
                <h3 class="fw-bold mb-0 text-primary">{{ $totalGuests }}</h3>
                <small class="text-muted">{{ $from->format('M d') }} - {{ $to->format('M d, Y') }}</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-success border-4">
                <small class="text-muted text-uppercase fw-bold">Repeat Visitors</small>
                <h3 class="fw-bold mb-0 text-success">{{ $repeatVisitors }}</h3>
                <small class="text-muted">2+ visits ({{ $totalGuests > 0 ? round(($repeatVisitors / $totalGuests) * 100) : 0 }}%)</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 border-start border-info border-4">
                <small class="text-muted text-uppercase fw-bold">New Guests</small>
                <h3 class="fw-bold mb-0 text-info">{{ $firstTimeVisitors }}</h3>
                <small class="text-muted">First visit ({{ $totalGuests > 0 ? round(($firstTimeVisitors / $totalGuests) * 100) : 0 }}%)</small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Gender --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-venus-mars me-2"></i>Gender
                </div>
                <div class="card-body">
                    <canvas id="genderChart" height="180"></canvas>
                    <table class="table table-sm mt-3 mb-0">
                        <tr>
                            <td><i class="fas fa-male text-primary me-1"></i> Male</td>
                            <td class="text-end fw-bold">{{ $byGender['male'] }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-female text-danger me-1"></i> Female</td>
                            <td class="text-end fw-bold">{{ $byGender['female'] }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-question-circle text-muted me-1"></i> Unspecified</td>
                            <td class="text-end fw-bold">{{ $byGender['unspecified'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Age Distribution --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-calendar-alt me-2"></i>Age Distribution
                </div>
                <div class="card-body">
                    <canvas id="ageChart" height="180"></canvas>
                </div>
            </div>
        </div>

        {{-- Guest Types --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-user-tag me-2"></i>Guest Categories
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="text-end">Guests</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byGuestType as $gt)
                            <tr>
                                <td><span class="badge" style="background: {{ $gt->color ?? '#6c757d' }}">{{ $gt->name }}</span></td>
                                <td class="text-end">{{ $gt->total }}</td>
                                <td class="text-end">₦{{ number_format($gt->revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Nationalities --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-globe-africa me-2"></i>Top Nationalities
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nationality</th>
                            <th class="text-end">Guests</th>
                            <th class="text-end">Share</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalNationality = $byNationality->sum('total'); @endphp
                        @forelse($byNationality as $n)
                        <tr>
                            <td class="fw-bold">{{ $n->nationality }}</td>
                            <td class="text-end">{{ $n->total }}</td>
                            <td class="text-end">{{ $totalNationality > 0 ? round(($n->total / $totalNationality) * 100, 1) : 0 }}%</td>
                            <td class="text-end">₦{{ number_format($n->revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No nationality data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top Companies --}}
    @if($topCompanies->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-building me-2"></i>Top Corporate Guests
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th class="text-end">Stays</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCompanies as $c)
                        <tr>
                            <td class="fw-bold">{{ $c->company_name }}</td>
                            <td class="text-end">{{ $c->total }}</td>
                            <td class="text-end">₦{{ number_format($c->revenue, 2) }}</td>
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

@push('page-scripts')
<script>
    var genderCtx = document.getElementById('genderChart').getContext('2d');
    var genderLabels = {!! json_encode(array_keys(array_filter($byGender, fn($v) => $v > 0))) !!};
    var genderData = {!! json_encode(array_values(array_filter($byGender, fn($v) => $v > 0))) !!};

    if (genderLabels.length) {
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: genderLabels,
                datasets: [{ data: genderData, backgroundColor: ['#007bff', '#dc3545', '#6c757d'] }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
            }
        });
    }

    var ageCtx = document.getElementById('ageChart').getContext('2d');
    var ageLabels = {!! json_encode($byAge->pluck('label')) !!};
    var ageData = {!! json_encode($byAge->pluck('count')) !!};

    if (ageLabels.length) {
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ageLabels,
                datasets: [{ label: 'Guests', data: ageData, backgroundColor: '#C8A165', borderRadius: 3 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });
    }
</script>
@endpush
