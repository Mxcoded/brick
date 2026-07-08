<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Admin - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/sass/app.scss'])
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: #333;
            padding: 10px 20px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #e9ecef;
            color: #007bff;
        }

        .content {
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
      <div class="sidebar col-md-2">
    <h4 class="text-center mb-4">Website Admin</h4>
    <ul class="nav flex-column">
        @can('access_website_dashboard')
        <li class="nav-item">
            <a class="nav-link {{ Route::is('website.admin.dashboard') ? 'active' : '' }}"
                href="{{ route('website.admin.dashboard') }}">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('website.admin.rooms.index') }}"><i class="fas fa-bed me-2"></i>
                Rooms</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('website.admin.amenities.index') }}">
                <i class="fas fa-concierge-bell me-2"></i> Amenities
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('website.admin.bookings.index') }}">
                <i class="fas fa-book me-2"></i> Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('website.admin.contact-messages.index') }}">
                <i class="fas fa-envelope me-2"></i> Contact Messages
            </a>
        </li>
        @endcan

        @can('manage_settings')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('website.admin.settings.index') }}">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </li>
        @endcan
    </ul>
</div>
        <!-- Content -->
        <div class="content col-md-10">
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand">Hotel Management</span>
                    <div class="navbar-nav ms-auto">
                        <a class="nav-link" href="{{ route('website.home') }}">Back to Website</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </div>
                </div>
            </nav>
            @yield('content')
        </div>
    </div>
    @vite(['resources/js/app.js'])
    <!-- In layouts/admin.blade.php -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            const valueField = document.getElementById('value-field');
            const imageField = document.getElementById('image-field');
            const videoField = document.getElementById('video-field');
            function toggleFields() {
                valueField.style.display = typeSelect.value === 'string' || typeSelect.value === 'json' ? 'block' : 'none';
                imageField.style.display = typeSelect.value === 'image' ? 'block' : 'none';
                videoField.style.display = typeSelect.value === 'video' ? 'block' : 'none';
            }
            typeSelect.addEventListener('change', toggleFields);
            toggleFields();
        }
    });
</script>
</body>

</html>
