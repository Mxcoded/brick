@can('access_admin_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#adminSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}" aria-controls="adminSubmenu">
    <span><i class="fas fa-user-shield fa-fw me-3"></i> Administration</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">

    {{-- Overview --}}
    <a href="{{ route('admin.dashboard') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt me-2"></i> Overview
    </a>

    <div class="sidebar-divider"></div>
    <small class="sidebar-subheading">Access Control</small>

    @can('manage_users')
    <a href="{{ route('admin.users.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="fas fa-users me-2"></i> Users
    </a>
    @endcan

    @can('manage_roles')
    <a href="{{ route('admin.roles.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag me-2"></i> Roles
    </a>
    @endcan

    @can('permissions.read')
    <a href="{{ route('admin.permissions.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
        <i class="fas fa-key me-2"></i> Permissions
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <small class="sidebar-subheading">System</small>

    @can('manage_settings')
    <a href="{{ route('admin.modules.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
        <i class="fas fa-cubes me-2"></i> Modules
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <small class="sidebar-subheading">Audit &amp; Logs</small>

    <a href="{{ route('admin.activity-logs.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
        <i class="fas fa-history me-2"></i> Activity Logs
    </a>

    <a href="{{ route('admin.login-logs.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.login-logs.*') ? 'active' : '' }}">
        <i class="fas fa-sign-in-alt me-2"></i> Login History
    </a>

</div>
@endcan
