@extends('website::layouts.master')

@section('title', 'Welcome to ' . ($currentProperty?->name ?? 'Brickspoint Boutique Aparthotel'))

@section('content')
    <!-- Scroll Progress Bar -->
    <div id="scroll-progress" role="progressbar" aria-label="Page scroll progress"></div>

    <!-- Hero Section with Video Background -->
    @php
        $prop = $currentProperty;
        $propName = $prop?->name ?? 'Brickspoint Boutique Aparthotel';
        $propLocation = $prop?->city ?? 'Abuja';
        $heroTagline = $settings['hero_tagline'] ?? ($prop ? ($prop->name . ' — Luxury Meets Comfort') : 'Experience Unmatched Luxury');
        $heroSubtext = $settings['hero_subtext'] ?? ($prop
            ? ('Discover ' . $prop->name . ' in the heart of ' . $propLocation . '. Premium short and long stays.')
            : 'Discover our exquisite accommodations across prime locations in Abuja.');
        $heroCtaText = $prop ? 'Explore ' . $prop->name . ' Rooms' : 'Explore Rooms';
    @endphp
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
                                alt="{{ $propName }} Logo" class="mb-4 hotel-logo">
                            <h4 class="display-3 fw-light mb-4 text-white"
                                style="text-transform: uppercase;">{{ $heroTagline }}</h4>
                            <p class="lead mb-5">{{ $heroSubtext }}</p>
                            <div
                                class="d-flex justify-content-center gap-3 mb-5">
                                <a href="{{ route('website.rooms.index') }}" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="fas fa-bed me-2"></i>{{ $heroCtaText }}
                                </a>
                                <a href="{{ route('website.book') }}" class="btn btn-outline-light btn-lg px-5 py-3">
                                    <i class="fas fa-calendar-check me-2"></i>Book Direct
                                </a>
                                {{-- <a href="{{ url('https://guest.reservations.ng/BRICKSPOINTBOUTIQUEAPARTHOTELAS0/step1') }}" class="btn btn-outline-light btn-lg px-5 py-3">
                                    <i class="fas fa-calendar-check me-2"></i>Book Direct
                                </a> --}}
                            </div>

                            <!-- Quick Booking Form - Moved below CTA buttons -->
                            <div class="quick-booking-form bg-white p-4 rounded shadow mx-auto mt-4"
                                style="max-width: 900px;">
                                <form action="{{ route('website.rooms.index') }}" method="GET"
                                    class="shadow-lg p-4 bg-white rounded rounded-3 position-relative z-index-1 mt-n5 mx-auto"
                                    style="max-width: 1000px;">
                                    {{-- <form action="{{ url('https://guest.reservations.ng/BRICKSPOINTBOUTIQUEAPARTHOTELAS0/step1') }}" method="GET"
                                    class="shadow-lg p-4 bg-white rounded rounded-3 position-relative z-index-1 mt-n5 mx-auto"
                                    style="max-width: 1000px;"> --}}
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-uppercase small text-muted">Check
                                                In</label>
                                            <input type="date" name="check_in" class="form-control bg-light border-0"
                                                required min="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-uppercase small text-muted">Check
                                                Out</label>
                                            <input type="date" name="check_out" class="form-control bg-light border-0"
                                                required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
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
                                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                                <i class="fas fa-search me-1"></i> Find Rooms
                                            </button>
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
                            <h1 class="display-3 fw-bold mb-4 text-white">Premium Amenities</h1>
                            <p class="lead mb-5">Enjoy world-class services and facilities</p>
                            <div class="d-flex justify-content-center gap-3 mb-5">
                                <a href="{{ route('website.amenities') }}"
                                    class="btn btn-outline-light btn-lg px-5 py-3">Explore Amenities</a>
                                <a href="#dining" class="btn btn-primary btn-lg px-5 py-3">Discover Dining</a>
                            </div>

                            <!-- Quick Booking Form for second slide -->
                            <div class="quick-booking-form bg-white p-4 rounded shadow mx-auto mt-4"
                                style="max-width: 900px;">
                                <form action="{{ route('website.book') }}" method="GET" class="row g-3 align-items-end">
                                    {{-- <form action="{{ url('https://guest.reservations.ng/BRICKSPOINTBOUTIQUEAPARTHOTELAS0/step1') }}" method="GET" class="row g-3 align-items-end"> --}}
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
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-1"></i> Find Rooms
                                        </button>
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
                    <i class="fas fa-chevron-down fa-2x text-primary"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="py-5" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);">
        <div class="container">
            <div class="row g-4 text-center">
                @php
                    $statProperties = $allProperties->count();
                    $statRooms = $featuredRooms->count();
                    $statReviews = $testimonials->count();
                    $statYears = 10;
                @endphp
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-number h2 fw-bold mb-1 text-white" data-count="{{ $statProperties }}">0</div>
                        <div class="stat-label small text-uppercase" style="color: rgba(255,255,255,0.6); letter-spacing: 1px;">Properties</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="stat-number h2 fw-bold mb-1 text-white" data-count="{{ $statRooms }}">0</div>
                        <div class="stat-label small text-uppercase" style="color: rgba(255,255,255,0.6); letter-spacing: 1px;">Signature Rooms</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number h2 fw-bold mb-1 text-white" data-count="{{ $statReviews }}">0</div>
                        <div class="stat-label small text-uppercase" style="color: rgba(255,255,255,0.6); letter-spacing: 1px;">Guest Reviews</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-item">
                        <div class="stat-icon mb-2">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-number h2 fw-bold mb-1 text-white" data-count="{{ $statYears }}">0</div>
                        <div class="stat-label small text-uppercase" style="color: rgba(255,255,255,0.6); letter-spacing: 1px;">Years of Excellence</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Properties Section (main domain only) -->
    @if (!$currentProperty && $allProperties->count() > 1)
        <section id="our-properties" class="py-5 py-lg-7">
            <div class="container">
                <div class="section-header text-center mb-5 reveal">
                    <h2 class="display-5 fw-bold mb-3" style="text-transform: uppercase;">Our Locations</h2>
                    <div class="section-accent"></div>
                    <p class="text-muted mx-auto" style="max-width: 700px;">Choose from our premium properties across Abuja. Each location offers a unique experience with the same commitment to luxury and comfort.</p>
                </div>

                <div class="row g-4 justify-content-center">
                    @foreach ($allProperties as $property)
                        @php
                            $roomCount = \App\Models\RoomType::where('property_id', $property->id)->where('is_active', true)->count();
                            $propertyUrl = $property->domain ? 'https://' . $property->domain . '.' . request()->getHost() : route('website.rooms.index', ['property_id' => $property->id]);
                        @endphp
                        <div class="col-lg-5 col-md-6">
                            <div class="property-card card border-0 shadow-sm h-100 overflow-hidden">
                                <div class="property-card-body p-4 p-lg-5 text-center">
                                    <div class="property-icon mb-4">
                                        <i class="fas fa-map-marker-alt fa-3x" style="color: #C8A165;"></i>
                                    </div>
                                    <h3 class="h4 fw-bold mb-2">{{ $property->name }}</h3>
                                    <p class="text-muted mb-3">
                                        <i class="fas fa-location-dot me-1" style="color: #C8A165;"></i>
                                        {{ $property->address }}, {{ $property->city }}
                                    </p>
                                    <div class="d-flex justify-content-center gap-3 mb-4">
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            <i class="fas fa-bed me-1" style="color: #C8A165;"></i> {{ $roomCount }} Room Types
                                        </span>
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            <i class="fas fa-building me-1" style="color: #C8A165;"></i> {{ $property->is_headquarters ? 'Headquarters' : 'Satellite' }}
                                        </span>
                                    </div>
                                    @if ($property->contact_phone)
                                        <p class="small text-muted mb-4">
                                            <i class="fas fa-phone me-1" style="color: #C8A165;"></i> {{ $property->contact_phone }}
                                        </p>
                                    @endif
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ $propertyUrl }}" class="btn btn-primary px-4">
                                            <i class="fas fa-bed me-2"></i>View Rooms
                                        </a>
                                        <a href="{{ route('website.contact') }}?property={{ $property->slug }}" class="btn btn-outline-primary px-4">
                                            <i class="fas fa-envelope me-2"></i>Contact
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Featured Rooms Section -->
    <section id="featured-rooms" class="py-5 py-lg-7 @if (!$currentProperty && $allProperties->count() > 1) bg-light @else bg-light @endif">
        <div class="container">
            <div class="section-header text-center mb-5 reveal">
                <h2 class="display-5 fw-bold mb-3" style="text-transform: uppercase;">
                    @if ($currentProperty)
                        {{ $currentProperty->name }} — Signature Rooms
                    @else
                        Our Signature Rooms & Suites
                    @endif
                </h2>
                <div class="section-accent"></div>
                <p class="text-muted mx-auto" style="max-width: 700px;">Each of our accommodations is designed to provide
                    the ultimate comfort and luxury experience.</p>
            </div>

            <div class="row g-4">
                @foreach ($featuredRooms as $roomType)
                    @php
                        $roomProperty = $allProperties->firstWhere('id', $roomType->property_id);
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="room-card card border-0 shadow-sm h-100 overflow-hidden">
                            <div class="room-img-container position-relative overflow-hidden">
                                <img src="{{ $roomType->image_url ?? 'https://via.placeholder.com/400x300' }}"
                                    class="card-img-top room-image" alt="{{ $roomType->name }}">
                                @if (!$currentProperty && $roomProperty)
                                    <span class="position-absolute top-0 start-0 m-2 badge" style="background: rgba(200, 161, 101, 0.9); backdrop-filter: blur(4px);">
                                        <i class="fas fa-building me-1"></i>{{ $roomProperty->name }}
                                    </span>
                                @endif
                                <div class="price-tag position-absolute btn-primary text-white px-3 py-2">
                                    ₦{{ number_format($roomType->price, 2) }} <small>/ night</small>
                                </div>
                                <span class="position-absolute bottom-0 start-0 m-2 badge bg-info">
                                    <i class="fas fa-door-open me-1"></i>{{ $roomType->units_count }}
                                    {{ Str::plural('Room', $roomType->units_count) }}
                                </span>
                                <div class="room-overlay d-flex align-items-center justify-content-center">
                                    <a href="{{ route('website.rooms.show', $roomType->slug ?? $roomType->id) }}"
                                        class="btn btn-outline-light btn-lg">View Details</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="h5 card-title">{{ $roomType->name }}</h3>
                                <p class="card-text text-muted">{{ Str::limit($roomType->description, 100) }}</p>
                                <div class="room-features d-flex flex-wrap gap-2 mb-3">
                                    @foreach ($roomType->amenities->take(3) as $amenity)
                                        <span class="badge bg-light text-dark border">
                                            <i
                                                class="{{ $amenity->icon ?? 'fas fa-check-circle' }} text-primary me-1"></i>
                                            {{ $amenity->name }}
                                        </span>
                                    @endforeach

                                    @if ($roomType->amenities->count() > 3)
                                        <span class="badge bg-light text-muted border">
                                            +{{ $roomType->amenities->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="{{ route('website.rooms.show', $roomType->slug ?? $roomType->id) }}"
                                    class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Select Room
                                </a>
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
                <div class="col-lg-6 mb-5 mb-lg-0 reveal-left">
                    <div class="pe-lg-5">
                        <h2 class="display-5 fw-bold mb-4" style="font-family: FuturaLight;">Why Choose Our Hotel</h2>
                        <div class="section-accent-start"></div>
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
                                    <h3 class="h5 mb-2">Taste Restaurant</h3>
                                    <p class="mb-0 text-muted">Award-winning restaurants offering world-class cuisine.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 reveal-right">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-lg">
                        <img src="{{ !empty($settings['hotel_feature_image'])
                            ? asset($settings['hotel_feature_image'])
                            : asset('images/default-hotel.jpg') }}"
                            alt="Hotel Feature" class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dining Section -->
    <section id="dining" class="py-5 py-lg-7 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5 reveal">
                <h2 class="display-5 fw-bold mb-3" style="font-family: FuturaLight; text-transform: uppercase;">Exquisite
                    Dining Experiences</h2>
                <div class="section-accent"></div>
                <p class="text-muted mx-auto" style="max-width: 700px;">Indulge in culinary delights at our award-winning
                    restaurants.</p>
            </div>

            <div class="row g-4">
                @foreach ($dining as $option)
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 overflow-hidden dining-card">
                            <img src="{{ $option->image_url }}" class="card-img-top dining-image"
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
                <div class="col-lg-6 order-lg-2 ps-lg-5 reveal-right">
                    <h2 class="display-5 fw-bold mb-4" style="font-family: FuturaLight;">Rejuvenate at Our Spa</h2>
                    <div class="section-accent-start"></div>
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
                <div class="col-lg-6 order-lg-1 mb-4 mb-lg-0 reveal-left">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-lg">
                        <img src="{{ asset('images/spa.jpg') }}" alt="Spa"
                            class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login / Signup Section -->
    <section id="auth-section" class="py-5 py-lg-7" style="background: linear-gradient(135deg, #f8f6f1 0%, #efece4 100%);">
        <div class="container">
            <div class="section-header text-center mb-5 reveal">
                <h2 class="display-5 fw-bold mb-3" style="text-transform: uppercase; color: #1a1a1a;">Guest Access</h2>
                <div class="section-accent"></div>
                <p class="mx-auto" style="max-width: 650px; color: #555; font-size: 1.1rem;">Sign in to manage your bookings, or create a new account to get started.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4" style="border-radius: 12px 12px 0 0;">
                            <ul class="nav nav-tabs border-0 justify-content-center gap-2 auth-tabs" id="authTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-semibold px-4" id="login-tab" data-bs-toggle="tab"
                                        data-bs-target="#login" type="button" role="tab" aria-selected="true">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold px-4" id="register-tab" data-bs-toggle="tab"
                                        data-bs-target="#register" type="button" role="tab" aria-selected="false">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="authTabContent">
                                {{-- Login Tab --}}
                                <div class="tab-pane fade show active" id="login" role="tabpanel">
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" style="color: #333;">Email Address</label>
                                            <input type="email" name="email" class="form-control" required
                                                placeholder="your@email.com" value="{{ old('email') }}"
                                                style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold" style="color: #333;">Password</label>
                                            <input type="password" name="password" class="form-control" required
                                                placeholder="Enter your password"
                                                style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="form-check">
                                                <input type="checkbox" name="remember" class="form-check-input" id="remember"
                                                    style="border-color: #C8A165;">
                                                <label class="form-check-label" for="remember" style="color: #555;">Remember me</label>
                                            </div>
                                            <a href="{{ route('password.request') }}" class="text-decoration-none small fw-semibold"
                                                style="color: #C8A165;">Forgot password?</a>
                                        </div>
                                        <button type="submit" class="btn w-100 py-2 fw-bold border-0"
                                            style="background: linear-gradient(135deg, #C8A165, #b8924f); color: #fff; border-radius: 8px;">
                                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                        </button>
                                    </form>
                                </div>

                                {{-- Register Tab --}}
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <form method="POST" action="{{ route('register') }}">
                                        @csrf

                                        <div style="position: absolute; left: -9999px;" aria-hidden="true">
                                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                                        </div>
                                        <input type="hidden" name="register_time" value="{{ time() }}">
                                        
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" style="color: #333;">Full Name</label>
                                                <input type="text" name="name" class="form-control" required
                                                    placeholder="John Doe" value="{{ old('name') }}"
                                                    style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" style="color: #333;">Email Address</label>
                                                <input type="email" name="email" class="form-control" required
                                                    placeholder="your@email.com" value="{{ old('email') }}"
                                                    style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" style="color: #333;">Phone Number</label>
                                                <input type="tel" name="contact_number" class="form-control phone-input" required
                                                    placeholder="+234 800 000 0000" value="{{ old('contact_number') }}"
                                                    style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold" style="color: #333;">Password</label>
                                                <input type="password" name="password" class="form-control" required
                                                    minlength="8" placeholder="Min 8 characters"
                                                    style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold" style="color: #333;">Confirm Password</label>
                                                <input type="password" name="password_confirmation" class="form-control" required
                                                    placeholder="Repeat password"
                                                    style="border-radius: 8px; border: 1px solid #e0dcd3; padding: 0.65rem 1rem;">
                                            </div>
                                        </div>
                                        <div class="form-check mt-3">
                                            <input type="checkbox" name="terms" class="form-check-input" id="terms" required
                                                style="border-color: #C8A165;">
                                            <label class="form-check-label" for="terms" style="color: #555;">
                                                I agree to the <a href="#" style="color: #C8A165; font-weight: 600;">Terms of Service</a> and
                                                <a href="#" style="color: #C8A165; font-weight: 600;">Privacy Policy</a>
                                            </label>
                                        </div>
                                        <button type="submit" class="btn w-100 py-2 fw-bold mt-4 border-0"
                                            style="background: linear-gradient(135deg, #C8A165, #b8924f); color: #fff; border-radius: 8px;">
                                            <i class="fas fa-user-plus me-2"></i>Create Account
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-5 py-lg-7 bg-dark text-white">
        <div class="container">
            <div class="section-header text-center mb-5 reveal">
                <h2 class="display-5 fw-bold mb-3">What Our Guests Say</h2>
                <div class="section-accent"></div>
                <p class="text-light mx-auto" style="max-width: 700px; opacity: 0.8;">Don't just take our word for it &mdash; hear from our satisfied guests.</p>
            </div>

            @if ($testimonials->count() > 0)
                <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-indicators">
                        @foreach ($testimonials as $index => $testimonial)
                            <button type="button" data-bs-target="#testimonialsCarousel"
                                data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"
                                aria-label="Testimonial {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach ($testimonials as $index => $testimonial)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div class="row justify-content-center">
                                    <div class="col-lg-8 text-center px-4">
                                        <div class="testimonial-card py-4 px-3">
                                            <div class="rating mb-3 text-warning">
                                                @for ($i = 0; $i < 5; $i++)
                                                    <i class="fas fa-star{{ $i < $testimonial->rating ? '' : '-empty' }}"></i>
                                                @endfor
                                            </div>
                                            <p class="lead mb-4 px-lg-4" style="font-style: italic; line-height: 1.8;">&ldquo;{{ $testimonial->comment }}&rdquo;</p>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <img src="{{ $testimonial->guest_image }}"
                                                    class="rounded-circle me-3" width="56" height="56"
                                                    alt="{{ $testimonial->guest_name }}"
                                                    style="object-fit: cover; border: 2px solid rgba(200,161,101,0.3); padding: 2px;">
                                                <div class="text-start">
                                                    <h5 class="mb-0 text-white">{{ $testimonial->guest_name }}</h5>
                                                    <small style="color: rgba(255,255,255,0.5);">{{ $testimonial->guest_type }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-light" style="opacity: 0.6;">No testimonials available yet.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5 py-lg-7 cta-section text-white">
        <div class="container text-center">
            <div class="reveal">
                <h2 class="display-5 fw-bold mb-4">Ready for an Unforgettable Experience?</h2>
                <div class="section-accent"></div>
                <p class="lead mb-5 mx-auto" style="max-width: 700px;">Book your stay today and discover the perfect blend of
                    luxury, comfort, and exceptional service.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('website.rooms.index') }}" class="btn btn-light btn-lg px-5">
                        <i class="fas fa-bed me-2"></i>Browse Rooms
                    </a>
                    <a href="{{ route('website.contact') }}" class="btn btn-outline-light btn-lg px-5">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Back to Top -->
    <button id="back-to-top" aria-label="Back to top" title="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    @push('styles')
        <style>
            /* ===== SCROLL REVEAL ===== */
            .reveal {
                opacity: 0;
                transform: translateY(40px);
                transition: opacity 0.7s ease, transform 0.7s ease;
            }
            .reveal.visible {
                opacity: 1;
                transform: translateY(0);
            }
            .reveal-left {
                opacity: 0;
                transform: translateX(-40px);
                transition: opacity 0.7s ease, transform 0.7s ease;
            }
            .reveal-left.visible {
                opacity: 1;
                transform: translateX(0);
            }
            .reveal-right {
                opacity: 0;
                transform: translateX(40px);
                transition: opacity 0.7s ease, transform 0.7s ease;
            }
            .reveal-right.visible {
                opacity: 1;
                transform: translateX(0);
            }

            /* ===== SECTION ACCENT ===== */
            .section-accent {
                width: 60px;
                height: 3px;
                background: #C8A165;
                margin: 0 auto 1rem;
                border-radius: 2px;
            }
            .section-accent-start {
                width: 50px;
                height: 3px;
                background: #C8A165;
                margin: 0.75rem 0 1.5rem;
                border-radius: 2px;
            }

            /* ===== HERO SECTION ===== */
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
                background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.6) 100%);
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

            .hero-content h1,
            .hero-content .display-3,
            .hero-content h4 {
                text-shadow: 0 2px 20px rgba(0,0,0,0.3);
            }

            .quick-booking-form {
                z-index: 10;
            }

            .scroll-down-indicator {
                position: absolute;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                color: rgba(255,255,255,0.7);
                font-size: 24px;
                animation: scrollBounce 2.5s ease-in-out infinite;
                z-index: 5;
                cursor: pointer;
                transition: color 0.3s ease;
            }
            .scroll-down-indicator:hover {
                color: #C8A165;
            }

            @keyframes scrollBounce {
                0%, 100% { transform: translateX(-50%) translateY(0); opacity: 0.7; }
                50% { transform: translateX(-50%) translateY(10px); opacity: 1; }
            }

            /* ===== ROOM CARDS ===== */
            .room-card {
                transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                border-radius: 12px !important;
                overflow: hidden;
            }

            .room-card:hover {
                transform: translateY(-12px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            }

            .room-img-container {
                height: 220px;
                overflow: hidden;
                position: relative;
            }

            .room-image {
                transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                height: 100%;
                width: 100%;
                object-fit: cover;
            }

            .room-card:hover .room-image {
                transform: scale(1.12);
            }

            .price-tag {
                top: 16px;
                right: 16px;
                border-radius: 8px;
                font-weight: 700;
                font-size: 0.9rem;
                padding: 0.4rem 1rem !important;
                backdrop-filter: blur(4px);
                background: rgba(200, 161, 101, 0.92) !important;
                border: 1px solid rgba(255,255,255,0.15);
            }

            .room-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.3) 100%);
                opacity: 0;
                transition: opacity 0.35s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .room-card:hover .room-overlay {
                opacity: 1;
            }

            .room-overlay .btn {
                transform: translateY(10px);
                transition: transform 0.35s ease, opacity 0.35s ease;
                opacity: 0;
            }

            .room-card:hover .room-overlay .btn {
                transform: translateY(0);
                opacity: 1;
            }

            .room-card .card-body {
                padding: 1.25rem;
            }

            .room-card .card-title {
                font-weight: 600;
            }

            .room-card .card-footer {
                padding: 0.75rem 1.25rem;
            }

            .room-card .card-footer .btn {
                border-radius: 8px;
                padding: 0.6rem 1rem;
                font-weight: 600;
                background: #C8A165;
                border: 1px solid #C8A165;
                color: #fff;
                transition: all 0.3s ease;
            }

                .room-card .card-footer .btn:hover {
                    background: #b8924f;
                    border-color: #b8924f;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(200, 161, 101, 0.4);
                }

                .property-card-body { padding: 1.5rem !important; }
                .property-icon { width: 60px; height: 60px; }
                .property-icon i { font-size: 1.75rem !important; }
                .property-card .h4 { font-size: 1.1rem; }
                .property-card .badge { font-size: 0.75rem; padding: 0.3rem 0.6rem !important; }

            /* ===== PROPERTY CARDS ===== */
            .property-card {
                transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                border-radius: 16px !important;
                overflow: hidden;
                border: 1px solid rgba(200, 161, 101, 0.1) !important;
            }

            .property-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
                border-color: rgba(200, 161, 101, 0.3) !important;
            }

            .property-card-body {
                position: relative;
            }

            .property-card-body::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #C8A165, #d4b07a, #C8A165);
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .property-card:hover .property-card-body::before {
                opacity: 1;
            }

            .property-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto;
                background: rgba(200, 161, 101, 0.08);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.3s ease, background 0.3s ease;
            }

            .property-card:hover .property-icon {
                transform: scale(1.1);
                background: rgba(200, 161, 101, 0.15);
            }

            /* ===== DINING CARDS ===== */
            .dining-card {
                transition: all 0.3s ease;
                border-radius: 12px !important;
                overflow: hidden;
            }

            .dining-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
            }

            .dining-image {
                height: 200px;
                object-fit: cover;
                transition: transform 0.5s ease;
            }

            .dining-card:hover .dining-image {
                transform: scale(1.08);
            }

            .dining-card .card-body {
                padding: 1.25rem;
            }

            .dining-hours {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: rgba(200, 161, 101, 0.1);
                padding: 0.4rem 0.85rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .dining-card .card-footer .btn {
                border-radius: 8px;
                font-weight: 600;
                border: 1px solid #C8A165;
                color: #C8A165;
                transition: all 0.3s ease;
            }

            .dining-card .card-footer .btn:hover {
                background: #C8A165;
                color: #fff;
            }

            /* ===== FEATURES SECTION ===== */
            .feature-icon {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .feature-item:hover .feature-icon {
                transform: scale(1.1);
                box-shadow: 0 4px 16px rgba(200, 161, 101, 0.25);
            }

            .feature-item p {
                line-height: 1.6;
            }

            /* ===== SPA SECTION ===== */
            .spa-features .d-flex {
                transition: transform 0.3s ease;
                padding: 0.75rem;
                border-radius: 10px;
            }

            .spa-features .d-flex:hover {
                transform: translateX(6px);
                background: rgba(200, 161, 101, 0.04);
            }

            /* ===== AUTH SECTION ===== */
            .auth-tabs .nav-link {
                color: #666 !important;
                background: transparent !important;
                border: none !important;
                padding: 0.6rem 1.2rem !important;
                transition: all 0.2s ease;
                position: relative;
            }

            .auth-tabs .nav-link::after {
                content: '';
                position: absolute;
                bottom: -1px;
                left: 1.2rem;
                right: 1.2rem;
                height: 3px;
                background: #C8A165;
                transform: scaleX(0);
                transition: transform 0.2s ease;
            }

            .auth-tabs .nav-link:hover {
                color: #333 !important;
            }

            .auth-tabs .nav-link.active {
                color: #C8A165 !important;
            }

            .auth-tabs .nav-link.active::after {
                transform: scaleX(1);
            }

            #auth-section .form-control:focus {
                border-color: #C8A165 !important;
                box-shadow: 0 0 0 3px rgba(200, 161, 101, 0.15) !important;
            }

            /* ===== TESTIMONIALS ===== */
            .testimonial-card {
                position: relative;
                border-radius: 16px;
                overflow: hidden;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                background: rgba(255,255,255,0.04) !important;
                border: 1px solid rgba(255,255,255,0.06);
            }

            .testimonial-card::before {
                content: '\201C';
                position: absolute;
                top: -10px;
                left: 16px;
                font-size: 5rem;
                color: rgba(200, 161, 101, 0.15);
                font-family: serif;
                line-height: 1;
            }

            .testimonial-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 12px 30px rgba(0,0,0,0.3);
                border-color: rgba(200, 161, 101, 0.2);
            }

            .testimonial-card p {
                font-style: italic;
                line-height: 1.7;
                position: relative;
                z-index: 1;
            }

            .testimonial-card .rating {
                font-size: 0.9rem;
                letter-spacing: 2px;
            }

            .testimonial-card img {
                border: 2px solid rgba(200, 161, 101, 0.3);
                padding: 2px;
                object-fit: cover;
            }

            /* ===== CTA SECTION ===== */
            .cta-section {
                background: linear-gradient(135deg, #C8A165 0%, #a8853d 100%) !important;
                position: relative;
                overflow: hidden;
            }

            .cta-section::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
                animation: ctaShimmer 8s ease-in-out infinite;
            }

            @keyframes ctaShimmer {
                0%, 100% { transform: translate(0, 0); }
                50% { transform: translate(10%, 10%); }
            }

            .cta-section .btn-light {
                border-radius: 8px;
                font-weight: 600;
                padding: 0.75rem 2rem;
                transition: all 0.3s ease;
            }

            .cta-section .btn-light:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            }

            .cta-section .btn-outline-light {
                border-radius: 8px;
                font-weight: 600;
                padding: 0.75rem 2rem;
                transition: all 0.3s ease;
            }

            .cta-section .btn-outline-light:hover {
                background: rgba(255,255,255,0.15);
                transform: translateY(-3px);
            }

            /* ===== GENERAL BUTTON THEME ===== */
            .btn-primary {
                background: #C8A165 !important;
                border-color: #C8A165 !important;
                color: #fff !important;
            }

            .btn-primary:hover {
                background: #b8924f !important;
                border-color: #b8924f !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(200, 161, 101, 0.35);
            }

            .btn-outline-primary {
                border-color: #C8A165 !important;
                color: #C8A165 !important;
            }

            .btn-outline-primary:hover {
                background: #C8A165 !important;
                color: #fff !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(200, 161, 101, 0.3);
            }

            .text-primary {
                color: #C8A165 !important;
            }

            .bg-primary {
                background-color: #C8A165 !important;
            }

            /* ===== STAR RATING ===== */
            .fa-star-empty {
                color: rgba(255,255,255,0.15);
            }

            /* ===== SECTION DIVIDER ===== */
            .section-divider {
                width: 100%;
                height: 1px;
                background: linear-gradient(to right, transparent, rgba(200, 161, 101, 0.2), transparent);
                margin: 0;
            }

            /* ===== RESPONSIVE - TABLET ===== */
            @media (max-width: 768px) {
                .hero-section {
                    height: auto;
                    min-height: 550px;
                }
                .hotel-logo { max-width: 180px; }
                .hero-content h1, .hero-content .display-3 { font-size: 1.875rem !important; }
                .hero-content .lead { font-size: 1rem; }
                .hero-content .d-flex.justify-content-center.gap-3 { flex-wrap: wrap; justify-content: center; gap: 0.75rem !important; }
                .hero-content .btn-lg { padding: 0.75rem 1.5rem !important; font-size: 0.9rem !important; min-width: 160px; }

                .quick-booking-form {
                    position: relative;
                    width: 100%;
                    margin-top: 20px;
                    padding: 1.25rem !important;
                    border-radius: 12px !important;
                    background: rgba(255,255,255,0.98) !important;
                    box-shadow: 0 4px 24px rgba(0,0,0,0.12) !important;
                }
                .quick-booking-form form { padding: 0.75rem !important; }
                .quick-booking-form .row { gap: 0.75rem; }
                .quick-booking-form .col-md-3 { flex: 0 0 100%; max-width: 100%; }
                .quick-booking-form .form-control,
                .quick-booking-form .form-select { padding: 0.625rem 0.875rem; font-size: 1rem !important; min-height: 46px; border-radius: 8px !important; }
                .quick-booking-form button { padding: 0.75rem !important; font-size: 0.95rem !important; min-height: 46px; border-radius: 8px !important; }

                .section-header h2, .display-5 { font-size: 1.75rem !important; }
                .section-header p { font-size: 0.95rem; }
                .py-lg-7 { padding-top: 3rem !important; padding-bottom: 3rem !important; }
                .feature-icon { width: 50px !important; height: 50px !important; }
                .feature-item h3 { font-size: 1rem; }
                .feature-item p { font-size: 0.875rem; }
                .spa-features .fa-2x { font-size: 1.25rem; }
                .auth-tabs .nav-link { padding: 0.5rem 0.75rem !important; font-size: 0.875rem; }
                .d-flex.gap-3.flex-wrap { flex-direction: column; align-items: center; }
                .d-flex.gap-3.flex-wrap .btn { width: 100%; max-width: 280px; }
            }

            /* ===== RESPONSIVE - MOBILE ===== */
            @media (max-width: 576px) {
                .hero-section { min-height: auto; padding-bottom: 2rem; }
                .hotel-logo { max-width: 140px; }
                .hero-content { padding-top: 1rem !important; padding-bottom: 1.5rem !important; }
                .hero-content h1, .hero-content .display-3 { font-size: 1.4rem !important; margin-bottom: 0.75rem !important; line-height: 1.3; }
                .hero-content .lead { font-size: 0.875rem; margin-bottom: 1.25rem !important; padding: 0 0.75rem; line-height: 1.5; }

                .hero-content .d-flex.justify-content-center.gap-3 { flex-direction: column; align-items: center; gap: 0.625rem !important; margin-bottom: 1.25rem !important; }
                .hero-content .btn-lg { padding: 0.875rem 1.5rem !important; font-size: 0.9rem !important; width: 100%; max-width: 280px; min-height: 48px; }

                .quick-booking-form { padding: 1rem !important; margin-top: 1rem !important; border-radius: 12px !important; background: rgba(255,255,255,0.98) !important; box-shadow: 0 4px 24px rgba(0,0,0,0.12) !important; }
                .quick-booking-form form { padding: 0.5rem !important; }
                .quick-booking-form .row { gap: 0.75rem; }
                .quick-booking-form .col-md-3 { flex: 0 0 100% !important; max-width: 100% !important; }
                .quick-booking-form .form-label { font-size: 0.7rem; margin-bottom: 0.35rem; display: block; }
                .quick-booking-form .form-control, .quick-booking-form .form-select { padding: 0.75rem 0.875rem; font-size: 1rem !important; border-radius: 8px !important; min-height: 48px; }
                .quick-booking-form button { padding: 0.875rem !important; font-size: 0.95rem !important; min-height: 48px; border-radius: 8px !important; }

                .carousel-control-prev, .carousel-control-next { width: 44px; opacity: 0.6; }
                .carousel-control-prev-icon, .carousel-control-next-icon { width: 1.5rem; height: 1.5rem; }

                .section-header h2, .display-5 { font-size: 1.4rem !important; }
                .section-header p, .lead { font-size: 0.875rem !important; }
                .py-5 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
                .mb-5 { margin-bottom: 1.5rem !important; }

                .room-card { margin-bottom: 1rem; }
                .room-img-container { height: 180px; }
                .room-card .card-body { padding: 1rem; }
                .room-card .h5 { font-size: 1rem; }
                .room-card .card-text { font-size: 0.85rem; }
                .price-tag { padding: 0.35rem 0.75rem !important; font-size: 0.8rem; }
                .room-features .badge { font-size: 0.7rem; padding: 0.35rem 0.5rem; }

                .dining-card .card-body { padding: 1rem; }
                .dining-image { height: 160px; }
                .dining-card .h5 { font-size: 1rem; }
                .dining-card .card-text { font-size: 0.85rem; }
                .dining-hours { font-size: 0.8rem; }

                .feature-icon { width: 45px !important; height: 45px !important; min-width: 45px; }
                .feature-icon i { font-size: 1rem !important; }
                .feature-item { margin-bottom: 1rem !important; }
                .feature-item h3 { font-size: 0.95rem; margin-bottom: 0.25rem !important; }
                .feature-item p { font-size: 0.8rem; }
                .pe-lg-5 { padding-right: 0 !important; }

                .spa-features .d-flex { margin-bottom: 1rem !important; }
                .spa-features .fa-2x { font-size: 1.1rem !important; }
                .spa-features h4 { font-size: 0.95rem; }
                .spa-features p { font-size: 0.8rem; }
                .col-lg-6.order-lg-2.ps-lg-5 { padding-left: 0 !important; }

                #auth-section .card { margin: 0 -0.5rem; }
                .auth-tabs { gap: 0.5rem !important; }
                .auth-tabs .nav-link { padding: 0.4rem 0.6rem !important; font-size: 0.8rem; }
                .auth-tabs .nav-link i { display: none; }
                #auth-section .card-body { padding: 1rem !important; }
                #auth-section .form-label { font-size: 0.85rem; }
                #auth-section .form-control { padding: 0.5rem 0.75rem !important; font-size: 0.875rem; }
                #auth-section .row.g-3 .col-md-6 { flex: 0 0 100%; max-width: 100%; }

                .testimonial-card { padding: 1rem !important; }
                .testimonial-card p { font-size: 0.875rem; }
                .testimonial-card h5 { font-size: 0.95rem; }
                .testimonial-card img { width: 40px !important; height: 40px !important; }

                .cta-section .display-5 { font-size: 1.3rem !important; }
                .cta-section .lead { font-size: 0.9rem !important; margin-bottom: 1.5rem !important; }
                .cta-section .d-flex.gap-3 { gap: 0.75rem !important; }
                .cta-section .btn-lg { padding: 0.6rem 1.5rem !important; font-size: 0.85rem !important; }

                .carousel-control-prev, .carousel-control-next { width: 10%; }
                .carousel-control-prev-icon, .carousel-control-next-icon { width: 1.5rem; height: 1.5rem; }
                .scroll-down-indicator { bottom: 15px; }
                .scroll-down-indicator .fa-2x { font-size: 1.25rem !important; }
                .container { padding-left: 1rem; padding-right: 1rem; }
                .g-4 { --bs-gutter-y: 1rem; --bs-gutter-x: 1rem; }
                .ratio.ratio-16x9 { margin-bottom: 1.5rem; }
                .text-center.mt-5 { margin-top: 1.5rem !important; }
                                .btn-outline-primary.btn-lg.px-5 { padding: 0.6rem 2rem !important; font-size: 0.9rem; }

                .stat-number { font-size: 1.75rem !important; }
                .stat-icon i { font-size: 1.25rem !important; }
                .stat-label { font-size: 0.65rem !important; }

                #testimonials .testimonial-card p { font-size: 0.95rem !important; }
                #testimonials .carousel-control-prev,
                #testimonials .carousel-control-next { display: none; }

                #back-to-top { bottom: 16px; right: 16px; width: 42px; height: 42px; font-size: 0.9rem; }
            }

            /* ===== SCROLL PROGRESS BAR ===== */
            #scroll-progress {
                position: fixed;
                top: 0;
                left: 0;
                width: 0%;
                height: 3px;
                background: linear-gradient(90deg, #C8A165, #d4b07a);
                z-index: 9999;
                transition: width 0.1s linear;
                box-shadow: 0 0 6px rgba(200, 161, 101, 0.5);
            }

            /* ===== BACK TO TOP ===== */
            #back-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: #C8A165;
                color: #fff;
                border: none;
                font-size: 1.1rem;
                cursor: pointer;
                opacity: 0;
                visibility: hidden;
                transform: translateY(12px);
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease, box-shadow 0.3s ease;
                z-index: 999;
                box-shadow: 0 4px 12px rgba(200, 161, 101, 0.35);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #back-to-top.visible {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            #back-to-top:hover {
                background: #b8924f;
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(200, 161, 101, 0.5);
            }

            /* ===== STATS SECTION ===== */
            .stat-item {
                padding: 1.5rem 0.5rem;
                transition: transform 0.3s ease;
            }

            .stat-item:hover {
                transform: translateY(-4px);
            }

            .stat-icon i {
                font-size: 1.75rem;
                color: #C8A165;
                opacity: 0.9;
            }

            .stat-number {
                font-size: 2.25rem;
                font-weight: 700;
            }

            .stat-label {
                font-size: 0.75rem;
                font-weight: 600;
            }

            /* ===== TESTIMONIALS CAROUSEL ===== */
            #testimonials .testimonial-card {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                transform: none !important;
            }

            #testimonials .testimonial-card p {
                font-size: 1.1rem;
            }

            #testimonials .carousel-indicators {
                bottom: -20px;
            }

            #testimonials .carousel-indicators button {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                border: none;
                margin: 0 5px;
                transition: all 0.3s ease;
            }

            #testimonials .carousel-indicators button.active {
                background: #C8A165;
                width: 28px;
                border-radius: 5px;
            }

            #testimonials .carousel-control-prev,
            #testimonials .carousel-control-next {
                width: 48px;
                height: 48px;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(200,161,101,0.15);
                border-radius: 50%;
                opacity: 0;
                transition: opacity 0.3s ease, background 0.3s ease;
            }

            #testimonials:hover .carousel-control-prev,
            #testimonials:hover .carousel-control-next {
                opacity: 1;
            }

            #testimonials .carousel-control-prev:hover,
            #testimonials .carousel-control-next:hover {
                background: rgba(200,161,101,0.3);
            }

            #testimonials .carousel-control-prev { left: -60px; }
            #testimonials .carousel-control-next { right: -60px; }

            /* ===== LAZY LOADING IMAGES ===== */
            img.lazy {
                opacity: 0;
                transition: opacity 0.5s ease;
            }

            img.lazy.loaded {
                opacity: 1;
            }

            /* ===== ROOM & DINING CARD VISIBLE STATE ===== */
            .room-card.visible,
            .dining-card.visible {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }

            /* ===== FOCUS & ACCESSIBILITY ===== */
            a:focus-visible,
            button:focus-visible,
            input:focus-visible,
            select:focus-visible,
            textarea:focus-visible {
                outline: 2px solid #C8A165;
                outline-offset: 2px;
                border-radius: 4px;
            }

            .carousel-item {
                transition: transform 0.7s ease, opacity 0.7s ease !important;
            }

            /* ===== RESPONSIVE - EXTRA SMALL ===== */
            @media (max-width: 400px) {
                .hero-section { min-height: 450px; }
                .hotel-logo { max-width: 120px; }
                .hero-content .display-3 { font-size: 1.25rem !important; }
                .hero-content .lead { font-size: 0.8rem; }
                .hero-content .btn-lg { padding: 0.6rem 1rem !important; font-size: 0.8rem !important; }
                .quick-booking-form { padding: 0.5rem !important; }
                .section-header h2, .display-5 { font-size: 1.2rem !important; }
                .room-img-container { height: 150px; }
                .price-tag { font-size: 0.75rem; padding: 0.25rem 0.5rem !important; }
                .auth-tabs .nav-link { font-size: 0.75rem; padding: 0.35rem 0.5rem !important; }
                .stat-number { font-size: 1.25rem !important; }
                .stat-icon i { font-size: 1rem !important; }
                .stat-label { font-size: 0.6rem !important; }
                .col-6.col-lg-3 { padding-left: 0.25rem; padding-right: 0.25rem; }
                #back-to-top { bottom: 12px; right: 12px; width: 38px; height: 38px; font-size: 0.85rem; }
                #testimonials .carousel-control-prev,
                #testimonials .carousel-control-next { display: none; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                /* ----- SMOOTH SCROLL FOR ANCHOR LINKS ----- */
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    });
                });

                /* ----- INTERSECTION OBSERVER FOR SCROLL REVEAL ----- */
                const revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                        }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

                document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .room-card, .dining-card').forEach(el => {
                    if (!el.classList.contains('reveal') && !el.classList.contains('reveal-left') && !el.classList.contains('reveal-right')) {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(30px)';
                        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    }
                    revealObserver.observe(el);
                });

                /* ----- VIDEO AUTOPLAY ----- */
                const video = document.querySelector('video');
                if (video) {
                    video.play().catch(() => {});
                }

                /* ----- SCROLL PROGRESS BAR ----- */
                const progressBar = document.getElementById('scroll-progress');
                if (progressBar) {
                    window.addEventListener('scroll', function() {
                        const scrollTop = window.scrollY;
                        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                        const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                        progressBar.style.width = progress + '%';
                    }, { passive: true });
                }

                /* ----- BACK TO TOP BUTTON ----- */
                const backToTop = document.getElementById('back-to-top');
                if (backToTop) {
                    window.addEventListener('scroll', function() {
                        if (window.scrollY > 500) {
                            backToTop.classList.add('visible');
                        } else {
                            backToTop.classList.remove('visible');
                        }
                    }, { passive: true });

                    backToTop.addEventListener('click', function() {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                }

                /* ----- STATS COUNTER ANIMATION ----- */
                const statsSection = document.getElementById('stats');
                if (statsSection) {
                    const statNumbers = statsSection.querySelectorAll('.stat-number');
                    const counterObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                statNumbers.forEach(counter => {
                                    const target = parseInt(counter.getAttribute('data-count'));
                                    if (isNaN(target)) return;
                                    const duration = 1500;
                                    const startTime = performance.now();

                                    function updateCounter(currentTime) {
                                        const elapsed = currentTime - startTime;
                                        const progress = Math.min(elapsed / duration, 1);
                                        const easeOut = 1 - Math.pow(1 - progress, 3);
                                        const current = Math.floor(easeOut * target);
                                        counter.textContent = current.toLocaleString();
                                        if (progress < 1) {
                                            requestAnimationFrame(updateCounter);
                                        } else {
                                            counter.textContent = target.toLocaleString();
                                        }
                                    }
                                    requestAnimationFrame(updateCounter);
                                });
                                counterObserver.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.5 });

                    counterObserver.observe(statsSection);
                }

                /* ----- HERO PARALLAX ----- */
                const heroSection = document.querySelector('.hero-section');
                if (heroSection) {
                    const heroContent = heroSection.querySelector('.hero-content');
                    const videoBg = heroSection.querySelector('.video-background');
                    let ticking = false;

                    window.addEventListener('scroll', function() {
                        if (!ticking) {
                            window.requestAnimationFrame(function() {
                                const scrollY = window.scrollY;
                                const heroBottom = heroSection.offsetTop + heroSection.offsetHeight;
                                if (scrollY <= heroBottom) {
                                    const offset = scrollY * 0.4;
                                    if (heroContent) {
                                        heroContent.style.transform = 'translateY(' + offset + 'px)';
                                        heroContent.style.transition = 'transform 0.1s linear';
                                    }
                                    if (videoBg) {
                                        videoBg.style.transform = 'translateY(' + (scrollY * 0.15) + 'px)';
                                    }
                                }
                                ticking = false;
                            });
                            ticking = true;
                        }
                    }, { passive: true });
                }

                /* ----- LAZY LOADING IMAGES WITH FADE-IN ----- */
                if ('IntersectionObserver' in window) {
                    const lazyImages = document.querySelectorAll('img[loading="lazy"], img.lazy-load');
                    if (lazyImages.length > 0) {
                        const imageObserver = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    const img = entry.target;
                                    img.classList.add('loaded');
                                    imageObserver.unobserve(img);
                                }
                            });
                        }, { rootMargin: '100px 0px' });

                        lazyImages.forEach(img => {
                            img.classList.add('lazy');
                            imageObserver.observe(img);
                        });
                    }
                }

                /* ----- ADD lazy-load CLASS TO ROOM IMAGES ----- */
                document.querySelectorAll('.room-image, .dining-image').forEach(img => {
                    if (!img.hasAttribute('loading')) {
                        img.setAttribute('loading', 'lazy');
                    }
                    img.classList.add('lazy');
                    img.classList.add('lazy-load');
                    if ('IntersectionObserver' in window) {
                        const imgObserver = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    entry.target.classList.add('loaded');
                                    imgObserver.unobserve(entry.target);
                                }
                            });
                        }, { rootMargin: '100px 0px' });
                        imgObserver.observe(img);
                    }
                });
            });
        </script>
    @endpush
@endsection
