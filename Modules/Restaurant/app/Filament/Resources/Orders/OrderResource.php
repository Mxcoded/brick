<?php

namespace Modules\Restaurant\Filament\Resources\Orders;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Restaurant\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use Modules\Restaurant\Filament\Resources\Orders\RelationManagers\PaymentsRelationManager;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Support\OrderStatus;
use Modules\Restaurant\Support\RestaurantPermission;
use UnitEnum;

/**
 * Read-mostly by design.
 *
 * An order is a financial record with lines attached, so the Filament screen
 * offers the status transitions a manager actually needs (accept, reject,
 * void) and leaves taking payment to the Payments relation manager, which goes
 * through OrderPaymentService. Nothing here can reprice a served order: the
 * only mutable fields are the status pair and the rejection reason.
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Restaurant';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'order';

    protected static ?string $pluralModelLabel = 'orders';

    protected static ?string $recordTitleAttribute = 'customer_name';

    protected static ?string $slug = 'restaurant/orders';

    public static function form(Schema $schema): Schema
    {
        // Orders are not created by hand. The waiter POS and the online checkout
        // are the only paths that build one, because they own the cart and the
        // pricing rules.
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Order')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order #'),

                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str_replace('_', ' ', ucfirst((string) $state)))
                            ->color(fn (?string $state): string => match ($state) {
                                'online' => 'info',
                                'table' => 'success',
                                'room' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => OrderStatus::color($state)),

                        TextEntry::make('tracking_status')
                            ->label('Progress')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => str_replace('_', ' ', ucfirst((string) $state)))
                            ->placeholder('—'),

                        TextEntry::make('customer_name')
                            ->label('Guest')
                            ->placeholder('Walk-in'),

                        TextEntry::make('customer_phone')
                            ->label('Phone')
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Placed')
                            ->since(),
                    ]),

                Section::make('Totals')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->money('NGN'),

                        TextEntry::make('discount')
                            ->money('NGN'),

                        TextEntry::make('vat')
                            ->label('VAT')
                            ->money('NGN'),

                        TextEntry::make('grand_total')
                            ->label('Total')
                            ->money('NGN')
                            ->weight('bold'),

                        TextEntry::make('balance')
                            ->label('Balance due')
                            ->money('NGN')
                            // The thing worth noticing when opening an order.
                            ->color(fn (Order $record): string => $record->balance > 0 ? 'danger' : 'success'),

                        TextEntry::make('reason')
                            ->label('Reason')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('Order')
                    ->formatStateUsing(fn (int $state): string => '#'.$state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Guest')
                    ->searchable()
                    ->placeholder('Walk-in'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => OrderStatus::color($state))
                    ->sortable(),

                TextColumn::make('tracking_status')
                    ->label('Progress')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): ?string => $state ? str_replace('_', ' ', ucfirst($state)) : null)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Due')
                    ->money('NGN')
                    ->color(fn (Order $record): string => $record->balance > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::options())
                    ->multiple(),

                SelectFilter::make('type')
                    ->options([
                        'table' => 'Table',
                        'room' => 'Room',
                        'online' => 'Online',
                    ])
                    ->multiple(),

                SelectFilter::make('created_at')
                    ->label('Placed')
                    ->options([
                        'today' => 'Today',
                        'week' => 'This week',
                        'month' => 'This month',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today' => $query->whereDate('created_at', today()),
                            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                            'month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Order $record): bool => RestaurantPermission::allows(RestaurantPermission::ORDERS_ACCEPT)
                        && OrderStatus::canAccept($record))
                    ->action(fn (Order $record) => $record->forceFill([
                        'status' => OrderStatus::ACCEPTED,
                        'tracking_status' => OrderStatus::PREPARING,
                    ])->save()),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => RestaurantPermission::allows(RestaurantPermission::ORDERS_REJECT)
                        && OrderStatus::canReject($record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->forceFill([
                            'status' => OrderStatus::REJECTED,
                            'reason' => $data['reason'],
                        ])->save();
                    }),

                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => RestaurantPermission::allows(RestaurantPermission::ORDERS_VOID)
                        && OrderStatus::canVoid($record))
                    ->requiresConfirmation()
                    // Voiding after payment would leave money posted against a
                    // cancelled order, so it is called out rather than hidden.
                    ->modalDescription(fn (Order $record): ?string => $record->payments()
                        ->where('status', 'completed')
                        ->exists()
                            ? 'This order already has a completed payment. Voiding will not reverse it — refund it instead.'
                            : 'The order will be cancelled and marked as void.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->forceFill([
                            'status' => OrderStatus::VOID,
                            'tracking_status' => OrderStatus::CANCELLED,
                            'reason' => $data['reason'],
                        ])->save();
                    }),

                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('accept')
                        ->label('Accept selected')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::ORDERS_ACCEPT))
                        ->action(function (Collection $records): void {
                            foreach ($records as $order) {
                                if (OrderStatus::canAccept($order)) {
                                    $order->forceFill([
                                        'status' => OrderStatus::ACCEPTED,
                                        'tracking_status' => OrderStatus::PREPARING,
                                    ])->save();
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::ORDERS_READ);
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        // Status moves through the table actions, which validate the transition
        // and capture a reason. The edit form has no fields worth changing.
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->withCount('orderItems')
            ->withSum(['payments as paid_total' => fn ($query) => $query->where('status', 'completed')], 'amount');
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
