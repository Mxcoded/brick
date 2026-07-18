<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $theme ?? 'gold-legacy' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', config('app.name', 'BRICKSPOINT ERP'))</title>
    <meta name="description" content="@yield('meta_description', config('app.name', 'BRICKSPOINT ERP') . ' — Staff & Administration Portal')">
    <meta name="author" content="{{ config('app.name') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.cdnfonts.com/css/proxima-nova" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">

    @vite(['resources/sass/app.scss'])
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">

    <style>
        :root {
            --bs-body-font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        }

        /* ─── intl-tel-input Overrides ─── */
        .iti { width: 100%; }

        /* Fix left padding for separate dial code (flag + code is wider than 52px) */
        .iti--separate-dial-code input,
        .iti--separate-dial-code input[type=text],
        .iti--separate-dial-code input[type=tel] {
            padding-left: 90px !important;
        }

        .iti__flag-container { z-index: 2; }

        .iti__selected-flag {
            background: transparent !important;
            border-right: 1px solid #dee2e6;
        }
        .iti__selected-flag:hover { background-color: rgba(0,0,0,0.03) !important; }

        .iti__selected-dial-code { font-size: 0.875rem; color: #6c757d; }

        .iti__country-list {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-top: 4px !important;
            z-index: 9999;
        }
        .iti__country.iti__highlight { background-color: #f3f4f6; }

        /* Dark mode */
        [data-bs-theme="dark"] .iti__selected-flag { border-color: #495057; }
        [data-bs-theme="dark"] .iti__selected-flag:hover { background-color: rgba(255,255,255,0.05) !important; }
        [data-bs-theme="dark"] .iti__selected-dial-code { color: #adb5bd; }
        [data-bs-theme="dark"] .iti__country-list { background-color: #343a40; border-color: #495057; }
        [data-bs-theme="dark"] .iti__country { color: #fff; }
        [data-bs-theme="dark"] .iti__country:hover { background-color: rgba(255,255,255,0.08); }
        [data-bs-theme="dark"] .iti__country.iti__highlight { background-color: rgba(255,255,255,0.15); }

        /* Readonly - prevent interaction */
        .iti:has(.phone-input[readonly]) { pointer-events: none; opacity: 0.7; }
        /* Show validation feedback when intl-tel-input wraps the input */
        .iti:has(.is-invalid) ~ .invalid-feedback,
        .was-validated .iti:has(input:invalid) ~ .invalid-feedback { display: block; }
        /* Floating label fix for intl-tel-input */
        .form-floating:has(.iti) > label {
            left: 90px !important;
            width: calc(100% - 90px) !important;
        }
        .form-floating .iti:focus-within ~ label,
        .form-floating.has-focus .iti ~ label,
        .form-floating.has-value .iti ~ label,
        .form-floating-custom.has-focus .iti ~ label {
            opacity: .65;
            transform: scale(.85) translateY(-0.5rem) translateX(.15rem);
        }

        body {
            font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        }

        /* ── Gold Legacy (default) ── */
        [data-theme="gold-legacy"],
        :root {
            --theme-primary: #C8A165;
            --theme-primary-hover: #b08c54;
            --theme-primary-dark: #7A5F34;
            --theme-primary-rgb: 200, 161, 101;
            --theme-primary-light: rgba(200, 161, 101, 0.12);
            --theme-btn-text: #ffffff;
            --theme-accent: #d4b07a;
            --theme-body-bg: #f0f2f5;
            --theme-card-bg: #ffffff;
            --theme-text: #333333;
            --theme-text-muted: #6c757d;
            --theme-heading: #1a1a1a;
            --theme-border: #e9ecef;

            --sidebar-bg: #1e1e2d;
            --sidebar-bg-alt: #1a1a28;
            --sidebar-text: #b8b9ce;
            --sidebar-text-hover: #ffffff;
            --sidebar-brand: #C8A165;
            --sidebar-brand-light: #d4b07a;
            --sidebar-active-bg: rgba(200, 161, 101, 0.12);
            --sidebar-active-text: #d4b07a;
            --sidebar-active-border: #C8A165;
            --sidebar-submenu-text: #a0a1b8;
            --sidebar-submenu-bg: rgba(0, 0, 0, 0.25);
            --sidebar-item-hover: rgba(255, 255, 255, 0.04);
            --sidebar-divider: rgba(255, 255, 255, 0.06);
            --sidebar-toggle-color: #c8c9db;
            --sidebar-radius: 0;
            --sidebar-transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);

            --luxury-gold: #C5A572;
            --luxury-gold-hover: #B8956A;
        }

        /* ── Platinum Noir ── */
        [data-theme="platinum-noir"] {
            --theme-primary: #E8E8E8;
            --theme-primary-hover: #d0d0d0;
            --theme-primary-dark: #B0B0B0;
            --theme-primary-rgb: 232, 232, 232;
            --theme-primary-light: rgba(232, 232, 232, 0.10);
            --theme-btn-text: #0D0D0D;
            --theme-accent: #B0B0B0;
            --theme-body-bg: #121212;
            --theme-card-bg: #1e1e1e;
            --theme-text: #e0e0e0;
            --theme-text-muted: #a8a8a8;
            --theme-heading: #ffffff;
            --theme-border: #2a2a2a;

            --sidebar-bg: #0D0D0D;
            --sidebar-bg-alt: #111111;
            --sidebar-text: #999999;
            --sidebar-text-hover: #ffffff;
            --sidebar-brand: #E8E8E8;
            --sidebar-brand-light: #ffffff;
            --sidebar-active-bg: rgba(255, 255, 255, 0.06);
            --sidebar-active-text: #ffffff;
            --sidebar-active-border: #E8E8E8;
            --sidebar-submenu-text: #888888;
            --sidebar-submenu-bg: rgba(0, 0, 0, 0.35);
            --sidebar-item-hover: rgba(255, 255, 255, 0.05);
            --sidebar-divider: rgba(255, 255, 255, 0.08);
            --sidebar-toggle-color: #aaaaaa;

            --luxury-gold: #E8E8E8;
            --luxury-gold-hover: #cccccc;
        }

        /* ── Sapphire Regal ── */
        [data-theme="sapphire-regal"] {
            --theme-primary: #1B3A5C;
            --theme-primary-hover: #2C5F8A;
            --theme-primary-dark: #1B3A5C;
            --theme-primary-rgb: 27, 58, 92;
            --theme-primary-light: rgba(27, 58, 92, 0.10);
            --theme-btn-text: #ffffff;
            --theme-accent: #C8A165;
            --theme-body-bg: #f0f2f5;
            --theme-card-bg: #ffffff;
            --theme-text: #2d3748;
            --theme-text-muted: #8094a8;
            --theme-heading: #1a202c;
            --theme-border: #e2e8f0;

            --sidebar-bg: #0F1A2E;
            --sidebar-bg-alt: #0C1525;
            --sidebar-text: #9cb2ce;
            --sidebar-text-hover: #ffffff;
            --sidebar-brand: #C8A165;
            --sidebar-brand-light: #d4b07a;
            --sidebar-active-bg: rgba(200, 161, 101, 0.12);
            --sidebar-active-text: #d4b07a;
            --sidebar-active-border: #C8A165;
            --sidebar-submenu-text: #8aa0bc;
            --sidebar-submenu-bg: rgba(0, 0, 0, 0.30);
            --sidebar-item-hover: rgba(255, 255, 255, 0.04);
            --sidebar-divider: rgba(255, 255, 255, 0.07);
            --sidebar-toggle-color: #a0b4cc;

            --luxury-gold: #C8A165;
            --luxury-gold-hover: #b08c54;
        }

        body {
            color: var(--theme-text);
            background-color: var(--theme-body-bg);
        }

        .card {
            background-color: var(--theme-card-bg);
            border-color: var(--theme-border);
        }
        .card-header.bg-white,
        .card-footer.bg-white {
            background-color: var(--theme-card-bg) !important;
        }

        .text-gold {
            color: var(--theme-primary-dark) !important;
        }
        .text-primary-accent {
            color: var(--theme-primary) !important;
        }

        .bg-gold {
            background-color: var(--theme-primary) !important;
        }

        .btn-gold,
        .btn-themed {
            background-color: var(--theme-primary);
            border-color: var(--theme-primary);
            color: var(--theme-btn-text);
        }
        .btn-gold:hover,
        .btn-themed:hover {
            background-color: var(--theme-primary-hover);
            border-color: var(--theme-primary-hover);
            color: var(--theme-btn-text);
        }

        .btn-outline-gold {
            border-color: var(--theme-primary);
            color: var(--theme-primary);
        }

        .btn-outline-gold:hover {
            background-color: var(--theme-primary);
            border-color: var(--theme-primary);
            color: #fff;
        }

        .border-gold {
            border-color: var(--theme-primary) !important;
        }

        .text-heading {
            color: var(--theme-heading);
        }

        .text-muted {
            color: var(--theme-text-muted) !important;
        }

        @media (max-width: 767.98px) {
            [data-theme="platinum-noir"] .navbar.bg-white {
                background-color: var(--theme-card-bg) !important;
                border-bottom-color: var(--theme-border) !important;
            }
        }

        /* Theme toggle pill switch */
        .theme-toggle {
            background: transparent;
            cursor: pointer;
            padding: 4px 10px 4px 4px;
            border-radius: 50px;
            transition: background 0.3s ease;
        }
        .theme-toggle:hover {
            background: rgba(0,0,0,0.05);
        }
        [data-bs-theme="dark"] .theme-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
        .theme-toggle-track {
            width: 40px;
            height: 22px;
            background: #e9ecef;
            border-radius: 50px;
            position: relative;
            transition: background 0.3s ease;
            flex-shrink: 0;
        }
        [data-bs-theme="dark"] .theme-toggle-track {
            background: #495057;
        }
        .theme-toggle-thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 18px;
            height: 18px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            color: #f39c12;
        }
        [data-bs-theme="dark"] .theme-toggle-thumb {
            transform: translateX(18px);
            color: #6c757d;
        }
        .theme-toggle-thumb i {
            transition: transform 0.4s ease;
            font-size: 10px;
        }
        [data-bs-theme="dark"] .theme-toggle-thumb i {
            transform: rotate(360deg);
        }
        .theme-toggle-label {
            color: #6c757d;
            transition: color 0.3s ease;
        }
        [data-bs-theme="dark"] .theme-toggle-label {
            color: #adb5bd;
        }
    </style>

    @yield('styles')
</head>

<body style="font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;">

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

    @vite(['resources/js/app.js'])
    @yield('scripts')
    <script>
        (() => {
            'use strict'

            const getStoredTheme = () => localStorage.getItem('theme')
            const setStoredTheme = theme => localStorage.setItem('theme', theme)

            const getPreferredTheme = () => {
                const storedTheme = getStoredTheme()
                if (storedTheme) {
                    return storedTheme
                }
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
            }

            const setTheme = theme => {
                document.documentElement.setAttribute('data-bs-theme', theme)
                const sunIcon = document.getElementById('theme-icon-sun');
                const moonIcon = document.getElementById('theme-icon-moon');
                const themeLabel = document.getElementById('theme-label');
                if (sunIcon && moonIcon) {
                    if (theme === 'dark') {
                        sunIcon.classList.add('d-none');
                        moonIcon.classList.remove('d-none');
                    } else {
                        sunIcon.classList.remove('d-none');
                        moonIcon.classList.add('d-none');
                    }
                }
                if (themeLabel) {
                    themeLabel.textContent = theme === 'dark' ? 'Dark' : 'Light';
                }
            }

            setTheme(getPreferredTheme())

            window.addEventListener('DOMContentLoaded', () => {
                const themeToggler = document.getElementById('theme-toggle');
                if (themeToggler) {
                    themeToggler.addEventListener('click', () => {
                        const currentTheme = getStoredTheme() || getPreferredTheme();
                        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                        setStoredTheme(newTheme);
                        setTheme(newTheme);
                    });
                }
            });
        })()
    </script>
    <script>
        $(document).ready(function() {
            if (typeof intlTelInput !== 'undefined') {
                $('input.phone-input').each(function() {
                    var input = this;
                    if ($(input).data('iti')) return;
                    var iti = window.intlTelInput(input, {
                        initialCountry: 'auto',
                        geoIpLookup: function(callback) {
                            $.get('https://ipapi.co/json/', function(data) {}, 'jsonp').always(function(resp) {
                                var countryCode = (resp && resp.country) ? resp.country : 'ng';
                                callback(countryCode);
                            });
                        },
                        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js',
                        separateDialCode: true,
                        autoPlaceholder: 'aggressive',
                        nationalMode: true,
                    });
                    $(input).on('input', function () {
                        var val = $(this).val().trim();
                        $(this).closest('.form-floating, .form-floating-custom').toggleClass('has-value', val !== '');
                    });
                    $(input).on('focusin focusout', function (e) {
                        $(this).closest('.form-floating, .form-floating-custom').toggleClass('has-focus', e.type === 'focusin');
                    });
                    $(input).on('blur', function () {
                        var val = $(this).val().trim();
                        if (iti.isValidNumber()) {
                            $(input).removeClass('is-invalid').addClass('is-valid');
                        } else if (val !== '') {
                            $(input).removeClass('is-valid').addClass('is-invalid');
                        } else {
                            $(input).removeClass('is-valid is-invalid');
                        }
                    });
                    if ($(input).val().trim() !== '') {
                        var $floating = $(input).closest('.form-floating, .form-floating-custom');
                        $floating.addClass('has-value');
                        $(input).trigger('blur');
                    }
                });
            }
        });
    </script>
</body>

</html>
