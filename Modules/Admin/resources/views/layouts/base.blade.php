<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Staff Module - {{ config('app.name', 'Staff Management') }}&trade;</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Vite (Bootstrap, Icons, FontAwesome) -->
    @vite(['resources/sass/app.scss'])
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        .nav-pills .nav-link.active {
            background-color: gold !important;
            color: black;
        }
    </style>
    <!-- Yield custom styles -->
    @yield('styles')
</head>
<body>
    <!-- Header Section -->
    <header>
        @yield('header')
    </header>

    <!-- Breadcrumb Section -->
    <section>
        @yield('breadcrumb')
    </section>

    <!-- Main Content Section -->
    <main class="container my-4">
        @yield('content')
    </main>

    <!-- Footer Section -->
    <footer class="bg-dark text-white p-3">
        <div class="container">
            <p class="text-center">&copy; {{ date('Y') }} BRICKSPOINT<sup style="font-size: 8pt">ERP</sup><sub style="font-size: 7pt">V 1.0</sub>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Core Scripts (moved from body to ensure proper loading order) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    @vite(['resources/js/app.js'])

    <!-- Yield additional scripts -->
    @yield('scripts')
</body>
</html>