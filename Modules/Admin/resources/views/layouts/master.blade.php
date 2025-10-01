@extends('admin::layouts.base')

@section('header')
    <!-- Navbar -->
    @include('admin::layouts.navbar')
    {{-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <!-- Brand Logo -->
            <a class="navbar-brand fw-bold" href="#">BRICKSPOINT</a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Left Side Links -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.leaves.index') ? 'active' : '' }} disabled"
                            href="{{ route('staff.leaves.index') }}">
                            <i class="fas fa-calendar-alt me-2"></i> My Leaves
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}"
                            href="{{ route('tasks.index') }}">
                            <i class="fa fa-list-alt me-1"></i> Tasks
                        </a>
                    </li>
                    @can('staff-view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('staff.index') ? 'active' : '' }}"
                                href="{{ route('staff.index') }}">
                                <i class="fa fa-users me-1"></i> Staff list
                            </a>
                        </li>
                    @endcan

                    @can('manage-user')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}"
                                href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users me-1"></i> Manage Users
                            </a>
                        </li>
                    @endcan

                    @can('manage-roles-permission')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}"
                                href="{{ route('admin.permissions.index') }}">
                                <i class="fas fa-key me-1"></i> Manage Permissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}"
                                href="{{ route('admin.roles.index') }}">
                                <i class="fas fa-user-tag me-1"></i> Manage Roles
                            </a>
                        </li>
                    @endcan

                </ul>

                <!-- Right Side Links (Auth Dropdown) -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    @auth
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-2"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i
                                            class="fas fa-user-alt me-2"></i>My Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                <li>
                                    <a class="dropdown-item" href="{{ route('staff.leaves.index') }}">
                                        <i class="fas fa-calendar-alt me-2"></i> My Leaves
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <!-- Login Link -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav> --}}
@endsection

@section('breadcrumb')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="bg-light py-3 shadow-sm">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                @yield('current-breadcrumb')
            </ol>
        </div>
    </nav>
@endsection

@section('content')
    <!-- Main Content -->
    <div class="container my-5">
        @yield('page-content')
    </div>
@endsection

@section('scripts')
    <!-- FontAwesome Icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <!-- Page-Specific Scripts -->
    @yield('page-scripts')
@endsection
