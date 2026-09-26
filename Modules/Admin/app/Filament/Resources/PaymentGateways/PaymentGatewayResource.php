<?php

namespace Modules\Admin\Filament\Resources\PaymentGateways;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Admin\Filament\Resources\PaymentGateways\Pages\CreatePaymentGateway;
use Modules\Admin\Filament\Resources\PaymentGateways\Pages\EditPaymentGateway;
use Modules\Admin\Filament\Resources\PaymentGateways\Pages\ListPaymentGateways;
use Modules\Admin\Filament\Resources\PaymentGateways\Schemas\PaymentGatewayForm;
use Modules\Admin\Filament\Resources\PaymentGateways\Tables\PaymentGatewaysTable;
use Modules\Website\Models\PaymentGateway;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Payment Gateways';

    protected static ?string $modelLabel = 'payment gateway';

    protected static ?string $pluralModelLabel = 'payment gateways';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'payment-gateways';

    /**
     * Per-gateway credential fields shown in the admin form.
     */
    public static array $credentialFields = [
        'paystack' => [
            'secret' => 'Secret Key',
            'public' => 'Public Key',
        ],
        'stripe' => [
            'secret' => 'Secret Key',
            'public' => 'Publishable Key',
            'webhook_secret' => 'Webhook Secret',
        ],
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('payment_gateways.manage') ?? false;
    }

    public static function filterCredentials(string $code, array $credentials): array
    {
        $allowed = static::$credentialFields[$code] ?? [];

        return collect($credentials)
            ->only(array_keys($allowed))
            ->map(fn ($value) => $value === null ? '' : $value)
            ->toArray();
    }

    public static function syncDefault(PaymentGateway $gateway): void
    {
        if ($gateway->is_default) {
            PaymentGateway::where('id', '!=', $gateway->id)->update(['is_default' => false]);
            $gateway->update(['is_active' => true]);
        } elseif (! PaymentGateway::where('is_default', true)->exists()) {
            $gateway->update(['is_default' => true]);
        }
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewaysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGateways::route('/'),
            'create' => CreatePaymentGateway::route('/create'),
            'edit' => EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
