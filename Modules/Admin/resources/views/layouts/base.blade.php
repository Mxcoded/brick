<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Staff Module - {{ config('app.name', 'Staff Management') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            <p>&copy; {{ date('Y') }} Staff Module. All rights reserved.</p>
        </div>
    </footer>

    <!-- Core Scripts (moved from body to ensure proper loading order) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Yield additional scripts -->
    @yield('scripts')
</body>
</html>