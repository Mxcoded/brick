<h1>Pending Orders</h1>
@foreach ($orders as $order)
    <div style="border: 1px solid #ccc; padding: 10px; margin: 10px;">
        <h2>Table {{ $order->table->number }} - Order #{{ $order->id }}</h2>
        @foreach ($order->orderItems as $item)
            <p>{{ $item->menuItem->name }} (x{{ $item->quantity }}) - {{ $item->instructions ?: 'No instructions' }}</p>
        @endforeach
        <form action="{{ url('/waiter/orders/' . $order->id . '/accept') }}" method="POST">
            @csrf
            <button type="submit">Accept Order</button>
        </form>
    </div>
@endforeach