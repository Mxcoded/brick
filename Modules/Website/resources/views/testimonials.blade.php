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
    .star-rating .star-label { cursor: pointer; }
    .star-rating .star-label i { color: #ddd; transition: color 0.15s ease; }
    .star-rating .star-label.active i { color: #f1c40f; }
</style>
@endpush

@push('scripts')
<script>
    let currentRating = {{ old('rating', 0) }};

    function setRating(rating) {
        document.querySelectorAll('.star-label').forEach(label => {
            let val = parseInt(label.dataset.value);
            label.classList.toggle('active', val <= rating);
        });
    }

    function updateRatingText(rating, isHover) {
        let el = document.getElementById('ratingText');
        if (rating > 0) {
            let prefix = isHover ? 'Rate' : 'Your rating';
            el.innerHTML = '<i class="fas fa-star text-warning me-1"></i>' + prefix + ': ' + rating + ' / 5';
        } else {
            el.innerHTML = '<span class="text-muted"><i class="far fa-star me-1"></i>Tap a star to rate</span>';
        }
    }

    function hoverStar(el) {
        let rating = parseInt(el.dataset.value);
        setRating(rating);
        updateRatingText(rating, true);
    }

    function resetStars() {
        setRating(currentRating);
        updateRatingText(currentRating, false);
    }

    function setStar(el) {
        currentRating = parseInt(el.dataset.value);
        document.querySelectorAll('.star-input').forEach(input => {
            input.checked = parseInt(input.value) === currentRating;
        });
        resetStars();
    }

    document.addEventListener('DOMContentLoaded', resetStars);
</script>
@endpush
