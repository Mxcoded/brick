<?php

namespace Modules\Admin\Filament\Resources\Users\Tables;

use App\Mail\AccountCreated;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'staff' ? 'info' : 'gray')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->badge()
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->separator(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'staff' => 'Staff',
                        'guest' => 'Guest',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'deactivated' => 'Deactivated',
                    ]),

                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('setStatus')
                    ->label('Set Status')
                    ->icon(Heroicon::OutlinedPower)
                    ->color('warning')
                    ->modalHeading('Update user status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended',
                                'deactivated' => 'Deactivated',
                            ])
                            ->default(fn (User $record): string => $record->status)
                            ->required(),

                        Textarea::make('suspension_reason')
                            ->label('Suspension reason')
                            ->maxLength(500)
                            ->helperText('Required when suspending or deactivating.')
                            ->rules(['nullable', 'string', 'max:500'])
                            ->required(fn (Get $get): bool => $get('status') !== 'active'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $isActive = $data['status'] === 'active';

                        $record->update([
                            'status' => $data['status'],
                            'suspended_at' => $isActive ? null : now(),
                            'suspension_reason' => $isActive ? null : ($data['suspension_reason'] ?? null),
                        ]);

                        $labels = ['active' => 'activated', 'suspended' => 'suspended', 'deactivated' => 'deactivated'];

                        Notification::make()
                            ->success()
                            ->title("{$record->name} has been {$labels[$data['status']]}.")
                            ->send();
                    }),

                Action::make('resendCredentials')
                    ->label('Resend Credentials')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('info')
                    ->hidden(fn (User $record): bool => $record->isGuest())
                    ->requiresConfirmation()
                    ->modalHeading('Resend login credentials')
                    ->modalDescription("A temporary password will be sent to this user's email.")
                    ->action(function (User $record): void {
                        $tempPassword = Str::random(16);
                        $record->update(['password' => $tempPassword]);

                        try {
                            Mail::to($record->email)->send(new AccountCreated($record, $tempPassword));

                            Notification::make()
                                ->success()
                                ->title("New login credentials have been sent to {$record->email}.")
                                ->send();
                        } catch (\Exception $e) {
                            \Log::error('Failed to resend credentials for user '.$record->id.': '.$e->getMessage());

                            Notification::make()
                                ->danger()
                                ->title('Failed to send credentials email. The mail server may be misconfigured.')
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
