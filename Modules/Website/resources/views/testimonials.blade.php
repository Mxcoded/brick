@extends('website::layouts.master')

@section('title', $typeLabel . ' Reviews')

@push('head')
    @if ($settings['testimonials_backdrop'] ?? null)
    <link rel="preload" as="image" href="{{ Storage::url($settings['testimonials_backdrop']) }}" fetchpriority="high">
    @endif
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

        .review-filter-btn {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1.5px solid #dee2e6;
            background: #fff;
            color: #495057;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .review-filter-btn:hover {
            border-color: #C8A165;
            color: #C8A165;
            background: rgba(200, 161, 101, 0.05);
            transform: translateY(-1px);
        }
        .review-filter-btn.active {
            background: #C8A165;
            border-color: #C8A165;
            color: #fff;
            box-shadow: 0 4px 12px rgba(200, 161, 101, 0.3);
        }
        .review-filter-btn .badge-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0 6px;
            margin-left: 6px;
            background: rgba(0,0,0,0.08);
            color: inherit;
            transition: all 0.2s;
        }
        .review-filter-btn.active .badge-count {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }

        .review-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: 1px solid rgba(0,0,0,0.04);
        }
        .review-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.08) !important;
        }

        .hero-section {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40vh;
        }
        @media (min-width: 768px) {
            .hero-section { min-height: 50vh; }
        }
        .hero-section .backdrop {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center 35%;
        }
        .hero-section .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.35) 50%, rgba(0,0,0,0.6) 100%);
        }
        .hero-section .overlay-accent {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 30% 50%, rgba(200,161,101,0.1) 0%, transparent 60%);
        }
    </style>
@endpush

