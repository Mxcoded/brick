@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-history me-2"></i>User Activity Logs</h3>
        <span class="text-muted small">{{ $logs->total() }} total entries</span>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search description, action, IP, user..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach ($actions as $a)
                            <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-gold w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Method</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $parts = explode('.', $log->action);
                                $verb = end($parts);
                                $resource = implode(' ', array_slice($parts, 0, -1));
                                $verbLabel = match ($verb) {
                                    'create', 'store' => 'Created',
                                    'update', 'edit' => 'Updated',
                                    'delete', 'destroy' => 'Deleted',
                                    'assign-room' => 'Room Assigned',
                                    'assign' => 'Assigned',
                                    'approve' => 'Approved',
                                    'reject' => 'Rejected',
                                    'cancel' => 'Cancelled',
                                    'page_view' => 'Viewed',
                                    'login' => 'Logged In',
                                    'logout' => 'Logged Out',
                                    default => ucfirst(str_replace(['-', '_'], ' ', $verb)),
                                };
                                $color = match ($verb) {
                                    'create', 'store' => 'success',
                                    'update', 'edit' => 'primary',
                                    'delete', 'destroy' => 'danger',
                                    'assign-room', 'assign', 'approve' => 'info',
                                    'reject', 'cancel' => 'warning',
                                    'page_view' => 'gold',
                                    default => 'dark',
                                };
                            @endphp
                            <tr class="border-start border-3 border-{{ $color }}">
                                <td class="small text-nowrap">{{ $log->created_at->format('M d, H:i') }}</td>
                                <td class="fw-semibold">
                                    @if($log->user)
                                        <a href="{{ route('admin.activity-logs.index', ['user_id' => $log->user_id]) }}" class="text-decoration-none">{{ $log->user->name }}</a>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $color }} rounded-pill">{{ $verbLabel }}</span>
                                    @if($resource)
                                        <div class="small text-muted mt-1 text-capitalize">{{ str_replace(['-', '_'], ' ', $resource) }}</div>
                                    @endif
                                </td>
                                <td class="small">
                                    {{ $log->description }}
                                    @if($log->model_type && $log->model_id)
                                        <span class="badge bg-light text-dark border ms-1" title="{{ $log->model_type }}">
                                            {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                        </span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $log->method }}</span></td>
                                <td class="small text-muted font-monospace">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No activity logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
@endsection

@section('styles')
<style>
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
</style>
@endsection