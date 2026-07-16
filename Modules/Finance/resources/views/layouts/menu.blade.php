<a href="{{ route('finance.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('finance.index') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
</a>

<a href="{{ route('finance.coa.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('finance.coa.*') ? 'active' : '' }}">
    <i class="fas fa-list fa-fw me-2"></i> Chart of Accounts
</a>

<a href="{{ route('finance.journal.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('finance.journal.*') ? 'active' : '' }}">
    <i class="fas fa-book fa-fw me-2"></i> Journal Entries
</a>

<a href="{{ route('finance.reports.index') }}"
   class="list-group-item list-group-item-action {{ request()->routeIs('finance.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-pie fa-fw me-2"></i> Reports
</a>
