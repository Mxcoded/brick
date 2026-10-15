<?php

namespace Modules\Frontdeskcrm\Filament\Resources\RegistrationResource\RelationManagers;

use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;

/**
 * The guest's bill, on the registration it belongs to.
 *
 * Read-only here: creating an invoice from a folio, splitting it or closing it
 * are operational actions that already live on the folio's own screen, and a
 * second copy on the registration would be two places to keep in step. Each row
 * links through to that screen instead.
 */
class FoliosRelationManager extends RelationManager
{
    protected static string $relationship = 'folios';

    protected static ?string $title = 'Folios';

    protected static ?string $recordTitleAttribute = 'folio_number';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('folio_number')
                    ->label('Folio #')
                    ->weight('bold')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('folio_name')
                    ->label('Name')
                    ->placeholder('Main Folio'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(static fn (?string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'gray',
                        'void' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->money('NGN')
                    ->alignEnd()
                    ->color(static fn (float $state): string => $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Opened')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(static fn (Folio $record): string => FolioResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No folios')
            ->emptyStateDescription('This stay has no folio yet. One is created the first time a charge is posted.');
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
