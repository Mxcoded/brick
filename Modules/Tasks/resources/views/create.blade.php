@extends('staff::layouts.master')

@section('title', 'Create Task')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Create Task</li>
@endsection
@section('content')
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
@endsection