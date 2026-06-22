@can('access_banquet_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#banquetSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('banquet.*') ? 'true' : 'false' }}" aria-controls="banquetSubmenu">
    <i class="fas fa-glass-cheers fa-fw"></i>
    <span>Banquet</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('banquet.*') ? 'show' : '' }}" id="banquetSubmenu">

    <a href="{{ route('banquet.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.index') || request()->routeIs('banquet.orders.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    @can('banquet.create')
    <a href="{{ route('banquet.orders.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.orders.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle fa-fw me-2"></i> New Event
    </a>
    @endcan

    <a href="{{ route('banquet.customers.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.customers.*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Customers
    </a>

    <a href="{{ route('banquet.enquiries.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.enquiries.*') ? 'active' : '' }}">
        <i class="fas fa-question-circle fa-fw me-2"></i> Enquiries
    </a>

    <a href="{{ route('banquet.lead-events.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.lead-events.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt fa-fw me-2"></i> Lead Events
    </a>

    <a href="{{ route('banquet.event-leads.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.event-leads.*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw me-2"></i> Event Leads
    </a>

    @can('access_website_dashboard')
    <a href="{{ route('website.admin.meeting.edit') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.meeting.*') ? 'active' : '' }}">
        <i class="fas fa-building fa-fw me-2"></i> Meetings Page
    </a>
    @endcan

    <div class="sidebar-divider"></div>
    <div class="sidebar-subheading">Reports</div>

    <a href="{{ route('banquet.reports.form') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw me-2"></i> Reports
    </a>

</div>
@endcan
