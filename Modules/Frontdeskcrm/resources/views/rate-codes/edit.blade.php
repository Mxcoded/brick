@extends('layouts.master')

@section('title', 'Edit Rate Code')
@section('page-content')

<div class="container-fluid py-4">
    <h4 class="mb-4 fw-bold">Edit Rate Code: {{ $rateCode->code }}</h4>

    <form action="{{ route('frontdesk.rate-codes.update', $rateCode) }}" method="POST" class="row g-3">
        @csrf @method('PUT')

        <div class="col-md-4">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code', $rateCode->code) }}" maxlength="20" required>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $rateCode->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-2">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" class="form-control" value="{{ old('currency', $rateCode->currency) }}" maxlength="3">
        </div>
        <div class="col-md-2">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $rateCode->sort_order) }}" min="0">
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2">{{ old('description', $rateCode->description) }}</textarea>
        </div>

        <div class="col-md-3">
            <label class="form-label">Default Rate <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="default_rate" class="form-control @error('default_rate') is-invalid @enderror"
                   value="{{ old('default_rate', $rateCode->default_rate) }}" required min="0">
            @error('default_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-2">
            <label class="form-label">Min LOS</label>
            <input type="number" name="min_los" class="form-control" value="{{ old('min_los', $rateCode->min_los) }}" min="1">
        </div>
        <div class="col-md-2">
            <label class="form-label">Max LOS</label>
            <input type="number" name="max_los" class="form-control" value="{{ old('max_los', $rateCode->max_los) }}" min="1">
        </div>
        <div class="col-md-2">
            <label class="form-label">Valid From</label>
            <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', $rateCode->valid_from?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Valid To</label>
            <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to', $rateCode->valid_to?->format('Y-m-d')) }}">
        </div>

        <div class="col-12">
            <div class="card bg-light p-3">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="closed_to_arrival" class="form-check-input" value="1" {{ old('closed_to_arrival', $rateCode->closed_to_arrival) ? 'checked' : '' }}>
                            <label class="form-check-label">CTA</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="closed_to_departure" class="form-check-input" value="1" {{ old('closed_to_departure', $rateCode->closed_to_departure) ? 'checked' : '' }}>
                            <label class="form-check-label">CTD</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="apply_weekdays" class="form-check-input" value="1" {{ old('apply_weekdays', $rateCode->apply_weekdays) ? 'checked' : '' }}>
                            <label class="form-check-label">Weekdays</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="apply_weekends" class="form-check-input" value="1" {{ old('apply_weekends', $rateCode->apply_weekends) ? 'checked' : '' }}>
                            <label class="form-check-label">Weekends</label>
                        </div>
                    </div>
                    <div class="col-md-3 mt-2">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $rateCode->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-gold">Update Rate Code</button>
            <a href="{{ route('frontdesk.rate-codes.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection
