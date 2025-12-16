@extends('layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.orders.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.orders.show', $order->order_id) }}">Order #{{ $order->order_id }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Update Menu Item</li>
@endsection

@section('page-content')
<div class="container-fluid my-4 banquet-theme">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold display-5 text-charcoal">
            <i class="fas fa-edit me-3 text-gold"></i>Edit Menu Item
            <div class="h6 text-muted mt-2 fw-normal"><i class="fas fa-calendar-alt me-1"></i> {{ $day->event_date->format('F j, Y') }}</div>
        </h1>
        <a href="{{ route('banquet.orders.show', $order->order_id) }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to Order
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-gold text-white py-3">
            <h5 class="card-title mb-0"><i class="fas fa-utensils me-2"></i>Menu Item Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('banquet.orders.update-menu-item', [$order->order_id, $day->id, $menuItem->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="meal_type" class="form-label fw-bold text-charcoal">Meal Type</label>
                    <select name="meal_type" id="meal_type" class="form-select" required>
                        <option value="">Select Meal Type</option>
                        @foreach ($mealTypes as $type)
                            <option value="{{ $type }}" {{ old('meal_type', $menuItem->meal_type ?? '') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('meal_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-charcoal">Menu Items</label>
                    <div id="menu-items-container" class="mb-3">
                        @php
                            $menuItems = old('menu_items', $menuItem ? json_decode($menuItem->menu_items, true) : ['']);
                        @endphp
                        @foreach($menuItems as $index => $item)
                        <div class="input-group mb-2">
                            <input type="text" name="menu_items[]"
                                   class="form-control"
                                   value="{{ $item }}"
                                   placeholder="Enter menu item"
                                   required>
                            @if($loop->first)
                            <button type="button" class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-lock"></i>
                            </button>
                            @else
                            <button type="button" class="btn btn-outline-danger remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-menu-item" class="btn btn-outline-gold">
                        <i class="fas fa-plus me-2"></i>Add Another Item
                    </button>
                    @error('menu_items')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('menu_items.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" name="quantity" id="quantity"
                                   class="form-control"
                                   value="{{ old('quantity', $menuItem->quantity ?? '') }}"
                                   min="1"
                                   required>
                            <label for="quantity" class="text-muted">Quantity</label>
                            @error('quantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" name="unit_price" id="unit_price"
                                   class="form-control"
                                   value="{{ old('unit_price', $menuItem->unit_price ?? '') }}"
                                   step="0.01"
                                   min="0"
                                   required>
                            <label for="unit_price" class="text-muted">Unit Price (₦)</label>
                            @error('unit_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-light border shadow-sm mb-0">
                            <i class="fas fa-coins me-2 text-gold"></i>
                            <strong>Total Price:</strong> ₦<span id="total-price" class="fw-bold">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="mb-3 text-charcoal"><i class="fas fa-allergies me-2 text-gold"></i>Dietary Restrictions (Optional)</h6>
                            <div class="row g-3">
                                @php
                                    $dietaryRestrictions = old('dietary_restrictions', $menuItem ? json_decode($menuItem->dietary_restrictions, true) : []);
                                @endphp
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="dietary_restrictions[]" value="Vegetarian" id="veg"
                                               {{ in_array('Vegetarian', $dietaryRestrictions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="veg"><i class="fas fa-leaf me-1 text-success"></i> Vegetarian</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="dietary_restrictions[]" value="Gluten-Free" id="gf"
                                               {{ in_array('Gluten-Free', $dietaryRestrictions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="gf"><i class="fas fa-bread-slice me-1 text-warning"></i> Gluten-Free</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="dietary_restrictions[]" value="Nut-Free" id="nf"
                                               {{ in_array('Nut-Free', $dietaryRestrictions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="nf"><i class="fas fa-peanut me-1 text-danger"></i> Nut-Free</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('banquet.orders.show', $order->order_id) }}" class="btn btn-outline-charcoal btn-lg">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-gold btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i>Update Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Add Menu Item
    $('#add-menu-item').click(function() {
        const newItem = `
            <div class="input-group mb-2">
                <input type="text" name="menu_items[]" class="form-control" placeholder="Enter menu item" required>
                <button type="button" class="btn btn-outline-danger remove-item"><i class="fas fa-trash"></i></button>
            </div>`;
        $('#menu-items-container').append(newItem);
    });

    // Remove Menu Item
    $(document).on('click', '.remove-item', function() {
        if ($('#menu-items-container .input-group').length > 1) {
            $(this).closest('.input-group').remove();
        }
    });

    // Price Calculator
    function calculateTotal() {
        const quantity = parseFloat($('#quantity').val()) || 0;
        const unitPrice = parseFloat($('#unit_price').val()) || 0;
        $('#total-price').text(new Intl.NumberFormat().format((quantity * unitPrice).toFixed(2)));
    }

    $('#quantity, #unit_price').on('input', calculateTotal);
    calculateTotal();
});
</script>
@endsection

<style>
    .banquet-theme { font-family: 'Proxima Nova', Arial, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: white; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: white; }
    .btn-outline-gold { color: #C8A165; border-color: #C8A165; }
    .btn-outline-gold:hover { background-color: #C8A165; color: white; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: white; }
    .form-control:focus, .form-select:focus { border-color: #C8A165; box-shadow: 0 0 0 0.25rem rgba(200, 161, 101, 0.25); }
</style>
@endsection