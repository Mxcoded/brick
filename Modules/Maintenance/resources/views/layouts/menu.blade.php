<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
    data-bs-toggle="collapse" href="#maintenanceSubmenu" role="button"
    aria-expanded="{{ request()->routeIs('maintenance.*') ? 'true' : 'false' }}"
    aria-controls="maintenanceSubmenu">
    <span><i class="fas fa-tools fa-fw me-3"></i> Maintenance</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('maintenance.*') ? 'show' : '' }}" id="maintenanceSubmenu">

    {{-- Everyone with access_tasks_dashboard (regular staff) --}}
    @can('access_tasks_dashboard')
        <a href="{{ route('maintenance.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.index') || request()->routeIs('maintenance.show') ? 'active' : '' }}"><i class="fas fa-list me-2"></i> All Logs</a>
    @endcan

    {{-- Maintenance managers --}}
    @can('access_maintenance_dashboard')
        <div class="sidebar-divider"></div>
        <small class="sidebar-subheading">Management</small>

        <a href="{{ route('maintenance.dashboard') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="{{ route('maintenance.create') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.create') ? 'active' : '' }}"><i class="fas fa-plus-circle me-2"></i> New Log</a>
        <a href="{{ route('maintenance.report') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.report') ? 'active' : '' }}"><i class="fas fa-chart-bar me-2"></i> Reports</a>
        <a href="{{ route('maintenance.readings.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('maintenance.readings.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list me-2"></i> Daily Readings</a>
    @endcan

</div>
