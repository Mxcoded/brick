<?php

namespace Modules\Admin\Filament\Resources\Users;

use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\Users\Pages\CreateUser;
use Modules\Admin\Filament\Resources\Users\Pages\EditUser;
use Modules\Admin\Filament\Resources\Users\Pages\ListUsers;
use Modules\Admin\Filament\Resources\Users\Schemas\UserForm;
use Modules\Admin\Filament\Resources\Users\Tables\UsersTable;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'user';

    protected static ?string $pluralModelLabel = 'users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'users';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(
            'users.manage',
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
