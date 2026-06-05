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
</style>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-tasks me-2"></i>Task #{{ $task->task_number }}</h3>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Tasks
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3 mb-4">
                @if ($isCreator)
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
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: body.toString()
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) return;

                    var newStatus = data.status;
                    toggle.querySelectorAll('.st-btn').forEach(function (b) {
                        var st = b.closest('form') ? b.closest('form').querySelector('input[name="status"]').value : '';
                        b.classList.remove('active');
                        b.disabled = false;
                        if (st === newStatus) {
                            b.classList.add('active');
                            b.disabled = true;
                        }
                    });

                    var card = toggle.closest('.card');
                    if (card) {
                        card.querySelector('.card-body').style.borderLeftColor =
                            newStatus === 'completed' ? '#198754' : newStatus === 'in_progress' ? '#0d6efd' : '#ffc107';
                    }
                })
                .catch(function () {});
            });
        });
    });
</script>
@endsection
