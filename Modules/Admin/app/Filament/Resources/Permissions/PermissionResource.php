<?php

namespace Modules\Admin\Filament\Resources\Permissions;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\Permissions\Pages\CreatePermission;
use Modules\Admin\Filament\Resources\Permissions\Pages\EditPermission;
use Modules\Admin\Filament\Resources\Permissions\Pages\ListPermissions;
use Modules\Admin\Filament\Resources\Permissions\Schemas\PermissionForm;
use Modules\Admin\Filament\Resources\Permissions\Tables\PermissionsTable;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Permissions';

    protected static ?string $modelLabel = 'permission';

    protected static ?string $pluralModelLabel = 'permissions';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'permissions';

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(
            'permissions.create',
            'permissions.read',
            'permissions.update',
            'permissions.delete',
        ) ?? false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
