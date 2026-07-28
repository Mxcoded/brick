@extends('layouts.master')

@section('title', 'Create Rate Code')
@section('page-content')

<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">Create Rate Code</h4>

    <form action="{{ route('frontdesk.rate-codes.store') }}" method="POST" class="row g-3">
        @csrf

        <div class="col-md-4">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code') }}" maxlength="20" required placeholder="e.g. RACK">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required placeholder="e.g. Rack Rate">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-2">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" class="form-control" value="{{ old('currency', 'NGN') }}" maxlength="3">
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Default Rate (per night) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="default_rate" class="form-control @error('default_rate') is-invalid @enderror"
                   value="{{ old('default_rate') }}" required min="0">
            @error('default_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-2">
            <label class="form-label">Min LOS</label>
            <input type="number" name="min_los" class="form-control" value="{{ old('min_los', 1) }}" min="1">
        </div>
        <div class="col-md-2">
            <label class="form-label">Max LOS</label>
            <input type="number" name="max_los" class="form-control" value="{{ old('max_los') }}" min="1" placeholder="∞">
        </div>

        <div class="col-md-2">
            <label class="form-label">Valid From</label>
            <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Valid To</label>
            <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to') }}">
        </div>

        <div class="col-12">
            <div class="card bg-light p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="closed_to_arrival" class="form-check-input" value="1" {{ old('closed_to_arrival') ? 'checked' : '' }}>
                            <label class="form-check-label">Closed to Arrival (CTA)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="closed_to_departure" class="form-check-input" value="1" {{ old('closed_to_departure') ? 'checked' : '' }}>
                            <label class="form-check-label">Closed to Departure (CTD)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="apply_weekdays" class="form-check-input" value="1" {{ old('apply_weekdays', true) ? 'checked' : '' }}>
                            <label class="form-check-label">Apply on Weekdays</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="apply_weekends" class="form-check-input" value="1" {{ old('apply_weekends', true) ? 'checked' : '' }}>
                            <label class="form-check-label">Apply on Weekends</label>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-gold">Create Rate Code</button>
            <a href="{{ route('frontdesk.rate-codes.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
