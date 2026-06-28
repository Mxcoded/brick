@extends('layouts.master')

@section('title', 'Run Night Audit')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-moon me-2"></i>Night Audit — {{ $today->format('D, M d, Y') }}</h4>
            <p class="text-muted mb-0">Review the summary below before running the end-of-day procedure</p>
        </div>
        <a href="{{ route('frontdesk.audit.index') }}" class="btn btn-light">
            <i class="fas fa-arrow-left me-1"></i> Back to History
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Overview Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-primary bg-opacity-10 text-primary">
                <div class="card-body text-center py-4">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h3 class="fw-bold mb-0">{{ $occupancyCount }}</h3>
                    <small>Checked In</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-success bg-opacity-10 text-success">
                <div class="card-body text-center py-4">
                    <i class="fas fa-door-open fa-2x mb-2"></i>
                    <h3 class="fw-bold mb-0">{{ $occupancyCount }} / {{ $totalRooms }}</h3>
                    <small>Occupancy ({{ $occupancyPercent }}%)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-warning bg-opacity-10 text-warning">
                <div class="card-body text-center py-4">
                    <i class="fas fa-bed fa-2x mb-2"></i>
                    <h3 class="fw-bold mb-0">₦{{ number_format($roomRevenue, 2) }}</h3>
                    <small>Room Charges to Post</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-info bg-opacity-10 text-info">
                <div class="card-body text-center py-4">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <h3 class="fw-bold mb-0">₦{{ number_format($recentPayments, 2) }}</h3>
                    <small>Payments Today</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-calculator me-2"></i>Revenue Summary
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Room Charges ({{ count($toCharge) }} rooms)</td>
                            <td class="text-end fw-bold">₦{{ number_format($roomRevenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td>VAT ({{ $taxRate }}%)</td>
                            <td class="text-end">₦{{ number_format($taxAmount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold border-top" style="font-size: 1.05rem;">
                            <td>Grand Total</td>
                            <td class="text-end text-primary">₦{{ number_format($grandRevenue, 2) }}</td>
                        </tr>
                        <tr class="text-success">
                            <td>Payments Collected Today</td>
                            <td class="text-end">- ₦{{ number_format($recentPayments, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-info-circle me-2"></i>What Will Happen
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Daily room charges will be <strong>auto-posted</strong> to the folio of each checked-in guest</li>
                        <li class="mb-2">A <strong>VAT {{ app(\App\Services\PropertyService::class)->taxRate() }}%</strong> line item will be recorded</li>
                        <li class="mb-2">Revenue and occupancy <strong>metrics</strong> will be calculated and stored</li>
                        <li class="mb-2">Today's <strong>payments</strong> will be tallied</li>
                        <li class="mb-2">Guests checking out today <strong>will not</strong> be charged an extra night</li>
                        <li>This action <strong>cannot be undone</strong> automatically for today's date</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(empty($toCharge))
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>No guests are currently checked in. The audit will still run and record a vacant day.
    </div>
    @else
    {{-- Rooms to Charge --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fas fa-list me-2"></i>Room Charges to Post ({{ count($toCharge) }})</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Rate</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($toCharge as $item)
                        <tr>
                            <td class="fw-bold">{{ $item['guest'] }}</td>
                            <td>{{ $item['room'] }}</td>
                            <td>{{ $item['room_type'] }}</td>
                            <td>{{ $item['check_in'] }}</td>
                            <td>{{ $item['check_out'] }}</td>
                            <td>₦{{ number_format($item['rate'], 2) }}</td>
                            <td class="{{ $item['balance'] > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                ₦{{ number_format($item['balance'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Execute Form --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('frontdesk.audit.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Audit Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Any observations or notes for this audit..."></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold btn-lg">
                        <i class="fas fa-play-circle me-1"></i> Run Night Audit
                    </button>
                    <a href="{{ route('frontdesk.audit.index') }}" class="btn btn-light btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
