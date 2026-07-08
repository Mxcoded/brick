@extends('layouts.master')

@section('title', 'Create Purchase Order')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Create Purchase Order</h1>
            <p class="text-muted mb-0">Create a new order to procure items from a supplier</p>
        </div>
        <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('inventory.purchase-orders.store') }}">
        @csrf
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Order Details</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">PO Number</label>
                        <input type="text" class="form-control bg-light" name="po_number" value="{{ $poNumber }}" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Supplier</label>
                        <select class="form-select" name="supplier_id" required>
                            <option value="">Select supplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Store (Destination)</label>
                        <select class="form-select" name="store_id" required>
                            <option value="">Select store...</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Notes</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Optional notes or reference..."></textarea>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Order Items</h5>
                <button type="button" class="btn btn-success btn-sm rounded-pill" id="addItemBtn">
                    <i class="fas fa-plus me-1"></i>Add Item
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="min-width:250px;">Item</th>
                                <th class="text-center" style="width:120px;">Quantity</th>
                                <th class="text-center" style="width:140px;">Unit Price</th>
                                <th class="text-end pe-4" style="width:140px;">Subtotal</th>
                                <th class="text-center" style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td class="ps-4">
                                    <select class="form-select item-select" name="items[0][item_id]" required>
                                        <option value="">Select item...</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->id }}" data-price="{{ $item->price ?? 0 }}">{{ $item->description }} @if($item->category)({{ $item->category }})@endif</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="number" class="form-control text-center item-qty" name="items[0][quantity_ordered]" min="1" required placeholder="Qty">
                                </td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" class="form-control item-price" name="items[0][unit_price]" step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                </td>
                                <td class="text-end pe-4 item-subtotal fw-bold">₦0.00</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item border-0" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end ps-4">Total:</th>
                                <th class="text-end pe-4" id="grandTotal">₦0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <button type="submit" class="btn btn-success shadow-sm px-4">
                <i class="fas fa-save me-2"></i>Create Purchase Order
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let rowIndex = 1;

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td class="ps-4">
                <select class="form-select item-select" name="items[\${rowIndex}][item_id]" required>
                    <option value="">Select item...</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" data-price="{{ $item->price ?? 0 }}">{{ $item->description }} @if($item->category)({{ $item->category }})@endif</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <input type="number" class="form-control text-center item-qty" name="items[\${rowIndex}][quantity_ordered]" min="1" required placeholder="Qty">
            </td>
            <td class="text-center">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₦</span>
                    <input type="number" class="form-control item-price" name="items[\${rowIndex}][unit_price]" step="0.01" min="0" required placeholder="0.00">
                </div>
            </td>
            <td class="text-end pe-4 item-subtotal fw-bold">₦0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item border-0">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        rowIndex++;
        attachRowEvents(row);
    });

    function attachRowEvents(row) {
        row.querySelector('.item-select').addEventListener('change', function() {
            const price = this.options[this.selectedIndex]?.dataset?.price || 0;
            const priceInput = row.querySelector('.item-price');
            if (price > 0 && !priceInput.value) {
                priceInput.value = price;
            }
            calcRow(row);
        });
        row.querySelector('.item-qty').addEventListener('input', () => calcRow(row));
        row.querySelector('.item-price').addEventListener('input', () => calcRow(row));
        row.querySelector('.remove-item').addEventListener('click', function() {
            row.remove();
            calcGrandTotal();
        });
    }

    function calcRow(row) {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        row.querySelector('.item-subtotal').textContent = '₦' + (qty * price).toFixed(2);
        calcGrandTotal();
    }

    function calcGrandTotal() {
        let total = 0;
        document.querySelectorAll('.item-subtotal').forEach(el => {
            total += parseFloat(el.textContent.replace(/[₦,]/g, '')) || 0;
        });
        document.getElementById('grandTotal').textContent = '₦' + total.toFixed(2);
    }

    document.querySelectorAll('.item-row').forEach(row => attachRowEvents(row));
</script>
@endsection
