@extends('layouts.master')

@section('title', 'Finance Dashboard')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Finance Dashboard</h2>
        <div class="btn-group">
            <a href="{{ route('finance.coa.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list me-1"></i> Chart of Accounts
            </a>
            <a href="{{ route('finance.journal.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-book me-1"></i> Journal
            </a>
            <a href="{{ route('finance.reports.trial-balance') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-pie me-1"></i> Reports
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Accounts</div>
                    <div class="fs-3 fw-bold">{{ $accountsCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Posted Entries</div>
                    <div class="fs-3 fw-bold">{{ $postedCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-muted small text-uppercase mb-2">Quick Reports</div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('finance.reports.trial-balance') }}" class="btn btn-sm btn-light border">Trial Balance</a>
                        <a href="{{ route('finance.reports.profit-loss') }}" class="btn btn-sm btn-light border">Profit &amp; Loss</a>
                        <a href="{{ route('finance.reports.balance-sheet') }}" class="btn btn-sm btn-light border">Balance Sheet</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-transparent fw-semibold">Recent Journal Entries</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Entry #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td class="font-monospace">{{ $entry->entry_number }}</td>
                                <td>{{ $entry->date->format('d M Y') }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>
                                    <span class="badge bg-{{ $entry->status === 'posted' ? 'success' : 'secondary' }}">
                                        {{ $entry->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('finance.journal.show', $entry) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No journal entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($entries->hasPages())
            <div class="card-footer bg-transparent">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
@endsection
