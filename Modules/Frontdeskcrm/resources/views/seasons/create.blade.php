@extends('layouts.master')

@section('title', 'Create Season')
@section('page-content')

<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">Create Season</h4>

    <form action="{{ route('frontdesk.seasons.store') }}" method="POST" class="row g-3">
        @csrf

        <div class="col-md-4">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code') }}" maxlength="20" required placeholder="e.g. PEAK2026">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required placeholder="e.g. Peak Season 2026">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Valid From <span class="text-danger">*</span></label>
            <input type="date" name="valid_from" class="form-control @error('valid_from') is-invalid @enderror"
                   value="{{ old('valid_from') }}" required>
            @error('valid_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Valid To <span class="text-danger">*</span></label>
            <input type="date" name="valid_to" class="form-control @error('valid_to') is-invalid @enderror"
                   value="{{ old('valid_to') }}" required>
            @error('valid_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Rate Multiplier <span class="text-danger">*</span></label>
            <input type="number" step="0.0001" name="rate_multiplier" class="form-control @error('rate_multiplier') is-invalid @enderror"
                   value="{{ old('rate_multiplier', '1.0000') }}" required min="0" max="999.9999">
            @error('rate_multiplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">1.0000 = normal, 1.5000 = 50% premium, 0.7500 = 25% discount</div>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-gold">Create Season</button>
            <a href="{{ route('frontdesk.seasons.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
