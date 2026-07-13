<a href="{{ route('inventory.procurement.dashboard') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('inventory.procurement.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
</a>

@can('procurement.create_request')
<a href="{{ route('inventory.procurement.requests.create') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('inventory.procurement.requests.create') ? 'active' : '' }}">
    <i class="fas fa-plus-circle fa-fw me-2"></i> New Request
</a>
@endcan

<a href="{{ route('inventory.procurement.requests.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('inventory.procurement.requests.index') ? 'active' : '' }}">
    <i class="fas fa-list fa-fw me-2"></i> All Requests
</a>
