@extends('staff::layouts.master')


@section('title', 'Edit Task')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Edit Task</li>
@endsection
{{-- @section('content')
    <div class="container">
        <h1>Edit Task</h1>

        @if ($task->is_successful)
            <div class="alert alert-info">This task has been evaluated as successful and cannot be updated.</div>
        @else
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
                <a href="{{ route('tasks.index') }}" class="btn btn-danger">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </form>
        @endif

        <!-- Evaluation Form for General Manager -->
        @if (Auth::user()->hasRole('admin'))
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

        <!-- Task Update History -->
        <h2 class="mt-5">Task Update History</h2>
        @if ($task->updates->isNotEmpty())
            <ul class="list-group">
                @foreach ($task->updates as $update)
                    <li class="list-group-item">
                        <strong>{{ $update->user->name }}</strong> {{ $update->action == 'updated_completion' ? 'updated the task' : 'evaluated the task' }}
                        on {{ $update->created_at->format('Y-m-d H:i:s') }}
                        <ul>
                            @foreach ($update->changes as $field => $value)
                                <li>{{ ucfirst(str_replace('_', ' ', $field)) }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? 'N/A') }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @else
            <p>No updates recorded.</p>
        @endif
    </div>
@endsection --}}


@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Edit Task #{{ $task->task_number }}</h1>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Back to tasks">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if ($task->is_successful)
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                This task has been evaluated as successful and cannot be updated.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @else
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="is_completed" class="form-label">Completed</label>
                            <select name="is_completed" id="is_completed" class="form-select" aria-label="Task completion status">
                                <option value="1" {{ $task->is_completed ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$task->is_completed ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="completion_date" class="form-label">Completion Date</label>
                            <input type="date" name="completion_date" id="completion_date" class="form-control" value="{{ $task->completion_date ? $task->completion_date->format('Y-m-d') : '' }}" aria-label="Task completion date">
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add notes" aria-label="Task notes">{{ $task->notes }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="non_completion_reason" class="form-label">Reason for Non-Completion</label>
                            <textarea name="non_completion_reason" id="non_completion_reason" class="form-control" rows="3" placeholder="Explain why the task was not completed" aria-label="Non-completion reason">{{ $task->non_completion_reason }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" aria-label="Update task">
                                <i class="fas fa-save me-1"></i> Update Task
                            </button>
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-outline-white btn-danger" aria-label="Cancel">
                                <i class="fas fa-times-circle me-1"></i> Cancel Update
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if (Auth::user()->hasRole('gm'))
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Evaluate Task</h2>
                    <form action="{{ route('tasks.evaluate', $task->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="is_successful" class="form-label">Successfully Completed</label>
                            <select name="is_successful" id="is_successful" class="form-select" aria-label="Task success status">
                                <option value="1" {{ $task->is_successful ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$task->is_successful ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="meets_expectations" class="form-label">Meets Expectations</label>
                            <select name="meets_expectations" id="meets_expectations" class="form-select" aria-label="Task expectation status">
                                <option value="1" {{ $task->meets_expectations ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$task->meets_expectations ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="gm_notes" class="form-label">General Manager Notes</label>
                            <textarea name="gm_notes" id="gm_notes" class="form-control" rows="3" placeholder="Add evaluation notes" aria-label="GM notes">{{ $task->gm_notes }}</textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" aria-label="Evaluate task">
                                <i class="fas fa-check me-1"></i> Evaluate Task
                            </button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary" aria-label="Cancel">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h4 mb-3">Task Update History</h2>
                @if ($task->updates->isNotEmpty())
                    <ul class="list-group">
                        @foreach ($task->updates as $update)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $update->user->name }}</strong>
                                        {{ $update->action == 'updated_completion' ? 'updated the task' : 'evaluated the task' }}
                                        <small class="text-muted">on {{ $update->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                                <ul class="mt-2">
                                    @foreach ($update->changes as $field => $value)
                                        <li>{{ ucfirst(str_replace('_', ' ', $field)) }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? 'N/A') }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No updates recorded.</p>
                @endif
            </div>
        </div>
    </div>
@endsection