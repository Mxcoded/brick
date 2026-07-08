@extends('layouts.master')

@section('title', $corporateAccount->company_name)

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.registrations.dashboard') }}">Front Desk</a></li>
    <li class="breadcrumb-item"><a href="{{ route('frontdesk.city-ledger.index') }}">City Ledger</a></li>
    <li class="breadcrumb-item active">{{ $corporateAccount->company_name }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold">{{ $corporateAccount->company_name }}</h3>
            <p class="text-muted mb-0">{{ $corporateAccount->contact_person ? 'Contact: '.$corporateAccount->contact_person : 'Corporate Account' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.city-ledger.edit', $corporateAccount) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('frontdesk.city-ledger.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Balance Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold text-danger mb-0">₦{{ number_format($corporateAccount->current_balance, 2) }}</h4>
                    <small class="text-muted">Current Balance</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold mb-0">₦{{ number_format($corporateAccount->credit_limit, 2) }}</h4>
                    <small class="text-muted">Credit Limit</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    @php $avail = $corporateAccount->credit_limit - $corporateAccount->current_balance; @endphp
                    <h4 class="fw-bold {{ $avail > 0 ? 'text-success' : 'text-danger' }} mb-0">₦{{ number_format($avail, 2) }}</h4>
                    <small class="text-muted">Available Credit</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h4 class="fw-bold text-info mb-0">{{ $transactions->total() }}</h4>
                    <small class="text-muted">Transactions</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Account Details --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Account Details</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted small">Contact Person</dt>
                        <dd class="col-sm-7">{{ $corporateAccount->contact_person ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Email</dt>
                        <dd class="col-sm-7">{{ $corporateAccount->email ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Phone</dt>
                        <dd class="col-sm-7">{{ $corporateAccount->phone ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Address</dt>
                        <dd class="col-sm-7">{{ $corporateAccount->address ?? '—' }}</dd>

                        <dt class="col-sm-5 text-muted small">Payment Terms</dt>
                        <dd class="col-sm-7">{{ str_replace('_', ' ', strtoupper($corporateAccount->payment_terms)) }}</dd>

                        <dt class="col-sm-5 text-muted small">Status</dt>
                        <dd class="col-sm-7">
                            @if($corporateAccount->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Record Payment --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave me-2 text-success"></i>Record Payment</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('frontdesk.city-ledger.payment', $corporateAccount) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" min="0.01" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="Cheque #, Txn ID">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Payment description">
                        </div>
                        <button type="submit" class="btn btn-success w-100 btn-sm">
                            <i class="fas fa-check me-1"></i> Record Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Transaction History</h6>
                    <span class="badge bg-secondary">{{ $transactions->total() }} entries</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $txn)
                                    <tr>
                                        <td class="text-nowrap"><small>{{ $txn->created_at->format('d M Y H:i') }}</small></td>
                                        <td>
                                            @if($txn->type === 'charge')
                                                <span class="badge bg-danger bg-opacity-75">Charge</span>
                                            @elseif($txn->type === 'payment')
                                                <span class="badge bg-success bg-opacity-75">Payment</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Adjustment</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $txn->description ?? '—' }}</small>
                                            @if($txn->registration)
                                                <br><small class="text-muted">
                                                    <a href="{{ route('frontdesk.registrations.show', $txn->registration) }}" class="text-muted">
                                                        #{{ $txn->registration->reservation_code ?? $txn->registration->id }}
                                                    </a>
                                                </small>
                                            @endif
                                        </td>
                                        <td><small>{{ $txn->reference ?? '—' }}</small></td>
                                        <td class="text-end">
                                            <span class="{{ $txn->type === 'charge' ? 'text-danger' : 'text-success' }} fw-bold">
                                                {{ $txn->type === 'charge' ? '+' : '-' }}₦{{ number_format($txn->amount, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">₦{{ number_format($txn->balance_after, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                            No transactions yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
