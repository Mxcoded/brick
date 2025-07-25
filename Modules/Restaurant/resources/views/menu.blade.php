<h1>Fancy Menu - Table {{ $table }}</h1>
@foreach ($menuItems as $item)
    <div style="border: 1px solid #ccc; padding: 10px; margin: 10px;">
        <h2>{{ $item->name }} - ${{ $item->price }}</h2>
        <p>{{ $item->description }}</p>
        <form action="{{ url('/table/' . $table . '/cart/add') }}" method="POST">
            @csrf
            <input type="hidden" name="item_id" value="{{ $item->id }}">
            <label>Quantity: <input type="number" name="quantity" value="1" min="1"></label><br>
            <label>Instructions: <textarea name="instructions"></textarea></label><br>
            <button type="submit">Add to Cart</button>
        </form>
    </div>
@endforeach
<a href="{{ url('/table/' . $table . '/cart') }}">View Cart</a>