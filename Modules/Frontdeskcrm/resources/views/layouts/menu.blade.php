@can('access_frontdesk_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#frontdeskSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('frontdesk.*') ? 'true' : 'false' }}" aria-controls="frontdeskSubmenu">
    <i class="fas fa-concierge-bell fa-fw"></i>
    <span>Front Desk</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('frontdesk.*') ? 'show' : '' }}" id="frontdeskSubmenu">

    <div class="sidebar-subheading">Stay Management</div>

    <a href="{{ route('frontdesk.registrations.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.dashboard') || request()->routeIs('frontdesk.registrations.index') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list fa-fw me-2"></i> Stay Management
    </a>

    @can('check_in_guest')
    <a href="{{ route('frontdesk.registrations.createWalkin') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.createWalkin') || request()->routeIs('frontdesk.registrations.lookup') ? 'active' : '' }}">
        <i class="fas fa-walking fa-fw me-2"></i> Walk-In Check-In
    </a>
    @endcan

    <a href="{{ route('frontdesk.rooms.rack') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rooms.rack') ? 'active' : '' }}">
        <i class="fas fa-th fa-fw me-2"></i> Room Status
    </a>

    <a href="{{ route('frontdesk.rooms.schedule') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rooms.schedule') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt fa-fw me-2"></i> Booking Calendar
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Guests</div>

    <a href="{{ route('frontdesk.guests.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guests.index') || request()->routeIs('frontdesk.guests.datatable') ? 'active' : '' }}">
        <i class="fas fa-address-book fa-fw me-2"></i> Guest Directory
    </a>

    <a href="{{ route('frontdesk.guests.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guests.create') ? 'active' : '' }}">
        <i class="fas fa-user-plus fa-fw me-2"></i> Add Guest
    </a>

    <a href="{{ route('frontdesk.guests.import') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guests.import') ? 'active' : '' }}">
        <i class="fas fa-file-import fa-fw me-2"></i> Import Guests
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Channels &amp; Sources</div>

    <a href="{{ route('frontdesk.channels.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.channels.*') ? 'active' : '' }}">
        <i class="fas fa-globe fa-fw me-2"></i> Channel Manager
    </a>

    <a href="{{ route('frontdesk.booking-sources.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.booking-sources.*') ? 'active' : '' }}">
        <i class="fas fa-plug fa-fw me-2"></i> Booking Sources
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Configuration</div>

    <a href="{{ route('frontdesk.guest-types.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guest-types.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag fa-fw me-2"></i> Guest Categories
    </a>

</div>
@endcan
