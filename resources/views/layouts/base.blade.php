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
                if (sunIcon && moonIcon) {
                    if (theme === 'dark') {
                        sunIcon.classList.add('d-none');
                        moonIcon.classList.remove('d-none');
                    } else {
                        sunIcon.classList.remove('d-none');
                        moonIcon.classList.add('d-none');
                    }
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
                    if (!$(this).data('iti')) {
                        intlTelInput(this, {
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
                    }
                });
            }
        });
    </script>
</body>

</html>
