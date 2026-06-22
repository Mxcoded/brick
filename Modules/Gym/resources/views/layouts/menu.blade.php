@can('access_gym_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#gymSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('gym.*') ? 'true' : 'false' }}" aria-controls="gymSubmenu">
    <i class="fas fa-dumbbell fa-fw"></i>
    <span>Gym &amp; Club</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('gym.*') ? 'show' : '' }}" id="gymSubmenu">

    <a href="{{ route('gym.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('gym.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
    </a>

    <a href="{{ route('gym.memberships.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('gym.memberships.create') ? 'active' : '' }}">
        <i class="fas fa-user-plus fa-fw me-2"></i> New Membership
    </a>

    <a href="{{ route('gym.trainers.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('gym.trainers.*') ? 'active' : '' }}">
        <i class="fas fa-chalkboard-teacher fa-fw me-2"></i> Trainers
    </a>

</div>
@endcan
