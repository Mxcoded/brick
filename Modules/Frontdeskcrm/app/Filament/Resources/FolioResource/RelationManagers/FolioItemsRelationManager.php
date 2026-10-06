<?php

namespace Modules\Frontdeskcrm\Filament\Resources\FolioResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Models\FolioItem;

/**
 * The line items on one folio.
 *
 * Read-only: posting a charge is an operational action done from the folio
 * (the blade "Post Charge" modal or the nightly audit), so there is no inline
 * create/edit here - voiding happens on the folio itself.
 */
class FolioItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Folio items';

    protected static ?string $recordTitleAttribute = 'description';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('post_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('post_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('charge_type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('description')
                    ->placeholder('—')
                    ->limit(40),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('NGN')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('Tax')
                    ->money('NGN')
                    ->alignEnd()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('NGN')
                    ->alignEnd()
                    ->getStateUsing(fn (FolioItem $record): float => (float) $record->amount + (float) $record->tax_amount),

                Tables\Columns\TextColumn::make('postedBy.name')
                    ->label('Posted by')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->emptyStateHeading('No items on this folio')
            ->emptyStateDescription('Post a charge to see it listed here.');
    }
}
