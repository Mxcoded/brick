@extends('restaurant::layouts.master')
@section('title', 'Welcome')
@section('content')
<div class="landing-container py-5 px-3" style="background: linear-gradient(to right, #fff5f5, #ffecec 0.5);">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white animate__animated animate__fadeInDown">Welcome to Taste Restaurant</h1>
        <p class="lead text-white animate__animated animate__fadeInUp">Choose to dine in, order online for delivery, or view your order history.</p>
    </div>

    <!-- Direct Online Menu Button -->
    <div class="text-center mb-5">
        <a href="{{ route('restaurant.online.menu') }}" class="btn btn-danger btn-lg rounded-pill px-5 py-3 shadow-lg hover-card">Order Online Now</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Dine In 🪑</h3>
                    <p class="text-muted">Select a table to start your dining experience.</p>
                    <form action="{{ route('restaurant.select-source') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="table">
                        <div class="mb-3">
                            <label for="table_id" class="form-label">Select Table</label>
                            <select name="source_id" id="table_id" class="form-select rounded-pill @error('source_id') is-invalid @enderror" required>
                                <option value="">Choose a table</option>
                                @foreach ($sources['table']['items'] as $table)
                                    <option value="{{ $table['id'] }}">Table {{ $table['number'] }}</option>
                                @endforeach
                            </select>
                            @error('source_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if (empty($sources['table']['items']))
                                <small class="text-muted">No tables available.</small>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-danger w-100 rounded-pill" @if (empty($sources['table']['items'])) disabled @endif>Select Table</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="card h-100 shadow-lg border-0 rounded-4 text-center hover-card">
                <div class="card-body">
                    <h3 class="card-title fw-bold">Room Service 🛏️</h3>
                    <p class="text-muted">Select a room to start your service.</p>
                    <form action="{{ route('restaurant.select-source') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="room">
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Select Room</label>
                            <select name="source_id" id="room_id" class="form-select rounded-pill @error('source_id') is-invalid @enderror" required>
                                <option value="">Choose a Room</option>
                                @foreach ($sources['room']['items'] as $room)
                                    <option value="{{ $room['id'] }}"> {{ $room['name'] }}</option>
                                @endforeach
                            </select>
                            @error('source_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if (empty($sources['room']['items']))
                                <small class="text-muted">No rooms available.</small>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-danger w-100 rounded-pill" @if (empty($sources['room']['items'])) disabled @endif>Select Room</button>
                    </form>
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
@endsection