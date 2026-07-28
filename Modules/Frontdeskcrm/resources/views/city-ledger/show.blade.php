@extends('layouts.master')

@section('title', $account->name)
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">{{ $account->name }}</h4>
        <div>
            <a href="{{ route('frontdesk.city-ledger.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list"></i> All Accounts
            </a>
            <a href="{{ route('frontdesk.city-ledger.edit', $account) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Current Balance</h6>
                    <h3 class="fw-bold mb-0">&#8358;{{ number_format($account->balance, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Credit Limit</h6>
                    <h3 class="fw-bold mb-0">&#8358;{{ number_format($account->credit_limit, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Available</h6>
                    <h3 class="fw-bold mb-0">&#8358;{{ number_format(max(0, $account->credit_limit - $account->balance), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Status</h6>
                    <h3 class="fw-bold mb-0">
                        <span class="badge bg-{{ $account->status === 'active' ? 'success' : 'secondary' }} fs-6">
                            {{ ucfirst($account->status) }}
                        </span>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Account Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:140px">Code</th><td>{{ $account->code }}</td></tr>
                        <tr><th>Contact Person</th><td>{{ $account->contact_person ?? '—' }}</td></tr>
                        <tr><th>Email</th><td>{{ $account->email ?? '—' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $account->phone ?? '—' }}</td></tr>
                        <tr><th>Address</th><td>{{ $account->address ?? '—' }}</td></tr>
                        <tr><th>Tax ID</th><td>{{ $account->tax_id ?? '—' }}</td></tr>
                        <tr><th>Payment Terms</th><td>{{ strtoupper($account->payment_terms) }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Quick Actions</h6>
                </div>
                <div class="card-body d-flex gap-2">
                    <button type="button" class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#postChargeModal">
                        <i class="fas fa-plus-circle"></i> Post Charge
                    </button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="fas fa-money-bill"></i> Record Payment
                    </button>
                </div>
            </div>
            @if ($account->notes)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-semibold">Notes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $account->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Transaction History</h6>
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
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td>{{ $txn->transaction_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $txn->transaction_type === 'payment' ? 'success' : ($txn->transaction_type === 'credit_note' ? 'danger' : 'primary') }}">
                                    {{ ucwords(str_replace('_', ' ', $txn->transaction_type)) }}
                                </span>
                            </td>
                            <td>{{ $txn->description }}</td>
                            <td>{{ $txn->reference ?? '—' }}</td>
                            <td class="text-end {{ $txn->transaction_type === 'charge' ? 'text-danger' : 'text-success' }}">
                                {{ $txn->transaction_type === 'charge' ? '+' : '-' }}
                                {{ number_format($txn->amount, 2) }}
                            </td>
                            <td>{{ $txn->createdBy?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="postChargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('frontdesk.city-ledger.post-charge', $account) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Post Charge to {{ $account->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference (optional)</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Post Charge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('frontdesk.city-ledger.record-payment', $account) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment from {{ $account->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="pos">POS</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference (optional)</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
