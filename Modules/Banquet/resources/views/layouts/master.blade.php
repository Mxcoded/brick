<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Banquet Event Module - {{ config('app.name', 'Event Management') }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- In the <head> section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">



    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-staff', 'resources/assets/sass/app.scss', storage_path('vite.hot')) }} --}}
    <style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
    }
.card {
    border-radius: 0.75rem;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.form-floating > label {
    color: #6c757d;
    padding: 0.5rem 1rem;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}

.input-group .btn {
    border-radius: 0 0.5rem 0.5rem 0;
}

.card {
    border-radius: 0.75rem;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.15em;
}

.invalid-feedback {
    font-size: 0.85rem;
}

.alert {
    border-radius: 0.5rem;
}

</style>

</head>

<body>
  <div class="container mt-4">
   
    @yield('content')
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><!-- Add your JavaScript files here -->
    @yield('scripts') <!-- This is where your scripts will be injected -->


</body>
