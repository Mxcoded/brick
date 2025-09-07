@extends('layouts.master')

@section('page-content')
<div class="container-fluid my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Apply Leave on Behalf of an Employee</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.leaves.admin.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="employee_id" class="form-label"><strong>Select Employee</strong></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                <option value="" disabled selected>-- Choose an employee --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->staff_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="leave_type" class="form-label"><strong>Leave Type</strong></label>
                            <select class="form-select @error('leave_type') is-invalid @enderror" id="leave_type" name="leave_type" required>
                                <option value="">-- Select leave type --</option>
                                <option value="Annual" {{ old('leave_type') == 'Annual' ? 'selected' : '' }}>Annual Leave</option>
                                <option value="Sick" {{ old('leave_type') == 'Sick' ? 'selected' : '' }}>Sick Leave</option>
                                <option value="Casual" {{ old('leave_type') == 'Casual' ? 'selected' : '' }}>Casual Leave</option>
                                <option value="Maternity" {{ old('leave_type') == 'Maternity' ? 'selected' : '' }}>Maternity Leave</option>
                                <option value="Paternity" {{ old('leave_type') == 'Paternity' ? 'selected' : '' }}>Paternity Leave</option>
                                <option value="Compassionate" {{ old('leave_type') == 'Compassionate' ? 'selected' : '' }}>Compassionate Leave</option>
                            </select>
                            @error('leave_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label"><strong>Start Date</strong></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label"><strong>End Date</strong></label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label"><strong>Reason for Leave</strong></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">Submit Leave Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection