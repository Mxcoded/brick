<?php

namespace Modules\Admin\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Modules\Admin\Support\ExecutiveDashboardData;

class Dashboard extends BaseDashboard
{
    protected string $view = 'admin::filament.pages.executive-dashboard';

    protected static ?string $title = 'Executive Dashboard';

    protected static ?string $navigationLabel = 'Executive Dashboard';

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        return ExecutiveDashboardData::toArray();
    }
}
