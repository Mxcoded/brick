@extends('layouts.master')

@section('title', 'Housekeeping')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Housekeeping Dashboard</h4>
        <a href="{{ route('frontdesk.housekeeping.tasks') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-1"></i>All Tasks
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-primary text-white text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $summary['total'] }}</h2>
                    <small>Total Rooms</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-success text-white text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $summary['clean'] }}</h2>
                    <small>Clean</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-warning text-white text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $summary['dirty'] }}</h2>
                    <small>Dirty</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-info text-white text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $summary['inspected'] }}</h2>
                    <small>Inspected</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-secondary text-white text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $summary['outOfService'] }}</h2>
                    <small>Out of Service</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-danger text-white text-center">
                <div class="card-body">
                    <h2 class="mb-0">{{ $summary['pendingTasks'] }}</h2>
                    <small>Pending Tasks</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Room Status</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Room</th>
                                    <th>Floor</th>
                                    <th>Type</th>
                                    <th>Occupancy</th>
                                    <th>HK Status</th>
                                    <th>Last Cleaned</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rooms as $room)
                                <tr>
                                    <td><strong>{{ $room->room_number }}</strong></td>
                                    <td>{{ $room->floor }}</td>
                                    <td>{{ $room->roomType->name ?? '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $room->status === 'occupied' ? 'danger' : 'success' }}">
                                            {{ ucfirst($room->status) }}
                                        </span>
                                        @if($room->currentOccupant)
                                        <small class="d-block text-muted">{{ $room->currentOccupant->full_name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $room->housekeeping_status === 'clean' ? 'success' : ($room->housekeeping_status === 'dirty' ? 'warning' : ($room->housekeeping_status === 'inspected' ? 'info' : 'secondary')) }}">
                                            {{ ucfirst($room->housekeeping_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($room->last_cleaned_at)
                                            {{ \Carbon\Carbon::parse($room->last_cleaned_at)->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('frontdesk.housekeeping.update-status', $room) }}" method="POST" class="d-inline">
                                            @csrf
                                            <select name="housekeeping_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                <option value="clean" {{ $room->housekeeping_status === 'clean' ? 'selected' : '' }}>Clean</option>
                                                <option value="dirty" {{ $room->housekeeping_status === 'dirty' ? 'selected' : '' }}>Dirty</option>
                                                <option value="inspected" {{ $room->housekeeping_status === 'inspected' ? 'selected' : '' }}>Inspected</option>
                                                <option value="out_of_service" {{ $room->housekeeping_status === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                                            </select>
                                        </form>
                                        <button class="btn btn-sm btn-outline-info ms-1" data-bs-toggle="modal"
                                                data-bs-target="#createTaskModal-{{ $room->id }}">
                                            <i class="fas fa-tasks"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No rooms found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Pending Tasks</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($pendingTasks as $task)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between">
                            <strong>Room {{ $task->roomUnit->room_number ?? '—' }}</strong>
                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'info') }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <small class="text-muted">{{ ucfirst($task->task_type) }}</small>
                        @if($task->assignedTo)
                        <div class="mt-1"><small>Assigned: {{ $task->assignedTo->name }}</small></div>
                        @endif
                        @if($task->notes)
                        <div class="mt-1"><small class="text-muted">{{ $task->notes }}</small></div>
                        @endif
                        <div class="mt-2">
                            @if(!$task->assigned_to)
                            <form action="{{ route('frontdesk.housekeeping.assign-task', $task) }}" method="POST" class="d-inline">
                                @csrf
                                <select name="assigned_to" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                    <option value="">Assign to...</option>
                                    @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Staff'))->limit(10)->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @endif
                            @if(!$task->completed_at)
                            <form action="{{ route('frontdesk.housekeeping.complete-task', $task) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">No pending tasks.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($rooms as $room)
<div class="modal fade" id="createTaskModal-{{ $room->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form action="{{ route('frontdesk.housekeeping.create-task', $room) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Task for Room {{ $room->room_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Type</label>
                        <select name="task_type" class="form-select">
                            <option value="clean">Clean</option>
                            <option value="inspect">Inspect</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="deep_clean">Deep Clean</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'Staff'))->limit(20)->get() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Create Task</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
