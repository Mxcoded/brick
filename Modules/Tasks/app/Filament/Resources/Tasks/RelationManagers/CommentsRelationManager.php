<?php

namespace Modules\Tasks\Filament\Resources\Tasks\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Tasks\Services\TaskService;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                TextColumn::make('user.name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Comment')
                    ->wrap()
                    ->limit(120),

                TextColumn::make('created_at')
                    ->label('Posted')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at')
            ->headerActions([
                Action::make('addComment')
                    ->label('Add comment')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    // The creator and any assignee may comment; anyone else
                    // looking at this task must not be able to add to it.
                    ->visible(fn (): bool => app(TaskService::class)
                        ->canActOn($this->getOwnerRecord(), auth()->user()))
                    ->form([
                        Textarea::make('comment')
                            ->label('Comment')
                            ->required()
                            ->maxLength(2000)
                            ->rows(3),
                    ])
                    ->action(function (array $data): void {
                        app(TaskService::class)->comment(
                            $this->getOwnerRecord(),
                            auth()->user(),
                            $data['comment'],
                        );

                        Notification::make()
                            ->success()
                            ->title('Comment added')
                            ->send();
                    }),
            ]);
    }
}
