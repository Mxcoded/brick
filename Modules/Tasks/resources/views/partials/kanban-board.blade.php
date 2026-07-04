@php
    $columns = [
        'pending' => ['label' => 'Pending', 'color' => '#ffc107', 'bg' => '#fff8e1'],
        'in_progress' => ['label' => 'In Progress', 'color' => '#0d6efd', 'bg' => '#e3f2fd'],
        'completed' => ['label' => 'Completed', 'color' => '#198754', 'bg' => '#e8f5e9'],
    ];
    $grouped = ['pending' => collect(), 'in_progress' => collect(), 'completed' => collect()];
    foreach ($tasks as $t) {
        $grouped[$t->status]->push($t);
    }
@endphp

<div class="row g-3">
    @foreach ($columns as $key => $col)
        <div class="col-md-4">
            <div class="kanban-column" data-status="{{ $key }}" id="kanban-col-{{ $key }}">
                <div class="kanban-header d-flex justify-content-between align-items-center" style="border-bottom-color: {{ $col['color'] }}; color: {{ $col['color'] }};">
                    <span>{{ $col['label'] }}</span>
                    <span class="badge" style="background: {{ $col['color'] }}; color: #fff;">{{ $grouped[$key]->count() }}</span>
                </div>
                <div class="kanban-cards">
                    @forelse ($grouped[$key] as $task)
                        @include('tasks::partials.task-card', [
                            'task' => $task,
                            'showActions' => isset($showBulkCheckbox) && $showBulkCheckbox,
                            'showAssigneeActions' => true,
                            'showBulkCheckbox' => isset($showBulkCheckbox) && $showBulkCheckbox,
                            'draggable' => true,
                        ])
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            <span>No tasks</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const draggables = document.querySelectorAll('.task-card[draggable="true"]');
        const columns = document.querySelectorAll('.kanban-column');

        draggables.forEach(function (card) {
            card.addEventListener('dragstart', function (e) {
                e.dataTransfer.setData('text/plain', card.dataset.taskId);
                e.dataTransfer.effectAllowed = 'move';
            });
        });

        columns.forEach(function (col) {
            col.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                this.classList.add('drag-over');
            });

            col.addEventListener('dragleave', function () {
                this.classList.remove('drag-over');
            });

            col.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                const taskId = e.dataTransfer.getData('text/plain');
                const newStatus = this.dataset.status;
                if (!taskId || !newStatus) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('tasks') }}/' + taskId + '/status';
                form.style.display = 'none';
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'PATCH';
                form.appendChild(method);
                const status = document.createElement('input');
                status.type = 'hidden';
                status.name = 'status';
                status.value = newStatus;
                form.appendChild(status);
                document.body.appendChild(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(form)).toString(),
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(function () {});
                document.body.removeChild(form);
            });
        });
    });
</script>
