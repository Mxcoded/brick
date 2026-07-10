@extends('website::layouts.master')

@section('title', $typeLabel . ' Reviews')

@push('head')
    @if ($settings['testimonials_backdrop'] ?? null)
    <link rel="preload" as="image" href="{{ Storage::url($settings['testimonials_backdrop']) }}" fetchpriority="high">
    @endif
@endpush

@section('content')
    <section class="hero-section position-relative overflow-hidden d-flex align-items-center justify-content-center" style="min-height: 50vh;">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('{{ ($settings['testimonials_backdrop'] ?? null) ? Storage::url($settings['testimonials_backdrop']) : 'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=1600&q=80' }}'); background-size: cover; background-position: center 35%;"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.35) 50%, rgba(0,0,0,0.6) 100%);"></div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(ellipse at 30% 50%, rgba(200,161,101,0.1) 0%, transparent 60%);"></div>
        <div class="container position-relative">
            <div class="text-center text-white">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: rgba(200,161,101,0.15); border: 1px solid rgba(200,161,101,0.25); font-size: 0.85rem; letter-spacing: 0.5px;">
                        <i class="fas fa-star text-warning" style="font-size: 0.7rem;"></i> We value every voice
                    </span>
                </div>
                <h1 class="display-4 fw-bold mb-3" style="text-shadow: 0 2px 20px rgba(53, 53, 53, 0.758); color: #fff;">Share Your Experience</h1>
                <p class="lead mb-0" style="text-shadow: 0 1px 10px rgba(0,0,0,0.2);">Tell us about your time at Brickspoint</p>
            </div>
        </div>
    </section>

    {{-- Filter Tabs --}}
    <section class="py-4" style="background: #f8f9fa;">
        <div class="container">
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="{{ route('website.testimonials', ['type' => 'stay']) }}"
                   class="btn {{ $type === 'stay' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
                    <i class="fas fa-hotel me-1"></i> Guest Reviews
                    @if ($stayCount > 0) <span class="badge bg-light text-dark ms-1">{{ $stayCount }}</span> @endif
                </a>
                <a href="{{ route('website.testimonials', ['type' => 'restaurant']) }}"
                   class="btn {{ $type === 'restaurant' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
                    <i class="fas fa-utensils me-1"></i> Restaurant Reviews
                    @if ($restaurantCount > 0) <span class="badge bg-light text-dark ms-1">{{ $restaurantCount }}</span> @endif
                </a>
                <a href="{{ route('website.testimonials', ['type' => 'event']) }}"
                   class="btn {{ $type === 'event' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
                    <i class="fas fa-calendar-alt me-1"></i> Event Reviews
                    @if ($eventCount > 0) <span class="badge bg-light text-dark ms-1">{{ $eventCount }}</span> @endif
                </a>
            </div>
        </div>
    </section>

    {{-- Reviews List --}}
    <section class="py-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($reviews->isEmpty())
                <div class="text-center py-5">
                    <i class="far fa-smile-wink fa-3x mb-3 opacity-50"></i>
                    <p class="text-muted fs-5">No {{ strtolower($typeLabel) }} reviews yet. Be the first!</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($reviews as $review)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        @if ($review->guest_image)
                                            <img src="{{ $review->guest_image }}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;" alt="">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width:48px;height:48px;background:rgba(200,161,101,0.15);color:var(--bs-primary);font-size:1.1rem;flex-shrink:0;">
                                                {{ substr($review->guest_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $review->guest_name }}</h6>
                                            <small class="text-muted">{{ $review->contextLabel() }}</small>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        @for ($i = 0; $i < 5; $i++)
                                            <i class="fa{{ $i < $review->rating ? 's' : 'r' }} fa-star text-warning" style="font-size:0.85rem"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-0 text-muted flex-grow-1" style="font-size:0.95rem;">{{ $review->text }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Submit Review Form --}}
    <section class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="h3 mb-2">Leave a Review</h2>
                            <p class="text-muted mb-4">Tell us about your experience at Brickspoint.</p>

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
                                    <label class="form-label fw-semibold">Email (Optional)</label>
                                    <input type="email" name="email" class="form-control form-control-lg"
                                        value="{{ old('email') }}" placeholder="To receive a confirmation of your review">
                                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Review Type</label>
                                    <select name="type" id="review_type" class="form-select form-control-lg" required>
                                        <option value="stay" {{ old('type', $type) === 'stay' ? 'selected' : '' }}>Stay Review</option>
                                        <option value="restaurant" {{ old('type', $type) === 'restaurant' ? 'selected' : '' }}>Restaurant / Dining Review</option>
                                        <option value="event" {{ old('type', $type) === 'event' ? 'selected' : '' }}>Event Review</option>
                                    </select>
                                </div>

                                <div class="mb-4" id="stay_context_field">
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

                                <div class="mb-4 d-none" id="restaurant_context_field">
                                    <label class="form-label fw-semibold">Dining Venue (Optional)</label>
                                    <input type="text" name="dining_venue" class="form-control form-control-lg"
                                        value="{{ old('dining_venue') }}" placeholder="e.g. Taste Restaurant, Taste Cafe">
                                </div>

                                <div class="mb-4 d-none" id="event_context_field">
                                    <label class="form-label fw-semibold">Event Name (Optional)</label>
                                    <input type="text" name="event_name" class="form-control form-control-lg"
                                        value="{{ old('event_name') }}" placeholder="e.g. New Year Gala, Wedding Reception">
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
                const stars = '&#9733;'.repeat(rating) + '&#9734;'.repeat(5 - rating);
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

        if (currentRating > 0) {
            setVisual(currentRating);
            updateText(currentRating, false);
        }

        // Type toggle
        const typeSelect = document.getElementById('review_type');
        const stayField = document.getElementById('stay_context_field');
        const restaurantField = document.getElementById('restaurant_context_field');
        const eventField = document.getElementById('event_context_field');

        function toggleContextFields() {
            const val = typeSelect.value;
            stayField.classList.toggle('d-none', val !== 'stay');
            restaurantField.classList.toggle('d-none', val !== 'restaurant');
            eventField.classList.toggle('d-none', val !== 'event');
        }

        typeSelect.addEventListener('change', toggleContextFields);
        toggleContextFields();
    });
</script>
@endpush
