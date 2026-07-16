@extends('layouts.master')

@section('title', 'Journal Entries')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Journal Entries</h2>
        <a href="{{ route('finance.coa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list me-1"></i> Chart of Accounts
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Entry #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Lines</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            @php
                                $amount = $entry->lines->sum('debit');
                            @endphp
                            <tr>
                                <td class="font-monospace">{{ $entry->entry_number }}</td>
                                <td>{{ $entry->date->format('d M Y') }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>{{ $entry->lines->count() }}</td>
                                <td class="text-end font-monospace">{{ number_format($amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $entry->status === 'posted' ? 'success' : 'secondary' }}">{{ $entry->status }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('finance.journal.show', $entry) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No journal entries yet.</td>
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
