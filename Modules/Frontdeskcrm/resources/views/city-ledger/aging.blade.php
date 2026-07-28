@extends('layouts.master')

@section('title', 'AR Aging Report')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">AR Aging Report</h4>
        <a href="{{ route('frontdesk.city-ledger.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Accounts
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Current</th>
                            <th class="text-end">1-30 Days</th>
                            <th class="text-end">31-60 Days</th>
                            <th class="text-end">61-90 Days</th>
                            <th class="text-end">90+ Days</th>
                            <th class="text-end">Total Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $row)
                        <tr>
                            <td>
                                <a href="{{ route('frontdesk.city-ledger.show', $row['account']) }}">
                                    <strong>{{ $row['account']->name }}</strong>
                                </a>
                                <br><small class="text-muted">{{ $row['account']->code }}</small>
                            </td>
                            <td class="text-end">{{ number_format($row['aging']['current'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['aging']['1_30'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['aging']['31_60'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['aging']['61_90'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['aging']['90_plus'], 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($row['total_outstanding'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No outstanding balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>TOTALS</td>
                            <td class="text-end">{{ number_format($totals['current'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['1_30'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['31_60'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['61_90'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['90_plus'], 2) }}</td>
                            <td class="text-end">{{ number_format($totals['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
