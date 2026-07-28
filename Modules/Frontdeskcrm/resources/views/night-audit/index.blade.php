@extends('layouts.master')

@section('title', 'Night Audit')
@section('page-content')

<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">Night Audit</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Status Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">In-House Guests</h6>
                    <h3 class="mb-0">{{ $inHouseCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Today's Room Revenue</h6>
                    <h3 class="mb-0">{{ number_format($todayRevenue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Last Audit</h6>
                    <h3 class="mb-0">{{ $lastAudit ? $lastAudit->business_date->format('M d, Y') : 'Never' }}</h3>
                    <small>{{ $lastAudit ? $lastAudit->rooms_occupied . ' rooms, ' . number_format($lastAudit->total_revenue_posted, 2) : '' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Audits Run</h6>
                    <h3 class="mb-0">{{ $auditLogs->total() }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Run Night Audit --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Run Night Audit</h5>
            <p class="text-muted">Post room charges for all in-house guests and advance the business date.</p>
            <form action="{{ route('frontdesk.night-audit.run') }}" method="POST" class="row g-2 align-items-end"
                  onsubmit="return confirm('Run night audit? This will post room charges for all in-house guests.')">
                @csrf
                <div class="col-auto">
                    <label class="form-label">Business Date</label>
                    <input type="date" name="date" class="form-control" value="{{ today()->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-moon me-2"></i>Run Night Audit
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('frontdesk.night-audit.preview') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-2"></i>Preview
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Audit Log --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Audit History</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Business Date</th>
                        <th>Status</th>
                        <th>Rooms</th>
                        <th>Revenue</th>
                        <th>Duration</th>
                        <th>Performed By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $log->business_date->format('M d, Y') }}</td>
                        <td>
                            @if($log->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($log->status === 'running')
                                <span class="badge bg-warning">Running</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td>{{ $log->rooms_occupied }}</td>
                        <td>{{ number_format($log->total_revenue_posted, 2) }}</td>
                        <td>
                            @if($log->completed_at)
                                {{ $log->started_at->diffInSeconds($log->completed_at) }}s
                            @else
                                &mdash;
                            @endif
                        </td>
                        <td>{{ $log->performedBy?->name ?? 'System' }}</td>
                        <td>
                            <a href="{{ route('frontdesk.night-audit.show', $log) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">No night audits run yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $auditLogs->links() }}</div>
    </div>
</div>
@endsection
