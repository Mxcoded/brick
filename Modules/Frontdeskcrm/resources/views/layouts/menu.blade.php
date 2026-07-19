@can('access_frontdesk_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#frontdeskSubmenu" role="button"
   aria-expanded="{{ (request()->routeIs('frontdesk.*') || request()->routeIs('website.admin.room-types.*') || request()->routeIs('website.admin.rooms.*') || request()->routeIs('website.admin.inventory.*') || request()->routeIs('website.admin.amenities.*')) ? 'true' : 'false' }}" aria-controls="frontdeskSubmenu">
    <i class="fas fa-concierge-bell fa-fw"></i>
    <span>Front Desk</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ (request()->routeIs('frontdesk.*') || request()->routeIs('website.admin.room-types.*') || request()->routeIs('website.admin.rooms.*') || request()->routeIs('website.admin.inventory.*') || request()->routeIs('website.admin.amenities.*')) ? 'show' : '' }}" id="frontdeskSubmenu">

    <div class="sidebar-subheading">Stay Management</div>

    <a href="{{ route('frontdesk.registrations.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.registrations.dashboard') || request()->routeIs('frontdesk.registrations.index') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list fa-fw me-2"></i> Stay Management
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Operations</div>

    <a href="{{ route('frontdesk.audit.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.audit.*') ? 'active' : '' }}">
        <i class="fas fa-moon fa-fw me-2"></i> Night Audit
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
    <div class="sidebar-subheading">Groups</div>

    <a href="{{ route('frontdesk.groups.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.groups.*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Group Bookings
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Accounts</div>

    <a href="{{ route('frontdesk.city-ledger.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.city-ledger.*') ? 'active' : '' }}">
        <i class="fas fa-building fa-fw me-2"></i> City Ledger
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Reports</div>

    <a href="{{ route('frontdesk.reports.daily') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.daily*') ? 'active' : '' }}">
        <i class="fas fa-file-alt fa-fw me-2"></i> Daily Report
    </a>
    <a href="{{ route('frontdesk.reports.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
    </a>
    <a href="{{ route('frontdesk.reports.occupancy') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.occupancy') ? 'active' : '' }}">
        <i class="fas fa-bed fa-fw me-2"></i> Occupancy
    </a>
    <a href="{{ route('frontdesk.reports.revenue') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.revenue') ? 'active' : '' }}">
        <i class="fas fa-chart-line fa-fw me-2"></i> Revenue
    </a>
    <a href="{{ route('frontdesk.reports.forecast') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.forecast') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw me-2"></i> Forecast
    </a>
    <a href="{{ route('frontdesk.reports.sources') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.sources') ? 'active' : '' }}">
        <i class="fas fa-globe fa-fw me-2"></i> Sources
    </a>
    <a href="{{ route('frontdesk.reports.demographics') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.reports.demographics') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Demographics
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
    <div class="sidebar-subheading">Rates &amp; Inventory</div>

    <a href="{{ route('frontdesk.rate-codes.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rate-codes.*') ? 'active' : '' }}">
        <i class="fas fa-tags fa-fw me-2"></i> Rate Codes
    </a>

    <a href="{{ route('frontdesk.rate-calendar.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.rate-calendar.*') ? 'active' : '' }}">
        <i class="fas fa-calculator fa-fw me-2"></i> Rate Calendar
    </a>

    <a href="{{ route('website.admin.room-types.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.room-types.*') ? 'active' : '' }}">
        <i class="fas fa-bed fa-fw me-2"></i> Room Types
    </a>

    <a href="{{ route('website.admin.inventory.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.inventory.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt fa-fw me-2"></i> Inventory Calendar
    </a>

    <a href="{{ route('website.admin.rooms.calendar') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.rooms.calendar') ? 'active' : '' }}">
        <i class="fas fa-th fa-fw me-2"></i> Room Schedule
    </a>

    <a href="{{ route('website.admin.amenities.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.amenities.*') ? 'active' : '' }}">
        <i class="fas fa-wifi fa-fw me-2"></i> Amenities
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Configuration</div>

    <a href="{{ route('frontdesk.guest-types.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.guest-types.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag fa-fw me-2"></i> Guest Categories
    </a>

    <a href="{{ route('frontdesk.charge-types.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('frontdesk.charge-types.*') ? 'active' : '' }}">
        <i class="fas fa-receipt fa-fw me-2"></i> Charge Types
    </a>

    </div>
@endcan
