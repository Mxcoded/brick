@can('access_inventory_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#inventorySubmenu" role="button"
   aria-expanded="{{ request()->routeIs('inventory.*') ? 'true' : 'false' }}" aria-controls="inventorySubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-boxes fa-fw me-3"></i> Inventory</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('inventory.*') ? 'show' : '' }}" id="inventorySubmenu">
    <a href="{{ route('inventory.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none;">Dashboard</a>
    
    @can('view_inventory')
    <a href="{{ route('inventory.items.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.items.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Items</a>
    @endcan
    
    @can('manage_suppliers')
    <a href="{{ route('inventory.suppliers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.suppliers.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Suppliers</a>
    @endcan
</div>
@endcan