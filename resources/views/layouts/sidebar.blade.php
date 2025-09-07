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