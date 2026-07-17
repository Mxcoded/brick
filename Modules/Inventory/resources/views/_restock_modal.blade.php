@php $stores = $stores ?? \Modules\Inventory\Models\Store::all(); @endphp
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restockModalLabel">Restock Item: <span id="restockItemName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restockForm" method="POST" action="{{ route('inventory.items.restock') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="restockItemId">
                    <div class="mb-3">
                        <label for="restock_store_id" class="form-label">Store</label>
                        <select class="form-select" id="restock_store_id" name="store_id" required>
                            <option value="">Select a store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="restock_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="restock_quantity" name="quantity" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="restock_lot_number" class="form-label">Lot Number</label>
                        <input type="text" class="form-control" id="restock_lot_number" name="lot_number" placeholder="Auto-generated if empty">
                    </div>
                    <div class="mb-3">
                        <label for="restock_expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="restock_expiry_date" name="expiry_date">
                    </div>
                    <div id="restockAlertContainer" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Restock</button>
                </div>
            </form>
        </div>
    </div>
</div>
