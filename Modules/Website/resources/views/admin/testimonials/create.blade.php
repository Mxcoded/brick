@extends('layouts.master')

@section('title', 'Create Testimonial')

@section('page-content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 rounded-top-4">
            <h1 class="h4 mb-0 fw-bold" style="color: var(--theme-heading);">
                <i class="fas fa-plus-circle me-2" style="color: var(--theme-primary);"></i>New Testimonial
            </h1>
            <a href="{{ route('website.admin.testimonials.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('website.admin.testimonials.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Guest Name <span class="text-danger">*</span></label>
                        <input type="text" name="guest_name" class="form-control @error('guest_name') is-invalid @enderror" value="{{ old('guest_name') }}" required>
                        @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Email (Optional)</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="guest@example.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                        <input type="number" name="rating" class="form-control @error('rating') is-invalid @enderror" min="1" max="5" value="{{ old('rating', 5) }}" required>
                        @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="type" id="testimonial_type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            @foreach (\Modules\Website\Models\Testimonial::TYPES as $t)
                                <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12" id="stay_fields">
                        <label class="form-label fw-semibold">Stay Type</label>
                        <input type="text" name="stay_type" class="form-control @error('stay_type') is-invalid @enderror" value="{{ old('stay_type') }}" placeholder="e.g. Deluxe Room, Presidential Suite">
                        @error('stay_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 d-none" id="restaurant_fields">
                        <label class="form-label fw-semibold">Dining Venue</label>
                        <input type="text" name="dining_venue" class="form-control @error('dining_venue') is-invalid @enderror" value="{{ old('dining_venue') }}" placeholder="e.g. Sky Restaurant, Pool Bar">
                        @error('dining_venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 d-none" id="event_fields">
                        <label class="form-label fw-semibold">Event Name</label>
                        <input type="text" name="event_name" class="form-control @error('event_name') is-invalid @enderror" value="{{ old('event_name') }}" placeholder="e.g. New Year Gala, Wedding Reception">
                        @error('event_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Guest Image (URL)</label>
                        <input type="text" name="guest_image" class="form-control @error('guest_image') is-invalid @enderror" value="{{ old('guest_image') }}" placeholder="https://example.com/image.jpg">
                        @error('guest_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mt-4">
                            <input type="checkbox" name="approved" class="form-check-input" id="approved" value="1" {{ old('approved') ? 'checked' : '' }}>
                            <label class="form-check-label" for="approved">Approved</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Review Text <span class="text-danger">*</span></label>
                        <textarea name="text" class="form-control @error('text') is-invalid @enderror" rows="4" required>{{ old('text') }}</textarea>
                        @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-themed rounded-pill px-5 py-2">
                            <i class="fas fa-save me-1"></i> Create Testimonial
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('testimonial_type');
        const stayFields = document.getElementById('stay_fields');
        const restaurantFields = document.getElementById('restaurant_fields');
        const eventFields = document.getElementById('event_fields');

        function toggleFields() {
            const val = typeSelect.value;
            stayFields.classList.toggle('d-none', val !== 'stay');
            restaurantFields.classList.toggle('d-none', val !== 'restaurant');
            eventFields.classList.toggle('d-none', val !== 'event');
        }

        typeSelect.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
@endpush
