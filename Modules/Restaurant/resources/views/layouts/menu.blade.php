@can('access_restaurant_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#restoSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('restaurant.*') ? 'true' : 'false' }}" aria-controls="restoSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-utensils fa-fw me-3"></i> Restaurant</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('restaurant.*') ? 'show' : '' }}" id="restoSubmenu">
    @can('take_orders')
    <a href="{{ route('restaurant.waiter.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.waiter.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none;">Dashboard</a>
    @endcan

    @can('manage_menu')
    <a href="{{ route('restaurant.admin.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('restaurant.menu.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Menu</a>
    @endcan
</div>
@endcan