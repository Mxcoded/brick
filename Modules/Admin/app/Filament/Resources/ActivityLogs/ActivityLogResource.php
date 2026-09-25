<?php

namespace Modules\Admin\Filament\Resources\ActivityLogs;

use App\Models\UserActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use Modules\Admin\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;

class ActivityLogResource extends Resource
{
    protected static ?string $model = UserActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'activity log';

    protected static ?string $pluralModelLabel = 'activity logs';

    protected static ?string $slug = 'activity-logs';

    /**
     * Human-readable verb derived from the trailing segment of an action name.
     */
    public static function verbLabel(string $action): string
    {
        $verb = static::verb($action);

        return match ($verb) {
            'create', 'store' => 'Created',
            'update', 'edit' => 'Updated',
            'delete', 'destroy' => 'Deleted',
            'assign-room' => 'Room Assigned',
            'assign' => 'Assigned',
            'approve' => 'Approved',
            'reject' => 'Rejected',
            'cancel' => 'Cancelled',
            'page_view' => 'Viewed',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            default => ucfirst(str_replace(['-', '_'], ' ', $verb)),
        };
    }

    /**
     * Tailwind-ish color key used to badge the verb label.
     */
    public static function verbColor(string $action): string
    {
        return match (static::verb($action)) {
            'create', 'store' => 'success',
            'update', 'edit' => 'primary',
            'delete', 'destroy' => 'danger',
            'assign-room', 'assign', 'approve' => 'info',
            'reject', 'cancel' => 'warning',
            'page_view' => 'gold',
            default => 'dark',
        };
    }

    /**
     * Resource portion of an action name, e.g. "room-types" from
     * "room-types.update". Used as the badge sub-label.
     */
    public static function resourceLabel(string $action): string
    {
        $parts = explode('.', $action);

        return ucwords(str_replace(['-', '_'], ' ', implode(' ', array_slice($parts, 0, -1))));
    }

    protected static function verb(string $action): string
    {
        $parts = explode('.', $action);

        return (string) end($parts);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('access_admin_dashboard') ?? false;
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
