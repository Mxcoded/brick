@extends('layouts.master')

@section('title', 'Tasks')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tasks</li>
@endsection

@section('styles')
<style>
    .task-card { border-radius: 10px; transition: box-shadow 0.2s, opacity 0.3s; border: 1px solid #f0f0f0 !important; }
    .task-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important; }
    .task-card.completed { opacity: 0.6; }
    .task-card.completed .task-link { text-decoration: line-through !important; color: #999 !important; }
    .task-link:hover { color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #fff; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #fff; }
    .btn-outline-gold { color: #C8A165; border-color: #C8A165; }
    .btn-outline-gold:hover { background-color: #C8A165; border-color: #C8A165; color: #fff; }

    .nav-tabs .nav-link { color: #666; border: none; padding: 10px 20px; }
    .nav-tabs .nav-link.active { color: #C8A165; font-weight: 600; border-bottom: 2px solid #C8A165; background: transparent; }
    .nav-tabs .nav-link .badge { font-size: 0.7rem; padding: 3px 7px; vertical-align: middle; }
    .nav-tabs .nav-link.active .badge { background: #C8A165 !important; color: #fff; }

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

    .stat-card { border-radius: 12px; border: 1px solid rgba(0,0,0,0.04); transition: transform 0.15s, box-shadow 0.15s; cursor: pointer; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .stat-card .stat-number { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
    .stat-card .stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.6px; }

    .kanban-column { min-height: 300px; border-radius: 12px; background: #f8f9fa; padding: 14px; border: 1px solid #eee; }
    .kanban-column .kanban-header { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 2px solid; }
    .kanban-column.droppable { background: #f0f7ff; }
    .kanban-card { cursor: grab; }
    .kanban-card:active { cursor: grabbing; }
    .kanban-column.drag-over { background: #e8f4fd; outline: 2px dashed #0d6efd; }

    .filter-bar { background: #fff; border-radius: 12px; padding: 20px; border: 1px solid #f0f0f0; }
    .filter-bar .form-control, .filter-bar .form-select { font-size: 0.85rem; }
    .filter-bar label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
    .bulk-bar { background: #1a1a2e; color: #fff; border-radius: 12px; padding: 10px 18px; display: none; align-items: center; gap: 12px; }
    .bulk-bar.show { display: flex; }
    .bulk-bar .bulk-count { font-weight: 600; font-size: 0.85rem; }
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

    {{-- Stats Bar --}}
    <div class="row g-2 mb-4">
        @php
            $statCards = [
                ['label' => 'Total', 'count' => $stats['total'], 'color' => '#2c3e50', 'bg' => '#f8f9fa', 'status' => ''],
                ['label' => 'Pending', 'count' => $stats['pending'], 'color' => '#ffc107', 'bg' => '#fff8e1', 'status' => 'pending'],
                ['label' => 'In Progress', 'count' => $stats['in_progress'], 'color' => '#0d6efd', 'bg' => '#e3f2fd', 'status' => 'in_progress'],
                ['label' => 'Completed', 'count' => $stats['completed'], 'color' => '#198754', 'bg' => '#e8f5e9', 'status' => 'completed'],
                ['label' => 'Overdue', 'count' => $stats['overdue'], 'color' => '#dc3545', 'bg' => '#fde8e8', 'status' => 'overdue'],
            ];
        @endphp
        @foreach ($statCards as $card)
            <div class="col-md col-6">
                <div class="stat-card p-3 text-center"
                     style="background: {{ $card['bg'] }};"
                     onclick="{{ $card['status'] ? "window.location.href='" . route('tasks.index', array_merge(request()->query(), ['status' => $card['status']])) . "'" : '' }}">
                    <div class="stat-number" style="color: {{ $card['color'] }};">{{ $card['count'] }}</div>
                    <div class="stat-label" style="color: {{ $card['color'] }};">{{ $card['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('tasks.index') }}" class="filter-bar mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search description or task #..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                    <option value="in_progress" @selected(($filters['status'] ?? '') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    <option value="high" @selected(($filters['priority'] ?? '') === 'high')>High</option>
                    <option value="medium" @selected(($filters['priority'] ?? '') === 'medium')>Medium</option>
                    <option value="low" @selected(($filters['priority'] ?? '') === 'low')>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Assignee</label>
                <select name="assignee_id" class="form-select">
                    <option value="">Anyone</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(($filters['assignee_id'] ?? '') == $emp->id)>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Date Range</label>
                <div class="d-flex gap-1">
                    <input type="date" name="date_from" class="form-control" placeholder="From" value="{{ $filters['date_from'] ?? '' }}" title="From date">
                    <input type="date" name="date_to" class="form-control" placeholder="To" value="{{ $filters['date_to'] ?? '' }}" title="To date">
                </div>
            </div>
            <div class="col-12 mt-2">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-gold px-4"><i class="fas fa-search me-1"></i>Apply</button>
                    <a href="{{ route('tasks.index', ['view' => $viewMode]) }}" class="btn btn-sm btn-outline-dark"><i class="fas fa-times me-1"></i>Clear</a>
                </div>
            </div>
        </div>
    </form>

    {{-- Bulk Action Bar --}}
    <div class="bulk-bar mb-3" id="bulkBar">
        <span class="bulk-count"><span id="bulkCount">0</span> selected</span>
        <select id="bulkStatusSelect" class="form-select form-select-sm" style="width: auto; color: #000;">
            <option value="">Change status...</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
        <button type="button" class="btn btn-sm btn-warning fw-semibold text-dark" id="bulkStatusBtn">Apply</button>
        @can('tasks.assign')
            <select id="bulkAssignSelect" class="form-select form-select-sm" style="width: auto; color: #000;">
                <option value="">Assign to...</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-warning fw-semibold text-dark" id="bulkAssignBtn">Apply</button>
        @endcan
        <button type="button" class="btn btn-sm btn-outline-light ms-auto" id="bulkClearBtn">Clear</button>
    </div>

    {{-- View Toggle --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
               class="btn {{ $viewMode === 'list' ? 'btn-gold' : 'btn-outline-dark' }}">
                <i class="fas fa-list me-1"></i>List
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}"
               class="btn {{ $viewMode === 'kanban' ? 'btn-gold' : 'btn-outline-dark' }}">
                <i class="fas fa-columns me-1"></i>Board
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="taskTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab">
                <i class="fas fa-inbox me-1"></i>My Tasks
                <span class="badge bg-dark ms-1">{{ method_exists($assignedTasks, 'total') ? $assignedTasks->total() : $assignedTasks->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="created-tab" data-bs-toggle="tab" data-bs-target="#created" type="button" role="tab">
                <i class="fas fa-paper-plane me-1"></i>Created
                <span class="badge bg-dark ms-1">{{ method_exists($createdTasks, 'total') ? $createdTasks->total() : $createdTasks->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="taskTabsContent">
        {{-- ASSIGNED TO ME --}}
        <div class="tab-pane fade show active" id="assigned" role="tabpanel">
            @if($viewMode === 'kanban')
                @include('tasks::partials.kanban-board', ['tasks' => $assignedTasks, 'showBulkCheckbox' => false])
            @else
                @forelse ($assignedTasks as $task)
                    @include('tasks::partials.task-card', ['task' => $task, 'showActions' => false, 'showAssigneeActions' => true, 'showBulkCheckbox' => false])
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
            @endif
        </div>

        {{-- CREATED BY ME --}}
        <div class="tab-pane fade" id="created" role="tabpanel">
            @if($viewMode === 'kanban')
                @include('tasks::partials.kanban-board', ['tasks' => $createdTasks, 'showBulkCheckbox' => true])
            @else
                @forelse ($createdTasks as $task)
                    @include('tasks::partials.task-card', ['task' => $task, 'showActions' => true, 'showAssigneeActions' => true, 'showBulkCheckbox' => true])
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
            @endif
        </div>
    </div>

    {{-- Hidden form for bulk operations --}}
    <form id="bulkForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="task_ids" id="bulkTaskIds">
    </form>
    <form id="bulkAssignForm" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="task_ids" id="bulkAssignTaskIds">
        <input type="hidden" name="employee_id" id="bulkAssignEmployeeId">
    </form>
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
            setTimeout(function () { const t = container.querySelector('.toast'); if (t) t.remove(); }, 4000);
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) { showToast(data.message || 'Error updating status', 'error'); return; }
                    var newStatus = data.status;
                    Object.keys(statusColors).forEach(function (s) { card.classList.remove('status-' + s); });
                    card.classList.add('status-' + newStatus);
                    card.querySelector('.card-body').style.borderLeftColor = statusColors[newStatus].border;
                    card.classList.toggle('completed', newStatus === 'completed');
                    var toggle = card.querySelector('[data-task-toggle]');
                    if (toggle) {
                        toggle.querySelectorAll('.st-btn').forEach(function (b) {
                            b.classList.remove('active');
                            b.disabled = false;
                            if (b.closest('form').querySelector('input[name="status"]').value === newStatus) {
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

        {{-- Bulk checkbox logic --}}
        const bulkCheckboxes = function () {
            return document.querySelectorAll('.task-bulk-checkbox:checked');
        };

        function updateBulkBar() {
            const bar = document.getElementById('bulkBar');
            const count = bulkCheckboxes().length;
            document.getElementById('bulkCount').textContent = count;
            bar.classList.toggle('show', count > 0);
        }

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('task-bulk-checkbox')) updateBulkBar();
        });

        document.getElementById('bulkClearBtn')?.addEventListener('click', function () {
            document.querySelectorAll('.task-bulk-checkbox').forEach(function (cb) { cb.checked = false; });
            updateBulkBar();
        });

        document.getElementById('bulkStatusBtn')?.addEventListener('click', function () {
            const ids = Array.from(bulkCheckboxes()).map(function (cb) { return cb.value; });
            const status = document.getElementById('bulkStatusSelect').value;
            if (!ids.length || !status) return;
            if (!confirm('Change ' + ids.length + ' task(s) to ' + status + '?')) return;
            document.getElementById('bulkTaskIds').value = JSON.stringify(ids);
            const form = document.getElementById('bulkForm');
            form.action = '{{ route('tasks.bulk.status') }}';
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PATCH';
            form.appendChild(methodInput);
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            form.appendChild(statusInput);
            form.submit();
        });

        document.getElementById('bulkAssignBtn')?.addEventListener('click', function () {
            const ids = Array.from(bulkCheckboxes()).map(function (cb) { return cb.value; });
            const employeeId = document.getElementById('bulkAssignSelect').value;
            if (!ids.length || !employeeId) return;
            if (!confirm('Assign ' + ids.length + ' task(s) to selected staff?')) return;
            document.getElementById('bulkAssignTaskIds').value = JSON.stringify(ids);
            document.getElementById('bulkAssignEmployeeId').value = employeeId;
            const form = document.getElementById('bulkAssignForm');
            form.action = '{{ route('tasks.bulk.assign') }}';
            form.submit();
        });
    });
</script>
@endsection
