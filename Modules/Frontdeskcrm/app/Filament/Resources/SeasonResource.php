<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Filament\Resources\SeasonResource\Pages;
use Modules\Frontdeskcrm\Models\Season;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'Season';

    protected static ?string $pluralModelLabel = 'Seasons';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Season')
                ->schema([
                    Forms\Components\TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->maxLength(1000)->columnSpanFull(),
                ])->columns(2),
            Section::make('Validity & Pricing')
                ->schema([
                    Forms\Components\DatePicker::make('valid_from')->required(),
                    Forms\Components\DatePicker::make('valid_to')->required(),
                    Forms\Components\TextInput::make('rate_multiplier')
                        ->label('Rate Multiplier')
                        ->numeric()
                        ->step(0.0001)
                        ->minValue(0)
                        ->default(1.0000)
                        ->helperText('Multiplier applied to base rates (e.g. 1.1000 for +10%).'),
                    Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('valid_from')->date()->sortable(),
                Tables\Columns\TextColumn::make('valid_to')->date()->sortable(),
                Tables\Columns\TextColumn::make('rate_multiplier')->numeric(4)->sortable(),
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
            'index' => Pages\ListSeasons::route('/'),
            'create' => Pages\CreateSeason::route('/create'),
            'edit' => Pages\EditSeason::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::SEASONS_READ);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::SEASONS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::SEASONS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::SEASONS_DELETE);
    }
}
