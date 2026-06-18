@extends('layouts.master')

@section('page-content')
<div class="container-fluid py-4">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-globe me-2 text-primary"></i>Channel Manager</h1>
            <p class="text-muted mb-0">Manage OTA connections and sync room availability across channels</p>
        </div>
        <a href="{{ route('frontdesk.channels.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Channel</a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
                    <div class="text-muted small">Total Channels</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-success">{{ $stats['active'] }}</div>
                    <div class="text-muted small">Active</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['needs_sync'] }}</div>
                    <div class="text-muted small">Needs Sync</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Channel</th>
                            <th>Provider</th>
                            <th>Status</th>
                            <th>Last Sync</th>
                            <th>Room Mappings</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($channels as $ch)
                        <tr>
                            <td class="fw-semibold">{{ $ch->name }}</td>
                            <td><span class="badge bg-secondary">{{ \Modules\Frontdeskcrm\Models\Channel::PROVIDERS[$ch->provider] ?? $ch->provider ?: '—' }}</span></td>
                            <td>
                                @if($ch->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($ch->last_sync_at)
                                <small>{{ $ch->last_sync_at->diffForHumans() }}</small>
                                @if($ch->last_sync_status === 'success')
                                <i class="fas fa-check-circle text-success ms-1"></i>
                                @elseif($ch->last_sync_status === 'failed')
                                <i class="fas fa-exclamation-circle text-danger ms-1"></i>
                                @endif
                                @else
                                <small class="text-muted">Never</small>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ $ch->roomMappings->count() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('frontdesk.channels.show', $ch->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('frontdesk.channels.edit', $ch->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('frontdesk.channels.destroy', $ch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this channel?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-globe fa-2x mb-2 d-block"></i>
                                No channels configured yet.
                                <a href="{{ route('frontdesk.channels.create') }}" class="d-block mt-2">Add your first channel</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection