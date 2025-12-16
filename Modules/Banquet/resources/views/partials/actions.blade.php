<div class="dropdown">
    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="{{ route('banquet.orders.show', $order->order_id) }}"><i class="fas fa-eye me-2 text-primary"></i> View Details</a></li>
        @can('manage_banquet')
            <li><a class="dropdown-item" href="{{ route('banquet.orders.edit', $order->order_id) }}"><i class="fas fa-edit me-2 text-warning"></i> Edit Order</a></li>
            <li><a class="dropdown-item" href="{{ route('banquet.orders.pdf', $order->order_id) }}" target="_blank"><i class="fas fa-file-pdf me-2 text-success"></i> Function Sheet</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                {{-- CHANGE: Added data-url attribute --}}
                <button class="dropdown-item text-danger delete-order" 
                        data-order-id="{{ $order->order_id }}"
                        data-url="{{ route('banquet.orders.destroy', $order->order_id) }}">
                    <i class="fas fa-trash-alt me-2"></i> Delete Order
                </button>
            </li>
        @endcan
    </ul>
</div>