@extends('website::layouts.master')

@section('title', 'Welcome to Our Luxury Hotel')

@section('content')
    <!-- Hero Section with Video Background -->
    <section class="hero-section position-relative overflow-hidden">
        <div id="heroCarousel" class="carousel slide h-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                <!-- Video Slide -->
                <div class="carousel-item active h-100">
                    <div class="video-background h-100">
                        <video autoplay loop muted playsinline class="w-100 h-100">
                            <source
                                src="{{ Storage::url($settings['hero_video'] ?? 'images/myvideo1.79ba4195a28673379baa.mp4') }}"
                                type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="video-overlay"></div>
                    </div>
                    <div class="container h-100 d-flex align-items-center position-relative z-index-1">
                        <div class="hero-content text-white text-center w-100 pt-5 pb-6">
                            <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}"
                                alt="Brickspoint Logo" class="mb-4 hotel-logo">
                            <h4 class="display-3 fw-light mb-4 animate__animated animate__fadeInDown"
                                style="text-transform: uppercase;">Experience Unmatched Luxury</h4>
                            <p class="lead mb-5 animate__animated animate__fadeInUp animate__delay-1s">Discover our
                                exquisite accommodations in the heart of Abuja</p>
                            <div
                                class="d-flex justify-content-center gap-3 animate__animated animate__fadeInUp animate__delay-2s mb-5">
                                <a href="{{ route('website.booking') }}" class="btn btn-primary btn-lg px-5 py-3">Book Your
                                    Stay</a>
                                <a href="#featured-rooms" class="btn btn-outline-light btn-lg px-5 py-3">Explore Rooms</a>
                            </div>

                            <!-- Quick Booking Form - Moved below CTA buttons -->
                            <div class="quick-booking-form bg-white p-4 rounded shadow mx-auto mt-4"
                                style="max-width: 900px;">
                                <form action="{{ route('website.rooms.index') }}" method="GET" class="shadow-lg p-4 bg-white rounded rounded-3 position-relative z-index-1 mt-n5 mx-auto" style="max-width: 1000px;">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold text-uppercase small text-muted">Check In</label>
            <input type="date" name="check_in" class="form-control bg-light border-0" required min="{{ date('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold text-uppercase small text-muted">Check Out</label>
            <input type="date" name="check_out" class="form-control bg-light border-0" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold text-uppercase small text-muted">Guests</label>
            <select name="adults" class="form-select bg-light border-0">
                <option value="1">1 Adult</option>
                <option value="2">2 Adults</option>
                <option value="3">3 Adults</option>
                <option value="4">4+ Adults</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Check Availability</button>
        </div>
    </div>
