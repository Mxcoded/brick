<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="start_time">Start Time</label>
            <input type="time" name="start_time" id="start_time" class="form-control" 
                   value="{{ old('start_time', $day->start_time ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="end_time">End Time</label>
            <input type="time" name="end_time" id="end_time" class="form-control" 
                   value="{{ old('end_time', $day->end_time ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="duration_minutes">Duration (Minutes)</label>
            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control" 
                   value="{{ old('duration_minutes', $day->duration_minutes ?? '') }}" readonly>
        </div>
    </div>
</div>

<!-- Auto-calculate duration -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const durationInput = document.getElementById('duration_minutes');

        function calculateDuration() {
            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;

            if (startTime && endTime) {
                const start = new Date(`1970-01-01T${startTime}:00`);
                const end = new Date(`1970-01-01T${endTime}:00`);
                const duration = (end - start) / 1000 / 60; // Convert to minutes
                durationInput.value = duration;
            }
        }

        startTimeInput.addEventListener('change', calculateDuration);
        endTimeInput.addEventListener('change', calculateDuration);
    });
</script>