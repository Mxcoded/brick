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

    @vite(['resources/sass/app.scss'])
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
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

<body>

    {{-- This will now be the injection point for our entire master layout --}}
    @yield('content')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
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