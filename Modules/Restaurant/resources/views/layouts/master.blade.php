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
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

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
            margin: auto;
        }
    </style>


</head>

<body>
    
    @if (View::getSection('title') !== 'Welcome')
        
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/restaurant') }}">Taste Restaurant</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="nav justify-content-center m-auto my-2 my-lg-0" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                    </li>
                    <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Link
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link disabled" aria-disabled="true"></a>
                    </li>
                </ul>
                <div class="d-flex" role="search">
                    <button class="btn btn-outline-danger position-relative" type="button" data-bs-toggle="modal" data-bs-target="#cartModal">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <span class="text-bg-danger position-absolute start-60 " id="cart-number" style="width: 20px; height auto; border-radius: 10px; top: -10px; font-size: 11px; padding: 2px; display: none; right: -10px;"></span>
                    </button>
                </div>
                </div>
            </div>
        </nav>
    @endif
            

    @yield('content')
    </div>

    {{-- Vite JS --}}
    {{-- {{ module_vite('build-restaurant', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}
       <!-- Load Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.12.0/dist/cdn.min.js"></script>
  
  <!-- Load persist plugin -->
  <script defer src="https://unpkg.com/@alpinejs/persist@3.12.0/dist/cdn.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')


</body>

</html>
