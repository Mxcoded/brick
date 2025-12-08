{{-- <div class="border-end" id="sidebar-wrapper" style="background-color: #333333;">

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
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.dashboard', 'admin.dashboard') ? 'active' : '' }}"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <i class="fas fa-home fa-fw me-3"></i> Home
        </a>

        <!-- Check-In Management -->
        @can('manage-checkins')
            <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse" href="#checkinSubmenu" role="button"
                aria-expanded="{{ request()->routeIs('frontdesk.registrations.*') ? 'true' : 'false' }}"
                aria-controls="checkinSubmenu"
                style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                <span><i class="fas fa-bed fa-fw me-3"></i> FrontDesk Portal</span>
                <i class="fas fa-chevron-down fa-xs"></i>
            </a>

            <div class="collapse submenu {{ request()->routeIs('frontdesk.registrations.*') ? 'show' : '' }}"
                id="checkinSubmenu">
                <div class="list-group list-group-flush">
                    <a href="{{ route('frontdesk.registrations.index') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('frontdesk.registrations.*') && !request()->routeIs('frontdesk.registrations.createWalkin') ? 'active' : '' }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-list fa-fw me-3"></i> Guest Registrations
                    </a>
                    <a class="list-group-item list-group-item-action p-3 {{ request()->routeIs('frontdesk.registrations.createWalkin') ? 'active' : '' }}"
                        href="{{ route('frontdesk.registrations.createWalkin') }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-user-plus fa-fw me-3"></i>New Check-In
                    </a>

                </div>
            </div>
        @endcan


        <!-- Leaves -->
        <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
            data-bs-toggle="collapse" href="#leavesSubmenu" role="button"
            aria-expanded="{{ request()->routeIs('staff.leaves.*') ? 'true' : 'false' }}" aria-controls="leavesSubmenu"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <span><i class="fas fa-calendar-alt fa-fw me-3"></i> Leaves</span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </a>
        <div class="collapse submenu {{ request()->routeIs('staff.leaves.*') ? 'show' : '' }}" id="leavesSubmenu">
            <div class="list-group list-group-flush">
                <a href="{{ route('staff.leaves.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.index') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-user-clock fa-fw me-3"></i> My Leaves
                </a>
                <a href="{{ route('staff.leaves.request') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.request') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-plus-circle fa-fw me-3"></i> New Request
                </a>
                @can('apply-leave-for-others')
                    <a href="{{ route('staff.leaves.admin.apply') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.apply') ? 'active' : '' }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-user-pen fa-fw me-3"></i> Apply for Staff
                    </a>
                @endcan
                @can('manage-leaves')
                    <a href="{{ route('staff.leaves.admin') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-tasks fa-fw me-3"></i> Manage Requests
                    </a>
                    <a href="{{ route('staff.leaves.admin.balances') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.balances') ? 'active' : '' }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-wallet fa-fw me-3"></i> Manage Balances
                    </a>
                    <a href="{{ route('staff.leaves.admin.history') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.leaves.admin.history') ? 'active' : '' }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-history fa-fw me-3"></i> Leave History
                    </a>
                @endcan
            </div>
        </div>

        <!-- Tasks -->
        <a href="{{ route('tasks.index') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('tasks.*') ? 'active' : '' }}"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <i class="fa fa-list-alt fa-fw me-3"></i> Tasks
        </a>

        <!-- Staff List -->
        @can('staff-view')
            <a href="{{ route('staff.index') }}"
                class="list-group-item list-group-item-action p-3 {{ request()->routeIs('staff.index') ? 'active' : '' }}"
                style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                <i class="fa fa-users fa-fw me-3"></i> Staff List
            </a>
        @endcan

        <!-- Manage User -->
        @can('manage-user')
            <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
                data-bs-toggle="collapse" href="#userSubmenu" role="button"
                aria-expanded="{{ request()->routeIs('admin.users.*', 'admin.permissions.*', 'admin.roles.*') ? 'true' : 'false' }}"
                aria-controls="userSubmenu"
                style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                <span><i class="fas fa-user-shield fa-fw me-3"></i> Manage User</span>
                <i class="fas fa-chevron-down fa-xs"></i>
            </a>
            <div class="collapse submenu {{ request()->routeIs('admin.users.*', 'admin.permissions.*', 'admin.roles.*') ? 'show' : '' }}"
                id="userSubmenu">
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.users.index') }}"
                        class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.users.index') ? 'active' : '' }}"
                        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                        <i class="fas fa-users-cog fa-fw me-3"></i> Users
                    </a>
                    @can('manage-roles-permission')
                        <a href="{{ route('admin.permissions.index') }}"
                            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}"
                            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                            <i class="fas fa-key fa-fw me-3"></i> Permissions
                        </a>
                        <a href="{{ route('admin.roles.index') }}"
                            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('admin.roles.index') ? 'active' : '' }}"
                            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
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
            aria-controls="inventorySubmenu"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <span><i class="fas fa-warehouse fa-fw me-3"></i> Inventory</span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </a>
        <div class="collapse submenu {{ request()->routeIs('inventory.*') ? 'show' : '' }}" id="inventorySubmenu">
            <div class="list-group list-group-flush">
                <a href="{{ route('inventory.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.index') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-list-alt fa-fw me-3"></i> Dashboard
                </a>
                <a href="{{ route('inventory.items.create') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.items.create') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-plus-circle fa-fw me-3"></i> Add New Item
                </a>
                <a href="{{ route('inventory.transfers.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.transfers.index') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-exchange-alt fa-fw me-3"></i> Transfer Items
                </a>
                <a href="{{ route('inventory.usage') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.usage') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-toolbox fa-fw me-3"></i> Record Usage
                </a>
                <a href="{{ route('inventory.suppliers.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.suppliers.index') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-truck-loading fa-fw me-3"></i> Manage Suppliers
                </a>
                <a href="{{ route('inventory.stores.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.stores.index') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-store fa-fw me-3"></i> Manage Stores
                </a>
                <a href="{{ route('inventory.departments.index') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.departments.index') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-users fa-fw me-3"></i> Manage Departments
                </a>
                <a href="{{ route('inventory.report') }}"
                    class="list-group-item list-group-item-action p-3 {{ request()->routeIs('inventory.report') ? 'active' : '' }}"
                    style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
                    <i class="fas fa-file fa-fw me-3"></i> Inventory Report
                </a>
            </div>
        </div>

        <!-- Maintenance -->
        <a href="{{ route('maintenance.index') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('maintenance.*') ? 'active' : '' }}"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <i class="fa fa-tools fa-fw me-3"></i> Maintenance Log
        </a>

        <!-- Banquet -->
        <a href="{{ route('banquet.orders.index') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('banquet.orders.*') ? 'active' : '' }}"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <i class="fa fa-utensils fa-fw me-3"></i> Banquet
        </a>

        <!-- Gym -->
        @can('manage-gym')
            <a href="{{ route('gym.index') }}"
                class="list-group-item list-group-item-action p-3 {{ request()->routeIs('gym.*') ? 'active' : '' }}"
                style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
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
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        font-weight: 800;
        font-size: 1.4rem;
        color: #C8A165;
        text-decoration: none;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .brand-sub {
        font-size: 9pt;
    }

    /* Sidebar item hover and active states */
    #sidebar-wrapper .list-group-item {
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        transition: all 0.3s ease;
    }

    #sidebar-wrapper .list-group-item:hover {
        background-color: rgba(200, 161, 101, 0.1) !important;
        color: #FFFFFF !important;
    }

    #sidebar-wrapper .list-group-item.active {
        background-color: #C8A165 !important;
        border-color: #C8A165 !important;
        color: #FFFFFF !important;
    }

    #sidebar-wrapper .submenu .list-group-item {
        padding-left: 2.5rem;
    }

    #sidebar-wrapper i.fa-chevron-down {
        color: #FFFFFF;
    }
