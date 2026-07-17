@extends('layouts.master')

@section('title', 'Add Dining Option')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('website.admin.dining.index') }}" class="btn btn-outline-secondary me-3">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <h1 class="h3 mb-0 fw-bold">Add Dining Option</h1>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('website.admin.dining.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Cuisine Type</label>
                        <input type="text" name="cuisine_type" class="form-control" value="{{ old('cuisine_type') }}" placeholder="e.g. Continental">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Dress Code</label>
                        <input type="text" name="dress_code" class="form-control" value="{{ old('dress_code') }}" placeholder="e.g. Smart Casual">
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" required>{{ old('description') }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Opening Hours</label>
                        <input type="text" name="opening_hours" class="form-control" value="{{ old('opening_hours') }}" placeholder="e.g. Mon–Sun 6:30am–10pm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Featured</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-book-open me-2" style="color: #C8A165;"></i> Menu</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Menu Link (URL)</label>
                        <input type="url" name="menu_link" class="form-control" value="{{ old('menu_link') }}" placeholder="https://example.com/menu">
                        <div class="form-text">External link to an online menu.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Menu PDF</label>
                        <input type="file" name="menu_pdf" class="form-control" accept=".pdf">
                        <div class="form-text">Upload a PDF menu document (max 20MB).</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-gold px-4">
                        <i class="fas fa-save me-1"></i> Create Dining Option
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-gold {
        background-color: #C8A165;
        border-color: #C8A165;
        color: #fff;
    }
    .btn-gold:hover {
        background-color: #b08d55;
        border-color: #b08d55;
        color: #fff;
    }
    .form-control:focus {
        border-color: #C8A165;
        box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.2);
    }
    .form-check-input:checked {
        background-color: #C8A165;
        border-color: #C8A165;
    }
</style>
@endpush
