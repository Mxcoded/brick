@can('access_banquet_dashboard')
<a class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center"
   data-bs-toggle="collapse" href="#banquetSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('banquet.*') ? 'true' : 'false' }}" aria-controls="banquetSubmenu">
    <span><i class="fas fa-glass-cheers fa-fw me-3"></i> Banquet</span>
    <i class="fas fa-chevron-down small"></i>
</a>

<div class="collapse {{ request()->routeIs('banquet.*') ? 'show' : '' }}" id="banquetSubmenu">
    {{-- 1. DASHBOARD / OVERVIEW --}}
    <a href="{{ route('banquet.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.index') || request()->routeIs('banquet.orders.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt me-2"></i> Overview
    </a>

    {{-- 2. CREATE ORDER (Managers Only) --}}
    @can('banquet.create')
    <a href="{{ route('banquet.orders.create') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.orders.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle me-2"></i> New Event
    </a>
    @endcan

    {{-- 3. CUSTOMERS --}}
    <a href="{{ route('banquet.customers.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.customers.*') ? 'active' : '' }}">
        <i class="fas fa-users me-2"></i> Customers
    </a>

    {{-- 4. ENQUIRIES --}}
    <a href="{{ route('banquet.enquiries.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.enquiries.*') ? 'active' : '' }}">
        <i class="fas fa-question-circle me-2"></i> Enquiries
    </a>

    {{-- 5. LEAD EVENTS (Campaign Management) --}}
    <a href="{{ route('banquet.lead-events.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.lead-events.*') ? 'active' : '' }}">
        <i class="fas fa-calendar-alt me-2"></i> Lead Events
    </a>

    {{-- 6. EVENT LEADS --}}
    <a href="{{ route('banquet.event-leads.index') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.event-leads.*') ? 'active' : '' }}">
        <i class="fas fa-users me-2"></i> Event Leads
    </a>

    {{-- 5. MEETINGS PAGE --}}
    @can('access_website_dashboard')
    <a href="{{ route('website.admin.meeting.edit') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('website.admin.meeting.*') ? 'active' : '' }}">
        <i class="fas fa-building me-2"></i> Meetings Page
    </a>
    @endcan

    {{-- 4. REPORTS --}}
    <a href="{{ route('banquet.reports.form') }}" 
       class="list-group-item list-group-item-action {{ request()->routeIs('banquet.reports.*') ? 'active' : '' }}">
        <i class="fas fa-chart-bar me-2"></i> Reports
    </a>
</div>
@endcan