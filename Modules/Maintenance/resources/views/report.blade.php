@extends('layouts.master')

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-chart-bar me-2" style="color: var(--luxury-gold);"></i>Maintenance Report</h2>
            <p class="text-muted mb-0">Filter and export maintenance logs</p>
        </div>
        <a href="{{ route('maintenance.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
        </a>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('maintenance.report') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        @foreach (\Modules\Maintenance\Models\MaintenanceLog::DEPARTMENTS as $key => $label)
                            <option value="{{ $key }}" {{ request('department') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn" style="background-color: var(--luxury-gold); color: #fff;">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('maintenance.report') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
            @if ($logs->count())
                <form action="{{ route('maintenance.report.export') }}" method="POST" class="mt-3 text-end">
                    @csrf
                    <input type="hidden" name="department" value="{{ request('department') }}">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="from" value="{{ request('from') }}">
                    <input type="hidden" name="to" value="{{ request('to') }}">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-primary">{{ $summary['total'] }}</div>
                    <div class="text-muted small">Total (Filtered)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-warning">{{ $summary['open'] }}</div>
                    <div class="text-muted small">Open / In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold text-success">{{ $summary['completed'] }}</div>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-4 fw-bold" style="color: var(--luxury-gold);">NGN {{ number_format($summary['totalCost'], 2) }}</div>
                    <div class="text-muted small">Total Cost</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="fas fa-list me-2" style="color: var(--luxury-gold);"></i>Log Entries
            <span class="badge bg-secondary ms-2">{{ $logs->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if ($logs->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Complaint</th>
                                <th>Lodged By</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Cost (NGN)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-muted">{{ $log->id }}</td>
                                    <td>{{ $log->location }}</td>
                                    <td><span class="badge bg-secondary">{{ $log->department }}</span></td>
                                    <td>{{ Str::limit($log->nature_of_complaint, 40) }}</td>
                                    <td>{{ $log->lodged_by }}</td>
                                    <td>
                                        @php
                                            $badge = ['new' => 'warning', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $badge[$log->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $log->complaint_datetime->format('M d, Y') }}</td>
                                    <td>{{ $log->cost_of_fixing ? number_format($log->cost_of_fixing, 2) : '--' }}</td>
                                    <td>
                                        <a href="{{ route('maintenance.show', $log->id) }}" class="btn btn-sm btn-outline-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            @else
                <p class="text-muted mb-0 text-center py-4">No logs match your filters.</p>
            @endif
        </div>
    </div>
@endsection

@section('styles')
<style>
    .card { border-radius: 10px; }
    .card-header { border-radius: 10px 10px 0 0 !important; border-bottom: 2px solid #f0f0f0; }
    .fs-4 { font-size: 1.5rem !important; }
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
</style>
@endsection
