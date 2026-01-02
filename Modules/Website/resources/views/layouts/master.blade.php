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
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

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

        h1, h2, h3, h4, h5, h6 {
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
            color: var(--color-dark-gray); /* Dark text for contrast on Gold */
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #b08d55; /* Darker Gold */
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
            height: 60px; /* Larger default size */
            width: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
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
                height: 45px;
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

        .text-muted-footer {
            opacity: 0.8;
        }
        
        /* Ensure proper footer layout */
        .footer-content {
            display: flex;
            flex-wrap: wrap;
        }
    </style>
    @stack('styles')
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navigation -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
            <div class="container">
                <a class="navbar-brand" href="{{ route('website.home') }}">
                    <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}" alt="Brickspoint ApartHotel"
                        class="d-inline-block align-top">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}"
                                href="{{ route('website.home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.rooms') ? 'active' : '' }}"
                                href="{{ route('website.rooms') }}">Rooms & Suites</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.amenities') ? 'active' : '' }}"
                                href="{{ route('website.amenities') }}">Amenities</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.location') ? 'active' : '' }}"
                                href="{{ route('website.location') }}">Location</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.about') ? 'active' : '' }}"
                                href="{{ route('website.about') }}">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}"
                                href="{{ route('website.contact') }}">Contact</a>
                        </li>
                    </ul>

                    <div class="d-flex">
                        <a href="{{ route('website.booking.form') }}" class="btn btn-primary px-4">
                            <i class="fas fa-calendar-check me-2"></i>Book Now
                        </a>
                    </div>
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
                     <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}" alt="Brickspoint Logo" class="footer-logo">
                    <p class="text-muted-footer">Experience the pinnacle of luxury and comfort in the heart of Abuja city.</p>
                    <div class="mt-4">
                        <a href="https://fb.com/bpaparthotel" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://x.com/bpaparthotel" class="text-white me-3"><i class="fab fa-x"></i></a>
                        <a href="https://instagram.com/brickspoint_asokoro" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-4">
                    <h4 class="h5 mb-4">Quick Links</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('website.home') }}"
                                class="text-muted-footer text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="{{ route('website.rooms') }}"
                                class="text-muted-footer text-decoration-none">Rooms</a></li>
                        <li class="mb-2"><a href="{{ route('website.amenities') }}"
                                class="text-muted-footer text-decoration-none">Amenities</a></li>
                        <li class="mb-2"><a href="{{ route('website.about') }}"
                                class="text-muted-footer text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="{{ route('website.contact') }}"
                                class="text-muted-footer text-decoration-none">Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4">
                    <h4 class="h5 mb-4">Contact Info</h4>
                    <ul class="list-unstyled text-muted-footer">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> 24 Jose Marti Crescent,
                            Asokoro, Abuja</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-primary"></i> +234 (809) 999-9627</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i> rsv@brickspoint.com</li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-4">
                    <h4 class="h5 mb-4">Newsletter</h4>
                    <p class="text-muted-footer">Subscribe for special offers and updates</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control bg-secondary border-0" placeholder="Your Email">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4 bg-secondary">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted-footer">&copy; {{ date('Y') }} Brickspoint ApartHotel. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted-footer text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted-footer text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    @stack('scripts')
</body>
</html>