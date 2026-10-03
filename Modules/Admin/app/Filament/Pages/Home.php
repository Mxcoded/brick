<?php

namespace Modules\Admin\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Neutral landing page for the shared panel.
 *
 * The Executive Dashboard is sensitive, so it cannot be the panel root: any
 * module-only user (finance, front desk, …) typing /admin used to hit a bare
 * 403. This page is reachable by everyone who can access the panel at all and
 * links onward to whichever modules they are actually allowed to see.
 */
class Home extends Page
{
    protected static ?string $slug = '/';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Home';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'admin::filament.pages.home';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return method_exists($user, 'canAccessPanel')
            && $user->canAccessPanel(Filament::getCurrentOrDefaultPanel());
    }

    /**
     * Grouped list of the pages this user can open, one entry per module area.
     *
     * @return array<int, array{group: string, links: array<int, array{label: string, url: string}>}>
     */
    public function getViewData(): array
    {
        $groups = [];

        foreach (Filament::getCurrentOrDefaultPanel()->getPages() as $page) {
            if ($page === static::class) {
                continue;
            }

            try {
                if (! $page::canAccess()) {
                    continue;
                }
            } catch (\Throwable) {
                // A page that cannot answer the question is simply not offered.
                continue;
            }

            $label = $page::getNavigationLabel() ?? $page::getModelLabel() ?? 'Page';

            $group = $this->groupLabel($page::getNavigationGroup())
                ?? $page::getSlug()
                ?? 'Modules';

            $groups[$group] ??= ['group' => $group, 'links' => []];

            if (count($groups[$group]['links']) < 4) {
                $groups[$group]['links'][] = [
                    'label' => $label,
                    'url' => $page::getUrl(),
                ];
            }
        }

        return [
            'destinationGroups' => array_values($groups),
        ];
    }

    protected function groupLabel(string|UnitEnum|null $group): ?string
    {
        if ($group instanceof UnitEnum) {
            return $group->getLabel();
        }

        return $group;
    }
}
