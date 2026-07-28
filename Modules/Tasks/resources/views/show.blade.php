@extends('layouts.master')

@section('title', 'Task Details')
@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}">Tasks</a></li>
    <li class="breadcrumb-item active" aria-current="page">Task Details</li>
@endsection

@section('styles')
<style>
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    dl.row dt { font-weight: 600; color: #555; }

    .status-toggle { display: inline-flex; border-radius: 20px; overflow: hidden; border: 1px solid #dee2e6; }
    .status-toggle .st-btn { border: none; padding: 4px 16px; font-size: 0.8rem; cursor: pointer; transition: all 0.15s; font-weight: 500; }
    .status-toggle .st-btn:not(:last-child) { border-right: 1px solid #dee2e6; }
    .status-toggle .st-btn.st-pending { background: #fff8e1; color: #8a6d00; }
    .status-toggle .st-btn.st-in_progress { background: #e3f2fd; color: #0a58ca; }
    .status-toggle .st-btn.st-completed { background: #e8f5e9; color: #146c43; }
    .status-toggle .st-btn.active.st-pending { background: #ffc107; color: #212529; }
    .status-toggle .st-btn.active.st-in_progress { background: #0d6efd; color: #fff; }
    .status-toggle .st-btn.active.st-completed { background: #198754; color: #fff; }
    .status-toggle .st-btn:not(.active):hover { filter: brightness(0.92); }
    .status-toggle .st-btn.readonly { cursor: default; }

    .update-timeline { position: relative; padding-left: 24px; }
    .update-timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #e0e0e0; }
    .update-item { position: relative; margin-bottom: 16px; }
    .update-item::before { content: ''; position: absolute; left: -20px; top: 6px; width: 10px; height: 10px; border-radius: 50%; background: #C8A165; border: 2px solid #fff; box-shadow: 0 0 0 2px #C8A165; }
    .update-item .update-action { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .update-item .update-meta { font-size: 0.75rem; color: #999; }
    .update-item .update-details { font-size: 0.85rem; color: #555; margin-top: 2px; }

    .comment-avatar { width: 36px; height: 36px; border-radius: 50%; background: #C8A165; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }

    .recurrence-badge { background: linear-gradient(135deg, #f0f7ff 0%, #e8f4fd 100%); border: 1px solid #b6d4fe; border-radius: 8px; padding: 10px 14px; font-size: 0.85rem; }
    .recurrence-badge .recurrence-icon { color: #0d6efd; }
</style>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-tasks me-2"></i>Task #{{ $task->task_number }}</h3>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Tasks
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Task Detail Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3 mb-4">
                @if ($isCreator || $isAssignee)
                    <div class="status-toggle mt-1" data-task-toggle="{{ $task->id }}">
                        @foreach (['pending', 'in_progress', 'completed'] as $st)
                            <form action="{{ route('tasks.status', $task->id) }}" method="POST" class="d-inline task-status-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $st }}">
                                <button type="submit"
                                    class="st-btn st-{{ $st }} {{ $task->status === $st ? 'active' : '' }}"
                                    {{ $task->status === $st ? 'disabled' : '' }}
                                    title="Set to {{ $st === 'in_progress' ? 'In Progress' : ucfirst($st) }}">
                                    {{ $st === 'in_progress' ? 'Doing' : ucfirst($st) }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @else
                    <div class="status-toggle mt-1">
                        @foreach (['pending', 'in_progress', 'completed'] as $st)
                            <span class="st-btn readonly st-{{ $st }} {{ $task->status === $st ? 'active' : '' }}">
                                {{ $st === 'in_progress' ? 'Doing' : ucfirst($st) }}
                            </span>
                        @endforeach
                    </div>
                @endif
                <div class="flex-grow-1">
                    <h4 class="mb-1 {{ $task->status === 'completed' ? 'text-decoration-line-through text-muted' : '' }}">{{ $task->description }}</h4>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning text-dark' : 'secondary') }} rounded-pill">
                            {{ ucfirst($task->priority) }}
                        </span>
                        @if ($task->is_recurring)
                            <span class="badge bg-primary rounded-pill">
                                <i class="fas fa-redo me-1"></i>{{ ucfirst($task->recurrence_type) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-3">Task Number</dt>
                <dd class="col-sm-9">{{ $task->task_number }}</dd>
                <dt class="col-sm-3">Created By</dt>
                <dd class="col-sm-9">{{ $task->creator->name }}</dd>
                <dt class="col-sm-3">Date Created</dt>
                <dd class="col-sm-9">{{ $task->date->format('M d, Y') }}</dd>
                <dt class="col-sm-3">Deadline</dt>
                <dd class="col-sm-9 {{ $task->deadline->isPast() && $task->status !== 'completed' ? 'text-danger fw-semibold' : '' }}">
                    {{ $task->deadline->format('M d, Y') }}
                    @if($task->deadline->isPast() && $task->status !== 'completed') (Overdue) @endif
                </dd>
                <dt class="col-sm-3">Assignees</dt>
                <dd class="col-sm-9">
                    @if ($task->employees->isNotEmpty())
                        {{ $task->employees->pluck('name')->implode(', ') }}
                    @else
                        <span class="text-muted">None</span>
                    @endif
                </dd>
                @if ($task->status === 'completed')
                    <dt class="col-sm-3">Completed On</dt>
                    <dd class="col-sm-9">{{ $task->completion_date ? $task->completion_date->format('M d, Y') : 'N/A' }}</dd>
                @endif
            </dl>

            {{-- Recurrence Info --}}
            @if ($task->is_recurring)
                <div class="recurrence-badge mt-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-redo recurrence-icon"></i>
                        <div>
                            <strong>Recurring {{ ucfirst($task->recurrence_type) }}</strong>
                            @if ($task->recurrence_end_date)
                                <span class="text-muted">— ends {{ $task->recurrence_end_date->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted">— no end date</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Parent Task Link --}}
            @if ($task->parentTask)
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-link me-1"></i>Continuation of
                        <a href="{{ route('tasks.show', $task->parentTask->id) }}" class="text-decoration-none">
                            {{ $task->parentTask->task_number }}
                        </a>
                    </small>
                </div>
            @endif

            {{-- Child Tasks --}}
            @if ($task->childTasks->isNotEmpty())
                <div class="mt-3">
                    <small class="text-muted fw-semibold d-block mb-1">
                        <i class="fas fa-list-ol me-1"></i>Recurrence Chain ({{ $task->childTasks->count() }} {{ Str::plural('occurrence', $task->childTasks->count()) }})
                    </small>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($task->childTasks->sortBy('created_at') as $child)
                            <a href="{{ route('tasks.show', $child->id) }}" class="badge text-decoration-none
                                {{ $child->status === 'completed' ? 'bg-success' : ($child->status === 'in_progress' ? 'bg-primary' : 'bg-warning text-dark') }}">
                                {{ $child->task_number }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Comments --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-comments me-2" style="color: #C8A165;"></i>Comments &amp; Updates</h6>
                </div>
                <div class="card-body">
                    @if ($isCreator || $isAssignee)
                        <form method="POST" action="{{ route('tasks.comment', $task->id) }}" class="mb-4">
                            @csrf
                            <div class="d-flex gap-2">
                                <textarea name="comment" class="form-control form-control-sm" rows="2" placeholder="Add a comment..." required maxlength="2000"></textarea>
                                <button type="submit" class="btn btn-gold btn-sm align-self-end flex-shrink-0"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </form>
                    @endif

                    <div class="update-timeline">
                        @forelse ($task->comments->sortByDesc('created_at') as $comment)
                            <div class="update-item">
                                <div class="d-flex gap-2 align-items-start">
                                    <div class="comment-avatar" style="background: #C8A165;">{{ substr($comment->user->name ?? '?', 0, 1) }}</div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold small">{{ $comment->user->name ?? 'Unknown' }}</span>
                                            <span class="update-meta">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="mb-0 mt-1" style="font-size: 0.9rem;">{{ $comment->comment }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No comments yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2" style="color: #C8A165;"></i>Activity</h6>
                </div>
                <div class="card-body">
                    <div class="update-timeline">
                        @forelse ($task->updates->sortByDesc('created_at') as $update)
                            <div class="update-item">
                                <span class="update-action" style="color: #C8A165;">{{ str_replace('_', ' ', $update->action) }}</span>
                                <span class="update-meta d-block">
                                    by {{ $update->user->name ?? 'System' }} &middot; {{ $update->created_at->diffForHumans() }}
                                </span>
                                @if($update->changes)
                                    <div class="update-details">
                                        @if(isset($update->changes['from']) && isset($update->changes['to']))
                                            <span>{{ $update->changes['from'] }} &rarr; {{ $update->changes['to'] }}</span>
                                        @elseif(isset($update->changes['comment_preview']))
                                            <span>&ldquo;{{ $update->changes['comment_preview'] }}&rdquo;</span>
                                        @elseif(isset($update->changes['assigned']))
                                            <span>Assigned to: {{ implode(', ', $update->changes['assigned']) }}</span>
                                        @elseif(isset($update->changes['parent_task_number']))
                                            <span>Created from {{ $update->changes['parent_task_number'] }} (recurring)</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No activity recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isCreator)
        <div class="d-flex gap-2">
            @can('tasks.update')
                <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-gold">
                    <i class="fas fa-edit me-1"></i>Edit Task
                </a>
            @endcan
            @can('tasks.delete')
                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            @endcan
        </div>
    @endif
@endsection

@section('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.task-status-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var btn = this.querySelector('button[type="submit"]');
                if (btn.disabled) return;
                var toggle = this.closest('[data-task-toggle]');
                if (!toggle) return;
                e.preventDefault();
                var token = this.querySelector('input[name="_token"]')?.value || '';
                var status = this.querySelector('input[name="status"]')?.value || '';
                var body = new URLSearchParams();
                body.append('_token', token);
                body.append('_method', 'PATCH');
                body.append('status', status);
                fetch(this.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) return;
                    var newStatus = data.status;
                    toggle.querySelectorAll('.st-btn').forEach(function (b) {
                        b.classList.remove('active');
                        b.disabled = false;
                        if (b.closest('form').querySelector('input[name="status"]').value === newStatus) {
                            b.classList.add('active');
                            b.disabled = true;
                        }
                    });
                    var card = toggle.closest('.card');
                    if (card) {
                        card.querySelector('.card-body').style.borderLeftColor =
                            newStatus === 'completed' ? '#198754' : newStatus === 'in_progress' ? '#0d6efd' : '#ffc107';
                    }
                    location.reload();
                })
                .catch(function () {});
            });
        });
    });
</script>
@endsection
