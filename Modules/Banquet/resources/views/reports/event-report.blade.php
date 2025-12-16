@extends('layouts.master')

@section('page-content')
<div class="container-fluid px-4 banquet-report-theme">
    
    <div class="d-print-none d-flex justify-content-between align-items-center mb-4 mt-3">
        <a href="{{ route('banquet.reports.form') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        
        <div class="d-flex gap-2">
            <form action="{{ route('banquet.reports.export') }}" method="POST" target="_blank">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="btn btn-success btn-sm text-white">
                    <i class="fas fa-file-excel me-1"></i> Export to Excel
                </button>
            </form>

            <button class="btn btn-gold btn-sm text-white" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print / PDF
            </button>
        </div>
    </div>

    <div class="header mb-4 pb-3">
        <div class="row align-items-center">
            <div class="col-md-4 col-12 text-md-start text-center mb-md-0 mb-3">
                <div class="logo-container">
                    <div class="logo-text text-gold">THE BRICK HALL</div>
                    <div class="logo-subtitle text-charcoal">Premium Event Center</div>
                </div>
            </div>
            <div class="col-md-8 col-12 text-md-end text-center">
                <h1 class="fw-bold display-6 text-charcoal mt-2 mb-0">Financial & Event Report</h1>
                <p class="text-muted mb-0">
                    Period: <span class="fw-bold">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</span> 
                    to <span class="fw-bold">{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</span>
                </p>
            </div>
        </div>
    </div>

    <div class="summary-card mb-5">
        <div class="row g-0">
            <div class="col-md-3 col-6 border-end p-3 text-center">
                <div class="text-uppercase small text-muted mb-1">Total Events</div>
                <div class="h3 fw-bold text-charcoal mb-0">{{ $summary['total_events'] }}</div>
                <small class="text-danger" style="font-size: 0.7rem;">{{ $summary['cancelled'] }} Cancelled</small>
            </div>
            <div class="col-md-3 col-6 border-end p-3 text-center">
                <div class="text-uppercase small text-muted mb-1">Total Revenue</div>
                <div class="h3 fw-bold text-gold mb-0">₦{{ number_format($totalRevenue) }}</div>
            </div>
            <div class="col-md-3 col-6 border-end p-3 text-center">
                <div class="text-uppercase small text-muted mb-1">Total Expenses</div>
                <div class="h3 fw-bold text-danger mb-0">₦{{ number_format($totalExpenses) }}</div>
            </div>
            <div class="col-md-3 col-6 p-3 text-center bg-light">
                <div class="text-uppercase small text-muted mb-1">Net Profit</div>
                <div class="h3 fw-bold text-success mb-0">₦{{ number_format($totalProfit) }}</div>
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
            <table class="table table-striped table-hover align-middle table-sm" style="font-size: 0.9rem;">
                <thead class="bg-gold text-white">
                    <tr>
                        <th class="py-2 ps-2">Date</th>
                        <th class="py-2">Client / Org</th>
                        <th class="py-2">Location(s)</th>
                        <th class="py-2 text-center">Guests</th>
                        <th class="py-2 text-end">Revenue</th>
                        <th class="py-2 text-end text-danger-subtle">Expenses</th>
                        <th class="py-2 text-end pe-2">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData as $data)
                        <tr>
                            <td class="ps-2 fw-bold text-charcoal">{{ $data['event_date_range'] }}</td>
                            <td>{{ Str::limit($data['organization'], 25) }}</td>
                            <td>
                                @foreach(explode(',', $data['location']) as $loc)
                                    <span class="badge bg-light text-dark border mb-1">{{ trim($loc) }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">{{ number_format($data['guest_count']) }}</td>
                            <td class="text-end fw-bold">₦{{ number_format($data['total_revenue']) }}</td>
                            <td class="text-end text-danger">
                                @if($data['expenses'] > 0) -₦{{ number_format($data['expenses']) }} @else - @endif
                            </td>
                            <td class="text-end fw-bold text-success pe-2">
                                ₦{{ number_format($data['profit']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end text-uppercase pe-3">Totals:</td>
                        <td class="text-end text-charcoal">₦{{ number_format($totalRevenue) }}</td>
                        <td class="text-end text-danger">-₦{{ number_format($totalExpenses) }}</td>
                        <td class="text-end text-success">₦{{ number_format($totalProfit) }}</td>
                    </tr>
                </tfoot>
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
    /* REPORT THEME */
    .banquet-report-theme { font-family: 'Proxima Nova', Arial, sans-serif; color: #333; background: #fff; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; }
    
    .header { border-bottom: 2px solid #C8A165; }
    .logo-text { font-size: 24px; font-weight: 800; letter-spacing: 1px; }
    .logo-subtitle { font-size: 14px; text-transform: uppercase; letter-spacing: 2px; }
    
    .summary-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .table thead th { border-bottom: none; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .signature-line { border-bottom: 1px solid #333; margin: 0 20px; height: 1px; }

    /* STRICT PRINT STYLES */
    @media print {
        /* 1. Hide EVERYTHING by default */
        body * {
            visibility: hidden; 
            height: 0; /* Collapse space */
            overflow: hidden;
        }

        /* 2. Show ONLY the Report Container and its children */
        .banquet-report-theme, 
        .banquet-report-theme * {
            visibility: visible !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* 3. Position the report at the top-left of the page */
        .banquet-report-theme {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0 !important;
            padding: 20px !important;
            background: white;
        }

        /* 4. Hide specific buttons inside the report container */
        .d-print-none, .btn { 
            display: none !important; 
        }

        /* 5. Force colors for background graphics (Gold headers) */
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bg-gold { background-color: #C8A165 !important; color: white !important; }
        .summary-card { box-shadow: none; border: 1px solid #ccc; }
        .d-print-block { display: block !important; }
    }
</style>
@endsection