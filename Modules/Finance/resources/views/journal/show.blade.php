@extends('layouts.master')

@section('title', 'Journal Entry')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <span class="font-monospace">{{ $journalEntry->entry_number }}</span>
            <span class="badge bg-{{ $journalEntry->status === 'posted' ? 'success' : 'secondary' }} ms-2">{{ $journalEntry->status }}</span>
        </h2>
        <a href="{{ route('finance.journal.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Journal
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><div class="text-muted small">Date</div><div>{{ $journalEntry->date->format('d M Y') }}</div></div>
                <div class="col-md-6"><div class="text-muted small">Description</div><div>{{ $journalEntry->description }}</div></div>
                <div class="col-md-3"><div class="text-muted small">Posted By</div><div>{{ $journalEntry->createdBy?->name ?? '—' }}</div></div>
            </div>
            @if ($journalEntry->reference_type)
                <hr>
                <div class="text-muted small">Source</div>
                <div>{{ $journalEntry->reference_type }} #{{ $journalEntry->reference_id }}</div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-transparent fw-semibold">Lines</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>GL Code</th>
                            <th>Account</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($journalEntry->lines as $line)
                            <tr>
                                <td class="font-monospace">{{ $line->account->code }}</td>
                                <td>{{ $line->account->name }}</td>
                                <td class="text-end font-monospace">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                                <td class="text-end font-monospace">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">Totals</td>
                            <td class="text-end font-monospace">{{ number_format($journalEntry->lines->sum('debit'), 2) }}</td>
                            <td class="text-end font-monospace">{{ number_format($journalEntry->lines->sum('credit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-between">
            <span class="{{ $journalEntry->isBalanced() ? 'text-success' : 'text-danger' }}">
                <i class="fas fa-{{ $journalEntry->isBalanced() ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
                {{ $journalEntry->isBalanced() ? 'Balanced' : 'Out of balance' }}
            </span>
        </div>
    </div>
@endsection
