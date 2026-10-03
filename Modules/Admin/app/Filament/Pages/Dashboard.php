<?php

namespace Modules\Admin\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Modules\Admin\Support\ExecutiveDashboardData;

class Dashboard extends BaseDashboard
{
    protected string $view = 'admin::filament.pages.executive-dashboard';

    /**
     * The panel root belongs to the neutral Home page so that module-only users
     * are not met with a 403 when they open /admin.
     */
    protected static string $routePath = 'executive-dashboard';

    protected static ?string $title = 'Executive Dashboard';

    protected static ?string $navigationLabel = 'Executive Dashboard';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(RoleEnum::ADMIN)
            || (auth()->user()?->can('access_admin_dashboard') ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewData(): array
    {
        return ExecutiveDashboardData::toArray();
    }
}
