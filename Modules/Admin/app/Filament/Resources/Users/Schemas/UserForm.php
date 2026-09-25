<?php

namespace Modules\Admin\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->dehydrated(false),

                Select::make('type')
                    ->options([
                        'staff' => 'Staff',
                        'guest' => 'Guest',
                    ])
                    ->required(),

                Select::make('status')
                    ->default('active')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'deactivated' => 'Deactivated',
                    ])
                    ->required(),

                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }
}
