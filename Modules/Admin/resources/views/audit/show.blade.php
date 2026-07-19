@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.audits.index') }}">Audit Trail</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $modelName }} #{{ $audit->auditable_id }}</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0">
            <i class="fas fa-shield-alt me-2"></i>Audit Record
            <span class="badge bg-light text-dark border ms-2">{{ $modelName }} #{{ $audit->auditable_id }}</span>
        </h3>
        <a href="{{ route('admin.audits.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Audit Trail
        </a>
    </div>

    {{-- Summary --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-1"></i> Summary</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small fw-semibold mb-1">User</div>
                    <div class="fw-semibold">
                        @if($audit->user)
                            {{ $audit->user->name }}
                            <div class="small text-muted">ID: {{ $audit->user_id }}</div>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small fw-semibold mb-1">Event</div>
                    @php
                        $eventColor = match ($audit->event) {
                            'created' => 'success',
                            'updated' => 'warning',
                            'deleted' => 'danger',
                            'restored' => 'info',
                            default => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $eventColor }} rounded-pill text-capitalize fs-6">{{ $audit->event }}</span>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small fw-semibold mb-1">Model</div>
                    <div>
                        <span class="badge bg-light text-dark border">{{ $modelName }}</span>
                        <div class="small text-muted">ID: {{ $audit->auditable_id }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small fw-semibold mb-1">IP Address</div>
                    <div class="small font-monospace">{{ $audit->ip_address }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small fw-semibold mb-1">Timestamp</div>
                    <div class="small">{{ $audit->created_at->format('M d, Y H:i:s') }}</div>
                </div>
            </div>
            @if($audit->user_agent)
                <div class="mt-3">
                    <div class="text-muted small fw-semibold mb-1">User Agent</div>
                    <div class="small text-muted font-monospace text-break">{{ $audit->user_agent }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Changes --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-semibold"><i class="fas fa-exchange-alt me-1"></i> Field Changes</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 25%">Field</th>
                            <th style="width: 37.5%">Old Value</th>
                            <th style="width: 37.5%">New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($changes as $key => $change)
                            @php
                                $hasChanged = $change['old'] != $change['new'] || is_null($change['old']) !== is_null($change['new']);
                            @endphp
                            <tr class="{{ $hasChanged ? 'table-warning' : '' }}">
                                <td class="fw-semibold text-charcoal">{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                <td class="small">
                                    @if(!is_null($change['old']))
                                        @if(is_array($change['old']))
                                            <code class="small">{{ json_encode($change['old']) }}</code>
                                        @else
                                            <span class="text-break">{{ $change['old'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted fst-italic">—</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if(!is_null($change['new']))
                                        @if(is_array($change['new']))
                                            <code class="small">{{ json_encode($change['new']) }}</code>
                                        @else
                                            <span class="text-break">{{ $change['new'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted fst-italic">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No field changes recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
