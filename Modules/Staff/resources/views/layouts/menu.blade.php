@can('access_staff_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#staffSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('staff.*') ? 'true' : 'false' }}" aria-controls="staffSubmenu">
    <i class="fas fa-users-cog fa-fw"></i>
    <span>{{ auth()->user()->hasRole('hr_manager') || auth()->user()->hasRole('admin') ? 'HR MODE' : 'Staff' }}</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('staff.*') ? 'show' : '' }}" id="staffSubmenu">

    @can('employees.read')
    <a href="{{ route('staff.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
    </a>

    <a href="{{ route('staff.attendance.clock') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.attendance.clock') ? 'active' : '' }}">
        <i class="fas fa-clock fa-fw me-2"></i> Clock In/Out
    </a>
    <a href="{{ route('staff.attendance.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.attendance.index') ? 'active' : '' }}">
        <i class="fas fa-calendar-day fa-fw me-2"></i> Daily Overview
    </a>
    <a href="{{ route('staff.attendance.report') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.attendance.report') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw me-2"></i> Monthly Report
    </a>
    @endcan

    <a href="{{ route('staff.leaves.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.index') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt fa-fw me-2"></i> My Leave Requests
    </a>
    <a href="{{ route('staff.leaves.request') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.request') ? 'active' : '' }}">
        <i class="fas fa-pen fa-fw me-2"></i> Request Leave
    </a>
    <a href="{{ route('staff.leaves.balance') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.balance') ? 'active' : '' }}">
        <i class="fas fa-chart-pie fa-fw me-2"></i> My Leave Balance
    </a>

    @canany(['employees.create', 'employees.read'])
    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">HR Management</div>

    @can('employees.create')
    <a href="{{ route('staff.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.create') ? 'active' : '' }}">
        <i class="fas fa-user-plus fa-fw me-2"></i> Add Staff
    </a>
    @endcan

    @can('employees.read')
    <a href="{{ route('staff.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.index') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Staff List
    </a>

    <a href="{{ route('staff.approvals.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.approvals.*') ? 'active' : '' }}">
        <i class="fas fa-check-double fa-fw me-2"></i> Staff Approvals
    </a>

    <a href="{{ route('staff.birthdays') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.birthdays') ? 'active' : '' }}">
        <i class="fas fa-birthday-cake fa-fw me-2"></i> Birthdays
    </a>

    <a href="{{ route('staff.settings') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.settings') ? 'active' : '' }}">
        <i class="fas fa-cog fa-fw me-2"></i> SMS Settings
    </a>

    <a href="{{ route('staff.performance.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.performance.*') && ! request()->routeIs('staff.performance.skills*') ? 'active' : '' }}">
        <i class="fas fa-star fa-fw me-2"></i> Performance Reviews
    </a>

    <a href="{{ route('staff.performance.skills') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.performance.skills*') ? 'active' : '' }}">
        <i class="fas fa-brain fa-fw me-2"></i> Skills Matrix
    </a>

    <a href="{{ route('staff.training.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.training.*') ? 'active' : '' }}">
        <i class="fas fa-graduation-cap fa-fw me-2"></i> Training &amp; Certifications
    </a>

    <a href="{{ route('staff.reports.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw me-2"></i> Advanced Reports
    </a>
    @endcan
    @endcanany

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Documents</div>

    <a href="{{ route('staff.documents.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.documents.*') ? 'active' : '' }}">
        <i class="fas fa-folder-open fa-fw me-2"></i> Shared Files
    </a>

    @can('leaves.approve')
    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Leave Management</div>

    <a href="{{ route('staff.leaves.admin') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}">
        <i class="fas fa-inbox fa-fw me-2"></i> Pending Requests
    </a>

    <a href="{{ route('staff.leaves.admin.balances') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.balances') ? 'active' : '' }}">
        <i class="fas fa-chart-pie fa-fw me-2"></i> Leave Balances
    </a>

    <a href="{{ route('staff.leaves.admin.apply') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.apply') ? 'active' : '' }}">
        <i class="fas fa-pen fa-fw me-2"></i> Apply for Staff
    </a>

    <a href="{{ route('staff.leaves.admin.history') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin.history') ? 'active' : '' }}">
        <i class="fas fa-history fa-fw me-2"></i> Leave History
    </a>

    <a href="{{ route('staff.leaves.calendar') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.calendar') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt fa-fw me-2"></i> Leave Calendar
    </a>

    <a href="{{ route('staff.leaves.report') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.report') ? 'active' : '' }}">
        <i class="fas fa-file-alt fa-fw me-2"></i> Reports
    </a>
    @endcan

</div>
@endcan
