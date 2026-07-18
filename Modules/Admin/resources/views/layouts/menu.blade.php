@can('access_admin_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#adminSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}" aria-controls="adminSubmenu">
    <i class="fas fa-user-shield fa-fw"></i>
    <span>Administration</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">

    <a href="{{ route('admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Access Control</div>

    @can('manage_users')
    <a href="{{ route('admin.users.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Users
    </a>
    @endcan

    @can('manage_roles')
    <a href="{{ route('admin.roles.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag fa-fw me-2"></i> Roles
    </a>
    @endcan

    @can('permissions.read')
    <a href="{{ route('admin.permissions.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
        <i class="fas fa-key fa-fw me-2"></i> Permissions
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">System</div>

    @can('manage_settings')
    <a href="{{ route('admin.modules.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
        <i class="fas fa-cubes fa-fw me-2"></i> Modules
    </a>

    <a href="{{ route('admin.appearance') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.appearance') ? 'active' : '' }}">
        <i class="fas fa-palette fa-fw me-2"></i> Appearance
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Configuration</div>

    @can('manage_settings')
    <a href="{{ route('admin.properties.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.properties.*') ? 'active' : '' }}">
        <i class="fas fa-building fa-fw me-2"></i> Properties
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Audit &amp; Logs</div>

    <a href="{{ route('admin.activity-logs.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
        <i class="fas fa-history fa-fw me-2"></i> Activity Logs
    </a>

    <a href="{{ route('admin.login-logs.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('admin.login-logs.*') ? 'active' : '' }}">
        <i class="fas fa-sign-in-alt fa-fw me-2"></i> Login History
    </a>

</div>
@endcan


