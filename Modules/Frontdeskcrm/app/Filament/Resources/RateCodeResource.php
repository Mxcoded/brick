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
            Section::make('Rate Code')
                ->schema([
                    Forms\Components\TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->maxLength(1000)->columnSpanFull(),
                ])->columns(2),
            Section::make('Pricing')
                ->schema([
                    Forms\Components\TextInput::make('default_rate')
                        ->label('Default Rate')
                        ->numeric()
                        ->prefix('₦')
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('currency')
                        ->maxLength(3)
                        ->default('NGN')
                        ->required(),
                    Forms\Components\TextInput::make('min_los')
                        ->label('Min LOS')
                        ->helperText('Minimum length of stay in nights.')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),
                    Forms\Components\TextInput::make('max_los')
                        ->label('Max LOS')
                        ->helperText('Leave empty for no maximum.')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('∞'),
                ])->columns(2),
            Section::make('Restrictions')
                ->schema([
                    Forms\Components\Toggle::make('closed_to_arrival')->label('Closed to Arrival (CTA)'),
                    Forms\Components\Toggle::make('closed_to_departure')->label('Closed to Departure (CTD)'),
                    Forms\Components\Toggle::make('apply_weekdays')->label('Apply on Weekdays')->default(true),
                    Forms\Components\Toggle::make('apply_weekends')->label('Apply on Weekends')->default(true),
                    Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('default_rate')
                    ->label('Default Rate')
                    ->money(fn (RateCode $record): string => $record->currency ?: 'NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')->badge()->color('gray')->sortable(),
                Tables\Columns\TextColumn::make('min_los')->label('Min LOS')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('max_los')->label('Max LOS')->numeric()->placeholder('∞')->sortable(),
                Tables\Columns\TextColumn::make('restrictions')
                    ->badge()
                    ->state(fn (RateCode $record): array => static::restrictionsFor($record))
                    ->color(fn (string $state): string => match ($state) {
                        'CTA' => 'warning',
                        'CTD' => 'danger',
                        'No Weekdays', 'No Weekends' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
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

    /**
     * Restrictions shown as badges: closed-to-arrival/departure and the
     * weekday/weekend switches collapse into one readable list.
     *
     * @return array<int, string>
     */
    protected static function restrictionsFor(RateCode $record): array
    {
        $restrictions = [];

        if ($record->closed_to_arrival) {
            $restrictions[] = 'CTA';
        }

        if ($record->closed_to_departure) {
            $restrictions[] = 'CTD';
        }

        if (! $record->apply_weekdays) {
            $restrictions[] = 'No Weekdays';
        }

        if (! $record->apply_weekends) {
            $restrictions[] = 'No Weekends';
        }

        return $restrictions ?: ['None'];
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
