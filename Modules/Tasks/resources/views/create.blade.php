@extends('layouts.master')

@section('title', 'Create Task')
@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Task</li>
@endsection

@section('styles')
<style>
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    .form-check-label { cursor: pointer; }
</style>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-plus-circle me-2"></i>Create Task</h3>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select name="priority" id="priority" class="form-select" required>
                            <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                        @error('priority')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="deadline" class="form-label">Deadline <span class="text-danger">*</span></label>
                        <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                        @error('deadline')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @if ($canAssign)
                    <div class="mb-3">
                        <label class="form-label">Assign to Staff</label>
                        <div class="row">
                            @foreach ($employees as $employee)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="assignees[]" id="assignee_{{ $employee->id }}" value="{{ $employee->id }}" class="form-check-input" {{ in_array($employee->id, old('assignees', [])) ? 'checked' : '' }}>
                                        <label for="assignee_{{ $employee->id }}" class="form-check-label">{{ $employee->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Leave unselected to create a personal task.</div>
                        @error('assignees')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="alert alert-info py-2 mb-3">
                        <i class="fas fa-info-circle me-1"></i>This will be created as your personal task.
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-save me-1"></i>Create Task
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


