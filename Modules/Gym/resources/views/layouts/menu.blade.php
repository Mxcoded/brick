@can('access_gym_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#gymSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('gym.*') ? 'true' : 'false' }}" aria-controls="gymSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-dumbbell fa-fw me-3"></i> Gym & Club</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('gym.*') ? 'show' : '' }}" id="gymSubmenu">
    <a href="{{ route('gym.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gym.index') ? 'active' : '' }}" style="color: #ddd; border: none;">Dashboard</a>
    <a href="{{ route('gym.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gym.members.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Members</a>
    <a href="{{ route('gym.trainers.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gym.trainers.*') ? 'active' : '' }}" style="color: #ddd; border: none;">Trainers</a>
</div>
@endcan