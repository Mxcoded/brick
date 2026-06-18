@extends('layouts.master')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Edit Channel</h1>
            <p class="text-muted mb-0">{{ $channel->name }}</p>
        </div>
        <a href="{{ route('frontdesk.channels.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <form method="POST" action="{{ route('frontdesk.channels.update', $channel->id) }}">
        @csrf @method('PUT')
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Channel Details</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Channel Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $channel->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Provider</label>
                            <select name="provider" class="form-select">
                                <option value="">Select Provider</option>
                                @foreach($providers as $val => $label)
                                <option value="{{ $val }}" {{ (old('provider', $channel->provider) === $val) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">API Key / Token</label>
                            <input type="password" name="api_key" class="form-control" value="{{ old('api_key', $channel->api_key) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">API Endpoint URL</label>
                            <input type="url" name="api_endpoint" class="form-control" value="{{ old('api_endpoint', $channel->api_endpoint) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Webhook URL</label>
                            <input type="url" name="webhook_url" class="form-control" value="{{ old('webhook_url', $channel->webhook_url) }}">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" {{ old('is_active', $channel->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (internal)</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $channel->notes) }}</textarea>
                        </div>
                        @if($channel->last_sync_at)
                        <div class="small text-muted">
                            Last sync: {{ $channel->last_sync_at->format('M d, Y h:i A') }}
                            @if($channel->last_sync_status === 'success') <i class="fas fa-check-circle text-success"></i>
                            @elseif($channel->last_sync_status === 'failed') <i class="fas fa-exclamation-circle text-danger"></i>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Room Mappings</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMapping()"><i class="fas fa-plus"></i></button>
                    </div>
                    <div class="card-body" id="mappingsContainer">
                        <p class="small text-muted mb-3">Map internal rooms to this channel's external room IDs.</p>
                        @php $existingMappings = old('room_mappings', $channel->roomMappings->toArray()); @endphp
                        @foreach($existingMappings as $i => $m)
                        <div class="mapping-row border rounded p-2 mb-2">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <select name="room_mappings[{{ $i }}][room_unit_id]" class="form-select form-select-sm">
                                        <option value="">Select Room</option>
                                        @foreach($roomUnits as $ru)
                                        <option value="{{ $ru->id }}" {{ ($m['room_unit_id'] ?? '') == $ru->id ? 'selected' : '' }}>{{ $ru->display_name ?? $ru->room_number }} ({{ $ru->roomType?->name ?? 'N/A' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="room_mappings[{{ $i }}][external_room_id]" class="form-control form-control-sm" placeholder="Ext. Room ID" value="{{ $m['external_room_id'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="room_mappings[{{ $i }}][external_room_name]" class="form-control form-control-sm" placeholder="Ext. Room Name" value="{{ $m['external_room_name'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mapping-row').remove()"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Update Channel</button>
            <a href="{{ route('frontdesk.channels.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('page-scripts')
<script>
let mappingIndex = {{ count($existingMappings) }};

function addMapping() {
    const html = `
    <div class="mapping-row border rounded p-2 mb-2">
        <div class="row g-2">
            <div class="col-md-4">
                <select name="room_mappings[${mappingIndex}][room_unit_id]" class="form-select form-select-sm">
                    <option value="">Select Room</option>
                    @foreach($roomUnits as $ru)
                    <option value="{{ $ru->id }}">{{ $ru->display_name ?? $ru->room_number }} ({{ $ru->roomType?->name ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="room_mappings[${mappingIndex}][external_room_id]" class="form-control form-control-sm" placeholder="Ext. Room ID">
            </div>
            <div class="col-md-3">
                <input type="text" name="room_mappings[${mappingIndex}][external_room_name]" class="form-control form-control-sm" placeholder="Ext. Room Name">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.mapping-row').remove()"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>`;
    document.getElementById('mappingsContainer').insertAdjacentHTML('beforeend', html);
    mappingIndex++;
}
</script>
@endsection