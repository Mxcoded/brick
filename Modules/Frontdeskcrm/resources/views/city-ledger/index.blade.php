@extends('layouts.master')

@section('title', 'City Ledger Accounts')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">City Ledger Accounts</h4>
        <div>
            <a href="{{ route('frontdesk.city-ledger.aging') }}" class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-clock"></i> Aging Report
            </a>
            <a href="{{ route('frontdesk.city-ledger.create') }}" class="btn btn-sm btn-gold">
                <i class="fas fa-plus"></i> New Account
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Outstanding</h6>
                    <h3 class="fw-bold mb-0">&#8358;{{ number_format($totalOutstanding, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Active Accounts</h6>
                    <h3 class="fw-bold mb-0">{{ $accounts->total() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th class="text-end">Balance</th>
                            <th class="text-end">Credit Limit</th>
                            <th>Terms</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                        <tr>
                            <td><strong>{{ $account->code }}</strong></td>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->contact_person ?? '—' }}</td>
                            <td>{{ $account->phone ?? '—' }}</td>
                            <td class="text-end">{{ number_format($account->balance, 2) }}</td>
                            <td class="text-end">{{ number_format($account->credit_limit, 2) }}</td>
                            <td>{{ strtoupper($account->payment_terms) }}</td>
                            <td>
                                <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('frontdesk.city-ledger.show', $account) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('frontdesk.city-ledger.edit', $account) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">No city ledger accounts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $accounts->links() }}
        </div>
    </div>
</div>
@endsection
