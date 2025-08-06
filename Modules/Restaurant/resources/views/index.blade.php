@extends('restaurant::layouts.master')
@section('title', 'Welcome')
@section('content')
<div class="landing-container py-5 px-3" style="background: linear-gradient(to right, #fff5f5, #ffecec 0.5);">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white animate__animated animate__fadeInDown">Welcome to Taste Restaurant</h1>
        <p class="lead text-white animate__animated animate__fadeInUp">Choose to dine in or order online for delivery.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-6 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Dine In 🪑</h3>
                    <p class="text-muted">Select a table to start your dining experience.</p>
                    <form action="{{ route('restaurant.select-table') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="table_id" class="form-label">Select Table</label>
                            <select name="table_id" id="table_id" class="form-select rounded-pill" required>
                                <option value="">Choose a table</option>
                                @foreach ($tables as $table)
                                    <option value="{{ $table->id }}">Table {{ $table->number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 rounded-pill">Select Table</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Order Online 📦</h3>
                    <p class="text-muted">Browse our menu and place an order for delivery.</p>
                    <a href="{{ route('restaurant.online.menu') }}" class="btn btn-danger w-100 rounded-pill">Order Now</a>
                </div>
            </div>
        </div>
         <div class="col-lg-6 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Order History 📦</h3>
                    <p class="text-muted">View your past orders and their statuses.</p>
                    <a href="{{ route('restaurant.online.orders') }}" class="btn btn-danger w-100 rounded-pill">View Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Animate.css for entrance animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    .hover-card {
        transition: all 0.3s ease-in-out;
    }
    .hover-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.2);
    }
    .btn-danger {
        background-color: #e74c3c;
        border-color: #e74c3c;
    }
    .btn-danger:hover {
        background-color: #c0392b;
        border-color: #c0392b;
    }
</style>
@endsection