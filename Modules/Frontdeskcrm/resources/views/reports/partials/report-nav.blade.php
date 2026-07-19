@php
    $reportLinks = [
        ['route' => 'frontdesk.reports.index', 'icon' => 'tachometer-alt', 'label' => 'Dashboard'],
        ['route' => 'frontdesk.reports.daily', 'icon' => 'file-alt', 'label' => 'Daily'],
        ['route' => 'frontdesk.reports.occupancy', 'icon' => 'bed', 'label' => 'Occupancy'],
        ['route' => 'frontdesk.reports.revenue', 'icon' => 'chart-line', 'label' => 'Revenue'],
        ['route' => 'frontdesk.reports.forecast', 'icon' => 'chart-bar', 'label' => 'Forecast'],
        ['route' => 'frontdesk.reports.sources', 'icon' => 'globe', 'label' => 'Sources'],
        ['route' => 'frontdesk.reports.demographics', 'icon' => 'users', 'label' => 'Demographics'],
    ];
@endphp
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap gap-2">
            @foreach($reportLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="btn btn-sm {{ request()->routeIs($link['route']) ? 'btn-primary' : 'btn-light' }}">
                    <i class="fas fa-{{ $link['icon'] }} me-1"></i>{{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
