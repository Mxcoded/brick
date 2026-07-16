@extends('layouts.master')

@section('title', 'Balance Sheet')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Balance Sheet</h2>
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Reports
        </a>
    </div>

    @include('finance::reports._date_filter')

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold">Assets</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            @foreach ($accounts->where('type', 'asset')->where(fn ($a) => $a->debit_total - $a->credit_total != 0) as $account)
                                <tr>
                                    <td>{{ $account->code }} — {{ $account->name }}</td>
                                    <td class="text-end font-monospace">{{ number_format($account->debit_total - $account->credit_total, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold table-light">
                                <td>Total Assets</td>
                                <td class="text-end font-monospace">{{ number_format($totalAssets, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold">Liabilities &amp; Equity</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            @foreach ($accounts->where('type', 'liability')->where(fn ($a) => $a->credit_total - $a->debit_total != 0) as $account)
                                <tr>
                                    <td>{{ $account->code }} — {{ $account->name }}</td>
                                    <td class="text-end font-monospace">{{ number_format($account->credit_total - $account->debit_total, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold">
                                <td>Total Liabilities</td>
                                <td class="text-end font-monospace">{{ number_format($totalLiabilities, 2) }}</td>
                            </tr>

                            @foreach ($accounts->where('type', 'equity')->where(fn ($a) => $a->credit_total - $a->debit_total != 0) as $account)
                                <tr>
                                    <td>{{ $account->code }} — {{ $account->name }}</td>
                                    <td class="text-end font-monospace">{{ number_format($account->credit_total - $account->debit_total, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold">
                                <td>Total Equity</td>
                                <td class="text-end font-monospace">{{ number_format($totalEquity, 2) }}</td>
                            </tr>

                            <tr>
                                <td>Retained Earnings (Net Income)</td>
                                <td class="text-end font-monospace">{{ number_format($netIncome, 2) }}</td>
                            </tr>
                            <tr class="fw-bold table-light">
                                <td>Total Liabilities &amp; Equity</td>
                                <td class="text-end font-monospace">{{ number_format($totalEquityAndLiabilities, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 text-end {{ abs($totalAssets - $totalEquityAndLiabilities) < 0.005 ? 'text-success' : 'text-danger' }}">
        <i class="fas fa-{{ abs($totalAssets - $totalEquityAndLiabilities) < 0.005 ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
        Assets {{ abs($totalAssets - $totalEquityAndLiabilities) < 0.005 ? 'balance' : 'do not balance' }}
        ({{ number_format($totalAssets, 2) }} vs {{ number_format($totalEquityAndLiabilities, 2) }})
    </div>
@endsection
