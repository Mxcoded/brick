<?php

namespace Modules\Finance\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Services\FiscalPeriodService;
use Modules\Finance\Support\SubLedger;

class JournalEntryForm
{
    public const MIN_LINES = 2;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                DatePicker::make('date')
                    ->required()
                    ->native(false)
                    ->default(now())
                    ->live(onBlur: true)
                    ->helperText(fn (Get $get): string => self::periodHint($get('date')))
                    ->rule(fn (): \Closure => self::lockedPeriodRule()),

                Select::make('reference_type')
                    ->label('Source')
                    ->options(self::referenceTypes())
                    ->default('manual')
                    ->helperText('Auto-posted entries carry their own source.'),

                Textarea::make('description')
                    ->required()
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->helperText('What this entry records, e.g. "Accrued September utility bill".'),

                Repeater::make('lines')
                    ->label('Lines')
                    ->required()
                    ->minItems(self::MIN_LINES)
                    ->defaultItems(2)
                    ->addActionLabel('Add line')
                    ->live(onBlur: true)
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        Select::make('account_id')
                            ->label('Account')
                            ->required()
                            ->searchable()
                            ->options(fn (): array => ChartOfAccount::query()
                                ->where('active', true)
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (ChartOfAccount $a) => [$a->id => "{$a->code} — {$a->name}"])
                                ->all()),

                        Select::make('party')
                            ->label('Sub-ledger')
                            ->searchable()
                            ->nullable()
                            ->placeholder('None')
                            ->getSearchResultsUsing(fn (string $search): array => SubLedger::options($search))
                            ->getOptionLabelUsing(fn ($value): ?string => SubLedger::labelFor($value))
                            ->helperText('Guest or company for the AR/AP line.'),

                        TextInput::make('debit')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01),

                        TextInput::make('credit')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01),

                        TextInput::make('description')
                            ->maxLength(255),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['account_id']
                        ? ChartOfAccount::find($state['account_id'])?->code
                        : null),

                TextEntry::make('balance_summary')
                    ->state(fn (Get $get): string => self::balanceSummary($get('lines'))['label'])
                    ->columnSpanFull()
                    ->hiddenLabel()
                    ->color(fn (Get $get): string => self::balanceSummary($get('lines'))['balanced'] ? 'success' : 'warning')
                    ->icon(fn (Get $get): string => self::balanceSummary($get('lines'))['balanced']
                        ? 'heroicon-o-check-circle'
                        : 'heroicon-o-exclamation-triangle'),
            ]);
    }

    /**
     * @return array{label: string, balanced: bool}
     */
    public static function balanceSummary(mixed $lines): array
    {
        $lines = is_array($lines) ? $lines : [];

        $debit = round(collect($lines)->sum(fn ($line) => (float) ($line['debit'] ?? 0)), 2);
        $credit = round(collect($lines)->sum(fn ($line) => (float) ($line['credit'] ?? 0)), 2);
        $difference = round($debit - $credit, 2);

        if (abs($difference) < 0.005) {
            return [
                'label' => 'Balanced — debits and credits both total '.number_format($debit, 2),
                'balanced' => true,
            ];
        }

        return [
            'label' => sprintf(
                'Out of balance by %s (debits %s, credits %s)',
                number_format(abs($difference), 2),
                number_format($debit, 2),
                number_format($credit, 2),
            ),
            'balanced' => false,
        ];
    }

    /**
     * Turns a form line into the attributes a JournalLine is stored with.
     *
     * The sub-ledger select carries one "Class:id" value; here it is split back
     * into the two nullable columns so a line without a party stores nulls
     * rather than an empty string.
     *
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    public static function normaliseLine(array $line): array
    {
        $party = SubLedger::parse($line['party'] ?? null);

        return [
            'account_id' => $line['account_id'],
            'party_type' => $party['type'] ?? null,
            'party_id' => $party['id'] ?? null,
            'debit' => round((float) ($line['debit'] ?? 0), 2),
            'credit' => round((float) ($line['credit'] ?? 0), 2),
            'description' => $line['description'] ?? null,
        ];
    }

    /**
     * The "Class:id" a line's stored party should be shown as in the form.
     */
    public static function partyValue(?string $type, mixed $id): ?string
    {
        return $type && $id ? $type.':'.$id : null;
    }

    protected static function lockedPeriodRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! $value) {
                return;
            }

            if (app(FiscalPeriodService::class)->isLockedFor($value)) {
                $fail('This date falls in a locked accounting period.');
            }
        };
    }

    protected static function periodHint(mixed $date): string
    {
        if (! $date) {
            return 'Journal date.';
        }

        $period = app(FiscalPeriodService::class)->periodFor($date);

        if (! $period) {
            return 'No fiscal calendar configured — posting is unrestricted.';
        }

        return $period->isLocked()
            ? "{$period->name} is locked."
            : "{$period->name} — open for posting.";
    }

    /**
     * @return array<string, string>
     */
    protected static function referenceTypes(): array
    {
        return [
            'manual' => 'Manual adjustment',
            'accrual' => 'Accrual',
            'payroll' => 'Payroll',
            'depreciation' => 'Depreciation',
            'tax' => 'Tax',
        ];
    }
}
