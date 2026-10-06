<?php

namespace Modules\Frontdeskcrm\Filament\Resources;

use BackedEnum;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource\Pages;
use Modules\Frontdeskcrm\Filament\Resources\FolioResource\RelationManagers\FolioItemsRelationManager;
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
                Tables\Columns\TextColumn::make('registration.guest.full_name')->searchable(),
                Tables\Columns\TextColumn::make('total_charges')->money('NGN'),
                Tables\Columns\TextColumn::make('total_payments')->money('NGN'),
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
        return [
            FolioItemsRelationManager::class,
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Folio')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('folio_number')
                            ->copyable()
                            ->weight('bold'),
                        TextEntry::make('folio_name')->placeholder('—'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('balance')
                            ->money('NGN')
                            ->color(fn (string $state): string => (float) $state > 0 ? 'danger' : 'gray'),
                        TextEntry::make('total_charges')->money('NGN'),
                        TextEntry::make('total_payments')->money('NGN'),
                        TextEntry::make('created_at')->dateTime('M d, Y H:i'),
                        TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Stay')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('registration_full_name')
                            ->label('Guest on stay')
                            ->weight('bold')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->full_name)
                            ->url(fn (Folio $record): ?string => $record->registration
                                ? RegistrationResource::getUrl('view', ['record' => $record->registration])
                                : null),
                        TextEntry::make('registration_reservation_code')
                            ->label('Reference')
                            ->placeholder('Walk-in')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->reservation_code),
                        TextEntry::make('registration_room_number')
                            ->label('Room')
                            ->badge()
                            ->color('gray')
                            ->placeholder('—')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->roomUnit?->room_number),
                        TextEntry::make('registration_check_in')
                            ->label('Check in')
                            ->date('M d, Y')
                            ->getStateUsing(fn (Folio $record): ?Carbon => $record->registration?->check_in),
                        TextEntry::make('registration_check_out')
                            ->label('Check out')
                            ->date('M d, Y')
                            ->getStateUsing(fn (Folio $record): ?Carbon => $record->registration?->check_out),
                        TextEntry::make('registration_stay_status')
                            ->label('Stay status')
                            ->badge()
                            ->placeholder('—')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->stay_status),
                    ]),

                Section::make('Guest')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('guest_full_name')
                            ->label('Name')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->guest?->full_name)
                            ->url(fn (Folio $record): ?string => $record->registration?->guest
                                ? GuestResource::getUrl('view', ['record' => $record->registration->guest])
                                : null),
                        TextEntry::make('guest_contact_number')
                            ->label('Phone')
                            ->placeholder('—')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->guest?->contact_number),
                        TextEntry::make('guest_email')
                            ->label('Email')
                            ->placeholder('—')
                            ->getStateUsing(fn (Folio $record): ?string => $record->registration?->guest?->email),
                    ]),
            ]);
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
