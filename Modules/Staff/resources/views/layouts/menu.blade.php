<a href="{{ route('staff.dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300 {{ request()->routeIs('staff.dashboard') ? 'bg-gray-800 text-white' : '' }}">
    <i class="fas fa-chart-line w-6 text-center mr-2"></i> Dashboard
</a>
{{-- Using a generic route check as 'staff.employees.*' assumes resource routes --}}
<a href="#" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300">
    <i class="fas fa-id-badge w-6 text-center mr-2"></i> Employees
</a>
<a href="#" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-800 text-gray-300">
    <i class="fas fa-calendar-check w-6 text-center mr-2"></i> Leave Requests
</a>