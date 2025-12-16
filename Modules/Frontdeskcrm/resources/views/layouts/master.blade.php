<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Brickspoint Aparthotel - @yield('title', 'Guest Check-in')</title>

    <meta name="description"
        content="{{ $description ?? 'Luxury apartment hotel experience with premium amenities and personalized service' }}">
    <meta name="keywords"
        content="{{ $keywords ?? 'aparthotel, luxury stay, business travel, vacation rental, premium accommodation' }}">
    <meta name="author" content="{{ $author ?? 'Brickspoint Aparthotel' }}">

    {{-- Core CSS Frameworks --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preload" href="/fonts/Proxima Nova Regular.ttf" as="font" type="font/ttf" crossorigin="anonymous">

    {{-- Tailwind CSS with Enhanced Configuration --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // ENHANCED TAILWIND CONFIG WITH BRICKSPOINT BRAND COLORS
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Primary Brand Colors
                        'primary-light': '#D9EAFD',
                        'primary-medium': '#BCCCDC',
                        'primary-dark': '#9AA6B2',
                        'primary-brand': '#1a56db',
                        'secondary-brand': '#059669',

                        // Neutral Colors
                        'base-white': '#F8FAFC',
                        'base-light': '#F1F5F9',
                        'base-medium': '#64748B',
                        'base-dark': '#334155',

                        // Semantic Colors
                        'success': '#10B981',
                        'warning': '#F59E0B',
                        'error': '#EF4444',
                        'info': '#3B82F6',

                        // Glass Effect Variables
                        'glass-bg': 'rgba(217, 234, 253, 0.1)',
                        'glass-border': 'rgba(154, 166, 178, 0.2)',
                        'glass-dark': 'rgba(255, 255, 255, 0.1)'
                    },
                    fontFamily: {
                        'sans': ['Figtree', 'ui-sans-serif', 'system-ui'],
                        'brand': ['BrownSugar', 'ui-serif', 'Georgia'],
                        'elegant': ['GothamLight', 'ui-sans-serif', 'system-ui'],
                        'modern': ['FuturaLT', 'ui-sans-serif', 'system-ui']
                    },
                    backgroundImage: {
                        'gradient-primary': 'linear-gradient(135deg, #1a56db 0%, #0e40a4 100%)',
                        'gradient-success': 'linear-gradient(135deg, #10B981 0%, #059669 100%)',
                        'gradient-glass': 'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
                        'hotel-pattern': "url('{{ asset('images/hotel-pattern.svg') }}')"
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.37)',
                        'smooth': '0 4px 20px 0 rgba(0, 0, 0, 0.1)',
                        'floating': '0 20px 40px 0 rgba(0, 0, 0, 0.15)'
                    },
                    animation: {
                        'pulse-soft': 'pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 3s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.5s ease-in-out'
                    },
                    keyframes: {
                        'pulse-soft': {
                            '0%, 100%': {
                                opacity: '1'
                            },
                            '50%': {
                                opacity: '0.8'
                            }
                        },
                        'float': {
                            '0%, 100%': {
                                transform: 'translateY(0px)'
                            },
                            '50%': {
                                transform: 'translateY(-10px)'
                            }
                        },
                        'fadeIn': {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(10px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        }
                    }
                }
            }
        }
    </script>

    {{-- Custom Styles --}}
    <style>
        :root {
            --brand-white: #FFFFFF;
            --brand-gold: #C8A165;
            --brand-charcoal: #333333;
            --brand-neutral: #f4f1ed;
            /* Soft Taupe/Beige */
        }

        @font-face {
            font-family: 'Proxima Nova';
            src: url('/fonts/Proxima Nova Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'BrownSugar';
            src: url('/fonts/Brown Sugar .ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* 1. Font Setup */
        body {
            font-family: 'Proxima Nova', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Helvetica Neue", sans-serif;
            color: var(--brand-charcoal);
            background-color: var(--brand-neutral);
            /* Use soft neutral for the page background */
        }

        /* 2. Helper Classes */
        .text-gold {
            color: var(--brand-gold) !important;
        }

        .bg-gold {
            background-color: var(--brand-gold) !important;
        }

        .bg-charcoal {
            background-color: var(--brand-charcoal) !important;
        }

        .text-charcoal {
            color: var(--brand-charcoal) !important;
        }

        /* 3. Component Overrides */
        .btn-gold {
            background-color: var(--brand-gold);
            color: var(--brand-white);
            border-color: var(--brand-gold);
        }

        .btn-gold:hover {
            background-color: #b38e56;
            /* A slightly darker gold for hover */
            color: var(--brand-white);
            border-color: #b38e56;
        }

        .btn-outline-gold {
            color: var(--brand-gold);
            border-color: var(--brand-gold);
        }

        .btn-outline-gold:hover {
            background-color: var(--brand-gold);
            color: var(--brand-white);
        }

        a {
            color: var(--brand-gold);
        }

        a:hover {
            color: #b38e56;
        }

        /* 4. Nav & Footer */
        .glass-nav {
            background-color: rgba(255, 255, 255, 0.85);
            /* White background with slight transparency */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            /* For Safari */
            border-bottom: 1px solid rgba(200, 161, 101, 0.3);
            /* Subtle gold border */
        }

        .footer-link {
            font-weight: 800;
            font-size: 1.4rem;
            color: #e1e9e2d6;
            text-decoration: none;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .footer-link:hover {
            color: var(--brand-white);
        }

        /* Update progress bar colors */
        .progress-bar {
            background-color: var(--brand-gold);
        }

        .step.active .step-icon {
            background-color: var(--brand-gold) !important;
            color: var(--brand-white) !important;
            transform: scale(1.1);
        }

        .step.active .step-label {
            color: var(--brand-gold);
            font-weight: 600;
        }
    </style>
    @stack('page-styles')

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    {{-- Structured Data for SEO --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Hotel",
        "name": "Brickspoint Aparthotel",
        "description": "Luxury apartment hotel offering premium accommodations with personalized service",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('images/logo.png') }}",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Abuja",
            "addressCountry": "Nigeria"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+2348099999620",
            "contactType": "customer service"
        }
    }
    </script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen">

        {{-- Navigation Header --}}
        <nav class="glass-nav shadow-sm sticky top-0 z-50 no-print">
            <div class="container mx-auto px-4 py-3">
                <div class="flex justify-between items-center">
                    {{-- Brand Logo & Name --}}
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-md">
                            <img src="{{ asset('storage/images/BrickspointLogo.png') }}" alt="Brickspoint Aparthotel"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                        <div>
                            {{-- UPDATED: Applied brand colors --}}
                            <h1 class="text-xl font-bold text-charcoal">Brickspoint Boutique Aparthotel</h1>
                            <p class="text-xs text-muted">Front Desk Portal</p>
                        </div>
                    </div>

                    {{-- User Actions --}}
                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="flex items-center space-x-3">
                                {{-- UPDATED: Applied brand colors --}}
                                <div
                                    class="w-8 h-8 bg-gold rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="text-charcoal font-medium hidden sm:inline">Welcome,
                                    {{ Auth::user()->name }}</span>

                                {{-- UPDATED: Applied brand colors (using default <a> tag styling) --}}
                                <a href="{{ route('logout') }}"
                                    class="text-red-600 hover:text-red-800 transition-colors duration-200 font-medium flex items-center space-x-1"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span class="hidden sm:inline">Logout</span>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        @else
                            {{-- <div class="flex items-center space-x-3">
                            <a href="{{ route('login') }}" class="font-medium">
                                Staff Login
                            </a>
                            </div> --}}
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- Main Content --}}
        <main>
            @yield('page-content')
        </main>

        {{-- Footer --}}
        {{-- UPDATED: Applied bg-charcoal and text-white --}}
        <footer class="bg-charcoal text-white py-8 mt-12 no-print">
            <div class="container mx-auto px-4">
                {{-- Thank You Message --}}
                {{-- <p UPDATED: Applied brand colors
                    class="text-center text-charcoal text-lg md:text-xl font-medium leading-relaxed bg-white p-6 rounded-lg shadow-sm">
                    Thank you for using the <span class="font-semibold text-gold">Brickspoint Aparthotel Front
                        Desk Portal</span>. We're committed to delivering exceptional service and a seamless
                    experience for both our guests and staff.
                </p> --}}

                {{-- Copyright --}}
                <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                    <div
                        style="display: inline-block; padding: 10px 20px; border-radius: 12px; border: 1px solid var(--glass-border); margin-right: 15px;">
                        <p class="mb-0 text-light">
                            &copy; {{ date('Y') }}
                            <a href="#" class="footer-link">
                                BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub> <sub
                                    style="font-size:8pt;">v1.0</sub>
                            </a>
                            . All rights reserved.
                        </p>
                    </div>
                    <p class="mb-0 text-light mt-2">™ Developed with ❤️ by IT Team </p>
                </div>
            </div>
        </footer>
    </div>

    {{-- Scripts Section --}}
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    {{-- Global JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Enhanced form handling
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                    }
                });
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>

    {{-- Page Specific Scripts --}}
    @stack('page-scripts')

</body>

</html>
