@can('access_inventory_dashboard')
@php
    // Keep the Inventory module menu closed while the user is inside the
    // procurement approval flow (routes are inventory.procurement.*).
    $inventoryOpen = request()->routeIs('inventory.*') && ! request()->routeIs('inventory.procurement.*');
@endphp
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#inventorySubmenu" role="button"
   aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}" aria-controls="inventorySubmenu">
    <i class="fas fa-boxes fa-fw"></i>
    <span>Inventory</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ $inventoryOpen ? 'show' : '' }}" id="inventorySubmenu">

    <a href="{{ route('inventory.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Items</div>

    @can('inventory.read')
    <a href="{{ route('inventory.items.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.items.index') ? 'active' : '' }}">
        <i class="fas fa-box-open fa-fw me-2"></i> All Items
    </a>
    @endcan

    @can('inventory.create')
    <a href="{{ route('inventory.items.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.items.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle fa-fw me-2"></i> Add New Item
    </a>
    <a href="{{ route('inventory.import') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.import') ? 'active' : '' }}">
        <i class="fas fa-file-import fa-fw me-2"></i> Import from Excel
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Operations</div>

    @can('inventory.usage')
    <a href="{{ route('inventory.usage') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.usage') ? 'active' : '' }}">
        <i class="fas fa-hand-holding-box fa-fw me-2"></i> Issue Items
    </a>
    @endcan

    @can('inventory.transfer')
    <a href="{{ route('inventory.transfers.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.transfers.*') ? 'active' : '' }}">
        <i class="fas fa-exchange-alt fa-fw me-2"></i> Stock Transfer
    </a>
    @endcan

    @can('inventory.adjustments')
    <a href="{{ route('inventory.adjustments.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }}">
        <i class="fas fa-balance-scale fa-fw me-2"></i> Adjustments
    </a>
    <a href="{{ route('inventory.returns.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.returns.*') ? 'active' : '' }}">
        <i class="fas fa-undo fa-fw me-2"></i> Returns
    </a>
    @endcan

    @can('inventory.read')
    <a href="{{ route('inventory.low-stock') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle fa-fw me-2"></i> Low Stock
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Counting</div>

    @can('inventory.adjustments')
    <a href="{{ route('inventory.stock-takes.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.stock-takes.*') ? 'active' : '' }}">
        <i class="fas fa-tasks fa-fw me-2"></i> Stock Takes
    </a>
    <a href="{{ route('inventory.cycle-counts.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.cycle-counts.*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list fa-fw me-2"></i> Cycle Counts
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Purchasing</div>

    @can('purchase_orders.create')
    <a href="{{ route('inventory.purchase-orders.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.purchase-orders.*') ? 'active' : '' }}">
        <i class="fas fa-shopping-cart fa-fw me-2"></i> Purchase Orders
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Barcodes</div>

    @can('inventory.scan')
    <a href="{{ route('inventory.scan') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.scan') ? 'active' : '' }}">
        <i class="fas fa-camera fa-fw me-2"></i> Barcode Scan
    </a>
    @endcan

    @can('inventory.read')
    <a href="{{ route('inventory.barcode-labels') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.barcode-labels') ? 'active' : '' }}">
        <i class="fas fa-tag fa-fw me-2"></i> Print Labels
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Management</div>

    @can('suppliers.read')
    <a href="{{ route('inventory.suppliers.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.suppliers.*') ? 'active' : '' }}">
        <i class="fas fa-truck fa-fw me-2"></i> Suppliers
    </a>
    @endcan

    @can('stores.read')
    <a href="{{ route('inventory.stores.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.stores.*') ? 'active' : '' }}">
        <i class="fas fa-store fa-fw me-2"></i> Stores / Warehouses
    </a>
    @endcan

    @can('departments.read')
    <a href="{{ route('inventory.departments.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.departments.*') ? 'active' : '' }}">
        <i class="fas fa-building fa-fw me-2"></i> Departments
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Reports</div>

    @can('inventory.reports')
    <a href="{{ route('inventory.report') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.report') ? 'active' : '' }}">
        <i class="fas fa-chart-line fa-fw me-2"></i> Inventory Report
    </a>
    <a href="{{ route('inventory.valuation') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.valuation') ? 'active' : '' }}">
        <i class="fas fa-cash-register fa-fw me-2"></i> Stock Valuation
    </a>
    <a href="{{ route('inventory.stock-aging') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.stock-aging') ? 'active' : '' }}">
        <i class="fas fa-hourglass-half fa-fw me-2"></i> Stock Aging &amp; Expiry
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Alerts</div>

    <a href="{{ route('inventory.alerts.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('inventory.alerts.*') ? 'active' : '' }}">
        <i class="fas fa-bell fa-fw me-2"></i> Stock Alerts
    </a>

</div>
@endcan
