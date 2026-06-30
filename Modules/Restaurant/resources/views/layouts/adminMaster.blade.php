<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Restaurant') — {{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/sass/app.scss'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        body {
            background: #f5f7fa;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        :root {
            --primary-color: #d4af37;
            --primary-hover: #bfa133;
            --border-radius: 14px;
            --box-shadow: 0 6px 24px rgba(0, 0, 0, 0.06);
            --transition: all 0.25s ease-in-out;
        }
        .button {
            background-color: var(--primary-color);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }
        .button:hover { background-color: var(--primary-hover); color: #fff; }
        .card-header.bg-light { background: #eef0f2; border-bottom: 1px solid rgba(0,0,0,0.06); }
        .card-header.bg-light h3 { color: #1a1d23; }
        .admin-header {
            background: linear-gradient(135deg, #1a1d23 0%, #2d323b 100%);
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
        }
        .admin-header h5 { margin: 0; font-weight: 700; }
        .admin-header .nav-links { display: flex; gap: 0.5rem; align-items: center; }
        .admin-header .nav-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.85rem;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            transition: all 0.15s;
        }
        .admin-header .nav-links a:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .admin-header .nav-links a.active { color: #fff; background: rgba(255,255,255,0.15); }
        .admin-content { padding: 1.5rem; }
    </style>
    @yield('head')
</head>
<body>
    <div class="admin-header">
        <div class="d-flex align-items-center gap-3">
            <h5><i class="bi bi-egg-fried me-2"></i>Restaurant Management</h5>
        </div>
        <div class="nav-links">
            <a href="{{ route('restaurant.admin.dashboard') }}" class="{{ request()->routeIs('restaurant.admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
            <a href="{{ route('restaurant.admin.settings') }}" class="{{ request()->routeIs('restaurant.admin.settings') ? 'active' : '' }}">
                <i class="bi bi-gear me-1"></i>Settings
            </a>
            <a href="{{ route('restaurant.waiter.dashboard') }}" target="_blank">
                <i class="bi bi-calculator me-1"></i>POS
            </a>
            <span class="text-white-50">|</span>
            <a href="{{ route('website.home') }}">
                <i class="bi bi-house me-1"></i>Home
            </a>
        </div>
    </div>
    <main class="admin-content">
        @yield('content')
    </main>
    @vite(['resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
