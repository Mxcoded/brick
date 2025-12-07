<a href="{{ route('restaurant.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('restaurant.dashboard') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-utensils w-6 text-center mr-2"></i> Resto Dashboard
</a>
<a href="{{ route('restaurant.orders.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('restaurant.orders.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-receipt w-6 text-center mr-2"></i> Orders
</a>
<a href="{{ route('restaurant.menu.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('restaurant.menu.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-book-open w-6 text-center mr-2"></i> Menu
</a>