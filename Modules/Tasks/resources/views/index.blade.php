@extends('layouts.master')

@section('title', 'Tasks')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tasks</li>
@endsection

@section('styles')
<style>
    .task-card { border-radius: 8px; transition: box-shadow 0.2s, opacity 0.3s; }
    .task-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; }
    .task-card.completed { opacity: 0.65; }
    .task-card.completed .task-link { text-decoration: line-through !important; color: #999 !important; }
    .task-link:hover { color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    .nav-tabs .nav-link { color: #666; }
    .nav-tabs .nav-link.active { color: #C8A165; font-weight: 600; border-bottom: 2px solid #C8A165; }
    .min-width-0 { min-width: 0; }
    .status-toggle { display: inline-flex; border-radius: 20px; overflow: hidden; border: 1px solid #dee2e6; }
    .status-toggle .st-btn { border: none; padding: 3px 12px; font-size: 0.75rem; cursor: pointer; transition: all 0.15s; font-weight: 500; }
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
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-clipboard-list me-2"></i>Tasks</h3>
        @can('tasks.create')
            <a href="{{ route('tasks.create') }}" class="btn btn-gold btn-sm">
                <i class="fas fa-plus me-1"></i>New Task
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div id="taskToastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="taskTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab">
                <i class="fas fa-inbox me-1"></i>My Tasks
                <span class="badge bg-secondary ms-1">{{ $assignedTasks->total() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="created-tab" data-bs-toggle="tab" data-bs-target="#created" type="button" role="tab">
                <i class="fas fa-paper-plane me-1"></i>Created
                <span class="badge bg-secondary ms-1">{{ $createdTasks->total() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="taskTabsContent">
        {{-- ASSIGNED TO ME --}}
        <div class="tab-pane fade show active" id="assigned" role="tabpanel">
            @forelse ($assignedTasks as $task)
                @include('tasks::partials.task-card', ['task' => $task, 'showActions' => false])
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    <p>No tasks assigned to you yet.</p>
                    @can('tasks.create')
                        <a href="{{ route('tasks.create') }}" class="btn btn-gold btn-sm">Create a Task</a>
                    @endcan
                </div>
            @endforelse
            <div class="mt-3">
                {{ $assignedTasks->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>

        {{-- CREATED BY ME --}}
        <div class="tab-pane fade" id="created" role="tabpanel">
            @forelse ($createdTasks as $task)
                @include('tasks::partials.task-card', ['task' => $task, 'showActions' => true])
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-paper-plane fa-3x mb-3 d-block"></i>
                    <p>You haven't created any tasks yet.</p>
                    @can('tasks.create')
                        <a href="{{ route('tasks.create') }}" class="btn btn-gold btn-sm">Create your first task</a>
                    @endcan
                </div>
            @endforelse
            <div class="mt-3">
                {{ $createdTasks->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('taskToastContainer');

        function showToast(message, type) {
            const bg = type === 'success' ? 'bg-success' : 'bg-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const html = '<div class="toast align-items-center text-white ' + bg + ' border-0 show" role="alert">' +
                '<div class="d-flex"><div class="toast-body"><i class="fas ' + icon + ' me-2"></i>' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
            const el = document.createElement('div');
            el.innerHTML = html;
            container.appendChild(el.firstElementChild);
            setTimeout(function () {
                const t = container.querySelector('.toast');
                if (t) t.remove();
            }, 4000);
        }

        const statusColors = {
            pending: { border: '#ffc107' },
            in_progress: { border: '#0d6efd' },
            completed: { border: '#198754' }
        };

        document.querySelectorAll('.task-status-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const btn = this.querySelector('button[type="submit"]');
                if (btn.disabled) return;

                const card = this.closest('.task-card');
                const token = this.querySelector('input[name="_token"]')?.value || '';
                const status = this.querySelector('input[name="status"]')?.value || '';

                const body = new URLSearchParams();
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
                    if (!data.success) {
                        showToast(data.message || 'Error updating status', 'error');
                        return;
                    }

                    var newStatus = data.status;
                    var colors = statusColors[newStatus];

                    Object.keys(statusColors).forEach(function (s) {
                        card.classList.remove('status-' + s);
                    });
                    card.classList.add('status-' + newStatus);
                    card.querySelector('.card-body').style.borderLeftColor = colors.border;
                    card.classList.toggle('completed', newStatus === 'completed');

                    var toggle = card.querySelector('[data-task-toggle]');
                    if (toggle) {
                        toggle.querySelectorAll('.st-btn').forEach(function (b) {
                            var st = b.closest('form') ? b.closest('form').querySelector('input[name="status"]').value : '';
                            b.classList.remove('active');
                            b.disabled = false;
                            if (st === newStatus) {
                                b.classList.add('active');
                                b.disabled = true;
                            }
                        });
                    }

                    showToast('Task ' + (newStatus === 'completed' ? 'completed' : newStatus === 'in_progress' ? 'in progress' : 'pending'), 'success');
                })
                .catch(function () { showToast('Something went wrong.', 'error'); });
            });
        });
    });
</script>
@endsection
