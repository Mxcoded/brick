<?php

namespace Modules\Website\Filament\Resources;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Website\Filament\Resources\ContactMessageResource\Pages;
use Modules\Website\Filament\Resources\ContactMessageResource\RelationManagers\RepliesRelationManager;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Support\WebsitePermission;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'Enquiry';

    protected static ?string $pluralModelLabel = 'Enquiries';

    protected static ?int $navigationSort = 3;

    /**
     * Unread enquiries, so the number that matters is the one still waiting for a
     * first reply. The accessor is a count of a relation, so it is wrapped in a
     * query rather than left to run once per render of the navigation tree.
     */
    public static function getNavigationBadge(): ?string
    {
        $unread = static::getModel()::query()
            ->where('status', 'unread')
            ->where('is_archived', false)
            ->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enquiry')
                    ->description('Written by the visitor on the public contact form.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            // Name, email, subject and message are what the guest
                            // typed. Editing them would falsify the record of what
                            // was actually asked, so the whole section is read-only.
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('subject')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('message')
                            ->rows(5)
                            ->required()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])->columns(2),
                Section::make('Handling')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'unread' => 'Unread',
                                'read' => 'Read',
                                'replied' => 'Replied',
                            ])
                            ->required(),
                        Forms\Components\Select::make('follow_up_status')
                            ->label('Follow-up')
                            ->options([
                                'pending' => 'Pending',
                                'followed_up' => 'Followed up',
                                'closed' => 'Closed',
                            ]),
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned to')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('is_archived')
                            ->label('Archived')
                            ->helperText('Archived enquiries stay searchable but drop out of the active list.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('(no subject)'),
                Tables\Columns\TextColumn::make('message')->limit(70)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unread' => 'danger',
                        'replied' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('follow_up_status')
                    ->label('Follow-up')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'followed_up' => 'Followed up',
                        'closed' => 'Closed',
                        default => '—',
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Assigned')
                    ->placeholder('Unassigned')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reply_count')
                    ->label('Replies')
                    ->counts('replies')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Received')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unread' => 'Unread',
                        'read' => 'Read',
                        'replied' => 'Replied',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('follow_up_status')
                    ->options([
                        'pending' => 'Pending',
                        'followed_up' => 'Followed up',
                        'closed' => 'Closed',
                    ])
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_archived')
                    ->label('Archived'),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned only')
                    ->query(fn ($query) => $query->whereNull('assigned_to')),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                // Replying and archiving are the two things staff actually do to an
                // enquiry, and both live on legacy routes gated on
                // `website.contact-messages.update`, so link straight to them rather
                // than reimplementing the mail send inside Filament.
                Actions\Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('primary')
                    ->url(fn (ContactMessage $record): string => route('website.admin.contact-messages.reply', $record)),
                Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (ContactMessage $record): bool => ! $record->is_archived)
                    ->url(fn (ContactMessage $record): string => route('website.admin.contact-messages.archive', $record)),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RepliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'edit' => Pages\EditContactMessage::route('/{record}/edit'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }

    public static function canAccess(): bool
    {
        return WebsitePermission::allowsAny(
            WebsitePermission::CONTACT_MESSAGES_READ,
            WebsitePermission::CONTACT_MESSAGES_UPDATE,
        );
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }

    /**
     * Enquiries arrive from the public contact form. There is no create capability
     * in the taxonomy and no create route here, so the screen cannot be reached by
     * a URL guess either: Filament refuses the page before it renders.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::CONTACT_MESSAGES_UPDATE);
    }

    public static function canDelete($record): bool
    {
        return WebsitePermission::allows(WebsitePermission::CONTACT_MESSAGES_DELETE);
    }
}