@section('content')
    <section class="hero-section">
        <div class="backdrop" style="background-image: url('{{ ($settings['testimonials_backdrop'] ?? null) ? Storage::url($settings['testimonials_backdrop']) : 'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=1600&q=80' }}');"></div>
        <div class="overlay"></div>
        <div class="overlay-accent"></div>
        <div class="container position-relative">
            <div class="text-center text-white">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: rgba(200,161,101,0.15); border: 1px solid rgba(200,161,101,0.25); font-size: 0.85rem; letter-spacing: 0.5px;">
                        <i class="fas fa-star text-warning" style="font-size: 0.7rem;"></i> We value every voice
                    </span>
                </div>
                <h1 class="display-4 fw-bold mb-3" style="text-shadow: 0 2px 20px rgba(53, 53, 53, 0.758); color: #fff;">{{ $typeLabel }} Reviews</h1>
                <p class="lead mb-0" style="text-shadow: 0 1px 10px rgba(0,0,0,0.2);">Real experiences from real people</p>
            </div>
        </div>
    </section>

    {{-- Filter Tabs --}}
    <section class="py-4 bg-light">
        <div class="container">
            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a href="{{ route('website.testimonials', ['type' => 'stay']) }}"
                   class="review-filter-btn {{ $type === 'stay' ? 'active' : '' }}">
                    <i class="fas fa-hotel me-1"></i> Guest Reviews
                    @if ($stayCount > 0) <span class="badge-count">{{ $stayCount }}</span> @endif
                </a>
                <a href="{{ route('website.testimonials', ['type' => 'restaurant']) }}"
                   class="review-filter-btn {{ $type === 'restaurant' ? 'active' : '' }}">
                    <i class="fas fa-utensils me-1"></i> Restaurant Reviews
                    @if ($restaurantCount > 0) <span class="badge-count">{{ $restaurantCount }}</span> @endif
                </a>
                <a href="{{ route('website.testimonials', ['type' => 'event']) }}"
                   class="review-filter-btn {{ $type === 'event' ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt me-1"></i> Event Reviews
                    @if ($eventCount > 0) <span class="badge-count">{{ $eventCount }}</span> @endif
                </a>
            </div>
        </div>
    </section>

    {{-- Reviews List --}}
    <section class="py-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($reviews->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3" style="font-size: 3rem; opacity: 0.3;">
                        <i class="far fa-star"></i>
                    </div>
                    <h4 class="fw-semibold mb-2">No {{ strtolower($typeLabel) }} Reviews Yet</h4>
                    <p class="text-muted mb-4">Be the first to share your experience!</p>
                    <a href="#submit-review" class="btn btn-primary rounded-pill px-5">
                        <i class="fas fa-pen me-2"></i>Write a Review
                    </a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($reviews as $review)
                        <div class="col-md-6 col-lg-4">
                            <div class="card review-card border-0 shadow-sm h-100 rounded-4">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        @if ($review->guest_image)
                                            <img src="{{ $review->guest_image }}" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;" alt="">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-3"
                                                 style="width:48px;height:48px;background:rgba(200,161,101,0.12);color:#C8A165;font-size:1.1rem;flex-shrink:0;">
                                                {{ substr($review->guest_name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <h6 class="mb-0 fw-semibold text-truncate">{{ $review->guest_name }}</h6>
                                            <small class="text-muted">{{ $review->contextLabel() }}</small>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        @for ($i = 0; $i < 5; $i++)
                                            <i class="fa{{ $i < $review->rating ? 's' : 'r' }} fa-star text-warning" style="font-size:0.85rem"></i>
                                        @endfor
                                    </div>
                                    <p class="mb-0 text-muted flex-grow-1" style="font-size:0.95rem; line-height:1.6;">{{ $review->text }}</p>
                                    @if ($review->created_at)
                                        <small class="text-muted mt-3 pt-3 border-top" style="font-size:0.75rem;">
                                            <i class="far fa-clock me-1"></i>{{ $review->created_at->diffForHumans() }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Submit Review Form --}}
    <section id="submit-review" class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2">Share Your Experience</h2>
                        <p class="text-muted">Help others discover what makes Brickspoint special.</p>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <form method="POST" action="{{ route('website.testimonials.store') }}">
                                @csrf

                                <div style="position: absolute; left: -9999px;" aria-hidden="true">
                                    <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Your Name <span class="text-danger">*</span></label>
                                        <input type="text" name="guest_name" class="form-control form-control-lg @error('guest_name') is-invalid @enderror" required
                                            value="{{ old('guest_name') }}" placeholder="Enter your full name">
                                        @error('guest_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" placeholder="For a confirmation of your review">
                                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row g-4 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Review Type <span class="text-danger">*</span></label>
                                        <select name="type" id="review_type" class="form-select form-control-lg" required>
                                            <option value="stay" {{ old('type', $type) === 'stay' ? 'selected' : '' }}>Stay</option>
                                            <option value="restaurant" {{ old('type', $type) === 'restaurant' ? 'selected' : '' }}>Restaurant / Dining</option>
                                            <option value="event" {{ old('type', $type) === 'event' ? 'selected' : '' }}>Event</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <div id="stay_context_field">
                                            <label class="form-label fw-semibold">Stay Type <span class="text-muted fw-normal">(Optional)</span></label>
                                            <select name="stay_type" class="form-select form-control-lg">
                                                <option value="">Select stay type</option>
                                                <option value="Business" @selected(old('stay_type') == 'Business')>Business</option>
                                                <option value="Leisure" @selected(old('stay_type') == 'Leisure')>Leisure</option>
                                                <option value="Couple" @selected(old('stay_type') == 'Couple')>Couple</option>
                                                <option value="Family" @selected(old('stay_type') == 'Family')>Family</option>
                                                <option value="Solo" @selected(old('stay_type') == 'Solo')>Solo</option>
                                            </select>
                                        </div>
                                        <div class="d-none" id="restaurant_context_field">
                                            <label class="form-label fw-semibold">Dining Venue <span class="text-muted fw-normal">(Optional)</span></label>
                                            <input type="text" name="dining_venue" class="form-control form-control-lg"
                                                value="{{ old('dining_venue') }}" placeholder="e.g. Taste Restaurant, Pool Bar">
                                        </div>
                                        <div class="d-none" id="event_context_field">
                                            <label class="form-label fw-semibold">Event Name <span class="text-muted fw-normal">(Optional)</span></label>
                                            <input type="text" name="event_name" class="form-control form-control-lg"
                                                value="{{ old('event_name') }}" placeholder="e.g. New Year Gala, Wedding">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
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
                                    <label class="form-label fw-semibold">Your Review <span class="text-danger">*</span></label>
                                    <textarea name="text" rows="5" class="form-control form-control-lg @error('text') is-invalid @enderror" required
                                        placeholder="Share your experience...">{{ old('text') }}</textarea>
                                    @error('text') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Review
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted small">
                            <i class="fas fa-shield-alt me-1"></i>
                            Your review will be moderated before being published.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

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
