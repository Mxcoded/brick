@extends('layouts.master')

@section('title')
Night Audit - {{ $audit->audit_date->format('M d, Y') }}
@endsection

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-moon me-2"></i>Night Audit — {{ $audit->audit_date->format('D, M d, Y') }}</h4>
            <p class="text-muted mb-0">
                Status:
                @if($audit->status === 'completed')
                    <span class="badge bg-success">Completed</span>
                @elseif($audit->status === 'rolled_back')
                    <span class="badge bg-secondary">Rolled Back</span>
                @else
                    <span class="badge bg-warning text-dark">{{ ucfirst($audit->status) }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($audit->status === 'completed')
            <form action="{{ route('frontdesk.audit.rollback', $audit) }}" method="POST" onsubmit="return confirm('Rolling back will remove the auto-posted room charges. Proceed?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-undo me-1"></i> Rollback
                </button>
            </form>
            @endif
            <a href="{{ route('frontdesk.audit.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <i class="fas fa-bed fa-2x text-primary mb-2"></i>
                    <h4 class="fw-bold mb-0">{{ $audit->room_revenue > 0 ? '₦'.number_format($audit->room_revenue, 2) : '₦0.00' }}</h4>
                    <small class="text-muted">Room Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <i class="fas fa-receipt fa-2x text-warning mb-2"></i>
                    <h4 class="fw-bold mb-0">{{ $audit->extra_revenue > 0 ? '₦'.number_format($audit->extra_revenue, 2) : '₦0.00' }}</h4>
                    <small class="text-muted">Extra Charges</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <i class="fas fa-percent fa-2x text-info mb-2"></i>
                    <h4 class="fw-bold mb-0">₦{{ number_format($audit->tax_amount, 2) }}</h4>
                    <small class="text-muted">VAT Collected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body text-center py-3">
                    <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                    <h4 class="fw-bold mb-0">₦{{ number_format($audit->total_payments, 2) }}</h4>
                    <small class="text-muted">Payments</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-chart-pie me-2"></i>Revenue Breakdown
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Room Revenue</td>
                            <td class="text-end fw-bold">₦{{ number_format($audit->room_revenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Extra Charges</td>
                            <td class="text-end">₦{{ number_format($audit->extra_revenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td>VAT ({{ app(\App\Services\PropertyService::class)->taxRate() }}%)</td>
                            <td class="text-end">₦{{ number_format($audit->tax_amount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold border-top">
                            <td>Total Revenue</td>
                            <td class="text-end text-primary" style="font-size: 1.1rem;">₦{{ number_format($audit->total_revenue, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Payments Collected</td>
                            <td class="text-end text-success">₦{{ number_format($audit->total_payments, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Charges Posted</td>
                            <td class="text-end">{{ $audit->charges_posted }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold">
                    <i class="fas fa-hotel me-2"></i>Occupancy Summary
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Checked-In Guests</td>
                            <td class="text-end">{{ $audit->checked_in_count }}</td>
                        </tr>
                        <tr>
                            <td>Occupied Rooms</td>
                            <td class="text-end">{{ $audit->occupancy_count }} / {{ $audit->total_rooms }}</td>
                        </tr>
                        <tr>
                            <td>Occupancy Rate</td>
                            <td class="text-end fw-bold">{{ $audit->occupancy_percentage }}%</td>
                        </tr>
                        <tr>
                            <td>Payments Today</td>
                            <td class="text-end">{{ $audit->payments_count }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Info --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-info-circle me-2"></i>Audit Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted d-block">Started By</small>
                    <span class="fw-bold">{{ $audit->starter?->name ?? '—' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Started At</small>
                    <span class="fw-bold">{{ $audit->started_at?->format('M d, Y H:i') ?? '—' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Completed By</small>
                    <span class="fw-bold">{{ $audit->completer?->name ?? $audit->starter?->name ?? '—' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Completed At</small>
                    <span class="fw-bold">{{ $audit->completed_at?->format('M d, Y H:i') ?? '—' }}</span>
                </div>
            </div>
            @if($audit->notes)
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted d-block mb-1">Notes</small>
                <p class="mb-0">{{ $audit->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Audit Logs --}}
    @if($audit->logs->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 fw-bold">
            <i class="fas fa-clipboard-list me-2"></i>Audit Log
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($audit->logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                            <td>{{ $log->amount ? '₦'.number_format($log->amount, 2) : '—' }}</td>
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
