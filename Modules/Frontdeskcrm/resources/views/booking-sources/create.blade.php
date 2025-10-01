@extends('layouts.master')

@section('page-content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4>Add Booking Source</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('frontdesk.booking-sources.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Type</label>
                <select class="form-select @error('type') is-invalid @enderror" name="type">
                    <option value="">General</option>
                    <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ old('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                    <option value="partner" {{ old('type') == 'partner' ? 'selected' : '' }}>Partner</option>
                </select>
                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Commission Rate (%)</label>
                <input type="number" step="0.01" class="form-control @error('commission_rate') is-invalid @enderror" name="commission_rate" value="{{ old('commission_rate', 0) }}" min="0" max="100">
                @error('commission_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('frontdesk.booking-sources.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection