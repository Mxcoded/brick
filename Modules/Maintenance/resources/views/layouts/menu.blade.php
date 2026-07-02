@canany(['access_maintenance_dashboard', 'maintenance.read', 'maintenance.create'])
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#maintenanceSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('maintenance.*') ? 'true' : 'false' }}" aria-controls="maintenanceSubmenu">
    <i class="fas fa-tools fa-fw"></i>
    <span>Maintenance</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('maintenance.*') ? 'show' : '' }}" id="maintenanceSubmenu">

    @can('access_tasks_dashboard')
    <a href="{{ route('maintenance.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.index') || request()->routeIs('maintenance.show') ? 'active' : '' }}">
        <i class="fas fa-list fa-fw me-2"></i> All Logs
    </a>
@endcanany

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Management</div>

    <a href="{{ route('maintenance.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
    </a>

    <a href="{{ route('maintenance.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle fa-fw me-2"></i> New Log
    </a>

    <a href="{{ route('maintenance.report') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.report') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw me-2"></i> Reports
    </a>

    <a href="{{ route('maintenance.readings.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.readings.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list fa-fw me-2"></i> Daily Readings
    </a>

</div>
@endcan
