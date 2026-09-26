<?php

namespace Modules\Admin\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->regex('/^[a-z0-9_]+$/')
                    ->unique(ignoreRecord: true)
                    ->helperText('Lowercase letters, numbers and underscores only.'),

                ViewField::make('permissions')
                    ->label('Permissions')
                    ->helperText('Tick the actions each role may perform. Permissions are grouped by business module.')
                    ->view('admin::filament.components.permission-matrix')
                    ->default([])
                    ->formatStateUsing(function (mixed $state, ?Role $record): array {
                        $permissions = is_iterable($state) && is_countable($state) && count($state) > 0
                            ? $state
                            : ($record?->permissions ?? []);

                        if ($permissions instanceof Collection) {
                            $permissions = $permissions->all();
                        }

                        return array_values(array_unique(array_map(
                            fn ($permission) => (int) ($permission instanceof Permission ? $permission->getKey() : $permission),
                            is_iterable($permissions) ? $permissions : [],
                        )));
                    })
                    ->dehydrateStateUsing(fn (mixed $state): array => collect($state ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->filter(fn ($id) => $id > 0)
                        ->unique()
                        ->values()
                        ->all()),

                Select::make('guard_name')
                    ->options([
                        'web' => 'web',
                    ])
                    ->default('web')
                    ->disabled(),
            ]);
    }
}
