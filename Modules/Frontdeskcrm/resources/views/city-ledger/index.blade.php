@extends('layouts.master')

@section('title', 'City Ledger')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item active">City Ledger</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold"><i class="fas fa-building me-2 text-primary"></i>City Ledger</h3>
            <p class="text-muted mb-0">Corporate accounts & accounts receivable</p>
        </div>
        <a href="{{ route('frontdesk.city-ledger.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Account
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold text-primary mb-0">₦{{ number_format($summary['total_outstanding'], 2) }}</h4>
                    <small class="text-muted">Total Outstanding</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold text-success mb-0">₦{{ number_format($summary['total_credit_limit'], 2) }}</h4>
                    <small class="text-muted">Total Credit Limit</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold text-info mb-0">{{ $summary['active_accounts'] }}</h4>
                    <small class="text-muted">Active Accounts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold mb-0">{{ $summary['total_accounts'] }}</h4>
                    <small class="text-muted">Total Accounts</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Accounts Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Credit Limit</th>
                            <th>Current Balance</th>
                            <th>Available Credit</th>
                            <th>Payment Terms</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr>
                                <td>
                                    <a href="{{ route('frontdesk.city-ledger.show', $account) }}" class="fw-bold text-dark text-decoration-none">
                                        {{ $account->company_name }}
                                    </a>
                                </td>
                                <td>
                                    <small>{{ $account->contact_person ?? '—' }}</small>
                                    @if($account->phone)
                                        <br><small class="text-muted">{{ $account->phone }}</small>
                                    @endif
                                </td>
                                <td>₦{{ number_format($account->credit_limit, 2) }}</td>
                                <td>
                                    <span class="fw-bold {{ $account->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                        ₦{{ number_format($account->current_balance, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @php $avail = $account->credit_limit - $account->current_balance; @endphp
                                    <span class="{{ $avail > 0 ? 'text-success' : 'text-danger' }}">
                                        ₦{{ number_format($avail, 2) }}
                                    </span>
                                </td>
                                <td><small>{{ str_replace('_', ' ', strtoupper($account->payment_terms)) }}</small></td>
                                <td>
                                    @if($account->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('frontdesk.city-ledger.show', $account) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('frontdesk.city-ledger.edit', $account) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-building fa-3x d-block mb-2"></i>
                                    No corporate accounts yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $accounts->links() }}
    </div>
</div>
@endsection
