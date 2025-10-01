@extends('banquet::layouts.master')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="header mb-4">
        <div class="row align-items-center">
            <!-- Logo on the Left -->
            <div class="col-md-4 col-12 text-md-left text-center mb-md-0 mb-3">
                <div class="logo-container">
                    <div class="logo-letter">B</div>
                    <div class="logo-text">BRICKSPOINT</div>
                    <div class="logo-subtitle">Boutique Aparthotel</div>
                    <div class="logo-location">Asokoro</div>
                </div>
            </div>
            <!-- Report Title and Date Range -->
            <div class="col-md-8 col-12 text-md-center text-center">
                <h1 class="fw-bold display-5 text-primary mt-3">Event Report</h1>
                <p class="text-muted">Generated for the period: {{ $startDate }} to {{ $endDate }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="summary-card mb-4">
        <div class="row">
            <div class="col-md-2 col-6 mb-3 mb-md-0">
                <div class="summary-item">
                    <h3>Total Registered Events</h3>
                    <p>{{ $summary['total_events'] }}</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3 mb-md-0">
                <div class="summary-item">
                    <h3>Confirmed</h3>
                    <p>{{ $summary['confirmed'] }}</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3 mb-md-0">
                <div class="summary-item">
                    <h3>Cancelled</h3>
                    <p>{{ $summary['cancelled'] }}</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3 mb-md-0">
                <div class="summary-item">
                    <h3>Completed</h3>
                    <p>{{ $summary['completed'] }}</p>
                </div>
            </div>
            <div class="col-md-4 col-12 mb-3 mb-md-0">
                <div class="summary-item">
                    <h3>Most Used Location</h3>
                    <p>{{ $summary['most_used_location'] }}</p>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12 text-center">
                <div class="summary-item total-revenue">
                    <h3>Total Revenue</h3>
                    <p>₦{{ number_format($totalRevenue, 2) }}<br>
                        <small class="text-muted font-size-sm"> vat: ₦{{ number_format($totalRevenue * 7.5 / 100, 2) }}<br>
                        Total service charge: ₦{{ number_format($totalRevenue * 10 / 100, 2) }}</small>
                    </p>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Report Data -->
    @if (empty($reportData))
        <p class="text-center text-muted">No events found in the selected date range.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>S/N</th>
                        <th>Organization</th>
                        <th>Guest Count</th>
                        <th>Event Date</th>
                        <th>Location</th>
                        <th>Hall Rental Fee</th>
                        <th>Food & Beverage</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reportData as $index => $data)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $data['organization'] }}</td>
                            <td>{{ $data['guest_count'] }}</td>
                            <td>{{ $data['event_date_range'] }}</td>
                            <td>{{ $data['location'] }}</td>
                            <td>{{ number_format($data['hall_rental_fees'], 2) }}</td>
                            <td>{{ number_format($data['food_beverage_total'], 2) }}</td>
                            <td>{{ number_format($data['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Signature Section (Hidden by Default, Visible Only in Print) -->
    <div class="signature-section mt-5 d-none d-print-block">
        <div class="row">
            <div class="col-md-4 col-12 mb-3 mb-md-0 text-center">
                <div class="signature-box">
                    <p>Department Head</p>
                    <div class="signature-line"></div>
                    <p>E-Signature</p>
                    <p>Date: ____________________</p>
                </div>
            </div>
            <div class="col-md-4 col-12 mb-3 mb-md-0 text-center">
                <div class="signature-box">
                    <p>General Manager (GM)</p>
                    <div class="signature-line"></div>
                    <p>E-Signature</p>
                    <p>Date: ____________________</p>
                </div>
            </div>
            <div class="col-md-4 col-12 text-center">
                <div class="signature-box">
                    <p>Managing Director (MD)</p>
                    <div class="signature-line"></div>
                    <p>E-Signature</p>
                    <p>Date: ____________________</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer mt-5 text-center text-muted">
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <!-- Print Button -->
    <div class="text-center mt-4">
        <button class="btn btn-primary" onclick="window.print()">Print Report</button>
    </div>
</div>

<style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        color: #333;
        line-height: 1.6;
    }
    .header {
        border-bottom: 2px solid #4a90e2;
        padding-bottom: 10px;
    }
    .logo-container {
        position: relative;
    }
    .logo-letter {
        font-size: 60px;
        font-weight: bold;
        color: #4a90e2;
        line-height: 1;
    }
    .logo-text {
        font-size: 22px;
        font-weight: bold;
        color: #2c3e50;
    }
    .logo-subtitle {
        font-size: 14px;
        color: #666;
    }
    .logo-location {
        font-size: 12px;
        color: #666;
    }
    .summary-card {
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .summary-item {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .summary-item h3 {
        margin: 0;
        font-size: 14px;
        color: #4a90e2;
        text-transform: uppercase;
    }
    .summary-item p {
        margin: 5px 0;
        font-size: 18px;
        font-weight: bold;
        color: #2c3e50;
    }
    .signature-section {
        border-top: 2px solid #4a90e2;
        padding-top: 20px;
    }
    .signature-box {
        width: 100%;
    }
    .signature-line {
        border-bottom: 1px solid #000;
        width: 80%;
        margin: 10px auto;
        height: 20px;
    }
    /* Adjusted table styles for new columns */
    .table th, .table td {
        vertical-align: middle;
        font-size: 14px;
    }
    .table th {
        font-size: 12px;
        text-transform: uppercase;
    }
    /* Print Styles */
    @media print {
        body {
            width: 100%;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container-fluid {
            width: 100%;
            padding: 10px;
        }
        .header h1 {
            font-size: 20px;
        }
        .header p {
            font-size: 12px;
        }
        .summary-item h3 {
            font-size: 12px;
        }
        .summary-item p {
            font-size: 16px;
        }
        .table th, .table td {
            padding: 6px;
            font-size: 11px;
        }
        .footer {
            font-size: 10px;
        }
        .btn {
            display: none;
        }
        .signature-section {
            display: block !important;
        }
    }
</style>
@endsection