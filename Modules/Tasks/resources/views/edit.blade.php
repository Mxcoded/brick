@extends('layouts.master')

@section('title', 'Edit Task')
@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.show', $task->id) }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Task</li>
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
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-edit me-2"></i>Edit Task</h3>
        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-dark btn-sm">
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
            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select" required>
                            <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline', $task->deadline->format('Y-m-d')) }}" required>
                    </div>
                </div>

                @if ($canAssign)
                    <div class="mb-3">
                        <label class="form-label">Assign to Staff</label>
                        <div class="row">
                            @foreach ($employees as $employee)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="assignees[]" id="assignee_{{ $employee->id }}" value="{{ $employee->id }}" class="form-check-input"
                                            {{ $task->employees->pluck('id')->contains($employee->id) ? 'checked' : '' }}>
                                        <label for="assignee_{{ $employee->id }}" class="form-check-label">{{ $employee->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Deselect all to make this a personal task.</div>
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-save me-1"></i>Update Task
                    </button>
                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-dark">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


