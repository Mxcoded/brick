@can('access_inventory_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#inventorySubmenu" role="button"
   aria-expanded="{{ request()->routeIs('inventory.*') ? 'true' : 'false' }}" aria-controls="inventorySubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-boxes fa-fw me-3"></i> Inventory</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('inventory.*') ? 'show' : '' }}" id="inventorySubmenu">
    
    {{-- Dashboard --}}
    <a href="{{ route('inventory.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
    </a>
    
    {{-- Operational Links --}}
    @can('view_inventory')
    <a href="{{ route('inventory.items.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.items.*') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-box-open me-2"></i> All Items
    </a>
    <a href="{{ route('inventory.transfers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.transfers.*') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-exchange-alt me-2"></i> Stock Transfer
    </a>
    <a href="{{ route('inventory.usage') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.usage') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-hand-holding-box me-2"></i> Record Usage
    </a>
    @endcan
    
    {{-- Management Links (Stores & Departments) --}}
    <div class="text-uppercase small text-muted mt-2 mb-1 ps-4 fw-bold" style="font-size: 0.7rem;">Management</div>

    @can('manage_suppliers')
    <a href="{{ route('inventory.suppliers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.suppliers.*') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-truck me-2"></i> Suppliers
    </a>
    @endcan

    {{-- Stores Management --}}
    <a href="{{ route('inventory.stores.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.stores.*') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-store me-2"></i> Stores / Warehouses
    </a>

    {{-- Departments Management --}}
    <a href="{{ route('inventory.departments.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.departments.*') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-building me-2"></i> Departments
    </a>

    {{-- Reports --}}
    <div class="text-uppercase small text-muted mt-2 mb-1 ps-4 fw-bold" style="font-size: 0.7rem;">Reports</div>
    
    <a href="{{ route('inventory.report') }}" class="list-group-item list-group-item-action {{ request()->routeIs('inventory.report') ? 'active' : '' }}" style="color: #ddd; border: none; padding-left: 3rem;">
        <i class="fas fa-chart-line me-2"></i> Inventory Report
    </a>

</div>
@endcan