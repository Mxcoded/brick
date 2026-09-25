<?php

namespace Modules\Admin\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->regex('/^[a-z0-9_\.-]+$/')
                    ->unique(ignoreRecord: true)
                    ->helperText('Lowercase letters, numbers, dots, hyphens and underscores only.'),

                Select::make('guard_name')
                    ->options([
                        'web' => 'web',
                    ])
                    ->default('web')
                    ->disabled(),

                CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->columns(3)
                    ->gridDirection('row'),
            ]);
    }
}
