<a href="{{ route('maintenance.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('maintenance.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-tools w-6 text-center mr-2"></i> Maintenance
</a>