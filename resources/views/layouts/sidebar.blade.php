<div class="border-end bg-dark" id="sidebar-wrapper">
    <div class="sidebar-heading">
        <div
            style="display: inline-block; padding: 10px 20px;   border-radius: 12px; background: var(--glass-effect); border: 1px solid var(--glass-border);
                box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2), 
                            -4px -4px 15px rgba(255, 255, 255, 0.05); transform: perspective(600px) rotateX(2deg); transition: var(--transition); margin-right: 15px;">

            <a href="home"
                style="
                font-weight: 800;
                font-size: 1.4rem;
                color: #fff;
                text-decoration: none;
                letter-spacing: -0.5px;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            ">
                BRICKSPOINT<sup>&trade;</sup><sub style="font-size:9pt;">ERP</sub>
            </a>
        </div>
    </div>
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.dashboard', 'admin.dashboard') ? 'active' : '' }}" href="{{ route('home') }}">
            <i class="fas fa-home fa-fw me-3"></i>Home
        </a>
@can('manage-registrations')
    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('frontdesk.registrations.*') ? 'active' : '' }}" href="{{ route('frontdesk.registrations.index') }}">
        <i class="fas fa-bed fa-fw me-3"></i>Guest Registrations
    </a>
@endcan
        <a class="list-group-item list-group-item-action p-3 justify-content-between d-flex align-items-center" data-bs-toggle="collapse" href="#leavesSubmenu" role="button" aria-expanded="{{ request()->routeIs('staff.leaves.*') ? 'true' : 'false' }}" aria-controls="leavesSubmenu">
            <span><i class="fas fa-calendar-alt fa-fw me-3"></i>Leaves</span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </a>
        <div class="collapse submenu {{ request()->routeIs('staff.leaves.*') ? 'show' : '' }}" id="leavesSubmenu">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.index') ? 'active' : '' }}" href="{{ route('staff.leaves.index') }}"><i class="fas fa-user-clock fa-fw me-3"></i>My Leaves</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.request') ? 'active' : '' }}" href="{{ route('staff.leaves.request') }}"><i class="fas fa-plus-circle fa-fw me-3"></i>New Request</a>
                @can('apply-leave-for-others')
                    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.apply') ? 'active' : '' }}" href="{{ route('staff.leaves.admin.apply') }}"><i class="fas fa-user-pen fa-fw me-3"></i>Apply for Staff</a>
                @endcan
                @can('manage-leaves')
                    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}" href="{{ route('staff.leaves.admin') }}"><i class="fas fa-tasks fa-fw me-3"></i>Manage Requests</a>
                    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.balances') ? 'active' : '' }}" href="{{ route('staff.leaves.admin.balances') }}"><i class="fas fa-wallet fa-fw me-3"></i>Manage Balances</a>
                    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.history') ? 'active' : '' }}" href="{{ route('staff.leaves.admin.history') }}"><i class="fas fa-history fa-fw me-3"></i>Leave History</a>
                @endcan
            </div>
        </div>

        <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
            <i class="fa fa-list-alt fa-fw me-3"></i>Tasks
        </a>

        @can('staff-view')
            <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.index') ? 'active' : '' }}" href="{{ route('staff.index') }}">
                <i class="fa fa-users fa-fw me-3"></i>Staff List
            </a>
        @endcan

        @can('manage-user')
            <a class="list-group-item list-group-item-action p-3 justify-content-between d-flex align-items-center" data-bs-toggle="collapse" href="#userSubmenu" role="button" aria-expanded="{{ request()->routeIs('admin.users.*', 'admin.permissions.*', 'admin.roles.*') ? 'true' : 'false' }}" aria-controls="userSubmenu">
                <span><i class="fas fa-user-shield fa-fw me-3"></i>Manage User</span>
                <i class="fas fa-chevron-down fa-xs"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.users.*', 'admin.permissions.*', 'admin.roles.*') ? 'show' : '' }}" id="userSubmenu">
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fas fa-users-cog fa-fw me-3"></i>Users</a>
                    @can('manage-roles-permission')
                        <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}"><i class="fas fa-key fa-fw me-3"></i>Permissions</a>
                        <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><i class="fas fa-user-tag fa-fw me-3"></i>Roles</a>
                    @endcan
                </div>
            </div>
        @endcan
        <a class="list-group-item list-group-item-action p-3 justify-content-between d-flex align-items-center" data-bs-toggle="collapse" href="#inventorySubmenu" role="button" aria-expanded="{{ request()->routeIs('inventory.*') ? 'true' : 'false' }}" aria-controls="inventorySubmenu">
            <span><i class="fas fa-warehouse fa-fw me-3"></i>Inventory</span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </a>
        <div class="collapse submenu {{ request()->routeIs('inventory.*') ? 'show' : '' }}" id="inventorySubmenu">
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}"><i class="fas fa-list-alt fa-fw me-3"></i>Dashboard</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.create') ? 'active' : '' }}" href="{{ route('inventory.items.create') }}"><i class="fas fa-plus-circle fa-fw me-3"></i>Add New Item</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.transfers.index') ? 'active' : '' }}" href="{{ route('inventory.transfers.index') }}"><i class="fas fa-exchange-alt fa-fw me-3"></i>Transfer Items</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.usage') ? 'active' : '' }}" href="{{ route('inventory.usage') }}"><i class="fas fa-toolbox fa-fw me-3"></i>Record Usage</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.suppliers.index') ? 'active' : '' }}" href="{{ route('inventory.suppliers.index') }}"><i class="fas fa-truck-loading fa-fw me-3"></i>Manage Suppliers</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.stores.index') ? 'active' : '' }}" href="{{ route('inventory.stores.index') }}"><i class="fas fa-store fa-fw me-3"></i>Manage Stores</a>
                 <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.departments.index') ? 'active' : '' }}" href="{{ route('inventory.departments.index') }}"><i class="fas fa-users fa-fw me-3"></i>Manage Departments</a>
                <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.report') ? 'active' : '' }}" href="{{ route('inventory.report') }}"><i class="fas fa-file fa-fw me-3"></i>Inventory Report</a>
            </div>
        </div>
        <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">
            <i class="fa fa-tools fa-fw me-3"></i>Maintenance Log
        </a>

        <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('banquet.orders.*') ? 'active' : '' }}" href="{{ route('banquet.orders.index') }}">
            <i class="fa fa-utensils fa-fw me-3"></i>Banquet
        </a>
        
        @can('manage-gym')
            <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('gym.*') ? 'active' : '' }}" href="{{ route('gym.index') }}">
                <i class="fas fa-dumbbell fa-fw me-3"></i>Gym
            </a>
        @endcan

    </div>
</div>