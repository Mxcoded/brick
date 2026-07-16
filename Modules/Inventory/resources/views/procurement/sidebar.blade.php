<a href="{{ route('inventory.procurement.dashboard') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('inventory.procurement.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
</a>

<a href="{{ route('inventory.procurement.requests.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('inventory.procurement.requests.index', 'inventory.procurement.requests.show', 'inventory.procurement.requests.edit') ? 'active' : '' }}">
    <i class="fas fa-list fa-fw me-2"></i> All Requests
</a>
