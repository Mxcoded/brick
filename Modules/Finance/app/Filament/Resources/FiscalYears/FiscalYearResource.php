<?php

namespace Modules\Finance\Filament\Resources\FiscalYears;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Finance\Filament\Resources\FiscalYears\Pages\CreateFiscalYear;
use Modules\Finance\Filament\Resources\FiscalYears\Pages\EditFiscalYear;
use Modules\Finance\Filament\Resources\FiscalYears\Pages\ListFiscalYears;
use Modules\Finance\Filament\Resources\FiscalYears\RelationManagers\FiscalPeriodsRelationManager;
use Modules\Finance\Filament\Resources\FiscalYears\Schemas\FiscalYearForm;
use Modules\Finance\Filament\Resources\FiscalYears\Tables\FiscalYearsTable;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Support\FinancePermission;
use UnitEnum;

class FiscalYearResource extends Resource
{
    protected static ?string $model = FiscalYear::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Fiscal Years';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'fiscal year';

    protected static ?string $pluralModelLabel = 'fiscal years';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'fiscal-years';

    public static function form(Schema $schema): Schema
    {
        return FiscalYearForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FiscalYearsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return FinancePermission::allowsAny(
            FinancePermission::FISCAL_YEARS_READ,
            FinancePermission::FISCAL_YEARS_CREATE,
            FinancePermission::FISCAL_YEARS_UPDATE,
        );
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return FinancePermission::allows(FinancePermission::FISCAL_YEARS_CREATE);
    }

    public static function canEdit($record): bool
    {
        return FinancePermission::allows(FinancePermission::FISCAL_YEARS_UPDATE);
    }

    /**
     * A fiscal year can never be deleted: posted journal entries are dated
     * against its periods, so removing it would orphan the ledger.
     */
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canView($record): bool
    {
        return FinancePermission::allows(FinancePermission::FISCAL_YEARS_READ);
    }

    public static function getRelations(): array
    {
        return [
            FiscalPeriodsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFiscalYears::route('/'),
            'create' => CreateFiscalYear::route('/create'),
            'edit' => EditFiscalYear::route('/{record}/edit'),
        ];
    }
}
