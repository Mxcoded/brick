<?php

namespace Modules\Website\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

/**
 * Sidebar entry point to the unified room calendar.
 *
 * The calendar is a full-screen Blade screen that extends the classic admin
 * layout rather than the Filament panel, so it cannot be a normal Filament
 * resource. This Page exists only to place a permission-gated item in the panel
 * navigation that points at that route; visiting the Filament URL directly
 * redirects to the real screen.
 */
class RoomCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Room Calendar';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'room-calendar';

    protected static ?string $title = 'Room Calendar';

    protected string $view = 'filament-panels::pages.page';

    public static function canAccess(): bool
    {
        return WebsitePermission::allows(WebsitePermission::INVENTORY_READ);
    }

    /**
     * Send the sidebar link to the actual calendar rather than this placeholder.
     */
    public static function getNavigationUrl(): string
    {
        return route('website.admin.inventory.index');
    }

    /**
     * Highlight the item while the calendar screen (its own route) is open.
     *
     * @return string | array<string>
     */
    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return 'website.admin.inventory.*';
    }

    public function mount(): void
    {
        $this->redirect(route('website.admin.inventory.index'), navigate: false);
    }
}
