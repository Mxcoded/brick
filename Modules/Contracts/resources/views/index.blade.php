@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Contracts &amp; Agreements</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 contracts-theme">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h4 class="fw-bold mb-0"><i class="fas fa-file-signature me-2 text-gold"></i>Contracts &amp; Agreements</h4>
        @can('contracts.create')
            <a href="{{ route('contracts.agreements.create') }}" class="btn btn-gold">
                <i class="fas fa-plus me-1"></i> New Agreement
            </a>
        @endcan
    </div>

    {{-- STAT ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Total Agreements</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-gold"><i class="fas fa-file-contract fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Drafts</h6>
                        <h3 class="fw-bold mb-0 text-secondary">{{ $stats['draft'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-secondary"><i class="fas fa-pen fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Awaiting Approval</h6>
                        <h3 class="fw-bold mb-0 text-warning">{{ $stats['awaiting_approval'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-warning"><i class="fas fa-clipboard-check fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Awaiting Signature</h6>
                        <h3 class="fw-bold mb-0 text-primary">{{ $stats['awaiting_signature'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-primary"><i class="fas fa-signature fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Active</h6>
                        <h3 class="fw-bold mb-0 text-success">{{ $stats['active'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-success"><i class="fas fa-check-double fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Expiring &lt; 30 Days</h6>
                        <h3 class="fw-bold mb-0 text-gold">{{ $stats['expiring_30'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-gold"><i class="fas fa-hourglass-half fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 small text-muted">Expired</h6>
                        <h3 class="fw-bold mb-0 text-danger">{{ $stats['expired'] }}</h3>
                    </div>
                    <div class="icon-circle bg-light rounded-circle p-3 text-danger"><i class="fas fa-calendar-times fa-lg"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- RECENT --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Recent Agreements</h6>
                    <a href="{{ route('contracts.agreements.index') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Reference</th>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recent as $agreement)
                                    <tr>
                                        <td>
                                            <a href="{{ route('contracts.agreements.show', $agreement) }}" class="fw-semibold text-decoration-none">{{ $agreement->agreement_number }}</a>
                                        </td>
                                        <td>{{ $agreement->client_name }}</td>
                                        <td><span class="small">{{ $agreement->typeEnum()->label() }}</span></td>
                                        <td>{{ $agreement->value_amount !== null ? '₦'.number_format((float) $agreement->value_amount, 2) : '—' }}</td>
                                        <td>{{ $agreement->expiry_label }}</td>
                                        <td>@include('contracts::partials.status-badge', ['agreement' => $agreement])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No agreements yet. <a href="{{ route('contracts.agreements.create') }}">Create your first</a>.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- EXPIRING SOON --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="fas fa-hourglass-half me-1 text-gold"></i>Expiring Soon (90 days)</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse ($expiringSoon as $agreement)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('contracts.agreements.show', $agreement) }}" class="fw-semibold text-decoration-none">{{ $agreement->agreement_number }}</a>
                                    <div class="small text-muted">{{ $agreement->client_name }}</div>
                                </div>
                                <span class="badge {{ $agreement->expiry_date?->lessThan(now()->addDays(30)) ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $agreement->expiry_date?->diffForHumans(['parts' => 2]) }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-4">No agreements expiring soon.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<style>
    .contracts-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
    .icon-circle { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
</style>
@endsection