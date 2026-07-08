@extends('website::layouts.master')

@section('title', 'Guest Reviews')

@section('content')
    <section class="hero-section position-relative overflow-hidden"
        style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset('images/contact-hero.jpg') }}') center/cover no-repeat; height: 50vh;">
        <div class="container h-100 d-flex align-items-center justify-content-center">
            <div class="text-center text-white">
                <h1 class="display-4 fw-bold mb-3">Share Your Experience</h1>
                <p class="lead mb-0">We value your feedback</p>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="h3 mb-2">Leave a Review</h2>
                            <p class="text-muted mb-4">Tell us about your stay at Brickspoint Boutique Aparthotel.</p>

                            <form method="POST" action="{{ route('website.testimonials.store') }}">
                                @csrf

                                <div style="position: absolute; left: -9999px;" aria-hidden="true">
                                    <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Your Name</label>
                                    <input type="text" name="guest_name" class="form-control form-control-lg" required
                                        value="{{ old('guest_name') }}" placeholder="Enter your full name">
                                    @error('guest_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Rating</label>
                                    <div class="star-rating d-flex gap-1 fs-3" id="starRating">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}"
                                                {{ old('rating') == $i ? 'checked' : '' }} class="d-none star-input">
                                            <label for="star{{ $i }}" class="star-label" data-value="{{ $i }}">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="mt-2" id="ratingText" style="font-size: 0.95rem; color: #6c757d;">
                                        @if (old('rating'))
                                            <i class="fas fa-star text-warning me-1"></i>Your rating: {{ old('rating') }} / 5
                                        @else
                                            <span class="text-muted"><i class="far fa-star me-1"></i>Tap a star to rate</span>
                                        @endif
                                    </div>
                                    @error('rating') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Stay Type (Optional)</label>
                                    <select name="stay_type" class="form-select form-control-lg">
                                        <option value="">Select stay type</option>
                                        <option value="Business" @selected(old('stay_type') == 'Business')>Business</option>
                                        <option value="Leisure" @selected(old('stay_type') == 'Leisure')>Leisure</option>
                                        <option value="Couple" @selected(old('stay_type') == 'Couple')>Couple</option>
                                        <option value="Family" @selected(old('stay_type') == 'Family')>Family</option>
                                        <option value="Solo" @selected(old('stay_type') == 'Solo')>Solo</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Your Review</label>
                                    <textarea name="text" rows="5" class="form-control form-control-lg" required
                                        placeholder="Share your experience...">{{ old('text') }}</textarea>
                                    @error('text') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your review will be moderated before being published.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('head')
<style>
    .star-rating { gap: 0.25rem; }
    .star-rating .star-label {
        cursor: pointer;
        padding: 0 0.1rem;
        transition: transform 0.15s ease;
    }
    .star-rating .star-label i {
        color: #ddd;
        transition: color 0.15s ease, transform 0.15s ease;
        font-size: 1.75rem;
    }
    .star-rating .star-label.active i,
    .star-rating .star-label.hover i {
        color: #f1c40f;
    }
    .star-rating .star-label.hover i {
        transform: scale(1.15);
    }
    .star-rating .star-label.selected i {
        filter: drop-shadow(0 0 4px rgba(241, 196, 15, 0.5));
    }
    @media (max-width: 576px) {
        .star-rating .star-label i { font-size: 1.5rem; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentRating = {{ old('rating', 0) }};
        const labels = document.querySelectorAll('.star-label');
        const ratingText = document.getElementById('ratingText');

        function setVisual(rating) {
            labels.forEach(label => {
                const val = parseInt(label.dataset.value);
                label.classList.toggle('active', val <= rating);
                label.classList.toggle('selected', val === rating && rating > 0);
            });
        }

        function updateText(rating, isHover) {
            if (rating > 0) {
                const prefix = isHover ? 'Rate' : 'Your rating';
                const stars = '★'.repeat(rating) + '☆'.repeat(5 - rating);
                ratingText.innerHTML = `<span class="text-warning fw-semibold">${stars}</span> ${prefix}: ${rating} / 5`;
            } else {
                ratingText.innerHTML = '<span class="text-muted"><i class="far fa-star me-1"></i>Tap a star to rate</span>';
            }
        }

        function applyRating(rating) {
            currentRating = rating;
            document.querySelectorAll('.star-input').forEach(input => {
                input.checked = parseInt(input.value) === rating;
            });
            setVisual(rating);
            updateText(rating, false);
        }

        labels.forEach(label => {
            label.addEventListener('mouseenter', function () {
                const val = parseInt(this.dataset.value);
                labels.forEach(l => {
                    const v = parseInt(l.dataset.value);
                    l.classList.toggle('hover', v <= val);
                    l.classList.toggle('active', v <= val);
                });
                updateText(val, true);
            });

            label.addEventListener('mouseleave', function () {
                labels.forEach(l => l.classList.remove('hover'));
                setVisual(currentRating);
                updateText(currentRating, false);
            });

            label.addEventListener('click', function () {
                applyRating(parseInt(this.dataset.value));
            });
        });

        // Initialise from old input
        if (currentRating > 0) {
            setVisual(currentRating);
            updateText(currentRating, false);
        }
    });
</script>
@endpush
