<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;

/**
 * What the guest has actually paid, on the registration it belongs to.
 *
 * Read-only on purpose. Recording and reversing a payment carry ledger effects
 * that the legacy payment form owns; this is the panel's view of the money so a
 * desk agent does not have to leave the record to answer "has this been paid?".
 */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('payment_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('NGN')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color(static fn (?string $state): string => match (strtolower((string) $state)) {
                        'city_ledger' => 'gray',
                        'cash' => 'success',
                        'complimentary' => 'info',
                        default => 'warning',
                    })
                    ->formatStateUsing(static fn (?string $state): string => str_replace('_', ' ', (string) $state)),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Reference')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Received by')
                    ->placeholder('System')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateHeading('No payments')
            ->emptyStateDescription('Nothing has been received against this stay yet.');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return FrontdeskPermission::allowsAny(
            FrontdeskPermission::FOLIOS_READ,
            FrontdeskPermission::REGISTRATIONS_READ,
            FrontdeskPermission::REGISTRATIONS_MANAGE,
        );
    }
}
