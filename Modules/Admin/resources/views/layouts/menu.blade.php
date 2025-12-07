<a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-tachometer-alt w-6 text-center mr-2"></i> Overview
</a>
<a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-users w-6 text-center mr-2"></i> Users
</a>
<a href="{{ route('admin.roles.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('admin.roles.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-user-shield w-6 text-center mr-2"></i> Roles
</a>
<a href="{{ route('admin.permissions.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('admin.permissions.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-key w-6 text-center mr-2"></i> Permissions
</a>