@extends('layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Add Menu Item(s)</li>
@endsection

@section('page-content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold display-5 text-primary">
            <i class="fas fa-utensils me-3"></i>Add Menu Item for Order #{{ $order->order_id }}
            <div class="h6 text-muted mt-2">{{ $day->event_date->format('F j, Y') }}</div>
        </h1>
        <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Menu Item Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('banquet.orders.store-menu-item', [$order->order_id, $day->id]) }}" method="POST">
                @csrf

                <!-- Meal Type -->
                <div class="mb-4">
                    <label for="meal_type" class="form-label fw-bold text-muted">Meal Type</label>
                    <select name="meal_type" id="meal_type" class="form-select" required>
                        <option value="">Select Meal Type</option>
                        @foreach ($mealTypes as $type)
                            <option value="{{ $type }}" {{ old('meal_type') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('meal_type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Menu Items -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-muted">Menu Items</label>
                    <div id="menu-items-container" class="mb-3">
                        @php $menuItems = old('menu_items', ['']) @endphp
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
                            <button type="button" class="btn btn-danger remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-menu-item" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Add Another Item
                    </button>
                    @error('menu_items')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('menu_items.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Pricing Section -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="number" name="quantity" id="quantity" 
                                   class="form-control" 
                                   value="{{ old('quantity') }}" 
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
                                   value="{{ old('unit_price') }}" 
                                   step="0.01" 
                                   min="0" 
                                   required>
                            <label for="unit_price" class="text-muted">Unit Price (&#8358;)</label>
                            @error('unit_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-calculator me-2"></i>
                            Total Price: &#8358;<span id="total-price">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Dietary Restrictions -->
                <div class="mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-allergies me-2"></i>Dietary Restrictions (Optional)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" 
                                               name="dietary_restrictions[]" 
                                               value="Vegetarian" 
                                               id="veg"
                                               {{ in_array('Vegetarian', old('dietary_restrictions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="veg">
                                            <i class="fas fa-leaf me-2 text-success"></i>Vegetarian
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" 
                                               name="dietary_restrictions[]" 
                                               value="Gluten-Free" 
                                               id="gf"
                                               {{ in_array('Gluten-Free', old('dietary_restrictions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="gf">
                                            <i class="fas fa-bread-slice me-2 text-warning"></i>Gluten-Free
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" 
                                               name="dietary_restrictions[]" 
                                               value="Nut-Free" 
                                               id="nf"
                                               {{ in_array('Nut-Free', old('dietary_restrictions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="nf">
                                            <i class="fas fa-peanut me-2 text-danger"></i>Nut-Free
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('dietary_restrictions')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Menu Item
                    </button>
                    <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Add Menu Item
    $('#add-menu-item').click(function() {
        const newItem = `
            <div class="input-group mb-2">
                <input type="text" name="menu_items[]" 
                       class="form-control" 
                       placeholder="Enter menu item"
                       required>
                <button type="button" class="btn btn-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
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
        $('#total-price').text((quantity * unitPrice).toFixed(2));
    }

    $('#quantity, #unit_price').on('input', calculateTotal);
    calculateTotal(); // Initial calculation
});
</script>
@endsection
