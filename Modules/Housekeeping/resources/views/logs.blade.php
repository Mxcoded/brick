@extends('layouts.master')

@section('page-content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="fas fa-history"></i> Cleaning Logs</h3>
        </div>
        <a href="{{ route('housekeeping.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Housekeeping
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date / Time</th>
                            <th>Room</th>
                            <th>Floor</th>
                            <th>Status Change</th>
                            <th>Cleaned By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-nowrap">
                                    <small>{{ $log->created_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->roomUnit?->room_number ?? 'N/A' }}</strong>
                                    <small class="text-muted d-block">{{ $log->roomUnit?->roomType?->name ?? '' }}</small>
                                </td>
                                <td>{{ $log->roomUnit?->floor ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ match($log->status_from) { 'dirty' => 'danger', 'cleaning' => 'warning', 'clean' => 'success', 'inspected' => 'info', default => 'secondary' } }} bg-opacity-75">
                                        {{ ucfirst($log->status_from) }}
                                    </span>
                                    <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                    <span class="badge bg-{{ match($log->status_to) { 'dirty' => 'danger', 'cleaning' => 'warning', 'clean' => 'success', 'inspected' => 'info', default => 'secondary' } }} bg-opacity-75">
                                        {{ ucfirst($log->status_to) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $log->cleanedBy?->name ?? 'System' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->notes ?: '-' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No cleaning logs yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
</div>
@endsection
