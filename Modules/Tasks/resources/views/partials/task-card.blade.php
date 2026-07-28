@php
    $isCreator = ($task->created_by === Auth::id());
    $statuses = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'];
@endphp
<div class="card border-0 shadow-sm mb-2 task-card status-{{ $task->status }} {{ $task->status === 'completed' ? 'completed' : '' }}"
     data-task-id="{{ $task->id }}"
     draggable="{{ isset($draggable) && $draggable ? 'true' : 'false' }}">
    <div class="card-body py-3" style="border-left: 4px solid; border-left-color: {{ $task->status === 'pending' ? '#ffc107' : ($task->status === 'in_progress' ? '#0d6efd' : '#198754') }}; border-radius: 8px 0 0 8px;">
        <div class="d-flex align-items-start gap-3">
            @if(isset($showBulkCheckbox) && $showBulkCheckbox)
                <div class="mt-1">
                    <input type="checkbox" class="form-check-input task-bulk-checkbox" value="{{ $task->id }}" style="cursor: pointer;">
                </div>
            @endif

            {{-- Status Toggle --}}
            <div class="mt-1">
                @if ($isCreator || (isset($showAssigneeActions) && $showAssigneeActions))
                    <div class="status-toggle" data-task-toggle="{{ $task->id }}">
                        @foreach (['pending', 'in_progress', 'completed'] as $st)
                            <form action="{{ route('tasks.status', $task->id) }}" method="POST" class="d-inline task-status-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $st }}">
                                <button type="submit"
                                    class="st-btn st-{{ $st }} {{ $task->status === $st ? 'active' : '' }}"
                                    {{ $task->status === $st ? 'disabled' : '' }}
                                    title="{{ $task->status === $st ? 'Current: ' . $statuses[$st] : 'Set to ' . $statuses[$st] }}">
                                    {{ $st === 'in_progress' ? 'Doing' : $statuses[$st] }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @else
                    <div class="status-toggle">
                        @foreach (['pending', 'in_progress', 'completed'] as $st)
                            <span class="st-btn readonly st-{{ $st }} {{ $task->status === $st ? 'active' : '' }}">
                                {{ $st === 'in_progress' ? 'Doing' : $statuses[$st] }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-grow-1 min-width-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="min-width-0 w-100">
                        <a href="{{ route('tasks.show', $task->id) }}" class="text-decoration-none text-dark fw-semibold task-link">
                            {{ $task->description }}
                        </a>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning text-dark' : 'secondary') }} rounded-pill">
                                {{ ucfirst($task->priority) }}
                            </span>
                            @if ($task->is_recurring)
                                <span class="badge bg-primary rounded-pill" title="Repeats {{ ucfirst($task->recurrence_type) }}">
                                    <i class="fas fa-redo me-1"></i>{{ ucfirst($task->recurrence_type) }}
                                </span>
                            @endif
                            <span class="small text-muted">
                                <i class="far fa-calendar-alt me-1"></i>{{ $task->deadline->format('M d, Y') }}
                                @if($task->deadline->isPast() && $task->status !== 'completed')
                                    <span class="text-danger fw-semibold">(Overdue)</span>
                                @endif
                            </span>
                            @if(isset($showActions) && $showActions && $task->employees->isNotEmpty())
                                <span class="small text-muted" title="{{ $task->employees->pluck('name')->implode(', ') }}">
                                    <i class="fas fa-users me-1"></i>{{ $task->employees->pluck('name')->implode(', ') }}
                                </span>
                            @elseif(!isset($showActions) || !$showActions)
                                <span class="small text-muted">
                                    <i class="fas fa-user me-1"></i>{{ $task->creator->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if (isset($showActions) && $showActions)
                        <div class="d-flex gap-1 ms-3 flex-shrink-0">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-dark" title="View"><i class="fas fa-eye"></i></a>
                            @can('tasks.update')
                                <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-dark" title="Edit"><i class="fas fa-edit"></i></a>
                            @endcan
                            @can('tasks.delete')
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-dark" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    @else
                        <div class="d-flex gap-1 ms-3 flex-shrink-0">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-dark" title="View"><i class="fas fa-eye"></i></a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
