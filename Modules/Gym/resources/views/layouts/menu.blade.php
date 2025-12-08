<a href="{{ route('gym.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('gym.dashboard') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-dumbbell w-6 text-center mr-2"></i> Gym Dashboard
</a>
<a href="{{ route('gym.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('gym.members.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-users w-6 text-center mr-2"></i> Members
</a>
<a href="{{ route('gym.trainers.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('gym.trainers.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-user-ninja w-6 text-center mr-2"></i> Trainers
</a>