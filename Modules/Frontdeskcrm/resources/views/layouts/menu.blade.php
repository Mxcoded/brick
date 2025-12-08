<a href="{{ route('frontdesk.registrations.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('frontdesk.registrations.dashboard') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-concierge-bell w-6 text-center mr-2"></i> Overview
</a>
<a href="{{ route('frontdesk.registrations.create') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('frontdesk.registrations.create') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-plus-circle w-6 text-center mr-2"></i> New Check-in
</a>
<a href="{{ route('frontdesk.registrations.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('frontdesk.registrations.index') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-list w-6 text-center mr-2"></i> All Guests
</a>
<a href="{{ route('frontdesk.registrations.createWalkin') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('frontdesk.registrations.createWalkin') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-walking w-6 text-center mr-2"></i> Walk-in
</a>