</style> --}}

<div class="border-end" id="sidebar-wrapper" style="background-color: #333333;">

    <div class="sidebar-heading">
        <div class="brand-wrapper">
            <a href="{{ route('home') }}" class="brand-link">
                BRICKSPOINT<sup>&trade;</sup><sub class="brand-sub">ERP</sub>
            </a>
        </div>
    </div>

    <div class="list-group list-group-flush">

        {{-- 1. DASHBOARD HUB (Visible to Everyone) --}}
        <a href="{{ route('home') }}"
            class="list-group-item list-group-item-action p-3 {{ request()->routeIs('home') ? 'active' : '' }}"
            style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <i class="fas fa-th-large fa-fw me-3"></i> Hub
        </a>

        {{-- =================================================== --}}
        {{-- DYNAMIC MODULE MENUS (Permission Based)             --}}
        {{-- =================================================== --}}

        {{-- ADMIN MODULE --}}
        @can('access_admin_dashboard')
            @includeIf('admin::layouts.menu')
        @endcan

        {{-- FRONT DESK MODULE --}}
        @can('access_frontdesk_dashboard')
            @includeIf('frontdeskcrm::layouts.menu')
        @endcan

        {{-- STAFF MODULE --}}
        @can('access_staff_dashboard')
            @includeIf('staff::layouts.menu')
        @endcan

        {{-- RESTAURANT MODULE --}}
        @can('access_restaurant_dashboard')
            @includeIf('restaurant::layouts.menu')
        @endcan

        {{-- GYM MODULE --}}
        @can('access_gym_dashboard')
            @includeIf('gym::layouts.menu')
        @endcan

        {{-- INVENTORY MODULE --}}
        @can('access_inventory_dashboard')
            @includeIf('inventory::layouts.menu')
        @endcan

        {{-- OPERATIONS (Tasks & Maintenance) --}}
        @if(auth()->user()->can('access_tasks_dashboard') || auth()->user()->can('access_maintenance_dashboard'))
            <div class="sidebar-heading mt-3 text-uppercase text-muted small fw-bold px-3">Operations</div>
            @includeIf('tasks::layouts.menu')
            @includeIf('maintenance::layouts.menu')
        @endif

        {{-- BANQUET MODULE --}}
        @can('access_banquet_dashboard')
            @includeIf('banquet::layouts.menu')
        @endcan

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="list-group-item list-group-item-action p-3 text-danger"
           style="background-color: transparent; border-color: rgba(255,255,255,0.1);">
            <i class="fas fa-power-off fa-fw me-3"></i> Logout
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

    </div>
</div>

<style>
    .brand-wrapper {
        display: inline-block;
        padding: 10px 20px;
        margin-right: 15px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 4px 4px 15px rgba(0, 0, 0, 0.2), -4px -4px 15px rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }

    .brand-link {
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        font-weight: 800;
        font-size: 1.2rem;
        color: #C8A165;
        text-decoration: none;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .brand-sub {
        font-size: 8pt;
        color: #fff;
        margin-left: 4px;
    }

    /* Sidebar Item Styling */
    #sidebar-wrapper .list-group-item {
        font-family: 'Proxima Nova', Arial, Helvetica, sans-serif;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    #sidebar-wrapper .list-group-item:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #C8A165 !important;
        padding-left: 1.5rem !important; /* Slide effect */
    }

    #sidebar-wrapper .list-group-item.active {
        background-color: rgba(200, 161, 101, 0.15) !important;
        color: #C8A165 !important;
        border-left: 3px solid #C8A165;
        font-weight: bold;
    }

    /* Submenu Styling */
    .collapse .list-group-item {
        padding-left: 3.5rem !important;
        font-size: 0.85rem;
        background-color: rgba(0, 0, 0, 0.2) !important;
    }
</style>