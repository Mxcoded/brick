@can('access_admin_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#adminSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}" aria-controls="adminSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-user-shield fa-fw me-3"></i> Admin</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none;">Overview</a>
    
    @can('manage_users')
    <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Users</a>
    @endcan

    @can('manage_roles')
    <a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Roles</a>
    @endcan

    @can('manage_permissions')
    <a href="{{ route('admin.permissions.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Permissions</a>
    @endcan
</div>
@endcan