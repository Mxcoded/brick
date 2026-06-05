@php
    $isCreator = ($task->created_by === Auth::id());
    $statuses = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'];
    $borderClass = ['pending' => 'border-warning', 'in_progress' => 'border-primary', 'completed' => 'border-success'];
@endphp
<div class="card border-0 shadow-sm mb-2 task-card status-{{ $task->status }} {{ $task->status === 'completed' ? 'completed' : '' }}" data-task-id="{{ $task->id }}">
    <div class="card-body py-3" style="border-left: 4px solid; border-left-color: {{ $task->status === 'pending' ? '#ffc107' : ($task->status === 'in_progress' ? '#0d6efd' : '#198754') }}; border-radius: 8px 0 0 8px;">
        <div class="d-flex align-items-start gap-3">
            {{-- Status Toggle --}}
            <div class="mt-1">
                @if ($isCreator)
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
                    <div>
                        <a href="{{ route('tasks.show', $task->id) }}" class="text-decoration-none text-dark fw-semibold task-link">
                            {{ Str::limit($task->description, 60) }}
                        </a>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning text-dark' : 'secondary') }} rounded-pill">
                                {{ ucfirst($task->priority) }}
                            </span>
                            <span class="small text-muted">
                                <i class="far fa-calendar-alt me-1"></i>{{ $task->deadline->format('M d, Y') }}
                                @if($task->deadline->isPast() && $task->status !== 'completed')
                                    <span class="text-danger fw-semibold">(Overdue)</span>
                                @endif
                            </span>
                            @if(isset($showActions) && $showActions && $task->employees->isNotEmpty())
                                <span class="small text-muted">
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
                        <div class="d-flex gap-1 ms-3">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                            @can('tasks.update')
                                <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            @endcan
                            @can('tasks.delete')
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    @else
                        <div class="d-flex gap-1 ms-3">
                            <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
