<?php

namespace Modules\Restaurant\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Services\OrderPaymentService;
use Modules\Restaurant\Support\RestaurantPermission;
use Throwable;

/**
 * The payment history for an order, and the only place a payment can be taken
 * from the back office.
 *
 * Both the record and the refund go through OrderPaymentService rather than
 * touching the ledger from a Filament action. The service owns the sequence —
 * create the payment, post the sale, close the order, update loyalty, fire the
 * events — and it never throws on a Finance failure, leaving the payment
 * committed with finance_posted = false for `restaurant:reconcile-finance`. A
 * resource action that reimplemented any part of that would break the invariant
 * that a completed payment has exactly one ledger entry.
 */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('paid_at', 'desc')
            ->columns([
                TextColumn::make('amount')
                    ->money('NGN')
                    ->sortable(),

                TextColumn::make('method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->description(fn (?string $state): ?string => $state === 'transfer' ? 'Bank transfer' : null),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'refunded' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('change_due')
                    ->label('Change')
                    ->money('NGN')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('finance_posted')
                    ->label('Ledger')
                    ->badge()
                    // Worth surfacing: an unposted payment is money taken with no
                    // ledger entry behind it yet.
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Posted' : 'Unposted')
                    ->colors(['success' => 'success', 'danger' => 'danger'])
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Paid')
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('recordPayment')
                    ->label('Record payment')
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (): bool => $this->canTakePayment($this->getOwnerRecord()))
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('NGN')
                            ->required()
                            ->minValue(0.01)
                            ->default(fn (): float => (float) $this->getOwnerRecord()->balance)
                            ->helperText('Defaults to the full balance. Part payments are allowed.'),

                        Select::make('method')
                            ->label('Method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'transfer' => 'Bank transfer',
                                'pos' => 'POS terminal',
                            ])
                            ->required()
                            ->default('cash')
                            ->live()
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                // Cash needs change, a terminal does not.
                                $set('amount_tendered', $state === 'cash' ? (float) $this->getOwnerRecord()->balance : null);
                            }),

                        TextInput::make('amount_tendered')
                            ->label('Cash tendered')
                            ->numeric()
                            ->prefix('NGN')
                            ->minValue(0)
                            ->visible(fn (?string $state): bool => $state === 'cash')
                            ->helperText('Change due is worked out automatically.'),

                        TextInput::make('reference')
                            ->label('Reference')
                            ->maxLength(255)
                            ->helperText('Card terminal slip, transfer reference, or till number.'),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data): void {
                        $order = $this->getOwnerRecord()->refresh();

                        try {
                            $result = app(OrderPaymentService::class)->takePayment($order, [
                                'method' => $data['method'],
                                'amount' => (float) $data['amount'],
                                'amount_tendered' => isset($data['amount_tendered']) && $data['amount_tendered'] !== null
                                    ? (float) $data['amount_tendered']
                                    : null,
                                'reference' => $data['reference'] ?? null,
                                'notes' => $data['notes'] ?? null,
                            ]);
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Payment not recorded')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        $parts = [number_format((float) $result['payment']->amount, 2).' received'];

                        if ($result['change_due'] > 0) {
                            $parts[] = 'change '.number_format($result['change_due'], 2);
                        }

                        if ($result['fully_paid']) {
                            $parts[] = 'order settled';
                        } else {
                            $parts[] = number_format($result['balance_remaining'], 2).' still due';
                        }

                        Notification::make()
                            ->title('Payment recorded')
                            ->body(implode(' · ', $parts))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (): bool => RestaurantPermission::allows(RestaurantPermission::PAYMENTS_REFUND)
                        && $this->getOwnerRecord()->status === Order::STATUS_COMPLETED
                        && $this->getOwnerRecord()->tracking_status === 'paid')
                    ->requiresConfirmation()
                    ->modalDescription('A reversing ledger entry will be created for the payment and the order will be marked refunded.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Recorded against the payment and on the order.'),
                    ])
                    ->action(function (array $data): void {
                        $order = $this->getOwnerRecord()->refresh();

                        try {
                            app(OrderPaymentService::class)->refundPayment($order, $data['reason']);
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Refund failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Payment refunded')
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
            ]);
    }

    /**
     * Payment is only offered when the order can actually take one, so the
     * button never leads to a rejected submission.
     */
    protected function canTakePayment(Order $order): bool
    {
        if (! RestaurantPermission::allows(RestaurantPermission::PAYMENTS_CREATE)) {
            return false;
        }

        // Void, rejected and already-settled orders are closed to new money.
        if (in_array($order->status, ['rejected', 'void'], true)) {
            return false;
        }

        return $order->balance > 0;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return RestaurantPermission::allows(RestaurantPermission::PAYMENTS_READ);
    }
}
