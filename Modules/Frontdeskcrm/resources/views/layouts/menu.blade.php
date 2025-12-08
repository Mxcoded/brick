<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#frontdeskSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('frontdesk.*') ? 'true' : 'false' }}" aria-controls="frontdeskSubmenu"
   style="color: #FFFFFF; background-color: transparent; border-color: rgba(255,255,255,0.1);">
    <span><i class="fas fa-concierge-bell fa-fw me-3"></i> Front Desk</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('frontdesk.*') ? 'show' : '' }}" id="frontdeskSubmenu">
    <a href="{{ route('frontdesk.registrations.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.dashboard') ? 'active' : '' }}" style="color: #ddd; border: none;">Overview</a>
    <a href="{{ route('frontdesk.registrations.create') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.create') ? 'active' : '' }}" style="color: #ddd; border: none;">Check In</a>
    <a href="{{ route('frontdesk.registrations.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.index') ? 'active' : '' }}" style="color: #ddd; border: none;">Guest List</a>
    <a href="{{ route('frontdesk.registrations.createWalkin') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.createWalkin') ? 'active' : '' }}" style="color: #ddd; border: none;">Walk-in</a>
</div>