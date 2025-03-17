@extends('maintenance::layouts.master')

@section('content')
<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm rounded-4">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">Create Maintenance Log</h4>
        </div>
        <div class="card-body">
          <form action="{{ route('maintenance.store') }}" method="POST">
            @csrf

            <!-- Location -->
            <div class="mb-3">
              <label for="location" class="form-label">Location</label>
              <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" required>
              @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Complaint Date & Time -->
            <div class="mb-3">
              <label for="complaint_datetime" class="form-label">Complaint Date &amp; Time</label>
              <input type="datetime-local" name="complaint_datetime" id="complaint_datetime" class="form-control @error('complaint_datetime') is-invalid @enderror" value="{{ old('complaint_datetime') }}" required>
              @error('complaint_datetime')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Nature of Complaint -->
            <div class="mb-3">
              <label for="nature_of_complaint" class="form-label">Nature of Complaint</label>
              <textarea name="nature_of_complaint" id="nature_of_complaint" rows="3" class="form-control @error('nature_of_complaint') is-invalid @enderror" required>{{ old('nature_of_complaint') }}</textarea>
              @error('nature_of_complaint')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Lodged By -->
            <div class="mb-3">
              <label for="lodged_by" class="form-label">Lodged By</label>
              <input type="text" name="lodged_by" id="lodged_by" class="form-control @error('lodged_by') is-invalid @enderror" value="{{ old('lodged_by') }}" required>
              @error('lodged_by')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Received By -->
            <div class="mb-3">
              <label for="received_by" class="form-label">Received By</label>
              <input type="text" name="received_by" id="received_by" class="form-control @error('received_by') is-invalid @enderror" value="{{ old('received_by') }}">
              @error('received_by')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Cost of Fixing -->
            <div class="mb-3">
              <label for="cost_of_fixing" class="form-label">Cost of Fixing</label>
              <input type="number" step="0.01" name="cost_of_fixing" id="cost_of_fixing" class="form-control @error('cost_of_fixing') is-invalid @enderror" value="{{ old('cost_of_fixing') }}">
              @error('cost_of_fixing')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Completion Date -->
            <div class="mb-3">
              <label for="completion_date" class="form-label">Completion Date</label>
              <input type="date" name="completion_date" id="completion_date" class="form-control @error('completion_date') is-invalid @enderror" value="{{ old('completion_date') }}">
              @error('completion_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Status -->
            <div class="mb-4">
              <label for="status" class="form-label">Status</label>
              <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="new" {{ old('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg">Submit</button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
