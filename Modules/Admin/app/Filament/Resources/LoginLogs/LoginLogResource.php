<?php

namespace Modules\Admin\Filament\Resources\LoginLogs;

use App\Models\UserLoginLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\LoginLogs\Pages\ActiveSessions;
use Modules\Admin\Filament\Resources\LoginLogs\Pages\ListLoginLogs;
use Modules\Admin\Filament\Resources\LoginLogs\Tables\LoginLogsTable;

class LoginLogResource extends Resource
{
    protected static ?string $model = UserLoginLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Login History';

    protected static ?string $modelLabel = 'login log';

    protected static ?string $pluralModelLabel = 'login logs';

    protected static ?string $slug = 'login-logs';

    public static function durationLabel(?int $minutes): string
    {
        if ($minutes === null) {
            return 'N/A';
        }

        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours < 24) {
            return $hours.'h '.$mins.'m';
        }

        $days = floor($hours / 24);
        $hours = $hours % 24;

        return $days.'d '.$hours.'h';
    }

    public static function sessionStateLabel(string $state): string
    {
        return match ($state) {
            'active' => 'Active',
            'idle' => 'Idle',
            default => 'Signed out',
        };
    }

    public static function sessionStateColor(string $state): string
    {
        return match ($state) {
            'active' => 'success',
            'idle' => 'warning',
            default => 'gray',
        };
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('login_logs.read') ?? false;
    }

    public static function table(Table $table): Table
    {
        return LoginLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginLogs::route('/'),
            'active-sessions' => ActiveSessions::route('/active-sessions'),
        ];
    }
}
