<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name', 'Brickspoint ApartHotel') }} — @yield('title')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    @vite(['resources/sass/app.scss'])
    <link rel="preload" href="https://fonts.bunny.net/css?family=Montserrat:400,500,600,700|Playfair+Display:400,500,700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.bunny.net/css?family=Montserrat:400,500,600,700|Playfair+Display:400,500,700&display=swap" rel="stylesheet"></noscript>

    <style>
        :root {
            --color-white: #FFFFFF;
            --color-gold: #C8A165;
            --color-dark-gray: #333333;
            --color-soft-neutral: #F5F5F0;
            --font-primary: 'Proxima Nova', Arial, Helvetica, sans-serif;
        }

        @font-face {
            font-family: 'Proxima Nova';
            src: url("{{ asset('fonts/Proxima Nova Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        body {
            background-color: var(--color-soft-neutral);
            font-family: var(--font-primary);
            color: var(--color-dark-gray);
        }

        .guest-navbar {
            background-color: var(--color-dark-gray) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .guest-navbar .navbar-brand img {
            height: 60px;
            width: auto;
            object-fit: contain;
        }

        .guest-navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .guest-navbar .nav-link:hover {
            color: var(--color-gold) !important;
        }

        .guest-navbar .nav-link i {
            margin-right: 6px;
        }

        .btn-guest-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.25);
            color: rgba(255,255,255,0.85);
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-guest-logout:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--color-gold);
            color: var(--color-gold);
        }

        .guest-footer {
            background-color: var(--color-dark-gray);
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            padding: 20px 0;
            margin-top: auto;
        }

        .guest-footer a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
        }

        .guest-footer a:hover {
            color: var(--color-gold);
        }

        html, body {
            height: 100%;
        }

        #guest-app { 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
        }

        main { 
            flex: 1 0 auto; 
        }

        .active-link {
            background: linear-gradient(135deg, var(--color-gold) 0%, #b8924f 100%) !important;
            color: #fff !important;
            font-weight: 600;
            border-left: 4px solid #a07a3f !important;
        }

        .list-group-item-action {
            transition: all 0.2s ease;
        }

        .list-group-item-action:hover:not(.active-link) {
            background-color: #f8f9fa;
            color: var(--color-dark-gray) !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="guest-app">
        {{-- Guest Navbar --}}
        <nav class="navbar navbar-expand-lg navbar-dark guest-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('website.home') }}">
                    <img src="{{ Storage::url($settings['logo'] ?? 'images/brickspoint_logo.png') }}"
                        alt="Brickspoint ApartHotel">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNavbar"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="guestNavbar">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('website.home') }}">
                                <i class="fas fa-external-link-alt"></i> Back to Site
                            </a>
                        </li>
                        <li class="nav-item d-none d-lg-block">
                            <span class="nav-link text-white-50">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-guest-logout">
                                    <i class="fas fa-sign-out-alt me-1"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Main Content --}}
        <main>
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="guest-footer">
            <div class="container text-center">
                <p class="mb-0">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    &mdash; <a href="{{ route('website.home') }}">Back to Website</a>
                </p>
            </div>
        </footer>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
