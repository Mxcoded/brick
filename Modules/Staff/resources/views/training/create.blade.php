@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">HR Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.training.index') }}">Training Records</a></li>
    <li class="breadcrumb-item active">New Record</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="mb-1">New Training Record</h1>
            <p class="text-muted mb-0">Log a course, training, or certification</p>
        </div>
        <a href="{{ route('staff.training.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.training.store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">Select Employee</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->department ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="course_name" class="form-label">Course Name <span class="text-danger">*</span></label>
                    <input type="text" name="course_name" id="course_name" class="form-control @error('course_name') is-invalid @enderror"
                           value="{{ old('course_name') }}" placeholder="e.g., Advanced Leadership Program" required>
                    @error('course_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="provider" class="form-label">Provider</label>
                    <input type="text" name="provider" id="provider" class="form-control @error('provider') is-invalid @enderror"
                           value="{{ old('provider') }}" placeholder="e.g., Cornell University">
                    @error('provider')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="training_type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="training_type" id="training_type" class="form-select @error('training_type') is-invalid @enderror" required>
                        <option value="internal" {{ old('training_type') === 'internal' ? 'selected' : '' }}>Internal</option>
                        <option value="external" {{ old('training_type') === 'external' ? 'selected' : '' }}>External</option>
                        <option value="certification" {{ old('training_type') === 'certification' ? 'selected' : '' }}>Certification</option>
                    </select>
                    @error('training_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="enrolled" {{ old('status') === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                        <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date') }}">
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="duration_hours" class="form-label">Duration (hours)</label>
                    <input type="number" name="duration_hours" id="duration_hours" step="0.1" min="0"
                           class="form-control @error('duration_hours') is-invalid @enderror"
                           value="{{ old('duration_hours') }}">
                    @error('duration_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="expiry_date" class="form-label">Certification Expiry</label>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                           value="{{ old('expiry_date') }}">
                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="certification_name" class="form-label">Certification Name</label>
                    <input type="text" name="certification_name" id="certification_name" class="form-control @error('certification_name') is-invalid @enderror"
                           value="{{ old('certification_name') }}" placeholder="e.g., PMP Certificate">
                    @error('certification_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="certification_url" class="form-label">Certification URL</label>
                    <input type="url" name="certification_url" id="certification_url" class="form-control @error('certification_url') is-invalid @enderror"
                           value="{{ old('certification_url') }}" placeholder="https://">
                    @error('certification_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Record
                </button>
                <a href="{{ route('staff.training.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>

</div>
@endsection
