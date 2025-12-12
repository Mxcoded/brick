@can('access_tasks_dashboard')
<a href="{{ route('tasks.index') }}"
   class="list-group-item list-group-item-action p-3 {{ request()->routeIs('tasks.*') ? 'active' : '' }}"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <i class="fas fa-clipboard-list fa-fw me-3"></i> Tasks
</a>
@endcan