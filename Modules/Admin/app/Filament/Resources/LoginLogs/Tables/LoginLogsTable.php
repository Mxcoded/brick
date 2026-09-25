<?php

namespace Modules\Admin\Filament\Resources\LoginLogs\Tables;

use App\Models\User;
use App\Models\UserLoginLog;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\LoginLogs\LoginLogResource;

class LoginLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('logged_in_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->formatStateUsing(fn (?string $state): string => $state ?? 'Unknown')
                    ->description(fn (UserLoginLog $record): ?string => $record->user?->email ?? 'N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('device_type')
                    ->label('Device / Browser')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? 'desktop'))
                    ->description(fn (UserLoginLog $record): string => ($record->browser ?? 'Unknown').' / '.($record->platform ?? 'Unknown'))
                    ->toggleable(),

                TextColumn::make('logged_in_at')
                    ->label('Login Time')
                    ->date('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('session_state')
                    ->label('Session')
                    ->state(fn (UserLoginLog $record): string => $record->logged_out_at
                        ? 'signed_out'
                        : ($record->is_active ? 'active' : 'idle'))
                    ->badge()
                    ->color(fn (string $state): string => LoginLogResource::sessionStateColor($state))
                    ->formatStateUsing(fn (string $state): string => LoginLogResource::sessionStateLabel($state)),

                TextColumn::make('logged_out_at')
                    ->label('Logout Time')
                    ->state(fn (UserLoginLog $record): string => $record->logged_out_at?->format('M d, Y h:i A') ?? '—')
                    ->toggleable(),

                TextColumn::make('session_duration')
                    ->label('Duration')
                    ->state(fn (UserLoginLog $record): string => $record->session_duration === null
                        ? 'Ongoing'
                        : LoginLogResource::durationLabel($record->session_duration)),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'success' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn (): array => User::query()
                        ->whereIn('id', UserLoginLog::query()->select('user_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),

                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),

                SelectFilter::make('device_type')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                    ]),

                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('From'),
                        DatePicker::make('date_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('logged_in_at', '>=', $date))
                            ->when($data['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('logged_in_at', '<=', $date));
                    }),
            ]);
    }
}
