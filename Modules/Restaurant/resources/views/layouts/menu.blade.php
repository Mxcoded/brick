@can('access_restaurant_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#restoSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('restaurant.*') ? 'true' : 'false' }}" aria-controls="restoSubmenu">
    <i class="fas fa-utensils fa-fw"></i>
    <span>Restaurant</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('restaurant.*') ? 'show' : '' }}" id="restoSubmenu">

    @can('take_orders')
    <a href="{{ route('restaurant.waiter.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.waiter.dashboard*') ? 'active' : '' }}">
        <i class="fas fa-calculator fa-fw me-2"></i> Waiter POS
    </a>
    @endcan

    @canany(['menu.create', 'menu.update', 'menu.delete', 'menu.read'])
    <a href="{{ route('restaurant.admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.dashboard') || request()->routeIs('restaurant.admin.edit*') || request()->routeIs('restaurant.admin.add*') || request()->routeIs('restaurant.admin.delete*') || request()->routeIs('restaurant.admin.update*') ? 'active' : '' }}">
        <i class="fas fa-book-open fa-fw me-2"></i> Manage Menu
    </a>
    @endcanany

    @can('menu.read')
    <a href="{{ route('restaurant.admin.settings') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.settings') ? 'active' : '' }}">
        <i class="fas fa-gear fa-fw me-2"></i> Settings
    </a>
    @endcan

    @can('access_restaurant_dashboard')
    <a href="{{ route('restaurant.admin.tables') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.tables') ? 'active' : '' }}">
        <i class="fas fa-table fa-fw me-2"></i> Tables
    </a>

    <a href="{{ route('restaurant.admin.kitchen') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.kitchen') ? 'active' : '' }}">
        <i class="fas fa-tv fa-fw me-2"></i> Kitchen Display
    </a>

    <a href="{{ route('restaurant.admin.reports.sales') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.reports*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw me-2"></i> Reports
    </a>

    <a href="{{ route('restaurant.admin.stock.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.stock*') ? 'active' : '' }}">
        <i class="fas fa-boxes fa-fw me-2"></i> Stock
    </a>

    <a href="{{ route('restaurant.admin.customers') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.customer*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Customers
    </a>
    @endcan

</div>
@endcan
