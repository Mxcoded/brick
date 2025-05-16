@extends('staff::layouts.master')

@section('title', 'Tasks List')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tasks List</li>
@endsection
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Tasks</h1>
       
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">Create New Task</a>
     
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Notification Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="taskToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">New Task Assigned</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

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
                    <td>{{ $task->employees->isNotEmpty() ? $task->employees->pluck('name')->implode(', ') : 'No assignees' }}</td>
                    <td>{{ $task->is_completed ? 'Yes' : 'No' }}</td>
                    <td>
                        @can('view-task')
                        <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-info btn-sm">View</a>
                        @endcan
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    
                     @can('delete-task')
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                            @endcan
                        
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check for new notifications
            fetch('/notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const toastEl = document.getElementById('taskToast');
                        const toastBody = toastEl.querySelector('.toast-body');
                        toastBody.textContent = data[0].data.message;
                        const toast = new bootstrap.Toast(toastEl);
                        toast.show();

                        // Mark notification as read
                        fetch('/notifications/' + data[0].id + '/read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                    }
                });
        });
    </script>
@endpush