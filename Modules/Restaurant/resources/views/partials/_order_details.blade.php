<div class="mt-2">
    <table class="table table-sm table-borderless">
        <thead class="table-light">
            <tr>
                <th scope="col">Item</th>
                <th scope="col" class="text-center">Qty</th>
                <th scope="col" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $item)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $item->menuItem && $item->menuItem->image ? asset('storage/' . $item->menuItem->image) : 'https://via.placeholder.com/50x50?text=No+Image' }}"
                                 alt="{{ $item->menuItem->name ?? 'Item' }}" class="img-fluid rounded me-2"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <span class="fw-bold">{{ $item->menuItem->name ?? 'Item not found' }}</span>
                                @if ($item->instructions)
                                    <small class="text-muted d-block fst-italic">"{{ $item->instructions }}"</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $item->menuItem ? '₦' . number_format($item->menuItem->price * $item->quantity, 2) : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold border-top">
                <td colspan="2" class="text-end">Grand Total:</td>
                <td class="text-end h5 mb-0 text-success">{{ '₦' . number_format($order->orderItems->sum(function($item) {
                    return $item->menuItem ? $item->menuItem->price * $item->quantity : 0;
                }), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>