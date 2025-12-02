<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Staff Module - {{ config('app.name', 'Staff Management') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.cdnfonts.com/css/proxima-nova" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <style>
        :root {
            --bs-body-font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
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
                if(themeToggler) {
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
</body>

</html>