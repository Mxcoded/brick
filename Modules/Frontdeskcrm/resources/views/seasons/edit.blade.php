@extends('layouts.master')

@section('title', 'Edit Season')
@section('page-content')

<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">Edit Season: {{ $season->name }}</h4>

    <form action="{{ route('frontdesk.seasons.update', $season) }}" method="POST" class="row g-3">
        @csrf @method('PUT')

        <div class="col-md-4">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code', $season->code) }}" maxlength="20" required>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-8">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $season->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description', $season->description) }}</textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Valid From <span class="text-danger">*</span></label>
            <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', $season->valid_from->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Valid To <span class="text-danger">*</span></label>
            <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to', $season->valid_to->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Rate Multiplier <span class="text-danger">*</span></label>
            <input type="number" step="0.0001" name="rate_multiplier" class="form-control"
                   value="{{ old('rate_multiplier', $season->rate_multiplier) }}" required min="0" max="999.9999">
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $season->is_active) ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-gold">Update Season</button>
            <a href="{{ route('frontdesk.seasons.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
