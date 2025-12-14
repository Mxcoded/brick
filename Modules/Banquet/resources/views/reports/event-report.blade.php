@extends('layouts.master')

@section('page-content')
<div class="container-fluid px-4 banquet-report-theme">
    
    <div class="d-print-none d-flex justify-content-between align-items-center mb-4 mt-3">
        <a href="{{ route('banquet.reports.form') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print / Save PDF
        </button>
    </div>

    <div class="header mb-4 pb-3">
        <div class="row align-items-center">
            <div class="col-md-4 col-12 text-md-start text-center mb-md-0 mb-3">
                <div class="logo-container">
                    <div class="logo-text text-gold">THE BRICK HALL</div>
                    <div class="logo-subtitle text-charcoal">Premium Event Center</div>
                    <div class="logo-location text-muted">Asokoro, Abuja</div>
                </div>
            </div>
            <div class="col-md-8 col-12 text-md-end text-center">
                <h1 class="fw-bold display-6 text-charcoal mt-2 mb-0">Event Summary Report</h1>
                <p class="text-muted mb-0">Period: <span class="fw-bold">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</span> to <span class="fw-bold">{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</span></p>
            </div>
        </div>
    </div>

    <div class="summary-card mb-5">
        <div class="row g-0">
            <div class="col-md-3 col-6 border-end p-3 text-center">
                <div class="text-uppercase small text-muted mb-1">Total Events</div>
                <div class="h3 fw-bold text-charcoal mb-0">{{ $summary['total_events'] }}</div>
            </div>
            <div class="col-md-3 col-6 border-end p-3 text-center">
                <div class="text-uppercase small text-muted mb-1">Confirmed</div>
                <div class="h3 fw-bold text-success mb-0">{{ $summary['confirmed'] }}</div>
            </div>
            <div class="col-md-3 col-6 border-end p-3 text-center">
                <div class="text-uppercase small text-muted mb-1">Total Revenue</div>
                <div class="h3 fw-bold text-gold mb-0">₦{{ number_format($totalRevenue, 2) }}</div>
            </div>
            <div class="col-md-3 col-12 p-3 text-center bg-light">
                <div class="text-uppercase small text-muted mb-1">Top Location</div>
                <div class="h5 fw-bold text-charcoal mb-0">{{ $summary['most_used_location'] ?: 'N/A' }}</div>
            </div>
        </div>
    </div>

    @if (empty($reportData))
        <div class="alert alert-light text-center border p-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
            <p class="text-muted mb-0">No events found in the selected date range.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="bg-gold text-white">
                    <tr>
                        <th class="py-3 ps-3">#</th>
                        <th class="py-3">Organization</th>
                        <th class="py-3 text-center">Guests</th>
                        <th class="py-3">Event Date</th>
                        <th class="py-3">Location</th>
                        <th class="py-3 text-end">Hall Fee</th>
                        <th class="py-3 text-end">F&B</th>
                        <th class="py-3 text-end pe-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData as $index => $data)
                        <tr>
                            <td class="ps-3">{{ $index + 1 }}</td>
                            <td class="fw-bold text-charcoal">{{ $data['organization'] }}</td>
                            <td class="text-center">{{ $data['guest_count'] }}</td>
                            <td>{{ $data['event_date_range'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $data['location'] }}</span></td>
                            <td class="text-end">₦{{ number_format($data['hall_rental_fees'], 2) }}</td>
                            <td class="text-end">₦{{ number_format($data['food_beverage_total'], 2) }}</td>
                            <td class="text-end fw-bold text-charcoal pe-3">₦{{ number_format($data['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="signature-section mt-5 pt-5 d-none d-print-block">
        <div class="row">
            <div class="col-4 text-center">
                <div class="signature-line"></div>
                <p class="small fw-bold text-uppercase mt-2">Department Head</p>
            </div>
            <div class="col-4 text-center">
                <div class="signature-line"></div>
                <p class="small fw-bold text-uppercase mt-2">General Manager</p>
            </div>
            <div class="col-4 text-center">
                <div class="signature-line"></div>
                <p class="small fw-bold text-uppercase mt-2">Managing Director</p>
            </div>
        </div>
        <div class="text-center mt-5">
            <small class="text-muted">Generated by The Brick Hall Management System on {{ now()->format('M d, Y h:i A') }}</small>
        </div>
    </div>
</div>

<style>
    /* CUSTOM THEME STYLES */
    .banquet-report-theme {
        font-family: 'Proxima Nova', Arial, sans-serif;
        color: #333333;
        background-color: #fff;
    }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    
    .header {
        border-bottom: 2px solid #C8A165;
    }
    
    .logo-text { font-size: 24px; font-weight: 800; letter-spacing: 1px; }
    .logo-subtitle { font-size: 14px; text-transform: uppercase; letter-spacing: 2px; }
    
    .summary-card {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .table thead th {
        border-bottom: none;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table tbody tr:last-child td { border-bottom: 0; }
    
    /* PRINT SPECIFIC */
    @media print {
        @page { size: landscape; margin: 1cm; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: #fff; }
        .banquet-report-theme { padding: 0 !important; }
        .btn, .d-print-none { display: none !important; }
        .summary-card { box-shadow: none; border: 1px solid #ccc; }
        .header { border-bottom: 2px solid #C8A165 !important; }
        .bg-gold { background-color: #C8A165 !important; color: white !important; }
        .signature-line { border-bottom: 1px solid #333; margin: 0 20px; height: 1px; }
        .d-print-block { display: block !important; }
    }
</style>
@endsection