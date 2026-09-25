<?php

namespace Modules\Admin\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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

                CheckboxList::make('permissions')
                    ->relationship('permissions', 'name')
                    ->columns(3)
                    ->gridDirection('row'),

                Select::make('guard_name')
                    ->options([
                        'web' => 'web',
                    ])
                    ->default('web')
                    ->disabled(),
            ]);
    }
}