</form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Slide -->
                <div class="carousel-item h-100">
                    <div class="hero-slide h-100"
                        style="background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('{{ Storage::url('images/hotel-hero-2.png') }}') center/cover no-repeat;">
                    </div>
                    <div class="container h-100 d-flex align-items-center">
                        <div class="hero-content text-white text-center w-100">
                            <h1 class="display-3 fw-bold mb-4">Premium Amenities</h1>
                            <p class="lead mb-5">Enjoy world-class services and facilities</p>
                            <div class="d-flex justify-content-center gap-3 mb-5">
                                <a href="{{ route('website.amenities') }}"
                                    class="btn btn-outline-light btn-lg px-5 py-3">Explore Amenities</a>
                                <a href="#dining" class="btn btn-primary btn-lg px-5 py-3">Discover Dining</a>
                            </div>

                            <!-- Quick Booking Form for second slide -->
                            <div class="quick-booking-form bg-white p-4 rounded shadow mx-auto mt-4"
                                style="max-width: 900px;">
                                <form action="{{ route('website.booking') }}" method="GET"
                                    class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label for="check_in_2" class="form-label">Check-In</label>
                                        <input type="date" class="form-control" id="check_in_2" name="check_in"
                                            min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="check_out_2" class="form-label">Check-Out</label>
                                        <input type="date" class="form-control" id="check_out_2" name="check_out"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="guests_2" class="form-label">Guests</label>
                                        <select class="form-select" id="guests_2" name="guests">
                                            <option value="1">1 Guest</option>
                                            <option value="2" selected>2 Guests</option>
                                            <option value="3">3 Guests</option>
                                            <option value="4">4 Guests</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">Check Availability</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>

            <!-- Scroll Indicator -->
            <div class="scroll-down-indicator">
                <a href="#featured-rooms" class="">
                    <i class="fas fa-chevron-down fa-2x"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Rooms Section -->
    <section id="featured-rooms" class="py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="text-transform: uppercase;">Our Signature Rooms & Suites</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">Each of our accommodations is designed to provide
                    the ultimate comfort and luxury experience.</p>
            </div>

            <div class="row g-4">
                @foreach ($featuredRooms as $room)
                    <div class="col-md-6 col-lg-4">
                        <div class="room-card card border-0 shadow-sm h-100 overflow-hidden">
                            <div class="room-img-container position-relative overflow-hidden">
                                <img src="{{ Storage::url($room->image) }}" class="card-img-top room-image"
                                    alt="{{ $room->name }}">
                                <div class="price-tag position-absolute btn-primary text-white px-3 py-2">
                                    ₦{{ number_format($room->price, 2) }} <small>/ night</small>
                                </div>
                                <div class="room-overlay d-flex align-items-center justify-content-center">
                                    <a href="{{ route('website.rooms.show', $room->id) }}"
                                        class="btn btn-outline-light btn-lg">View Details</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="h5 card-title">{{ $room->name }}</h3>
                                <p class="card-text text-muted">{{ Str::limit($room->description, 100) }}</p>
                                <div class="room-features d-flex flex-wrap gap-2 mb-3">
                                    {{-- Fix: Wrap the array in collect() to use take(), and output the string directly --}}
                                    @foreach (collect($room->amenities ?? [])->take(3) as $amenity)
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-check-circle text-primary me-1"></i> {{ $amenity }}
                                        </span>
                                    @endforeach

                                    {{-- Optional: Show count of remaining amenities --}}
                                    @if (count($room->amenities ?? []) > 3)
                                        <span class="badge bg-light text-muted">+{{ count($room->amenities) - 3 }}
                                            more</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="{{ route('website.booking', ['room_id' => $room->id]) }}"
                                    class="btn btn-primary w-100">Book Now</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('website.rooms.index') }}" class="btn btn-outline-primary btn-lg px-5">View All
                    Rooms</a>
            </div>
        </div>
    </section>

    <!-- Hotel Features Section -->
    <section class="py-5 py-lg-7">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="pe-lg-5">
                        <h2 class="display-5 fw-bold mb-4" style="font-family: FuturaLight;">Why Choose Our Hotel</h2>
                        <p class="lead mb-4" style="font-family: FuturaLight;">We provide exceptional services to make
                            your stay unforgettable</p>

                        <div class="feature-list">
                            <div class="feature-item d-flex mb-4">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="fas fa-concierge-bell fa-lg"></i>
                                </div>
                                <div>
                                    <h3 class="h5 mb-2">24/7 Concierge</h3>
                                    <p class="mb-0 text-muted">Our dedicated staff is always available to assist with your
                                        needs.</p>
                                </div>
                            </div>

                            <div class="feature-item d-flex mb-4">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="fas fa-wifi fa-lg"></i>
                                </div>
                                <div>
                                    <h3 class="h5 mb-2">High-Speed WiFi</h3>
                                    <p class="mb-0 text-muted">Complimentary high-speed internet throughout the property.
                                    </p>
                                </div>
                            </div>

                            <div class="feature-item d-flex">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4"
                                    style="width: 60px; height: 60px;">
                                    <i class="fas fa-utensils fa-lg"></i>
                                </div>
                                <div>
                                    <h3 class="h5 mb-2">Gourmet Dining</h3>
                                    <p class="mb-0 text-muted">Award-winning restaurants offering world-class cuisine.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-lg">
                        <img src="{{ asset('images/hotel-feature.jpg') }}" alt="Hotel Feature"
                            class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dining Section -->
    <section id="dining" class="py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="font-family: FuturaLight; text-transform: uppercase;">Exquisite
                    Dining Experiences</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">Indulge in culinary delights at our award-winning
                    restaurants.</p>
            </div>

            <div class="row g-4">
                @foreach ($dining as $option)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 overflow-hidden dining-card">
                            <img src="{{ $option->image }}" class="card-img-top dining-image"
                                alt="{{ $option->name }}">
                            <div class="card-body">
                                <h3 class="h5 card-title">{{ $option->name }}</h3>
                                <p class="card-text text-muted">{{ Str::limit($option->description, 100) }}</p>
                                <div class="dining-hours text-primary">
                                    <i class="fas fa-clock me-2"></i> {{ $option->opening_hours }}
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="{{ $option->menu_link }}" class="btn btn-outline-primary w-100">View Menu</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Spa Section -->
    <section class="py-5 py-lg-7">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2 ps-lg-5">
                    <h2 class="display-5 fw-bold mb-4" style="font-family: FuturaLight;">Rejuvenate at Our Spa</h2>
                    <p class="lead mb-4" style="font-family: FuturaLight;">Our world-class spa offers a sanctuary of
                        relaxation and rejuvenation with treatments designed to restore balance to both body and mind.</p>

                    <div class="spa-features mb-4">
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-spa fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Signature Treatments</h4>
                                <p class="mb-0 text-muted">Unique therapies using locally sourced ingredients</p>
                            </div>
                        </div>

                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary">
                                <i class="fas fa-hot-tub fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Wellness Facilities</h4>
                                <p class="mb-0 text-muted">Sauna, steam room, and hydrotherapy pools</p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="me-3 text-primary">
                                <i class="fas fa-user-md fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="h5 mb-1">Expert Therapists</h4>
                                <p class="mb-0 text-muted">Highly trained professionals for personalized care</p>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="btn btn-primary btn-lg">Explore Spa Services</a>
                </div>
                <div class="col-lg-6 order-lg-1 mb-4 mb-lg-0">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-lg">
                        <img src="{{ asset('images/spa.jpg') }}" alt="Spa"
                            class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 py-lg-7 bg-dark text-white">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">What Our Guests Say</h2>
                <p class="text-light mx-auto" style="max-width: 700px; opacity: 0.8;">Don't just take our word for it -
                    hear from our satisfied guests.</p>
            </div>

            <div class="row g-4">
                @foreach ($testimonials as $testimonial)
                    <div class="col-md-4">
                        <div class="testimonial-card bg-gray-800 p-4 h-100 rounded">
                            <div class="rating mb-3 text-warning">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star{{ $i < $testimonial->rating ? '' : '-empty' }}"></i>
                                @endfor
                            </div>
                            <p class="mb-4">"{{ $testimonial->comment }}"</p>
                            <div class="d-flex align-items-center">
                                <img src="{{ $testimonial->guest_image }}" class="rounded-circle me-3" width="50"
                                    height="50" alt="{{ $testimonial->guest_name }}">
                                <div>
                                    <h5 class="mb-0">{{ $testimonial->guest_name }}</h5>
                                    <small class="text-muted">{{ $testimonial->guest_type }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5 py-lg-7 btn-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready for an Unforgettable Experience?</h2>
            <p class="lead mb-5 mx-auto" style="max-width: 700px;">Book your stay today and discover the perfect blend of
                luxury, comfort, and exceptional service.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('website.booking') }}" class="btn btn-light btn-lg px-5">Book Now</a>
                <a href="{{ route('website.contact') }}" class="btn btn-outline-light btn-lg px-5">Contact Us</a>
            </div>
        </div>
    </section>

    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            /* Hero Section */
            .hero-section {
                position: relative;
                height: 100vh;
                min-height: 800px;
            }

            .video-background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                z-index: -1;
            }

            .video-background video {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .video-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.4);
            }

            .hotel-logo {
                max-width: 300px;
                height: auto;
            }

            .hero-slide {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }

            .quick-booking-form {
                z-index: 10;
            }

            .scroll-down-indicator {
                position: absolute;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                color: rgb(23, 17, 1);
                font-size: 24px;
                animation: bounce 2s infinite;
                z-index: 5;
            }

            /* Room Cards */
            .room-card {
                transition: all 0.3s ease;
            }

            .room-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
            }

            .room-img-container {
                height: 220px;
                overflow: hidden;
            }

            .room-image {
                transition: transform 0.5s ease;
                height: 100%;
                object-fit: cover;
            }

            .room-card:hover .room-image {
                transform: scale(1.05);
            }

            .price-tag {
                top: 20px;
                right: 20px;
                border-radius: 4px;
                font-weight: bold;
            }

            .room-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .room-card:hover .room-overlay {
                opacity: 1;
            }

            /* Dining Cards */
            .dining-card {
                transition: all 0.3s ease;
            }

            .dining-card:hover {
                transform: translateY(-5px);
            }

            .dining-image {
                height: 200px;
                object-fit: cover;
            }

            /* Animations */
            @keyframes bounce {

                0%,
                20%,
                50%,
                80%,
                100% {
                    transform: translateY(0) translateX(-50%);
                }

                40% {
                    transform: translateY(-20px) translateX(-50%);
                }

                60% {
                    transform: translateY(-10px) translateX(-50%);
                }
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .hero-section {
                    height: auto;
                    min-height: 600px;
                }

                .hotel-logo {
                    max-width: 200px;
                }

                .hero-content h1 {
                    font-size: 2.5rem;
                }

                .quick-booking-form {
                    position: relative;
                    bottom: auto;
                    left: auto;
                    transform: none;
                    width: 100%;
                    margin-top: 20px;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // Initialize Bootstrap carousel
            document.addEventListener('DOMContentLoaded', function() {
                // Smooth scroll for navigation
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.querySelector(this.getAttribute('href')).scrollIntoView({
                            behavior: 'smooth'
                        });
                    });
                });

                // Animate elements on scroll
                function animateOnScroll() {
                    const elements = document.querySelectorAll('.animate__animated');

                    elements.forEach(element => {
                        const elementPosition = element.getBoundingClientRect().top;
                        const scrollPosition = window.innerHeight * 0.8;

                        if (elementPosition < scrollPosition) {
                            element.classList.add(element.dataset.animate);
                        }
                    });
                }

                window.addEventListener('scroll', animateOnScroll);
                animateOnScroll(); // Initialize

                // Force video to play on mobile devices
                const video = document.querySelector('video');
                if (video) {
                    video.play().catch(error => {
                        // Autoplay was prevented, show fallback
                        console.log('Video autoplay prevented:', error);
                    });
                }
            });
        </script>
    @endpush
@endsection
