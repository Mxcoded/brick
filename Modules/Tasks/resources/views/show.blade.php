@extends('layouts.master')

@section('title', 'Task Details')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Task Details</li>
@endsection

@section('page-content')
    <div class="container-fluid my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Task #{{ $task->task_number }}</h1>
            <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning btn-primary" aria-label="Cancel">
                <i class="fas fa-arrow-circle-left me-1"></i> Task List
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Task Number</dt>
                    <dd class="col-sm-9">{{ $task->task_number }}</dd>
                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ $task->date->format('M d, Y') }}</dd>
                    <dt class="col-sm-3">Created By</dt>
                    <dd class="col-sm-9">{{ $task->creator->name }}</dd>
                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $task->description }}</dd>
                    <dt class="col-sm-3">Priority</dt>
                    <dd class="col-sm-9">
                        @if ($task->priority == 'high')
                            <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>High</span>
                        @elseif ($task->priority == 'medium')
                            <span class="badge bg-warning text-dark"><i class="fas fa-info-circle me-1"></i>Medium</span>
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-arrow-down me-1"></i>Low</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3">Deadline</dt>
                    <dd class="col-sm-9" @if($task->deadline->isPast()) class="text-danger" @endif>
                        {{ $task->deadline->format('M d, Y') }}
                    </dd>
                    <dt class="col-sm-3">Assignees</dt>
                    <dd class="col-sm-9">
                        @if ($task->employees->isNotEmpty())
                            {{ $task->employees->pluck('name')->implode(', ') }}
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">
                        @if ($task->status == 'Pending')
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                        @elseif ($task->status == 'Completed')
                            <span class="badge bg-primary"><i class="fas fa-check-circle me-1"></i>Completed</span>
                        @elseif ($task->status == 'Evaluated (Successful)')
                            <span class="badge bg-success"><i class="fas fa-star me-1"></i>Successful</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Not Successful</span>
                        @endif
                    </dd>
                    @if ($task->is_completed)
                        <dt class="col-sm-3">Completion Date</dt>
                        <dd class="col-sm-9">{{ $task->completion_date ? $task->completion_date->format('M d, Y') : 'N/A' }}</dd>
                    @else
                        <dt class="col-sm-3">Reason for Non-Completion</dt>
                        <dd class="col-sm-9">{{ $task->non_completion_reason ?? 'N/A' }}</dd>
                    @endif
                    <dt class="col-sm-3">Notes</dt>
                    <dd class="col-sm-9">{{ $task->notes ?? 'N/A' }}</dd>
                    @if ($task->is_successful !== null)
                        <dt class="col-sm-3">Successfully Completed</dt>
                        <dd class="col-sm-9">{{ $task->is_successful ? 'Yes' : 'No' }}</dd>
                        <dt class="col-sm-3">Meets Expectations</dt>
                        <dd class="col-sm-9">{{ $task->meets_expectations ? 'Yes' : 'No' }}</dd>
                        <dt class="col-sm-3">General Manager Notes</dt>
                        <dd class="col-sm-9">{{ $task->gm_notes ?? 'N/A' }}</dd>
                    @endif
                </dl>
                <div class="d-flex gap-2">
                    @can('edit-task')
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary btn-sm" aria-label="Edit task">
                            <i class="fas fa-edit me-1"></i> Update Task
                        </a>
                    @endcan
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-warning btn-secondary" aria-label="Cancel">
                        <i class="fas fa-arrow-circle-left me-1"></i> Task List
                    </a>
                </div>
            </div>
        </div>

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