<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Brickspoint Aparthotel - @yield('title', 'Guest Check-in')</title>

    <meta name="description" content="{{ $description ?? 'Luxury apartment hotel experience with premium amenities and personalized service' }}">
    <meta name="keywords" content="{{ $keywords ?? 'aparthotel, luxury stay, business travel, vacation rental, premium accommodation' }}">
    <meta name="author" content="{{ $author ?? 'Brickspoint Aparthotel' }}">

    {{-- Core CSS Frameworks --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.8' }
                        },
                        'float': {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        'fadeIn': {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>

    {{-- Custom Styles --}}
    <style>
        /* GLASS EFFECT STYLES */
        :root {
            --glass-effect: rgba(217, 234, 253, 0.1);
            --glass-border: rgba(154, 166, 178, 0.2);
            --glass-dark: rgba(255, 255, 255, 0.1);
        }

        /* Define Custom Local Fonts */
        @font-face {
            font-family: 'BrownSugar';
            src: url("{{ asset('fonts/Brown Sugar .otf') }}") format('opentype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'GothamLight';
            src: url("{{ asset('fonts/Gotham-Light.otf') }}") format('opentype');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'FuturaLT';
            src: url("{{ asset('fonts/FuturaLT-Light.ttf') }}") format('truetype');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }

        /* Enhanced Base Styles */
        body {
            background: linear-gradient(135deg, #F8FAFC 0%, #D9EAFD 50%, #EFF6FF 100%);
            background-attachment: fixed;
            font-family: 'Figtree', ui-sans-serif, system-ui;
            min-height: 100vh;
        }

        /* Glass Morphism Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus States */
        .focus-brand:focus {
            border-color: #1a56db;
            box-shadow: 0 0 0 0.2rem rgba(26, 86, 219, 0.25);
            outline: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Loading Animation */
        .loading-spinner {
            border: 2px solid #f3f4f6;
            border-top: 2px solid #1a56db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
        }

        /* High Contrast Support */
        @media (prefers-contrast: high) {
            .glass-card {
                background: white;
                backdrop-filter: none;
                border: 2px solid black;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            html {
                scroll-behavior: auto;
            }
        }
    </style>

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

<body class="min-h-screen bg-gradient-to-br from-base-white via-primary-light to-base-light font-sans antialiased">
    {{-- Navigation Header --}}
    <nav class="glass-nav shadow-smooth sticky top-0 z-50 no-print">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                {{-- Brand Logo & Name --}}
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gray-500 rounded-lg flex items-center justify-center shadow-md">
                        <i class="fas fa-building text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-base-dark font-brand">Brickspoint Aparthotel</h1>
                        <p class="text-xs text-base-medium">Front Desk Portal</p>
                    </div>
                </div>

                {{-- User Actions --}}
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-primary-brand rounded-full flex items-center justify-center text-white text-sm font-medium">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-base-dark font-medium hidden sm:inline">Welcome, {{ Auth::user()->name }}</span>
                            <a href="{{ route('logout') }}" 
                               class="text-error hover:text-red-700 transition-colors duration-200 font-medium flex items-center space-x-1"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="hidden sm:inline">Logout</span>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('login') }}" class="text-primary-brand hover:text-primary-dark transition-colors duration-200 font-medium">
                                Staff Login
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1 py-8">
        @yield('page-content')
    </main>

    {{-- Footer --}}
    <footer class="bg-base-dark text-white py-8 mt-12 no-print">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-1 gap-0">
                {{-- Contact Information --}}
                {{-- <div>
                    <h3 class="font-bold text-lg mb-4 font-brand">Brickspoint Aparthotel</h3>
                    <div class="space-y-2 text-base-light">
                        <p class="flex items-center space-x-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>24 Jose Marti Crescent, Abuja</span>
                        </p>
                        <p class="flex items-center space-x-2">
                            <i class="fas fa-phone"></i>
                            <span>+2348099999620</span>
                        </p>
                        <p class="flex items-center space-x-2">
                            <i class="fas fa-envelope"></i>
                            <span>info@brickspoint.com</span>
                        </p>
                    </div>
                </div> --}}

                {{-- Quick Links --}}
                {{-- <div>
                    <h3 class="font-bold text-lg mb-4">Quick Links</h3>
                    <div class="space-y-2">
                        <a href="#" class="text-base-light hover:text-white transition-colors block">About Us</a>
                        <a href="#" class="text-base-light hover:text-white transition-colors block">Services</a>
                        <a href="#" class="text-base-light hover:text-white transition-colors block">Contact</a>
                        <a href="#" class="text-base-light hover:text-white transition-colors block">Privacy Policy</a>
                    </div>
                </div> --}}

                {{-- Social Media --}}
                {{-- <div>
                    <h3 class="font-bold text-lg mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-base-medium hover:bg-primary-brand rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-base-medium hover:bg-primary-brand rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-base-medium hover:bg-primary-brand rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-base-medium hover:bg-primary-brand rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div> --}}
               <p class="text-center text-gray-700 text-lg md:text-xl font-medium leading-relaxed bg-gray-50 p-6 rounded-lg shadow-sm">
  Thank you for using the <span class="font-semibold text-primary">Brickspoint Aparthotel Front Desk Portal</span>. We're committed to delivering exceptional service and a seamless experience for both our guests and staff.
</p>
            </div>
            
            {{-- Copyright --}}
            <div class="border-t border-base-medium mt-8 pt-6 text-center text-base-light">
                 <footer class=" p-3 mt-auto border-top">
                <div class="container-fluid text-center">
                    <div
                        style="display: inline-block; padding: 10px 20px;   border-radius: 12px; background: var(--glass-effect); border: 1px solid var(--glass-border);box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2), 
                            -4px -4px 15px rgba(116, 114, 114, 1.2); transform: perspective(600px) rotateX(2deg); transition: var(--transition); margin-right: 15px;">
                        <p class="mb-0 text-light">&copy; {{ date('Y') }}

                            <a href="#"
                                style="font-weight: 800; font-size: 1.4rem; color: #e1e9e2d6;  text-decoration: none; letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                 ">
                                BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub> <sub
                                    style="font-size:8pt;">v1.0</sub>
                            </a>
                            . All rights reserved.
                    </div>
                    </p>
                    <p class="mb-0 text-light">™ Developed with ❤️ by IT Team </p>
                </div>
            </footer>
            </div>
        </div>
    </footer>

    {{-- Scripts Section --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    {{-- Global JavaScript --}}
    <script>
        // Enhanced Global Functions
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
                        submitBtn.innerHTML = '<span class="loading-spinner mr-2"></span>Processing...';
                    }
                });
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
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

            // Enhanced error handling
            window.addEventListener('error', function(e) {
                console.error('Application error:', e.error);
                // You can add error reporting service here
            });

            // Performance monitoring
            window.addEventListener('load', function() {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                console.log('Page load time:', loadTime + 'ms');
            });
        });
    </script>

    {{-- Page Specific Scripts --}}
    @stack('page-scripts')

    {{-- Analytics (Optional) --}}
    @if(config('app.analytics_enabled'))
    <script>
        // Add your analytics script here
    </script>
    @endif
</body>

</html>