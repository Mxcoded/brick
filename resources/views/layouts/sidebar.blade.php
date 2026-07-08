<div id="sidebar-wrapper">

    <div class="sidebar-heading">
        <div class="brand-wrapper">
            <a href="{{ route('home') }}" class="brand-link">
                BRICKSPOINT<sup>&trade;</sup><sub class="brand-sub">ERP</sub>
            </a>
        </div>
        <button class="btn btn-sm p-0 text-white border-0 d-md-none mt-2" id="sidebarClose" onclick="document.getElementById('wrapper').classList.remove('toggled')">
            <i class="fas fa-times fa-lg"></i>
        </button>
    </div>

    <div class="list-group list-group-flush">

        <a href="{{ route('home') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fas fa-th-large fa-fw"></i>
            <span>Hub</span>
        </a>

        @can('access_admin_dashboard')
            @includeIf('admin::layouts.menu')
        @endcan

        @can('access_frontdesk_dashboard')
            @includeIf('frontdeskcrm::layouts.menu')
        @endcan
        
        @canany(['access_website_dashboard', 'website.dashboard', 'website.bookings', 'website.contact-messages', 'website.facilities', 'website.offers', 'website.meeting', 'website.dining', 'website.room-types', 'website.inventory', 'website.amenities', 'website.testimonials', 'website.newsletter', 'website.settings'])
            @includeIf('website::layouts.menu')
        @endcanany

        @can('access_staff_dashboard')
            @includeIf('staff::layouts.menu')
        @endcan

        @can('access_restaurant_dashboard')
            @includeIf('restaurant::layouts.menu')
        @endcan

        @can('access_gym_dashboard')
            @includeIf('gym::layouts.menu')
        @endcan

        @can('access_inventory_dashboard')
            @includeIf('inventory::layouts.menu')
        @endcan

        @if (auth()->user()->can('access_tasks_dashboard') || auth()->user()->can('access_maintenance_dashboard'))
            <div class="sidebar-subheading" style="padding-left: 1rem; padding-top: 1rem;">Operations</div>
            @includeIf('tasks::layouts.menu')
            @includeIf('maintenance::layouts.menu')
        @endif

        @can('access_banquet_dashboard')
            @includeIf('banquet::layouts.menu')
        @endcan

        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="list-group-item list-group-item-action text-danger">
            <i class="fas fa-power-off fa-fw"></i>
            <span>Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>

    </div>
</div>
