<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', config('app.name', 'BRICKSPOINT ERP'))</title>
    <meta name="description" content="@yield('meta_description', config('app.name', 'BRICKSPOINT ERP') . ' — Staff & Administration Portal')">
    <meta name="author" content="{{ config('app.name') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link href="https://fonts.cdnfonts.com/css/proxima-nova" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">

    <!-- Vite (Bootstrap, Icons, FontAwesome) -->
    @vite(['resources/sass/app.scss'])
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">

    <style>
        :root {
            --bs-body-font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
            --luxury-gold: #C5A572;
            --luxury-gold-hover: #B8956A;
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
            color: #333333;
        }

        /* Gold accent color styling */
        .text-gold {
            color: #C8A165 !important;
        }

        .bg-gold {
            background-color: #C8A165 !important;
        }

        .btn-gold {
            background-color: #C8A165;
            border-color: #C8A165;
            color: white;
        }

        .btn-gold:hover {
            background-color: #b08c54;
            border-color: #b08c54;
            color: white;
        }

        .btn-outline-gold {
            border-color: #C8A165;
            color: #C8A165;
        }

        .btn-outline-gold:hover {
            background-color: #C8A165;
            border-color: #C8A165;
            color: white;
        }

        .border-gold {
            border-color: #C8A165 !important;
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

<body style="font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; color: #333333;">

    {{-- This will now be the injection point for our entire master layout --}}
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
