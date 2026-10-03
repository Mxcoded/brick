<x-filament-panels::page>
    @php
        $priorityStyles = [
            'high' => 'bg-danger-100 text-danger-700 dark:bg-danger-400/20 dark:text-danger-400',
            'medium' => 'bg-warning-100 text-warning-700 dark:bg-warning-400/20 dark:text-warning-400',
            'low' => 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300',
        ];
        $columnStyles = [
            'pending' => ['ring' => 'ring-warning-500/30', 'dot' => 'bg-warning-500'],
            'in_progress' => ['ring' => 'ring-info-500/30', 'dot' => 'bg-info-500'],
            'completed' => ['ring' => 'ring-success-500/30', 'dot' => 'bg-success-500'],
        ];
    @endphp

    <div class="fi-section mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-3">
            <div class="col-span-1">
                <label for="board-search" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Search
                </label>
                <input id="board-search" type="search" wire:model.live.debounce.400ms="search"
                    placeholder="Reference or description"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900" />
            </div>

            <div class="col-span-1">
                <label for="board-assignee" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Assigned to
                </label>
                <select id="board-assignee" wire:model.live="assignee"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900">
                    <option value="">Anyone</option>
                    @foreach ($this->employeeOptions() as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-1 flex items-end gap-2">
                @if (filled($search) || filled($assignee))
                    <x-filament::button color="gray" outlined wire:click="$set('search', null); $set('assignee', null)">
                        <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                        Clear
                    </x-filament::button>
                @endif

                <x-filament::button color="gray" outlined tag="a"
                    href="{{ \Modules\Tasks\Filament\Resources\Tasks\TaskResource::getUrl('index') }}">
                    <x-filament::icon icon="heroicon-o-list-bullet" class="h-4 w-4" />
                    Table view
                </x-filament::button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @foreach ($this->columns() as $status => $column)
            @php
                $tasks = $this->tasksFor($status);
                $style = $columnStyles[$status];
            @endphp

            <div wire:key="board-column-{{ $status }}"
                class="kanban-column flex min-h-96 flex-col rounded-xl bg-gray-100/60 p-3 ring-1 ring-gray-950/5 dark:bg-gray-800/40 dark:ring-white/10"
                data-status="{{ $status }}"
                id="kanban-col-{{ $status }}">

                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $style['dot'] }}"></span>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $column['label'] }}
                        </h3>
                    </div>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-medium text-gray-600 shadow-sm dark:bg-gray-900 dark:text-gray-300">
                        {{ $tasks->count() }}
                    </span>
                </div>

                <div class="kanban-cards flex flex-1 flex-col gap-2" data-status="{{ $status }}">
                    @forelse ($tasks as $task)
                        @php
                            $moveable = $this->canMove($task->id);
                        @endphp

                        <div wire:key="board-card-{{ $task->id }}"
                            class="task-card rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 transition dark:bg-gray-900 dark:ring-white/10 {{ $moveable ? 'cursor-grab' : 'cursor-default' }} {{ $task->status === 'completed' ? 'opacity-70' : '' }}"
                            data-task-id="{{ $task->id }}"
                            @if ($moveable) draggable="true" @endif>

                            <div class="mb-1 flex items-start justify-between gap-2">
                                <a href="{{ $this->viewUrl($task->id) }}"
                                    class="line-clamp-2 text-sm font-medium text-gray-950 hover:underline dark:text-white">
                                    {{ $task->description }}
                                </a>
                                <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $task->task_number }}
                                </span>
                            </div>

                            <div class="mb-2 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">
                                    {{ ucfirst($task->priority) }}
                                </span>

                                @if ($task->is_recurring)
                                    <span
                                        class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-400/20 dark:text-primary-400"
                                        title="Repeats {{ str_replace('_', ' ', $task->recurrence_type) }}">
                                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-3 w-3" />
                                        {{ ucfirst($task->recurrence_type) }}
                                    </span>
                                @endif

                                <span @class([
                                    'text-xs text-gray-500 dark:text-gray-400',
                                    'font-semibold text-danger-600 dark:text-danger-400' => $this->isOverdue($task),
                                ])>
                                    {{ $this->deadlineLabel($task) }}
                                </span>
                            </div>

                            <div class="mb-2 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <x-filament::icon icon="heroicon-o-user" class="h-3.5 w-3.5" />
                                <span class="truncate">
                                    {{ $task->employees->isNotEmpty() ? $task->employees->pluck('name')->implode(', ') : $task->creator?->name }}
                                </span>
                            </div>

                            @if ($moveable)
                                <div class="flex items-center gap-1">
                                    @if ($task->status !== 'pending')
                                        <x-filament::button size="xs" color="gray" outlined
                                            wire:click="reopen({{ $task->id }})" wire:loading.attr="disabled">
                                            Pending
                                        </x-filament::button>
                                    @endif

                                    @if ($task->status !== 'in_progress')
                                        <x-filament::button size="xs" color="info" outlined
                                            wire:click="start({{ $task->id }})" wire:loading.attr="disabled">
                                            In progress
                                        </x-filament::button>
                                    @endif

                                    @if ($task->status !== 'completed')
                                        <x-filament::button size="xs" color="success"
                                            wire:click="complete({{ $task->id }})" wire:loading.attr="disabled">
                                            Complete
                                        </x-filament::button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 py-8 text-center dark:border-gray-700">
                            <x-filament::icon icon="heroicon-o-inbox" class="h-6 w-6 text-gray-400 dark:text-gray-600" />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                No tasks
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const columns = document.querySelectorAll('.kanban-cards[data-status]');

                // Delegated, so cards re-rendered by Livewire still drag.
                document.addEventListener('dragstart', function (e) {
                    const card = e.target.closest('.task-card[draggable="true"]');

                    if (!card) {
                        return;
                    }

                    e.dataTransfer.setData('text/plain', card.dataset.taskId);
                    e.dataTransfer.effectAllowed = 'move';
                });

                columns.forEach(function (column) {
                    const wrapper = column.closest('.kanban-column');

                    column.addEventListener('dragover', function (e) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                        wrapper.classList.add('ring-2', 'ring-primary-500');
                    });

                    column.addEventListener('dragleave', function () {
                        wrapper.classList.remove('ring-2', 'ring-primary-500');
                    });

                    column.addEventListener('drop', function (e) {
                        e.preventDefault();
                        wrapper.classList.remove('ring-2', 'ring-primary-500');

                        const taskId = e.dataTransfer.getData('text/plain');
                        const newStatus = column.dataset.status;

                        if (!taskId || !newStatus) {
                            return;
                        }

                        @this.setStatus(parseInt(taskId, 10), newStatus);
                    });
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
