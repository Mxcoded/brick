@can('access_restaurant_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#restoSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('restaurant.*') ? 'true' : 'false' }}" aria-controls="restoSubmenu">
    <span><i class="fas fa-utensils fa-fw me-3"></i> Restaurant</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('restaurant.*') ? 'show' : '' }}" id="restoSubmenu">
    @can('take_orders')
    <a href="{{ route('restaurant.waiter.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.waiter.dashboard') ? 'active' : '' }}"><i class="fas fa-clipboard-list me-2"></i> Dashboard</a>
    @endcan

    @can('manage_menu')
    <a href="{{ route('restaurant.admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.menu.*') ? 'active' : '' }}"><i class="fas fa-book-open me-2"></i> Menu</a>
    @endcan
</div>
@endcan