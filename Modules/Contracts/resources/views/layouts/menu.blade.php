@can('access_contracts_dashboard')
<a class="list-group-item list-group-item-action" data-bs-toggle="collapse" href="#contractsSubmenu" role="button"
   aria-expanded="{{ request()->routeIs('contracts.*') ? 'true' : 'false' }}" aria-controls="contractsSubmenu">
    <i class="fas fa-file-contract fa-fw"></i>
    <span>Contracts &amp; Agreements</span>
    <i class="fas fa-chevron-down"></i>
</a>
<div class="collapse {{ request()->routeIs('contracts.*') ? 'show' : '' }}" id="contractsSubmenu">

    <a href="{{ route('contracts.dashboard') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('contracts.dashboard') || request()->routeIs('contracts.agreements.index') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt fa-fw me-2"></i> Overview
    </a>

    @can('contracts.create')
    <a href="{{ route('contracts.agreements.create') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('contracts.agreements.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle fa-fw me-2"></i> New Agreement
    </a>
    @endcan

    <a href="{{ route('contracts.agreements.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('contracts.agreements.index') || request()->routeIs('contracts.agreements.show') || request()->routeIs('contracts.agreements.edit') ? 'active' : '' }}">
        <i class="fas fa-folder-open fa-fw me-2"></i> All Agreements
    </a>

    @can('contracts.manage_templates')
    <a href="{{ route('contracts.templates.index') }}"
       class="list-group-item list-group-item-action {{ request()->routeIs('contracts.templates.*') ? 'active' : '' }}">
        <i class="fas fa-copy fa-fw me-2"></i> Templates
    </a>
    @endcan

</div>
@endcan