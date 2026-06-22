@can('access_tasks_dashboard')
<a href="{{ route('tasks.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
    <i class="fas fa-clipboard-list fa-fw"></i>
    <span>Tasks</span>
</a>
@endcan
