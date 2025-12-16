@can('access_staff_dashboard')
    <a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
        data-bs-toggle="collapse" href="#staffSubmenu" role="button"
        aria-expanded="{{ request()->routeIs('staff.*') ? 'true' : 'false' }}" aria-controls="staffSubmenu"
        style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
        <span><i class="fas fa-users-cog fa-fw me-3"></i>{{ auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('admin') ? 'HR MODE' : 'Staff' }}</span>
        <i class="fas fa-chevron-down small"></i>
    </a>
    <div class="collapse {{ request()->routeIs('staff.*') ? 'show' : '' }}" id="staffSubmenu">
        <a href="{{ route('staff.dashboard') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
            style="color: #ddd; border: none;">Dashboard</a>

        {{-- Every staff member should see their own leaves --}}
        <a href="{{ route('staff.leaves.index') }}"
            class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.index') || request()->routeIs('staff.leaves.create') ? 'active' : '' }}"
            style="color: #ddd; border: none;">My Leave Requests</a>

        {{-- HR Management Links --}}
        @can('manage_employees')
            <a href="{{ route('staff.create') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.create') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Add Staff</a>
            <a href="{{ route('staff.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.index') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Staff List</a>
            <a href="{{ route('staff.approvals.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.approvals.*') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Staff Approvals</a>
            <a href="{{ route('staff.birthdays') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.birthdays') ? 'active' : '' }}"
                style="color: #ddd; border: none;">
                <i class="fas fa-birthday-cake fa-fw me-2"></i> Birthdays
            </a>
        @endcan

        @can('approve_leaves')
            <div class="border-top my-2 mx-3" style="border-color: rgba(255,255,255,0.1) !important;"></div>
            <small class="text-gold ms-3 mb-2 d-block text-uppercase" style="font-size: 0.7rem;">Leave Management</small>

            <a href="{{ route('staff.leaves.admin') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Pending Requests</a>

            <a href="{{ route('staff.leaves.admin.balances') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.balances') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Leave Balances</a>

            <a href="{{ route('staff.leaves.admin.apply') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.apply') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Apply for Staff</a>

            <a href="{{ route('staff.leaves.admin.history') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.history') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Leave History</a>

            <a href="{{ route('staff.leaves.report') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.report') ? 'active' : '' }}"
                style="color: #ddd; border: none;">Reports</a>
        @endcan
    </div>
@endcan
