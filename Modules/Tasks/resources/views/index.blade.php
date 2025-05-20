@extends('staff::layouts.master')

@section('title', 'Tasks List')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tasks List</li>
@endsection
{{-- @section('content')
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
                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-warning btn-sm">Update</a>
                    
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
@endpush --}}



@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Tasks</h1>
        <div class="d-flex gap-2">
            @can('create-task')
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm" aria-label="Create New Task">
                    <i class="fas fa-plus me-1"></i>New Task
                </a>
            @endcan
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-label="Open Filters">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
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

    <!-- Mobile Search -->
    <div class="d-block d-lg-none mb-3">
        <form action="{{ route('tasks.index') }}" method="GET">
            <div class="input-group">
                <input type="text" name="q" id="mobile-search" class="form-control" placeholder="Search tasks..." value="{{ request('q') }}" aria-label="Search tasks">
                <button class="btn btn-primary" type="submit" aria-label="Search">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Desktop Filters -->
    <div class="row mb-3 d-none d-lg-flex align-items-end">
        <div class="col-md-4">
            <form action="{{ route('tasks.index') }}" method="GET" id="desktop-search-form">
                <div class="input-group">
                    <input type="text" name="q" id="desktop-search" class="form-control" placeholder="Search tasks..." value="{{ request('q') }}" aria-label="Search tasks">
                    <button class="btn btn-primary" type="submit" aria-label="Search">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-8">
            <form action="{{ route('tasks.index') }}" method="GET" id="desktop-filter-form" class="d-flex gap-2 align-items-end">
                <div class="flex-fill">
                    <label for="status" class="form-label visually-hidden">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Evaluated (Successful)" {{ request('status') == 'Evaluated (Successful)' ? 'selected' : '' }}>Evaluated (Successful)</option>
                        <option value="Evaluated (Not Successful)" {{ request('status') == 'Evaluated (Not Successful)' ? 'selected' : '' }}>Evaluated (Not Successful)</option>
                    </select>
                </div>
                <div class="flex-fill">
                    <label for="priority" class="form-label visually-hidden">Priority</label>
                    <select name="priority" id="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                @if (Auth::user()->hasRole('admin','gm'))
                    <div class="flex-fill">
                        <label for="assignee" class="form-label visually-hidden">Assignee</label>
                        <select name="assignee" id="assignee" class="form-select form-select-sm">
                            <option value="">All Assignees</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('assignee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="btn btn-primary btn-sm" aria-label="Apply Filters">Apply</button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Clear Filters">Clear</a>
            </form>
        </div>
    </div>

    <!-- Offcanvas for Mobile Filters -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form action="{{ route('tasks.index') }}" method="GET">
                <div class="mb-3">
                    <label for="mobile-q" class="form-label">Search</label>
                    <input type="text" name="q" id="mobile-q" class="form-control" placeholder="Search tasks..." value="{{ request('q') }}" aria-label="Search tasks">
                </div>
                <div class="mb-3">
                    <label for="mobile-status" class="form-label">Status</label>
                    <select name="status" id="mobile-status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Evaluated (Successful)" {{ request('status') == 'Evaluated (Successful)' ? 'selected' : '' }}>Evaluated (Successful)</option>
                        <option value="Evaluated (Not Successful)" {{ request('status') == 'Evaluated (Not Successful)' ? 'selected' : '' }}>Evaluated (Not Successful)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="mobile-priority" class="form-label">Priority</label>
                    <select name="priority" id="mobile-priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                @if (Auth::user()->hasRole('admin','gm'))
                    <div class="mb-3">
                        <label for="mobile-assignee" class="form-label">Assignee</label>
                        <select name="assignee" id="mobile-assignee" class="form-select">
                            <option value="">All Assignees</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('assignee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="d-none d-lg-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Task #</th>
                        <th scope="col">Date</th>
                        <th scope="col">Summary</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Assignees</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td class="fw-bold">{{ $task->task_number }}</td>
                            <td>{{ $task->date->format('M d, Y') }}</td>
                            <td>
                                <span class="text-truncate d-block" style="max-width: 150px" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $task->description }}" aria-label="{{ Str::limit($task->description, 30, '...') }}">
                                    {{ Str::limit($task->description, 30, '...') }}
                                </span>
                            </td>
                            <td>
                                @if($task->priority == 'high')
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>High</span>
                                @elseif($task->priority == 'medium')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-info-circle me-1"></i>Medium</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-arrow-down me-1"></i>Low</span>
                                @endif
                            </td>
                            <td @if($task->deadline->isPast()) class="text-danger" @endif>
                                {{ $task->deadline->format('M d, Y') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center" role="group" aria-label="Assignees">
                                    @if($task->employees->isNotEmpty())
                                        <div class="avatar-group">
                                            @foreach($task->employees->take(3) as $employee)
                                                <span class="avatar avatar-sm rounded text-bg-dark me-2" aria-label="{{ $employee->name }}">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if($task->employees->count() > 3)
                                            <small class="ms-2">+{{ $task->employees->count() - 3 }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Unassigned</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($task->status == 'Pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                                @elseif ($task->status == 'Completed')
                                    <span class="badge bg-primary"><i class="fas fa-check-circle me-1"></i>Completed</span>
                                @elseif ($task->status == 'Evaluated (Successful)')
                                    <span class="badge bg-success"><i class="fas fa-star me-1"></i>Successful</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Not Successful</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary" title="View Task" aria-label="View Task">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $task)
                                        <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit Task" aria-label="Edit Task">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $task)
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this task?')" title="Delete Task" aria-label="Delete Task">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No tasks found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="d-block d-lg-none">
        @forelse ($tasks as $task)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="card-title mb-0">#{{ $task->task_number }}</h5>
                            <small class="text-muted">{{ $task->date->format('M d, Y') }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Task Actions">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('tasks.show', $task->id) }}"><i class="fas fa-eye me-2"></i>View</a></li>
                                @can('update', $task)
                                    <li><a class="dropdown-item" href="{{ route('tasks.edit', $task->id) }}"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                @endcan
                                @can('delete', $task)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this task?')">
                                                <i class="fas fa-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                    <p class="card-text mb-2">{{ Str::limit($task->description, 50, '...') }}</p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @if($task->priority == 'high')
                            <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i>High</span>
                        @elseif($task->priority == 'medium')
                            <span class="badge bg-warning text-dark"><i class="fas fa-info-circle me-1"></i>Medium</span>
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-arrow-down me-1"></i>Low</span>
                        @endif
                        @if ($task->status == 'Pending')
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                        @elseif ($task->status == 'Completed')
                            <span class="badge bg-primary"><i class="fas fa-check-circle me-1"></i>Completed</span>
                        @elseif ($task->status == 'Evaluated (Successful)')
                            <span class="badge bg-success"><i class="fas fa-star me-1"></i>Successful</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Not Successful</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Deadline:</small>
                            <div @if($task->deadline->isPast()) class="text-danger" @endif>
                                {{ $task->deadline->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">Assignees:</small>
                            <div>
                                @if($task->employees->isNotEmpty())
                                    <a href="#" data-bs-toggle="popover" data-bs-content="{{ $task->employees->pluck('name')->implode(', ') }}" data-bs-trigger="focus" aria-label="View Assignees">
                                        {{ $task->employees->count() }} <i class="fas fa-users"></i>
                                    </a>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">No tasks found.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tasks->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('styles')
    <style>
        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
            line-height: 28px; /* Fallback for older browsers */
        }
        .avatar-group .avatar:not(:first-child) {
            margin-left: -8px;
        }
        .card-title {
            font-size: 1.1rem;
        }
        .pagination .page-link {
            border-radius: 0.25rem;
            margin: 0 2px;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        /* Ensure consistent badge styling */
        .badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(tooltipTriggerEl => {
                new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover focus' // Support keyboard navigation
                });
            });

            // Initialize popovers for mobile assignees
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            popoverTriggerList.forEach(popoverTriggerEl => {
                new bootstrap.Popover(popoverTriggerEl, {
                    html: true,
                    trigger: 'focus'
                });
            });

            // Check for new notifications
            if (typeof fetch !== 'undefined') {
                fetch('/notifications')
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            const toastEl = document.getElementById('taskToast');
                            const toastBody = toastEl.querySelector('.toast-body');
                            toastBody.textContent = data[0].data.message;
                            const toast = new bootstrap.Toast(toastEl);
                            toast.show();

                            fetch('/notifications/' + data[0].id + '/read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                            });
                        }
                    })
                    .catch(error => console.error('Notification fetch failed:', error));
            }
        });
    </script>
@endpush