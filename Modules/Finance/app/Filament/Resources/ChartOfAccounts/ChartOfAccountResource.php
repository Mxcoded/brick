<?php

namespace Modules\Finance\Filament\Resources\ChartOfAccounts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Pages\CreateChartOfAccount;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Pages\EditChartOfAccount;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Pages\ListChartOfAccounts;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Schemas\ChartOfAccountForm;
use Modules\Finance\Filament\Resources\ChartOfAccounts\Tables\ChartOfAccountsTable;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Support\FinancePermission;
use UnitEnum;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'account';

    protected static ?string $pluralModelLabel = 'accounts';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'chart-of-accounts';

    public static function form(Schema $schema): Schema
    {
        return ChartOfAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChartOfAccountsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return FinancePermission::allowsAny(
            FinancePermission::CHART_OF_ACCOUNTS_READ,
            FinancePermission::CHART_OF_ACCOUNTS_CREATE,
            FinancePermission::CHART_OF_ACCOUNTS_UPDATE,
        );
    }

    public static function canCreate(): bool
    {
        return FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return FinancePermission::allows(FinancePermission::CHART_OF_ACCOUNTS_DELETE);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChartOfAccounts::route('/'),
            'create' => CreateChartOfAccount::route('/create'),
            'edit' => EditChartOfAccount::route('/{record}/edit'),
        ];
    }
}
