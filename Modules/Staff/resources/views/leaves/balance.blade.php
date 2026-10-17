@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Leave Balance</h1>
        <a href="{{ route('staff.leaves.request') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill me-2"></i>Request New Leave
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Leave Balance ({{ $year }})</h5>
        </div>
        <div class="card-body">
            @if($leaveBalances->isNotEmpty())
                <div class="row">
                    @foreach ($leaveBalances as $balance)
                        @php
                            $percentageUsed = ($balance->total_days > 0) ? ($balance->used_days / $balance->total_days) * 100 : 0;
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-3">
                            <strong>{{ $balance->leave_type }}</strong>
                            <div class="progress" style="height: 20px;" title="{{ $balance->used_days }} of {{ $balance->total_days }} days used">
                                <div class="progress-bar" role="progressbar" style="width: {{ $percentageUsed }}%;" aria-valuenow="{{ $balance->used_days }}" aria-valuemin="0" aria-valuemax="{{ $balance->total_days }}">
                                    {{ $balance->used_days }} Used
                                </div>
                            </div>
                            <small class="text-muted">{{ $balance->remaining_days }} days remaining of {{ $balance->total_days }} total</small>
                        </div>
                    @endforeach
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leaveBalances as $balance)
                                <tr>
                                    <td>{{ $balance->leave_type }}</td>
                                    <td class="text-center">{{ $balance->total_days }}</td>
                                    <td class="text-center">{{ $balance->used_days }}</td>
                                    <td class="text-center">{{ $balance->remaining_days }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No leave balances have been configured for you this year.</p>
            @endif
        </div>
    </div>
</div>
@endsection
