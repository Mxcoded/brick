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
            <div class="sidebar-heading mt-3 text-uppercase text-gold small fw-bold px-3">Operations</div>
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