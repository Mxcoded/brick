<a href="{{ route('tasks.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('tasks.*') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-tasks w-6 text-center mr-2"></i> Tasks
</a>