@extends('layouts.master')

@section('title', 'Night Audit Detail')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Night Audit &mdash; {{ $auditLog->business_date->format('M d, Y') }}</h4>
        <a href="{{ route('frontdesk.night-audit.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6>Status</h6>
                    <h4 class="mb-0">{{ ucfirst($auditLog->status) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6>Rooms Occupied</h6>
                    <h4 class="mb-0">{{ $auditLog->rooms_occupied }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6>Revenue Posted</h6>
                    <h4 class="mb-0">{{ number_format($auditLog->total_revenue_posted, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <h6>Charges Created</h6>
                    <h4 class="mb-0">{{ $auditLog->charges_posted }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Details</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Started At</dt>
                <dd class="col-sm-9">{{ $auditLog->started_at->format('M d, Y H:i:s') }}</dd>

                <dt class="col-sm-3">Completed At</dt>
                <dd class="col-sm-9">{{ $auditLog->completed_at?->format('M d, Y H:i:s') ?? 'N/A' }}</dd>

                <dt class="col-sm-3">Performed By</dt>
                <dd class="col-sm-9">{{ $auditLog->performedBy?->name ?? 'System' }}</dd>

                @if($auditLog->notes)
                <dt class="col-sm-3">Notes</dt>
                <dd class="col-sm-9">{{ $auditLog->notes }}</dd>
                @endif
            </dl>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Charges Posted</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registration</th>
                        <th>Guest</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Charge Date</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLog->charges as $charge)
                    <tr>
                        <td>#{{ $charge->registration_id }}</td>
                        <td>{{ $charge->registration?->guest?->full_name ?? $charge->registration?->full_name ?? 'N/A' }}</td>
                        <td><span class="badge bg-info">{{ $charge->charge_type }}</span></td>
                        <td>{{ number_format($charge->amount, 2) }}</td>
                        <td>{{ $charge->charge_date->format('M d, Y') }}</td>
                        <td>{{ $charge->description }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No charges in this audit run.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
