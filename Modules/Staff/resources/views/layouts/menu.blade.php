<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#staffSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('staff.*') ? 'true' : 'false' }}" aria-controls="staffSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-users-cog fa-fw me-3"></i> HR & Staff</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('staff.*') ? 'show' : '' }}" id="staffSubmenu">
    <a href="{{ route('staff.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none;">Dashboard</a>
    <a href="{{ route('staff.leaves.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Leave Requests</a>
    <a href="{{route('staff.create')}}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.create') ? 'active' : '' }}" style="color: #ddd; border: none;">Add Staff</a>
    <a href="{{route('staff.index')}}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.index') ? 'active' : '' }}" style="color: #ddd; border: none;">Staff List</a>
    <a href="{{route('staff.approvals.index')}}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.approvals.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Staff Approvals</a>
    <a href="{{route('staff.leaves.admin')}}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.admin') ? 'active' : '' }}" style="color: #ddd; border: none;">Manage Leaves</a>
    <a href="{{route('staff.leaves.report')}}" class="list-group-item list-group-item-action {{ request()->routeIs('staff.leaves.report') ? 'active' : '' }}" style="color: #ddd; border: none;">Leave Reports</a>
</div>