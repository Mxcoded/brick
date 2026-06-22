<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', 'Inventory') — {{ config('app.name', 'BRICKSPOINT ERP') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <meta name="description" content="@yield('meta_description', config('app.name', 'BRICKSPOINT ERP') . ' — Inventory Management')">
    <meta name="author" content="{{ config('app.name') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-inventory', 'resources/assets/sass/app.scss', storage_path('vite.hot')) }} --}}
</head>

<body>
    @yield('content')

    {{-- Vite JS --}}
    {{-- {{ module_vite('build-inventory', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}
</body>
