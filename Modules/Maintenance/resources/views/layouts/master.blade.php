<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', 'Maintenance') — {{ config('app.name', 'BRICKSPOINT ERP') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <meta name="description" content="@yield('meta_description', config('app.name', 'BRICKSPOINT ERP') . ' — Maintenance Management')">
    <meta name="author" content="{{ config('app.name') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite (Bootstrap, Icons, FontAwesome) -->
    @vite(['resources/sass/app.scss'])

    <style>
        :root {
            --luxury-gold: #C5A572;
            --luxury-gold-hover: #B8956A;
            --luxury-gold-light: #F5EFE6;
        }
        .hover-scale tr {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-scale tr:hover {
            transform: translateY(-2px);
            background: #f8f9fa !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .rounded-4 {
            border-radius: 1rem !important;
        }
        .bg-light-100 {
            background-color: #f8f9fa;
        }
        .empty-state {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        .btn-light {
            background-color: #fff;
            border-color: #dee2e6 !important;
        }
        /* Luxury Gold Primary Button */
        .btn-primary {
            background-color: var(--luxury-gold) !important;
            border-color: var(--luxury-gold) !important;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--luxury-gold-hover) !important;
            border-color: var(--luxury-gold-hover) !important;
            color: #fff !important;
        }
        .btn-primary:active {
            background-color: var(--luxury-gold-hover) !important;
            border-color: var(--luxury-gold-hover) !important;
        }
        /* DataTables Pagination - Luxury Gold */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--luxury-gold) !important;
            border-color: var(--luxury-gold) !important;
            color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--luxury-gold-light) !important;
            border-color: var(--luxury-gold) !important;
            color: var(--luxury-gold) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            margin: 0 2px;
        }
        /* Filter buttons active state */
        .filter-btn.active {
            background-color: var(--luxury-gold) !important;
            border-color: var(--luxury-gold) !important;
            color: #fff !important;
        }
        .filter-btn:hover:not(.active) {
            background-color: var(--luxury-gold-light) !important;
            border-color: var(--luxury-gold) !important;
        }
        .text-gradient {
            background: linear-gradient(45deg, var(--luxury-gold), #D4AF37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Text primary color */
        .text-primary {
            color: var(--luxury-gold) !important;
        }
        /* Links */
        a.text-primary:hover {
            color: var(--luxury-gold-hover) !important;
        }
    </style>
    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-maintenance', 'resources/assets/sass/app.scss', storage_path('vite.hot')) }} --}}
</head>

<body>
   <div class="container mt-4">
        @yield('content')
    </div>
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @vite(['resources/js/app.js'])
    @yield('scripts')
    {{-- Vite JS --}}
    {{-- {{ module_vite('build-maintenance', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}
</body>
</html>
