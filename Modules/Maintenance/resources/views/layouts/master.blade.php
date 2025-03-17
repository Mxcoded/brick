<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Maintenance Module - {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome CSS (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
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
        .text-gradient {
            background: linear-gradient(45deg, #0d6efd, #00b4d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    {{-- Vite JS --}}
    {{-- {{ module_vite('build-maintenance', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}
</body>
</html>
