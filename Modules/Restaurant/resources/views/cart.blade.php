@extends('restaurant::layouts.master')

@section('content')
<h1>Your Cart - Table {{ $table }}</h1>
@if (empty($cart))
    <p>Your cart is empty.</p>
@else
    @foreach ($cart as $cartItem)
        <div>
            <h3>{{ $items[$cartItem['item_id']]->name }}</h3>
            <p>Quantity: {{ $cartItem['quantity'] }}</p>
            <p>Instructions: {{ $cartItem['instructions'] ?: 'None' }}</p>
        </div>
    @endforeach
    <form action="{{ url('/table/' . $table . '/order/submit') }}" method="POST">
        @csrf
        <button type="submit">Place Order</button>
    </form>
@endif
<a href="{{ route('restaurant.menu', $table) }}">Back to Menu</a>

@endsection