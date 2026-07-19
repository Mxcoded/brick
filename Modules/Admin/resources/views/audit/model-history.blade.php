@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.audits.index') }}">Audit Trail</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $modelName }} #{{ $id }} History</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0">
            <i class="fas fa-shield-alt me-2"></i>
            {{ $modelName }} #{{ $id }} History
        </h3>
        <div class="d-flex gap-2">
            <span class="text-muted small align-self-center">{{ $audits->total() }} records</span>
            <a href="{{ route('admin.audits.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Audit Trail
            </a>
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
                            @endphp
                            <tr class="border-start border-3 border-{{ $eventColor }}" style="cursor: pointer;" onclick="window.location='{{ route('admin.audits.show', $audit->id) }}'">
                                <td class="small">{{ $audit->id }}</td>
                                <td class="small text-nowrap">{{ $audit->created_at->format('M d, Y H:i') }}</td>
                                <td class="fw-semibold">
                                    @if($audit->user)
                                        {{ $audit->user->name }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $eventColor }} rounded-pill text-capitalize">{{ $audit->event }}</span>
                                </td>
                                <td class="small text-muted font-monospace">{{ $audit->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No audit records found for this model instance.</td></tr>
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
