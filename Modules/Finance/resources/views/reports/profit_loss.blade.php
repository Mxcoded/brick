@extends('layouts.master')

@section('title', 'Profit & Loss')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Profit &amp; Loss</h2>
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Reports
        </a>
    </div>

    @include('finance::reports._date_filter')

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-transparent fw-semibold">Revenues</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tbody>
                    @foreach ($accounts->where('type', 'income')->where(fn ($a) => $a->credit_total - $a->debit_total != 0) as $account)
                        <tr>
                            <td>{{ $account->code }} — {{ $account->name }}</td>
                            <td class="text-end font-monospace">{{ number_format($account->credit_total - $account->debit_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold table-light">
                        <td>Total Revenue</td>
                        <td class="text-end font-monospace">{{ number_format($totalIncome, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-transparent fw-semibold">Expenses</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tbody>
                    @foreach ($accounts->where('type', 'expense')->where(fn ($a) => $a->debit_total - $a->credit_total != 0) as $account)
                        <tr>
                            <td>{{ $account->code }} — {{ $account->name }}</td>
                            <td class="text-end font-monospace">{{ number_format($account->debit_total - $account->credit_total, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold table-light">
                        <td>Total Expense</td>
                        <td class="text-end font-monospace">{{ number_format($totalExpense, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm border-{{ $netIncome >= 0 ? 'success' : 'danger' }}">
        <div class="card-body d-flex justify-content-between fw-bold">
            <span>Net {{ $netIncome >= 0 ? 'Profit' : 'Loss' }}</span>
            <span class="font-monospace">{{ number_format($netIncome, 2) }}</span>
        </div>
    </div>
@endsection
