@extends('restaurant::layouts.master')
@section('title', 'Welcome')
@section('content')
<div class="landing-container py-5 px-3" style="background: linear-gradient(to right, #fff5f5, #ffecec 0.5);">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white animate__animated animate__fadeInDown">Welcome to Taste Restaurant</h1>
        <p class="lead text-white animate__animated animate__fadeInUp">Order online for delivery or pickup.</p>
    </div>

    <!-- Online ordering is the only flow the open web may start. Dine-in is the
         waiter's to take, and room service belongs to a checked-in guest. -->
    <div class="text-center mb-5">
        <a href="{{ route('restaurant.online.menu') }}" class="btn btn-danger btn-lg rounded-pill px-5 py-3 shadow-lg hover-card">Order Online Now</a>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-5 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Dine In 🪑</h3>
                    <p class="text-muted">Ask your server to place your order on the restaurant terminal.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Room Service 🛏️</h3>
                    <p class="text-muted">Already checked in? Order to your room from your guest dashboard.</p>
                    @auth
                        <a href="{{ route('guest.dashboard') }}" class="btn btn-danger w-100 rounded-pill">Go to Guest Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-danger w-100 rounded-pill">Sign In to Order</a>
                    @endauth
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Order History 📦</h3>
                    <p class="text-muted">View the orders placed in this session.</p>
                    {{-- The page is a plain form; the phone number is POSTed to a
                         session-scoped lookup, never placed in a shareable URL. --}}
                    <a href="{{ route('restaurant.online.orders') }}" class="btn btn-danger w-100 rounded-pill">View Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Animate.css for entrance animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endsection
