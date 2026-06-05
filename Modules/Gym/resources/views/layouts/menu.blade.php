@can('access_gym_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#gymSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('gym.*') ? 'true' : 'false' }}" aria-controls="gymSubmenu">
    <span><i class="fas fa-dumbbell fa-fw me-3"></i> Gym & Club</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('gym.*') ? 'show' : '' }}" id="gymSubmenu">
    <a href="{{ route('gym.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gym.index') ? 'active' : '' }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
    <a href="{{ route('gym.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gym.members.*') ? 'active' : '' }}"><i class="fas fa-users me-2"></i> Members</a>
    <a href="{{ route('gym.trainers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gym.trainers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher me-2"></i> Trainers</a>
</div>
@endcan