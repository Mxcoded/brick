@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Audit Trail</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-shield-alt me-2"></i>Audit Trail</h3>
        <span class="text-muted small">{{ $audits->total() }} total records</span>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Event</label>
                    <select name="event" class="form-select">
                        <option value="">All Events</option>
                        @foreach (['created', 'updated', 'deleted', 'restored'] as $event)
                            <option value="{{ $event }}" {{ request('event') === $event ? 'selected' : '' }}>{{ ucfirst($event) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Model</label>
                    <select name="model" class="form-select">
                        <option value="">All Models</option>
                        @foreach ($auditableModels as $model)
                            <option value="{{ $model }}" {{ request('model') === $model ? 'selected' : '' }}>{{ class_basename($model) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">User ID</label>
                    <input type="number" name="user_id" class="form-control" placeholder="User ID" value="{{ request('user_id') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-gold w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="{{ route('admin.audits.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Audits Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th>ID</th>
                            <th>Date / Time</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Model</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                            @php
                                $eventColor = match ($audit->event) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    'restored' => 'info',
                                    default => 'secondary',
                                };
                                $shortModel = class_basename($audit->auditable_type);
                            @endphp
                            <tr class="border-start border-3 border-{{ $eventColor }}" style="cursor: pointer;" onclick="window.location='{{ route('admin.audits.show', $audit->id) }}'">
                                <td class="small">{{ $audit->id }}</td>
                                <td class="small text-nowrap">{{ $audit->created_at->format('M d, Y H:i') }}</td>
                                <td class="fw-semibold">
                                    @if($audit->user)
                                        <a href="{{ route('admin.audits.index', ['user_id' => $audit->user_id]) }}" class="text-decoration-none" onclick="event.stopPropagation()">{{ $audit->user->name }}</a>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $eventColor }} rounded-pill text-capitalize">{{ $audit->event }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $shortModel }}</span>
                                    <span class="small text-muted">#{{ $audit->auditable_id }}</span>
                                </td>
                                <td class="small text-muted font-monospace">{{ $audit->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No audit records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $audits->withQueryString()->links() }}
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
