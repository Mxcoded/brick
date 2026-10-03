<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Filament\Resources\RateCodeResource\Pages;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

class RateCodeResource extends Resource
{
    protected static ?string $model = RateCode::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'Rate Code';

    protected static ?string $pluralModelLabel = 'Rate Codes';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('code')->required()->maxLength(50)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->maxLength(1000)->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRateCodes::route('/'),
            'create' => Pages\CreateRateCode::route('/create'),
            'edit' => Pages\EditRateCode::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::RATE_CODES_READ);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::RATE_CODES_CREATE);
    }

    public static function canEdit($record): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::RATE_CODES_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::RATE_CODES_DELETE);
    }
}
