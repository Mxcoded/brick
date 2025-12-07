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
</div> --}}
<div class="flex flex-col flex-none w-64 h-screen bg-gray-900 border-r border-gray-800 sidebar">
    
    {{-- 1. BRANDING / LOGO --}}
    <div class="flex items-center justify-center h-16 bg-gray-900 border-b border-gray-800">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-white hover:text-gray-200">
            {{-- Replace with your logo img tag if available --}}
            <i class="fas fa-layer-group text-gold-500"></i>
            <span>Brick ERP</span>
        </a>
    </div>

    {{-- 2. SCROLLABLE MENU AREA --}}
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="px-2 space-y-1">

            {{-- === SHARED / COMMON LINKS (Profile, Home) === --}}
            <a href="{{ route('home') }}" 
               class="flex items-center px-4 py-2 text-sm font-medium rounded-md group hover:bg-gray-800 {{ request()->routeIs('home') ? 'bg-gray-800 text-white' : 'text-gray-300' }}">
                <i class="fas fa-home w-6 text-center mr-2 opacity-75"></i>
                Dashboard Hub
            </a>

            {{-- =================================================== --}}
            {{-- DYNAMIC MODULE MENUS (Permission Based) --}}
            {{-- =================================================== --}}

            {{-- 1. ADMIN MODULE --}}
            @can('access_admin_dashboard')
                <div class="mt-4 mb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Administration
                </div>
                {{-- Ensure you create this file: Modules/Admin/resources/views/layouts/menu.blade.php --}}
                @includeIf('admin::layouts.menu')
            @endcan

            {{-- 2. STAFF MODULE (HR, Employees) --}}
            @can('access_staff_dashboard')
                <div class="mt-4 mb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Staff & HR
                </div>
                @includeIf('staff::layouts.menu')
            @endcan

            {{-- 3. FRONT DESK (Hotel Management) --}}
            @can('access_frontdesk_dashboard')
                <div class="mt-4 mb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Front Desk
                </div>
                @includeIf('frontdeskcrm::layouts.menu')
            @endcan

            {{-- 4. RESTAURANT MODULE --}}
            @can('access_restaurant_dashboard')
                <div class="mt-4 mb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Restaurant
                </div>
                @includeIf('restaurant::layouts.menu')
            @endcan

            {{-- 5. GYM MODULE --}}
            @can('access_gym_dashboard')
                <div class="mt-4 mb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Gym & Fitness
                </div>
                @includeIf('gym::layouts.menu')
            @endcan

            {{-- 6. INVENTORY MODULE --}}
            @can('access_inventory_dashboard')
                <div class="mt-4 mb-2 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Inventory
                </div>
                @includeIf('inventory::layouts.menu')
            @endcan
            
            {{-- 7. MAINTENANCE MODULE --}}
            @can('access_maintenance_dashboard')
                 @includeIf('maintenance::layouts.menu')
            @endcan

            {{-- 8. TASKS MODULE --}}
            @can('access_tasks_dashboard')
                 @includeIf('tasks::layouts.menu')
            @endcan
            
             {{-- 9. BANQUET MODULE --}}
            @can('access_banquet_dashboard')
                 @includeIf('banquet::layouts.menu')
            @endcan

        </nav>
    </div>

    {{-- 3. USER PROFILE / LOGOUT (Bottom) --}}
    <div class="flex-none p-4 bg-gray-900 border-t border-gray-800">
        <div class="flex items-center w-full">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-gold-500 flex items-center justify-center text-white font-bold">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
            </div>
            <div class="ml-3 min-w-0">
                <p class="text-sm font-medium text-white truncate">
                    {{ Auth::user()->name ?? 'User' }}
                </p>
                <p class="text-xs text-gray-400 truncate">
                    {{ Auth::user()->email ?? '' }}
                </p>
            </div>
            <div class="ml-auto">
                 <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
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
</style>
