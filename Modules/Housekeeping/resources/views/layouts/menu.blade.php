@can('access_frontdesk_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#housekeepingSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('housekeeping.*') ? 'true' : 'false' }}" aria-controls="housekeepingSubmenu">
    <i class="fas fa-broom fa-fw"></i>
    <span>Housekeeping</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('housekeeping.*') ? 'show' : '' }}" id="housekeepingSubmenu">

    <a href="{{ route('housekeeping.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('housekeeping.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
    </a>

    <a href="{{ route('housekeeping.logs') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('housekeeping.logs') ? 'active' : '' }}">
        <i class="fas fa-history fa-fw me-2"></i> Cleaning Logs
    </a>

</div>
@endcan