<div class="border-end bg-dark" id="sidebar-wrapper">

    <!-- Sidebar Heading -->
    <div class="sidebar-heading">
        <div class="brand-wrapper">
            <a href="{{ route('home') }}" class="brand-link">
                BRICKSPOINT<sup>&trade;</sup><sub class="brand-sub">ERP</sub>
            </a>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="list-group list-group-flush">

        <!-- Home -->
        <a href="{{ route('home') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.dashboard', 'admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home fa-fw me-3"></i> Home
        </a>

 <!-- Check-In Management -->
@can('manage-checkins')
    <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse" href="#checkinSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('frontdesk.registrations.*') ? 'true' : 'false' }}"
        aria-controls="checkinSubmenu">
        <span><i class="fas fa-bed fa-fw me-3"></i> Check-In Management</span>
        <i class="fas fa-chevron-down fa-xs"></i>
    </a>

    <div class="collapse submenu {{ request()->routeIs('frontdesk.registrations.*') ? 'show' : '' }}"
        id="checkinSubmenu">
        <div class="list-group list-group-flush">
            <a href="{{ route('frontdesk.registrations.index') }}"
                class="list-group-item list-group-item-action p-3 {{ request()->routeIs('frontdesk.registrations.index') ? 'active' : '' }}">
                <i class="fas fa-list fa-fw me-3"></i> Guest Registrations
            </a>
            <a class="list-group-item list-group-item-action p-3 text-white {{ request()->routeIs('frontdesk.registrations.agent-checkin') ? 'active' : '' }}"
                href="{{ route('frontdesk.registrations.agent-checkin') }}">
                <i class="fas fa-user-plus fa-fw me-3"></i>New Check-In
            </a>
            {{-- Optional: Add guest draft link if public access needed --}}
            <a href="{{ route('frontdesk.registrations.create') }}" class="list-group-item list-group-item-action p-3 text-white"
                target="_blank" rel="noopener">
                <i class="fas fa-user-check fa-fw me-3"></i>Guest Draft Form (Public)
            </a>
        </div>
    </div>
