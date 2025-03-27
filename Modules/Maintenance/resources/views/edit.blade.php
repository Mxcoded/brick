@extends('maintenance::layouts.master')
@section('content')
    <h1>Edit Maintenance Log</h1>
    <form action="{{ route('maintenance.update', $maintenanceLog->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                value="{{ old('location', $maintenanceLog->location) }}" required>
            @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="complaint_datetime">Complaint Date & Time</label>
            <input type="datetime-local" name="complaint_datetime"
                class="form-control @error('complaint_datetime') is-invalid @enderror"
                value="{{ old('complaint_datetime', $maintenanceLog->complaint_datetime->format('Y-m-d\TH:i')) }}" required>
            @error('complaint_datetime')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="nature_of_complaint">Nature of Complaint</label>
            <textarea name="nature_of_complaint" class="form-control @error('nature_of_complaint') is-invalid @enderror" required>{{ old('nature_of_complaint', $maintenanceLog->nature_of_complaint) }}</textarea>
            @error('nature_of_complaint')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="lodged_by">Lodged By</label>
            <input type="text" name="lodged_by" class="form-control @error('lodged_by') is-invalid @enderror"
                value="{{ old('lodged_by', $maintenanceLog->lodged_by) }}" required>
            @error('lodged_by')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="received_by">Received By</label>
            <input type="text" name="received_by" class="form-control @error('received_by') is-invalid @enderror"
                value="{{ old('received_by', $maintenanceLog->received_by) }}">
            @error('received_by')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="cost_of_fixing">Cost of Fixing</label>
            <input type="number" step="0.01" name="cost_of_fixing"
                class="form-control @error('cost_of_fixing') is-invalid @enderror"
                value="{{ old('cost_of_fixing', $maintenanceLog->cost_of_fixing) }}">
            @error('cost_of_fixing')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="completion_date">Completion Date</label>
            <input type="date" name="completion_date" class="form-control @error('completion_date') is-invalid @enderror"
                value="{{ old('completion_date', optional($maintenanceLog->completion_date)->format('Y-m-d')) }}">
            @error('completion_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                <option value="new" {{ old('status', $maintenanceLog->status) == 'new' ? 'selected' : '' }}>New</option>
                <option value="in_progress"
                    {{ old('status', $maintenanceLog->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ old('status', $maintenanceLog->status) == 'completed' ? 'selected' : '' }}>
                    Completed</option>
                <option value="cancelled" {{ old('status', $maintenanceLog->status) == 'cancelled' ? 'selected' : '' }}>
                    Cancelled</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
