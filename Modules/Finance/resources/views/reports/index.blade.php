@extends('layouts.master')

@section('title', 'Financial Reports')

@section('page-content')
    <h2 class="mb-3">Financial Reports</h2>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('finance.reports.trial-balance') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-body">
                        <div class="fs-4 mb-2"><i class="fas fa-balance-scale me-2 text-primary"></i> Trial Balance</div>
                        <div class="text-muted small">Debits and credits per account — must balance.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('finance.reports.profit-loss') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-body">
                        <div class="fs-4 mb-2"><i class="fas fa-chart-line me-2 text-success"></i> Profit &amp; Loss</div>
                        <div class="text-muted small">Revenues less expenses for the period.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('finance.reports.balance-sheet') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-body">
                        <div class="fs-4 mb-2"><i class="fas fa-landmark me-2 text-info"></i> Balance Sheet</div>
                        <div class="text-muted small">Assets against liabilities and equity.</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
