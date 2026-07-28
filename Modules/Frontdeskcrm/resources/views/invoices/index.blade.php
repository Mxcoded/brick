@extends('layouts.master')

@section('title', 'Invoices')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Invoices</h4>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="void" {{ request('status') === 'void' ? 'selected' : '' }}>Void</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From">
                </div>
                <div class="col-auto">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-gold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Guest</th>
                            <th>Issue Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                        <tr>
                            <td><strong>{{ $inv->invoice_number }}</strong></td>
                            <td>{{ $inv->registration?->full_name ?? '—' }}</td>
                            <td>{{ $inv->issue_date->format('M d, Y') }}</td>
                            <td class="text-end">{{ number_format($inv->total, 2) }}</td>
                            <td class="text-end">{{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($inv->total - $inv->paid_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'issued' ? 'primary' : ($inv->status === 'draft' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('frontdesk.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
