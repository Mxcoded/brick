{{-- @extends('restaurant::layouts.master')

@section('content')
<div class="text-center mb-4">
    <h1 class="display-4 fw-bold">{{$menuItems->restaurant_menu_categories->name}} Menu - Table {{ $table }}</h1>
    <p class="lead text-muted">Explore our delicious offerings and place your order!</p>
</div>

<div class="row">
    @foreach ($menuItems as $item)
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h2 class="card-title fw-bold">{{ $item->name }} <span class="float-end text-primary">&#8358; {{ number_format($item->price, 2) }}</span></h2>
                    <p class="card-text text-muted">{{ $item->description ?? 'No description available' }}</p>
                    <form action="{{ url('/table/' . $table . '/cart/add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <div class="mb-3">
                            <label for="quantity-{{ $item->id }}" class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="quantity-{{ $item->id }}" value="1" min="1" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="instructions-{{ $item->id }}" class="form-label">Special Instructions</label>
                            <textarea name="instructions" id="instructions-{{ $item->id }}" class="form-control" rows="3" placeholder="E.g., No onions"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="text-center mt-4">
    <a href="{{ url('/table/' . $table . '/cart') }}" class="btn btn-outline-primary btn-lg">View Cart</a>
</div>

<style>
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .card-title {
        font-size: 1.5rem;
    }
    .btn-primary {
        background-color: #d9534f;
        border-color: #d9534f;
    }
    .btn-primary:hover {
        background-color: #c9302c;
        border-color: #c9302c;
    }
</style>
@endsection --}}

@extends('restaurant::layouts.master')
@section('title', 'Menu')
@section('content')
    <div class="container-fluid content">
        <div class="text-center mb-4">
            <h1 class="display-4 fw-bold">{{ $categories->count() == 1 ? $categories->first()->name : 'Our Menu' }} - Table {{ $table }}</h1>
            <p class="lead text-muted">Explore our delicious offerings and place your order!</p>
        </div>

        {{-- @foreach ($categories as $category)
            <h2 class="mt-4 fw-bold">{{ $category->name }}</h2>
            <div class="row">
                @foreach ($category->menuItems as $item)
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h3 class="card-title fw-bold">{{ $item->name }} <span
                                        class="float-end text-primary">&#8358; {{ number_format($item->price, 2) }}</span>
                                </h3>
                                <p class="card-text text-muted">{{ $item->description ?? 'No description available' }}</p>
                                <form action="{{ url('/table/' . $table . '/cart/add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <div class="mb-3">
                                        <label for="quantity-{{ $item->id }}" class="form-label">Quantity</label>
                                        <input type="number" name="quantity" id="quantity-{{ $item->id }}"
                                            value="1" min="1" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="instructions-{{ $item->id }}" class="form-label">Special
                                            Instructions</label>
                                        <textarea name="instructions" id="instructions-{{ $item->id }}" class="form-control" rows="3"
                                            placeholder="E.g., No onions"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach --}}
        @foreach ($categories as $category)
    <h2 class="mt-4 fw-bold">{{ $category->name }}</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach ($category->menuItems as $item)
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <!-- Dish Image Placeholder -->
                    <img src="{{ asset('images/placeholder-dish.jpg') }}" class="card-img-top" alt="Dish image">

                    <div class="card-body">
                        <h3 class="card-title fw-bold">
                            {{ $item->name }}
                            <span class="float-end text-primary">&#8358; {{ number_format($item->price, 2) }}</span>
                        </h3>
                        <p class="card-text text-muted">
                            {{ $item->description ?? 'No description available' }}
                        </p>

                        <form action="{{ route('restaurant.cart.add', $table) }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">

                            <div class="mb-3">
                                <label for="quantity-{{ $item->id }}" class="form-label">Quantity</label>
                                <input type="number" name="quantity" id="quantity-{{ $item->id }}"
                                       value="1" min="1" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="instructions-{{ $item->id }}" class="form-label">Special Instructions</label>
                                <textarea name="instructions" id="instructions-{{ $item->id }}"
                                          class="form-control" rows="3"
                                          placeholder="E.g., No onions"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

        <div class="text-center mt-4">
            <a href="{{ route('restaurant.cart', $table) }}" class="btn btn-outline-primary btn-lg">View Cart</a>
        </div>
    </div>
    <style>
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            font-size: 1.5rem;
        }

        .btn-primary {
            background-color: #d9534f;
            border-color: #d9534f;
        }

        .btn-primary:hover {
            background-color: #c9302c;
            border-color: #c9302c;
        }
    </style>
@endsection
