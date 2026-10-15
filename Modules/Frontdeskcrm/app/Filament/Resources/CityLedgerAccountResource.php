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
use Modules\Frontdeskcrm\Filament\Resources\CityLedgerAccountResource\Pages;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use UnitEnum;

class CityLedgerAccountResource extends Resource
{
    protected static ?string $model = CityLedgerAccount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Frontdesk';

    protected static ?string $modelLabel = 'City Ledger Account';

    protected static ?string $pluralModelLabel = 'City Ledger Accounts';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account Information')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('code')->maxLength(50)->placeholder('Auto-generated if blank'),
                    Forms\Components\TextInput::make('contact_person')->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                    Forms\Components\TextInput::make('phone')->maxLength(255),
                    Forms\Components\Textarea::make('address')->columnSpanFull()->rows(3),
                    Forms\Components\TextInput::make('tax_id')->maxLength(255),
                ])->columns(2),
            Section::make('Billing')
                ->schema([
                    Forms\Components\TextInput::make('credit_limit')->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('balance')->numeric()->disabled()->dehydrated(false)->helperText('Calculated from transactions'),
                    Forms\Components\TextInput::make('payment_terms')->maxLength(255)->default('net30'),
                    Forms\Components\Select::make('status')->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])->default('active')->required(),
                    Forms\Components\Textarea::make('notes')->columnSpanFull()->rows(3),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('contact_person')->toggleable(),
                Tables\Columns\TextColumn::make('email')->toggleable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\TextColumn::make('credit_limit')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('balance')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('payment_terms')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'inactive' => 'danger',
                    default => 'gray',
                }),
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
            'index' => Pages\ListCityLedgerAccounts::route('/'),
            'create' => Pages\CreateCityLedgerAccount::route('/create'),
            'edit' => Pages\EditCityLedgerAccount::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::GUESTS_READ) || FrontdeskPermission::allows(FrontdeskPermission::FOLIOS_READ) || FrontdeskPermission::allows(FrontdeskPermission::INVOICES_READ);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return FrontdeskPermission::allowsAny(FrontdeskPermission::INVOICES_CREATE, FrontdeskPermission::INVOICES_ISSUE);
    }

    public static function canEdit($record): bool
    {
        return FrontdeskPermission::allowsAny(FrontdeskPermission::INVOICES_CREATE, FrontdeskPermission::INVOICES_ISSUE);
    }

    public static function canDelete($record): bool
    {
        return FrontdeskPermission::allows(FrontdeskPermission::INVOICES_VOID);
    }
}
