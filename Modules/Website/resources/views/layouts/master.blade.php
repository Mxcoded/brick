<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ config('app.name', 'Brickspoint ApartHotel') }} - @yield('title')</title>

    <meta name="description" content="{{ $description ?? 'Experience unparalleled luxury at our premium apart-hotel' }}">
    <meta name="keywords" content="{{ $keywords ?? 'hotel, luxury, accommodation, vacation, resort, Abuja' }}">
    <meta name="author" content="{{ $author ?? config('app.name') }}">

    <!-- Favicon -->
    <link rel="icon" href=" {{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}"
        type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/brickspoint_logo.png') }}">

    <!-- Preconnect to CDNs -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.datatables.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.bunny.net">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.bunny.net/css?family=Montserrat:400,500,600,700|Playfair+Display:400,500,700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        /* Design System Tokens */
        :root {
            --color-white: #FFFFFF;
            --color-gold: #C8A165;
            --color-dark-gray: #333333;
            --color-soft-neutral: #F5F5F0;

            --font-primary: 'Proxima Nova', Arial, Helvetica, sans-serif;
        }

        /* Typography */
        @font-face {
            font-family: 'Proxima Nova';
            src: url("{{ asset('fonts/Proxima Nova Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--color-white);
            color: var(--color-dark-gray);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-primary);
            color: var(--color-dark-gray);
        }

        /* Component Overrides */
        .bg-dark {
            background-color: var(--color-dark-gray) !important;
        }

        .bg-light {
            background-color: var(--color-soft-neutral) !important;
        }

        .text-primary {
            color: var(--color-gold) !important;
        }

        .btn-primary {
            background-color: var(--color-gold);
            border-color: var(--color-gold);
            color: var(--color-dark-gray);
            /* Dark text for contrast on Gold */
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #b08d55;
            /* Darker Gold */
            border-color: #b08d55;
            color: var(--color-white);
        }

        .btn-outline-primary {
            color: var(--color-gold);
            border-color: var(--color-gold);
        }

        .btn-outline-primary:hover {
            background-color: var(--color-gold);
            border-color: var(--color-gold);
            color: var(--color-dark-gray);
        }

        .btn-outline-light:hover {
            color: var(--color-dark-gray);
        }

        /* Enhanced logo styling */
        .navbar-brand {
            padding: 0;
        }

        .navbar-brand img {
            height: 100px;
            /* Larger default size */
            width: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            padding: 0.5rem 1rem !important;
            transition: color 0.3s ease;
        }

        .nav-link.active {
            color: #d4a017 !important;
            /* Gold/Primary Color */
        }

        .active-link {
            color: black !important;
            font-weight: bold;
            background-color: rgba(255, 215, 0, 0.1);
            /* subtle gold highlight */
            border-left: 4px solid gold;
            /* optional accent */
        }

        /* Footer logo styling */
        .footer-logo {
            height: 70px;
            width: auto;
            margin-bottom: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .navbar-brand img {
                height: 50px;
            }

            .footer-logo {
                height: 60px;
            }
        }

        @media (max-width: 768px) {
            .navbar-brand img {
                height: 50px;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand img {
                height: 40px;
            }

            .footer-logo {
                height: 50px;
            }
        }

        .navbar {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        /* Dropdown styling for Our Hotels */
        .navbar .dropdown-menu-dark {
            background-color: #2d2d2d;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .navbar .dropdown-item {
            padding: 0.75rem 1rem;
            transition: background-color 0.2s ease;
        }

        .navbar .dropdown-item:hover {
            background-color: rgba(200, 161, 101, 0.15);
        }

        .navbar .dropdown-item small {
            font-size: 0.75rem;
        }

        .text-muted-footer {
            opacity: 0.8;
        }

        /* Ensure proper footer layout */
        .footer-content {
            display: flex;
            flex-wrap: wrap;
        }

        /* Booking Progress Indicator */
        .booking-progress-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .booking-progress {
            position: relative;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }

        .progress-step .step-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .progress-step.pending .step-icon {
            background-color: #e9ecef;
            color: #6c757d;
            border: 2px solid #dee2e6;
        }

        .progress-step.active .step-icon {
            background-color: var(--color-gold);
            color: #fff;
            border: 2px solid var(--color-gold);
            box-shadow: 0 0 0 4px rgba(200, 161, 101, 0.2);
        }

        .progress-step.completed .step-icon {
            background-color: #198754;
            color: #fff;
            border: 2px solid #198754;
        }

        .progress-step .step-label {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        .progress-step.active .step-label {
            color: var(--color-gold);
        }

        .progress-step.completed .step-label {
            color: #198754;
        }

        .progress-line {
            flex: 1;
            height: 3px;
            background-color: #dee2e6;
            margin: 0 0.5rem;
            margin-bottom: 1.5rem;
            transition: background-color 0.3s ease;
        }

        .progress-line.completed {
            background-color: #198754;
        }

        @media (max-width: 576px) {
            .progress-step .step-icon {
                width: 36px;
                height: 36px;
                font-size: 0.85rem;
            }

            .progress-line {
                margin-bottom: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navigation -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('website.home') }}">
                    <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}"
                        alt="Brickspoint ApartHotel" height="50" class="d-inline-block"
                        style="width: auto; object-fit: contain;">
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}"
                                href="{{ route('website.home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.rooms.*', 'website.booking') ? 'active' : '' }}"
                                href="{{ route('website.rooms.index') }}">Rooms & Suites</a>
                            {{-- <a class="nav-link" href="{{ url('https://guest.reservations.ng/BRICKSPOINTBOUTIQUEAPARTHOTELAS0/step1') }}">Rooms & Suites</a> --}}
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.dining') ? 'active' : '' }}"
                                href="{{ route('website.dining') }}">Dining</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.amenities') ? 'active' : '' }}"
                                href="{{ route('website.amenities') }}">Amenities</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('website.location') ? 'active' : '' }}"
                                href="#" id="ourHotelsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Our Hotels
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="ourHotelsDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('website.location') }}">
                                        <i class="fas fa-building me-2 text-warning"></i>All Locations
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('website.location') }}#asokoro">
                                        <i class="fas fa-location-dot me-2 text-success"></i>Brickspoint Asokoro
                                        <small class="text-muted d-block ps-4">24 Jose Marti Crescent</small>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="https://brickspoint.ng" target="_blank" rel="noopener noreferrer">
                                        <i class="fas fa-location-dot me-2 text-info"></i>Brickspoint Wuse II
                                        <small class="text-muted d-block ps-4">11 Adzope Crescent <i class="fas fa-external-link-alt ms-1 small"></i></small>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.about') ? 'active' : '' }}"
                                href="{{ route('website.about') }}">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}"
                                href="{{ route('website.contact') }}">Contact</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.booking.login') ? 'active' : '' }}"
                                href="{{ route('website.booking.login') }}">
                                <i class="fas fa-search me-1 small"></i>My Booking
                            </a>
                        </li>
                    </ul>
                    @guest
                        <div class="d-flex align-items-center">
                            <a href="{{ route('website.book') }}"
                                class="btn btn-primary px-4 py-2 rounded fw-bold shadow-sm btn-book-mobile">
                                <i class="fas fa-calendar-check me-2"></i>Book Now
                            </a>
                            {{-- url('https://guest.reservations.ng/BRICKSPOINTBOUTIQUEAPARTHOTELAS0/step1') }} --}}
                        </div>
                    @else
                        <div class="d-flex align-items-center">
                            <a href="{{ route('home') }}"
                                class="btn btn-primary px-4 py-2 rounded fw-bold shadow-sm btn-book-mobile">
                                <i class="fas fa-dashboard me-2"></i>Dashboard
                            </a>
                        </div>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}"
                        alt="Brickspoint Logo" class="footer-logo">
                    <p class="text-muted-footer">Experience the pinnacle of luxury and comfort in the heart of Abuja
                        city.</p>
                    <div class="mt-4">
                        <a href="https://fb.com/bpaparthotel" class="text-white me-3"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="https://x.com/bpaparthotel" class="text-white me-3"><i class="fab fa-x"></i></a>
                        <a href="https://instagram.com/brickspoint_asokoro" class="text-white me-3"><i
                                class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4">
                    <h4 class="h5 mb-4">Quick Links</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('website.home') }}"
                                class="text-muted-footer text-decoration-none">Home</a></li>
                        <li class="mb-2"> <a href="{{ route('website.rooms.index') }}"
                                class="text-muted-footer text-decoration-none">Rooms</a></li>
                        {{-- <a href="{{ url('https://guest.reservations.ng/BRICKSPOINTBOUTIQUEAPARTHOTELAS0/step1') }}"
                                class="text-muted-footer text-decoration-none">Rooms</a> --}}
                        <li class="mb-2"><a href="{{ route('website.amenities') }}"
                                class="text-muted-footer text-decoration-none">Amenities</a></li>
                        <li class="mb-2">
                            <a href="{{ route('website.location') }}" class="text-muted-footer text-decoration-none">Our Hotels</a>
                            <ul class="list-unstyled ps-3 mt-1" style="font-size: 0.85rem;">
                                <li class="mb-1">
                                    <a href="{{ route('website.location') }}#asokoro" class="text-muted-footer text-decoration-none">
                                        <i class="fas fa-location-dot me-1 small text-success"></i>Asokoro
                                    </a>
                                </li>
                                <li>
                                    <a href="https://brickspoint.ng" target="_blank" rel="noopener noreferrer" class="text-muted-footer text-decoration-none">
                                        <i class="fas fa-location-dot me-1 small text-info"></i>Wuse II <i class="fas fa-external-link-alt ms-1" style="font-size: 0.65rem;"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="mb-2"><a href="{{ route('website.about') }}"
                                class="text-muted-footer text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="{{ route('website.booking.login') }}"
                                class="text-muted-footer text-decoration-none">Manage Booking</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4">
                    <h4 class="h5 mb-4">Contact Info</h4>
                    <ul class="list-unstyled text-muted-footer">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> 24 Jose Marti
                            Crescent,
                            Asokoro, Abuja</li>
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> 11 Adzope Crescent,
                            Wuse II, Abuja</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-primary"></i> +234 (809) 999-9627 <br>
                            +234 (809) 999-9620</li>
                        <li class="mb-2"><a href="mailto:rsv@brickspoint.com"
                                class="text-muted-footer text-decoration-none">
                                <i class="fas fa-envelope me-2 text-primary"></i> rsv@brickspoint.com(Asokoro)
                            </a></li>
                        <li class="mb-2"><a href="mailto:rsv@brickspoint.ng"
                                class="text-muted-footer text-decoration-none">
                                <i class="fas fa-envelope me-2 text-primary"></i> rsv@brickspoint.ng(Wuse)
                            </a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4">
                    <h4 class="h5 mb-4">Newsletter</h4>
                    <p class="text-muted-footer">Subscribe for special offers and updates</p>
                    <form id="newsletterForm" class="mb-3">
                        <div class="input-group">
                            <input type="email" id="newsletterEmail"
                                class="form-control bg-secondary border-0 text-white" placeholder="Your Email"
                                required>
                            <button class="btn btn-primary" type="submit" id="newsletterBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="newsletterFeedback" class="mt-2" style="display: none;"></div>
                    </form>
                </div>
            </div>

            <hr class="my-4 bg-secondary">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted-footer">&copy; {{ date('Y') }} Brickspoint ApartHotel. All rights
                        reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted-footer text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted-footer text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Newsletter Popup Modal -->
    <div class="modal fade" id="newsletterPopup" tabindex="-1" aria-labelledby="newsletterPopupLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="position-relative">
                    {{-- Background Image/Gradient --}}
                    <div style="background: linear-gradient(135deg, var(--bs-primary) 0%, #f7e141 100%); padding: 2rem;"
                        class="text-white text-center">
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                        <i class="fas fa-envelope-open-text fa-3x mb-3 opacity-75"></i>
                        <h4 class="fw-bold mb-1">Stay Updated!</h4>
                        <p class="mb-0 opacity-75">Get exclusive offers & travel tips</p>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted text-center mb-4">Subscribe to our newsletter and be the first to know about
                        special deals, new amenities, and exciting events at Brickspoint.</p>
                    <form id="newsletterPopupForm">
                        <div class="mb-3">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="fas fa-envelope text-muted"></i></span>
                                <input type="email" id="newsletterPopupEmail"
                                    class="form-control border-start-0 bg-light" placeholder="Enter your email"
                                    required>
                            </div>
                        </div>
                        <button type="submit" id="newsletterPopupBtn" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane me-2"></i>Subscribe Now
                        </button>
                        <div id="newsletterPopupFeedback" class="mt-3 text-center" style="display: none;"></div>
                    </form>
                    <p class="text-muted small text-center mt-3 mb-0">
                        <i class="fas fa-lock me-1"></i>We respect your privacy. Unsubscribe anytime.
                    </p>
                </div>
                <div class="modal-footer border-0 bg-light py-2 justify-content-center">
                    <button type="button" class="btn btn-link text-muted small" id="dontShowAgain">
                        Don't show this again
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    @stack('scripts')

    {{-- Newsletter Subscription Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Footer newsletter form
            const form = document.getElementById('newsletterForm');
            const emailInput = document.getElementById('newsletterEmail');
            const submitBtn = document.getElementById('newsletterBtn');
            const feedback = document.getElementById('newsletterFeedback');

            // Shared newsletter submit handler
            async function handleNewsletterSubmit(email, feedbackEl, btnEl, inputEl) {
                btnEl.disabled = true;
                const originalBtnHtml = btnEl.innerHTML;
                btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const response = await fetch('{{ route('website.newsletter.subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email
                        })
                    });

                    const data = await response.json();

                    feedbackEl.style.display = 'block';
                    if (data.success) {
                        feedbackEl.className = 'mt-2 small text-success';
                        feedbackEl.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + data.message;
                        inputEl.value = '';

                        // Mark as subscribed in localStorage
                        localStorage.setItem('newsletter_subscribed', 'true');

                        // Close popup after success (if it's the popup form)
                        if (btnEl.id === 'newsletterPopupBtn') {
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById(
                                    'newsletterPopup'));
                                if (modal) modal.hide();
                            }, 2000);
                        }
                    } else {
                        feedbackEl.className = 'mt-2 small text-warning';
                        feedbackEl.innerHTML = '<i class="fas fa-info-circle me-1"></i>' + data.message;
                    }

                    setTimeout(() => {
                        feedbackEl.style.display = 'none';
                    }, 5000);

                } catch (error) {
                    feedbackEl.style.display = 'block';
                    feedbackEl.className = 'mt-2 small text-danger';
                    feedbackEl.innerHTML =
                        '<i class="fas fa-exclamation-circle me-1"></i>An error occurred. Please try again.';
                } finally {
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalBtnHtml;
                }
            }

            // Footer form handler
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = emailInput.value.trim();
                    if (email) {
                        handleNewsletterSubmit(email, feedback, submitBtn, emailInput);
                    }
                });
            }

            // Popup form handler
            const popupForm = document.getElementById('newsletterPopupForm');
            const popupEmailInput = document.getElementById('newsletterPopupEmail');
            const popupSubmitBtn = document.getElementById('newsletterPopupBtn');
            const popupFeedback = document.getElementById('newsletterPopupFeedback');

            if (popupForm) {
                popupForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = popupEmailInput.value.trim();
                    if (email) {
                        handleNewsletterSubmit(email, popupFeedback, popupSubmitBtn, popupEmailInput);
                    }
                });
            }

            // "Don't show again" button
            const dontShowBtn = document.getElementById('dontShowAgain');
            if (dontShowBtn) {
                dontShowBtn.addEventListener('click', function() {
                    localStorage.setItem('newsletter_popup_dismissed', 'true');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('newsletterPopup'));
                    if (modal) modal.hide();
                });
            }

            // Show popup on page load (with delay)
            const newsletterPopup = document.getElementById('newsletterPopup');
            if (newsletterPopup) {
                const isDismissed = localStorage.getItem('newsletter_popup_dismissed') === 'true';
                const isSubscribed = localStorage.getItem('newsletter_subscribed') === 'true';
                const lastShown = localStorage.getItem('newsletter_popup_last_shown');
                const now = Date.now();
                const oneDay = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

                // Show popup if:
                // 1. User hasn't dismissed it permanently
                // 2. User hasn't already subscribed
                // 3. Popup hasn't been shown in the last 24 hours
                const shouldShow = !isDismissed && !isSubscribed && (!lastShown || (now - parseInt(lastShown)) >
                    oneDay);

                if (shouldShow) {
                    // Show popup after 3 seconds delay
                    setTimeout(() => {
                        const modal = new bootstrap.Modal(newsletterPopup);
                        modal.show();
                        localStorage.setItem('newsletter_popup_last_shown', now.toString());
                    }, 3000);
                }
            }
        });
    </script>
</body>

</html>
