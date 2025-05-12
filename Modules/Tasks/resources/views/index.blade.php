@extends('tasks::layouts.master')

@section('title', 'Tasks List')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">Create New Task</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Task Number</th>
                <th>Date</th>
                <th>Priority</th>
                <th>Deadline</th>
                <th>Assignees</th>
                <th>Completed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td>{{ $task->task_number }}</td>
                    <td>{{ $task->date->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($task->priority) }}</td>
                    <td>{{ $task->deadline->format('Y-m-d') }}</td>
                    <td>{{ $task->employees->pluck('name')->implode(', ') }}</td>
                    <td>{{ $task->is_completed ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection