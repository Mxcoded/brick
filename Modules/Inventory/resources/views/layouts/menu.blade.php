<a href="{{ route('inventory.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('inventory.dashboard') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-boxes w-6 text-center mr-2"></i> Inventory
</a>
<a href="{{ route('inventory.items.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('inventory.items.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-box w-6 text-center mr-2"></i> Items
</a>
<a href="{{ route('inventory.suppliers.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('inventory.suppliers.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-truck w-6 text-center mr-2"></i> Suppliers
</a>