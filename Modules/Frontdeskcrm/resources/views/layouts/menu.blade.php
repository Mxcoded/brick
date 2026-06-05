@can('access_frontdesk_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#frontdeskSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('frontdesk.*') ? 'true' : 'false' }}" aria-controls="frontdeskSubmenu">
    <span><i class="fas fa-concierge-bell fa-fw me-3"></i> Front Desk</span>
    <i class="fas fa-chevron-down small"></i>
</a>
<div class="collapse {{ request()->routeIs('frontdesk.*') ? 'show' : '' }}" id="frontdeskSubmenu">

    {{-- Check-in Operations --}}
    <a href="{{ route('frontdesk.registrations.dashboard') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.dashboard') || request()->routeIs('frontdesk.registrations.index') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list me-2"></i> Registrations
    </a>

    @can('check_in_guest')
    <a href="{{ route('frontdesk.registrations.createWalkin') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.createWalkin') || request()->routeIs('frontdesk.registrations.lookup') ? 'active' : '' }}">
        <i class="fas fa-walking me-2"></i> Walk-In
    </a>
    @endcan

    <a href="{{ route('frontdesk.rooms.rack') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rooms.rack') ? 'active' : '' }}">
        <i class="fas fa-th me-2"></i> Room Rack
    </a>

    <a href="{{ route('frontdesk.rooms.schedule') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rooms.schedule') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt me-2"></i> Calendar
    </a>

    <div class="sidebar-divider"></div>
    <small class="sidebar-subheading">Guests</small>

    <a href="{{ route('frontdesk.guests.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guests.index') || request()->routeIs('frontdesk.guests.datatable') ? 'active' : '' }}">
        <i class="fas fa-address-book me-2"></i> Guest Directory
    </a>

    <a href="{{ route('frontdesk.guests.create') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guests.create') ? 'active' : '' }}">
        <i class="fas fa-user-plus me-2"></i> Add Guest
    </a>

    <div class="sidebar-divider"></div>
    <small class="sidebar-subheading">Master Data</small>

    <a href="{{ route('frontdesk.booking-sources.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.booking-sources.*') ? 'active' : '' }}">
        <i class="fas fa-plug me-2"></i> Booking Sources
    </a>

    <a href="{{ route('frontdesk.guest-types.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guest-types.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag me-2"></i> Guest Types
    </a>

</div>
@endcan