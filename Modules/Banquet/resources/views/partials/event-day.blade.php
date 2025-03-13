<div class="day-section mt-3" id="day-${index}">
    <input type="hidden" name="event_days[${index}][event_status]" value="Pending">
    <div class="form-group">
        <label>Event Date</label>
        <input type="date" name="event_days[${index}][event_date]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Guest Count</label>
        <input type="number" name="event_days[${index}][guest_count]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Room</label>
        <input type="text" name="event_days[${index}][room]" class="form-control" required>
    </div>
    <div id="menu-container-${index}" class="menu-items"></div>
    <button type="button" onclick="addMenuItem(${index})" class="btn btn-secondary mt-2">Add Menu Item</button>
</div>