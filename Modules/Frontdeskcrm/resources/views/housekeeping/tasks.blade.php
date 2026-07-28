@extends('layouts.master')

@section('title', 'Housekeeping Tasks')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">All Housekeeping Tasks</h4>
        <a href="{{ route('frontdesk.housekeeping.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Room</th>
                            <th>Task Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Assigned At</th>
                            <th>Completed By</th>
                            <th>Completed At</th>
                            <th>Notes</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td><strong>{{ $task->roomUnit->room_number ?? '—' }}</strong></td>
                            <td>{{ ucfirst($task->task_type) }}</td>
                            <td>
                                <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'normal' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $task->completed_at ? 'success' : ($task->assigned_to ? 'primary' : 'secondary') }}">
                                    {{ $task->completed_at ? 'Completed' : ($task->assigned_to ? 'In Progress' : 'Unassigned') }}
                                </span>
                            </td>
                            <td>{{ $task->assignedTo->name ?? '—' }}</td>
                            <td>{{ $task->assigned_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td>{{ $task->completedBy->name ?? '—' }}</td>
                            <td>{{ $task->completed_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td>{{ $task->notes ?? '—' }}</td>
                            <td class="text-center">
                                @if(!$task->completed_at)
                                    @if(!$task->assigned_to)
                                    <form action="{{ route('frontdesk.housekeeping.assign-task', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        <select name="assigned_to" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="">Assign...</option>
                                            @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Staff'))->limit(10)->get() as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    @endif
                                    <form action="{{ route('frontdesk.housekeeping.complete-task', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" title="Complete"><i class="fas fa-check"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center py-4 text-muted">No tasks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection
