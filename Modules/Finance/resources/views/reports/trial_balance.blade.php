@extends('layouts.master')

@section('title', 'Trial Balance')

@php
    $balanced = abs($totalDebit - $totalCredit) < 0.005;
@endphp

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Trial Balance</h2>
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Reports
        </a>
    </div>

    @include('finance::reports._date_filter')

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>GL Code</th>
                            <th>Account</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            @if ($account->debit_total == 0 && $account->credit_total == 0)
                                @continue
                            @endif
                            <tr>
                                <td class="font-monospace">{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td class="text-end font-monospace">{{ $account->debit_total > 0 ? number_format($account->debit_total, 2) : '' }}</td>
                                <td class="text-end font-monospace">{{ $account->credit_total > 0 ? number_format($account->credit_total, 2) : '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No posted entries in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">Totals</td>
                            <td class="text-end font-monospace">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-end font-monospace">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-between">
            <span class="{{ $balanced ? 'text-success' : 'text-danger' }}">
                <i class="fas fa-{{ $balanced ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
                {{ $balanced ? 'Balanced' : 'Out of balance' }}
            </span>
        </div>
    </div>
@endsection
