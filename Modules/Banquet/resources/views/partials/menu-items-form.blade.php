<div class="menu-item mt-2">
    <div class="form-group">
        <label>Meal Type</label>
        <input type="text" name="event_days[${dayIndex}][menu_items][${menuIndex}][meal_type]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="event_days[${dayIndex}][menu_items][${menuIndex}][quantity]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Unit Price</label>
        <input type="number" step="0.01" name="event_days[${dayIndex}][menu_items][${menuIndex}][unit_price]" class="form-control" required>
    </div>
</div>