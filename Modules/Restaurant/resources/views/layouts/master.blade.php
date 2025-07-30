<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-restaurant', 'resources/assets/sass/app.scss', storage_path('vite.hot')) }} --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .landing-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4');
            background-size: cover;
            background-position: center;
            color: white;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background-color: #d9534f;
            border-color: #d9534f;
        }

        .btn-primary:hover {
            background-color: #c9302c;
            border-color: #c9302c;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .lead {
            font-size: 1.2rem;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .content {
            margin-top: 20px;
        }
    </style>
</head>

<body>
@if(View::getSection('title') !== 'Welcome')
    <nav class="navbar navbar-expand-lg transparent bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/restaurant') }}">Taste Restaurant</a>
        </div>
    </nav>
    <div class="container-fluid content">
@endif
    
        
            @yield('content')
        </div>
    
        {{-- Vite JS --}}
        {{-- {{ module_vite('build-restaurant', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