@endcan


        <!-- Leaves -->
        <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse" href="#leavesSubmenu" role="button"
            aria-expanded="{{ request()->routeIs('staff.leaves.*') ? 'true' : 'false' }}" aria-controls="leavesSubmenu">
            <span><i class="fas fa-calendar-alt fa-fw me-3"></i> Leaves</span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </a>
        <div class="collapse submenu {{ request()->routeIs('staff.leaves.*') ? 'show' : '' }}" id="leavesSubmenu">
            <div class="list-group list-group-flush">
                <a href="{{ route('staff.leaves.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.index') ? 'active' : '' }}">
                    <i class="fas fa-user-clock fa-fw me-3"></i> My Leaves
                </a>
                <a href="{{ route('staff.leaves.request') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.request') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle fa-fw me-3"></i> New Request
                </a>
                @can('apply-leave-for-others')
                    <a href="{{ route('staff.leaves.admin.apply') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.apply') ? 'active' : '' }}">
                        <i class="fas fa-user-pen fa-fw me-3"></i> Apply for Staff
                    </a>
                @endcan
                @can('manage-leaves')
                    <a href="{{ route('staff.leaves.admin') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}">
                        <i class="fas fa-tasks fa-fw me-3"></i> Manage Requests
                    </a>
                    <a href="{{ route('staff.leaves.admin.balances') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.balances') ? 'active' : '' }}">
                        <i class="fas fa-wallet fa-fw me-3"></i> Manage Balances
                    </a>
                    <a href="{{ route('staff.leaves.admin.history') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.history') ? 'active' : '' }}">
                        <i class="fas fa-history fa-fw me-3"></i> Leave History
                    </a>
                @endcan
            </div>
        </div>

        <!-- Tasks -->
        <a href="{{ route('tasks.index') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
            <i class="fa fa-list-alt fa-fw me-3"></i> Tasks
        </a>

        <!-- Staff List -->
        @can('staff-view')
            <a href="{{ route('staff.index') }}"
                class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.index') ? 'active' : '' }}">
                <i class="fa fa-users fa-fw me-3"></i> Staff List
            </a>
        @endcan

        <!-- Manage User -->
        @can('manage-user')
            <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse" href="#userSubmenu" role="button"
                aria-expanded="{{ request()->routeIs('admin.users.*', 'admin.permissions.*', 'admin.roles.*') ? 'true' : 'false' }}"
                aria-controls="userSubmenu">
                <span><i class="fas fa-user-shield fa-fw me-3"></i> Manage User</span>
                <i class="fas fa-chevron-down fa-xs"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.users.*', 'admin.permissions.*', 'admin.roles.*') ? 'show' : '' }}"
                id="userSubmenu">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.users.index') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <i class="fas fa-users-cog fa-fw me-3"></i> Users
                    </a>
                    @can('manage-roles-permission')
                        <a href="{{ route('admin.permissions.index') }}"
                            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}">
                            <i class="fas fa-key fa-fw me-3"></i> Permissions
                        </a>
                        <a href="{{ route('admin.roles.index') }}"
                            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}">
                            <i class="fas fa-user-tag fa-fw me-3"></i> Roles
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        <!-- Inventory -->
        <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse" href="#inventorySubmenu" role="button"
            aria-expanded="{{ request()->routeIs('inventory.*') ? 'true' : 'false' }}"
            aria-controls="inventorySubmenu">
            <span><i class="fas fa-warehouse fa-fw me-3"></i> Inventory</span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </a>
        <div class="collapse submenu {{ request()->routeIs('inventory.*') ? 'show' : '' }}" id="inventorySubmenu">
            <div class="list-group list-group-flush">
                <a href="{{ route('inventory.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                    <i class="fas fa-list-alt fa-fw me-3"></i> Dashboard
                </a>
                <a href="{{ route('inventory.items.create') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.items.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle fa-fw me-3"></i> Add New Item
                </a>
                <a href="{{ route('inventory.transfers.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.transfers.index') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt fa-fw me-3"></i> Transfer Items
                </a>
                <a href="{{ route('inventory.usage') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.usage') ? 'active' : '' }}">
                    <i class="fas fa-toolbox fa-fw me-3"></i> Record Usage
                </a>
                <a href="{{ route('inventory.suppliers.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.suppliers.index') ? 'active' : '' }}">
                    <i class="fas fa-truck-loading fa-fw me-3"></i> Manage Suppliers
                </a>
                <a href="{{ route('inventory.stores.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.stores.index') ? 'active' : '' }}">
                    <i class="fas fa-store fa-fw me-3"></i> Manage Stores
                </a>
                <a href="{{ route('inventory.departments.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.departments.index') ? 'active' : '' }}">
                    <i class="fas fa-users fa-fw me-3"></i> Manage Departments
                </a>
                <a href="{{ route('inventory.report') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.report') ? 'active' : '' }}">
                    <i class="fas fa-file fa-fw me-3"></i> Inventory Report
                </a>
            </div>
        </div>

        <!-- Maintenance -->
        <a href="{{ route('maintenance.index') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
            <i class="fa fa-tools fa-fw me-3"></i> Maintenance Log
        </a>

        <!-- Banquet -->
        <a href="{{ route('banquet.orders.index') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('banquet.orders.*') ? 'active' : '' }}">
            <i class="fa fa-utensils fa-fw me-3"></i> Banquet
        </a>

        <!-- Gym -->
        @can('manage-gym')
            <a href="{{ route('gym.index') }}"
                class="list-group-item list-group-item-action p-3 {{ request()->routeIs('gym.*') ? 'active' : '' }}">
                <i class="fas fa-dumbbell fa-fw me-3"></i> Gym
            </a>
        @endcan

    </div>
</div>

<!-- CSS for branding -->
<style>
    .brand-wrapper {
        display: inline-block;
        padding: 10px 20px;
        margin-right: 15px;
        border-radius: 12px;
        background: var(--glass-effect);
        border: 1px solid var(--glass-border);
        box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2),
            -4px -4px 15px rgba(255, 255, 255, 0.05);
        transform: perspective(600px) rotateX(2deg);
        transition: var(--transition);
    }

    .brand-link {
        font-weight: 800;
        font-size: 1.4rem;
        color: #fff;
        text-decoration: none;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .brand-sub {
        font-size: 9pt;
    }
</style>
