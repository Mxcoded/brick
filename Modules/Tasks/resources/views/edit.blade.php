@extends('tasks::layouts.master')

@section('title', 'Edit Task')

@section('content')
    <div class="container">
        <h1>Edit Task</h1>

        <!-- Task Update Form -->
        <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="mb-5">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="is_completed" class="form-label">Completed</label>
                <select name="is_completed" id="is_completed" class="form-select">
                    <option value="1" {{ $task->is_completed ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ !$task->is_completed ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="completion_date" class="form-label">Completion Date</label>
                <input type="date" name="completion_date" id="completion_date" class="form-control" value="{{ $task->completion_date ? $task->completion_date->format('Y-m-d') : '' }}">
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ $task->notes }}</textarea>
            </div>

            <div class="mb-3">
                <label for="non_completion_reason" class="form-label">Reason for Non-Completion</label>
                <textarea name="non_completion_reason" id="non_completion_reason" class="form-control" rows="3">{{ $task->non_completion_reason }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Task</button>
        </form>

        <!-- Evaluation Form for General Manager -->
        @if (Auth::user()) <!-- Replace with actual authorization logic -->
            <h2>Evaluate Task</h2>
            <form action="{{ route('tasks.evaluate', $task->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="is_successful" class="form-label">Successfully Completed</label>
                    <select name="is_successful" id="is_successful" class="form-select">
                        <option value="1" {{ $task->is_successful ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ !$task->is_successful ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="meets_expectations" class="form-label">Meets Expectations</label>
                    <select name="meets_expectations" id="meets_expectations" class="form-select">
                        <option value="1" {{ $task->meets_expectations ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ !$task->meets_expectations ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="gm_notes" class="form-label">General Manager Notes</label>
                    <textarea name="gm_notes" id="gm_notes" class="form-control" rows="3">{{ $task->gm_notes }}</textarea>
                </div>

                <button type="submit" class="btn btn-success">Evaluate Task</button>
            </form>
        @endif
    </div>
@endsection