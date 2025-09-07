@extends('layouts.master')

@section('title', 'Create Task')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Create Task</li>
@endsection
{{-- @section('content')
    <div class="container">
        <h1>Create Task</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Priority</label>
                <select name="priority" id="priority" class="form-select" required>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
                @error('priority')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}" required>
                @error('deadline')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Assignees</label>
                @foreach ($employees as $employee)
                    <div class="form-check">
                        <input type="checkbox" name="assignees[]" id="assignee_{{ $employee->id }}" value="{{ $employee->id }}" class="form-check-input" {{ in_array($employee->id, old('assignees', [])) ? 'checked' : '' }}>
                        <label for="assignee_{{ $employee->id }}" class="form-check-label">{{ $employee->name }} ({{ $employee->email }})</label>
                    </div>
                @endforeach
                @error('assignees')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Create Task</button>
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection --}}

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h3 mb-0">Create Task</h1>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Back to Tasks">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="4" required aria-describedby="descriptionHelp">{{ old('description') }}</textarea>
                                <div id="descriptionHelp" class="form-text">Provide a detailed description of the task.</div>
                                @error('description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select name="priority" id="priority" class="form-select" required aria-label="Select Priority">
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                </select>
                                @error('priority')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="deadline" class="form-label">Deadline</label>
                                <input type="date" name="deadline" id="deadline" class="form-control" value="{{ old('deadline') }}" min="{{ \Carbon\Carbon::today()->toDateString() }}" required aria-label="Select Deadline">
                                @error('deadline')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignees</label>
                        <div class="row">
                            @foreach ($employees as $employee)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="assignees[]" id="assignee_{{ $employee->id }}" value="{{ $employee->id }}" class="form-check-input" {{ in_array($employee->id, old('assignees', [])) ? 'checked' : '' }} aria-label="Assign to {{ $employee->name }}">
                                        <label for="assignee_{{ $employee->id }}" class="form-check-label">{{ $employee->name }} ({{ $employee->email }})</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('assignees')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" aria-label="Create Task">
                            <i class="fas fa-save me-1"></i>Create Task
                        </button>
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary" aria-label="Cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .form-check-label {
            cursor: pointer;
        }
        .card {
            border-radius: 0.5rem;
        }
    </style>
@endpush