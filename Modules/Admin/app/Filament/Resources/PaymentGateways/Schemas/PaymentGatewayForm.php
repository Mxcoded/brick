<?php

namespace Modules\Admin\Filament\Resources\PaymentGateways\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Admin\Filament\Resources\PaymentGateways\PaymentGatewayResource;

class PaymentGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        $credentialFields = PaymentGatewayResource::$credentialFields;

        return $schema
            ->components([

                Select::make('code')
                    ->options([
                        'paystack' => 'Paystack',
                        'stripe' => 'Stripe',
                    ])
                    ->required()
                    ->live()
                    ->disabledOn('edit')
                    ->dehydrated(),
                // On edit the code stays fixed (disabl ed but kept in state).

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->default(false),

                Toggle::make('is_default')
                    ->default(false),

                ...static::credentialComponents($credentialFields),
            ]);
    }

    protected static function credentialComponents(array $credentialFields): array
    {
        $components = [];

        foreach ($credentialFields as $code => $fields) {
            foreach ($fields as $field => $label) {
                $components[] = TextInput::make("credentials.{$field}")
                    ->label($label.' ('.ucfirst($code).')')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->dehydrated(fn (?string $state, string $operation): bool => $operation === 'create' || filled($state))
                    ->visible(fn (Get $get): bool => $get('code') === $code);
            }
        }

        return $components;
    }
}
