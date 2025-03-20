@extends('staff::layouts.master')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">Request Leave</h1>
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('staff.leaves.submit') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="leave_type" class="form-label">Leave Type</label>
                            <select name="leave_type" id="leave_type" class="form-select" required>
                                <option value="" disabled selected>Select Leave Type</option>
                                <option value="Vacation">Vacation</option>
                                <option value="Sick">Sick</option>
                                <option value="Maternity">Maternity</option>
                            </select>
                            @error('leave_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="e.g. Medical appointment"></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                        <a href="{{ route('staff.leaves.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection