<?php

namespace Modules\Admin\Filament\Resources\AuditTrails\Tables;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Admin\Filament\Resources\AuditTrails\AuditTrailResource;
use OwenIt\Auditing\Models\Audit;

class AuditTrailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->date('M d, H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->formatStateUsing(fn (Audit $record, ?string $state): string => static::actorLabel($record, $state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => AuditTrailResource::eventColor($state))
                    ->formatStateUsing(fn (string $state): string => AuditTrailResource::eventLabel($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('auditable_type')
                    ->label('Model')
                    ->formatStateUsing(fn (Audit $record): string => sprintf(
                        '%s #%s',
                        class_basename($record->auditable_type),
                        $record->auditable_id,
                    ))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('new_values')
                    ->label('Changes')
                    ->formatStateUsing(fn (Audit $record): string => AuditTrailResource::changesSummary($record))
                    ->wrap()
                    ->limit(140),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(60)
                    ->toggleable(),

                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(48)
                    ->toggleable(),

                TextColumn::make('tags')
                    ->label('Tags')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options(fn (): array => Audit::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->mapWithKeys(fn ($event) => [$event => AuditTrailResource::eventLabel($event)])
                        ->all()),

                SelectFilter::make('auditable_type')
                    ->label('Model')
                    ->options(fn (): array => Audit::query()
                        ->distinct()
                        ->orderBy('auditable_type')
                        ->pluck('auditable_type', 'auditable_type')
                        ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                        ->all()),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn (): array => DB::table('users')
                        ->whereIn('id', Audit::query()->select('user_id'))
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('restore')
                    ->label('Restore')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('warning')
                    ->visible(fn (Audit $record): bool => in_array($record->event, ['updated', 'deleted'], true))
                    ->action(function (Audit $record): void {
                        [$ok, $message] = AuditTrailResource::restoreAudit($record);

                        ($ok
                            ? Notification::make()->title($message)->success()
                            : Notification::make()->title($message)->danger())
                            ->send();
                    }),
            ]);
    }

    protected static function actorLabel(Audit $record, ?string $name): string
    {
        if ($name !== null && $name !== '') {
            return $name;
        }

        $guestTag = collect(explode(',', (string) $record->tags))
            ->first(fn ($tag) => str_starts_with($tag, 'guest:'));

        return $guestTag !== null
            ? Str::after($guestTag, 'guest:')
            : 'System';
    }
}
