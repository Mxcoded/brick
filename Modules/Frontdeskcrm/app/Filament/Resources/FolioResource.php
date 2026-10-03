<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource\Pages;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

class FolioResource extends Resource
{
    protected static ?string $model = Folio::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'Folio';

    protected static ?string $pluralModelLabel = 'Folios';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('folio_number')->searchable(),
                Tables\Columns\TextColumn::make('registration.full_name')->searchable(),
                Tables\Columns\TextColumn::make('guest.full_name')->searchable(),
                Tables\Columns\TextColumn::make('total_charges')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('total_payments')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('balance')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFolios::route('/'),
            'view' => Pages\ViewFolio::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::FOLIOS_READ);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }
}
