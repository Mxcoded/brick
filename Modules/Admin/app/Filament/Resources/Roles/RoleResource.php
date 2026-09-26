<?php

namespace Modules\Admin\Filament\Resources\Roles;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\Roles\Pages\CreateRole;
use Modules\Admin\Filament\Resources\Roles\Pages\EditRole;
use Modules\Admin\Filament\Resources\Roles\Pages\ListRoles;
use Modules\Admin\Filament\Resources\Roles\Schemas\RoleForm;
use Modules\Admin\Filament\Resources\Roles\Tables\RolesTable;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'role';

    protected static ?string $pluralModelLabel = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'roles';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(
            'manage_roles',
            'roles.manage',
            'roles.create',
            'roles.read',
            'roles.update',
            'roles.delete',
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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
