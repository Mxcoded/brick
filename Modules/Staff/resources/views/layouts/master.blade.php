@extends('staff::layouts.base')

@section('header')
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">BRICKSPOINT</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-pills">
                    <li class="nav-item">
                        @if (Auth::user()->hasRole('staff'))
                            <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
                                href="{{ route('staff.dashboard') }}">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        @elseif (Auth::user()->hasRole('admin'))
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        @endif
                    </li>
                    <li class="nav-item ">
                        
                        <a class="nav-link {{ request()->routeIs('staff.leaves.index') ? 'active' : '' }} disabled"
                            href="{{ route('staff.leaves.index') }}"><i class="fas fa-calendar-alt me-2"></i> My Leaves
                            
                        </a>
                       
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}"
                            href="{{ route('tasks.index') }}">
                            <i class="fa fa-list-alt me-1"></i> Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.index') ? 'active' : '' }}"
                            href="{{ route('staff.index') }}">
                            <i class="fa fa-users me-1"></i> Staff list
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i
                                            class="fas fa-user-alt me-2"></i>My Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                <li><a class="dropdown-item" href="{{ route('staff.leaves.index') }}"><i
                                            class="fas fa-calendar-alt me-2"></i> My Leaves</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>
                                            Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb" class="bg-light py-2">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Home</a></li>
                @yield('current-breadcrumb')
            </ol>
        </div>
    </nav>
@endsection

@section('content')
    @yield('page-content')
@endsection

@section('scripts')
    @yield('page-scripts')
@endsection
