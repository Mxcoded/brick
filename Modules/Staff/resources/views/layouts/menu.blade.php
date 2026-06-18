@can('access_staff_dashboard')
    <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse" href="#staffSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('staff.*') ? 'true' : 'false' }}" aria-controls="staffSubmenu">
        <span><i class="fas fa-users-cog fa-fw me-3"></i>{{ auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('admin') ? 'HR MODE' : 'Staff' }}</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('staff.*') ? 'show' : '' }}" id="staffSubmenu">
        @can('view_employees')
            <a href="{{ route('staff.dashboard') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        @endcan

        {{-- Every staff member should see their own leaves --}}
        <a href="{{ route('staff.leaves.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.index') || request()->routeIs('staff.leaves.create') ? 'active' : '' }}"><i class="fas fa-calendar-alt me-2"></i> My Leave Requests</a>

        {{-- HR Management Links --}}
        @can('manage_employees')
            <a href="{{ route('staff.create') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.create') ? 'active' : '' }}"><i class="fas fa-user-plus me-2"></i> Add Staff</a>
            <a href="{{ route('staff.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.index') ? 'active' : '' }}"><i class="fas fa-users me-2"></i> Staff List</a>
            <a href="{{ route('staff.approvals.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.approvals.*') ? 'active' : '' }}"><i class="fas fa-check-double me-2"></i> Staff Approvals</a>
            <a href="{{ route('staff.birthdays') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.birthdays') ? 'active' : '' }}">
                <i class="fas fa-birthday-cake fa-fw me-2"></i> Birthdays
            </a>
            <a href="{{ route('staff.settings') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.settings') ? 'active' : '' }}">
                <i class="fas fa-cog fa-fw me-2"></i> SMS Settings
            </a>
        @endcan

        @can('approve_leaves')
            <div class="sidebar-divider"></div>
            <small class="sidebar-subheading">Leave Management</small>

            <a href="{{ route('staff.leaves.admin') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}"><i class="fas fa-inbox me-2"></i> Pending Requests</a>

            <a href="{{ route('staff.leaves.admin.balances') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.balances') ? 'active' : '' }}"><i class="fas fa-chart-pie me-2"></i> Leave Balances</a>

            <a href="{{ route('staff.leaves.admin.apply') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.apply') ? 'active' : '' }}"><i class="fas fa-pen me-2"></i> Apply for Staff</a>

            <a href="{{ route('staff.leaves.admin.history') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.history') ? 'active' : '' }}"><i class="fas fa-history me-2"></i> Leave History</a>

            <a href="{{ route('staff.leaves.report') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.report') ? 'active' : '' }}"><i class="fas fa-file-alt me-2"></i> Reports</a>
        @endcan
    </div>
@endcan
