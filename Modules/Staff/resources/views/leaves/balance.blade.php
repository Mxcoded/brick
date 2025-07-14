@extends('staff::layouts.master')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">Leave Balance</h1>
        <div class="card shadow-sm">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.leaves.balance-submit') }}">
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
                            <label for="staff_code" class="form-label">Staff Code</label>
                            <input type="number" name="staff_code" id="staff_code" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="total_days" class="form-label">Total Days</label>
                            <input type="number" name="total_days" id="total_days" class="form-control" required>
                        </div>
                        
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Submit Balance</button>
                        <a href="{{ route('staff.leaves.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection