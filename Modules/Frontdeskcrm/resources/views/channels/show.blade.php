@extends('layouts.master')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-globe me-2 text-primary"></i>{{ $channel->name }}</h1>
            <p class="text-muted mb-0">{{ \Modules\Frontdeskcrm\Models\Channel::PROVIDERS[$channel->provider] ?? $channel->provider ?: 'No provider' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.channels.edit', $channel->id) }}" class="btn btn-primary"><i class="fas fa-edit me-1"></i> Edit</a>
            <a href="{{ route('frontdesk.channels.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Connection Details</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($channel->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Provider</td>
                            <td>{{ \Modules\Frontdeskcrm\Models\Channel::PROVIDERS[$channel->provider] ?? $channel->provider ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">API Endpoint</td>
                            <td><code class="small">{{ $channel->api_endpoint ?: '—' }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Webhook URL</td>
                            <td><code class="small">{{ $channel->webhook_url ?: '—' }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Sync</td>
                            <td>
                                @if($channel->last_sync_at)
                                {{ $channel->last_sync_at->format('M d, Y h:i A') }}
                                @if($channel->last_sync_status === 'success')
                                <i class="fas fa-check-circle text-success ms-1"></i>
                                @elseif($channel->last_sync_status === 'failed')
                                <i class="fas fa-exclamation-circle text-danger ms-1"></i>
                                @endif
                                @else
                                <span class="text-muted">Never</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By</td>
                            <td>{{ $channel->creator?->name ?: '—' }}</td>
                        </tr>
                        @if($channel->notes)
                        <tr>
                            <td class="text-muted">Notes</td>
                            <td>{{ $channel->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Room Mappings ({{ $channel->roomMappings->count() }})</div>
                <div class="card-body p-0">
                    @if($channel->roomMappings->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Internal Room</th>
                                    <th>Floor</th>
                                    <th>External Room ID</th>
                                    <th>External Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($channel->roomMappings as $mapping)
                                <tr>
                                    <td class="fw-semibold">{{ $mapping->roomUnit?->display_name ?? $mapping->roomUnit?->room_number ?? '—' }}</td>
                                    <td>{{ $mapping->roomUnit?->floor ?: '—' }}</td>
                                    <td><code>{{ $mapping->external_room_id ?: '—' }}</code></td>
                                    <td>{{ $mapping->external_room_name ?: '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-4 mb-0">No room mappings configured.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection