@extends('staff::layouts.master')

@section('title', 'Task Details')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Task Details</li>
@endsection
@section('content')
    <div class="container">
        <h1>Task Details</h1>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Task Number</dt>
                    <dd class="col-sm-9">{{ $task->task_number }}</dd>

                    <dt class="col-sm-3">Date</dt>
                    <dd class="col-sm-9">{{ $task->date->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Created By</dt>
                    <dd class="col-sm-9">{{ $task->creator->name }}</dd>

                    <dt class="col-sm-3">Description</dt>
                    <dd class="col-sm-9">{{ $task->description }}</dd>

                    <dt class="col-sm-3">Priority</dt>
                    <dd class="col-sm-9">{{ ucfirst($task->priority) }}</dd>

                    <dt class="col-sm-3">Deadline</dt>
                    <dd class="col-sm-9">{{ $task->deadline->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Assignees</dt>
                    <dd class="col-sm-9">{{ $task->employees->isNotEmpty() ? $task->employees->pluck('name')->implode(', ') : 'No assignees' }}</dd>

                    <dt class="col-sm-3">Completed</dt>
                    <dd class="col-sm-9">{{ $task->is_completed ? 'Yes' : 'No' }}</dd>

                    @if ($task->is_completed)
                        <dt class="col-sm-3">Completion Date</dt>
                        <dd class="col-sm-9">{{ $task->completion_date ? $task->completion_date->format('Y-m-d') : 'N/A' }}</dd>
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
            </div>
        </div>

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

        <div class="mt-3">
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Back to List</a>
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-primary">Edit Task</a>
            @endcan
        </div>
    </div>
@endsection