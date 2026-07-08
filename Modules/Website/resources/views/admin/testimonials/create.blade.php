@extends('layouts.master')

@section('title', 'Create Testimonial')

@section('page-content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Create Testimonial</h1>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('website.admin.testimonials.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="guest_name" class="form-label">Guest Name</label>
                        <input type="text" class="form-control @error('guest_name') is-invalid @enderror" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" required>
                        @error('guest_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="rating" class="form-label">Rating (1-5)</label>
                        <input type="number" class="form-control @error('rating') is-invalid @enderror" id="rating" name="rating" value="{{ old('rating', 5) }}" min="1" max="5" required>
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="stay_type" class="form-label">Stay Type</label>
                        <input type="text" class="form-control @error('stay_type') is-invalid @enderror" id="stay_type" name="stay_type" value="{{ old('stay_type') }}" placeholder="e.g., Business, Leisure">
                        @error('stay_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="text" class="form-label">Review</label>
                    <textarea class="form-control @error('text') is-invalid @enderror" id="text" name="text" rows="4" required>{{ old('text') }}</textarea>
                    @error('text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="guest_image" class="form-label">Guest Image URL</label>
                    <input type="text" class="form-control @error('guest_image') is-invalid @enderror" id="guest_image" name="guest_image" value="{{ old('guest_image') }}" placeholder="https://example.com/avatar.jpg">
                    @error('guest_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="approved" name="approved" value="1" {{ old('approved', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="approved">Approved (visible on website)</label>
                </div>
                <button type="submit" class="btn btn-primary">Create Testimonial</button>
            </form>
        </div>
    </div>
@endsection
