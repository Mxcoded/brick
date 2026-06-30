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

    @can('manage_menu')
    <a href="{{ route('restaurant.admin.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.dashboard') || request()->routeIs('restaurant.admin.edit*') || request()->routeIs('restaurant.admin.add*') || request()->routeIs('restaurant.admin.delete*') || request()->routeIs('restaurant.admin.update*') ? 'active' : '' }}">
        <i class="fas fa-book-open fa-fw me-2"></i> Manage Menu
    </a>
    @endcan

    @can('manage_menu')
    <a href="{{ route('restaurant.admin.settings') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.admin.settings') ? 'active' : '' }}">
        <i class="fas fa-gear fa-fw me-2"></i> Settings
    </a>
    @endcan

</div>
@endcan
