<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsArticleResource\Pages;
use App\Filament\Support\SeoFields;
use App\Models\NewsArticle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsArticleResource extends Resource
{
    protected static ?string $model = NewsArticle::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'News & Review Queue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Story')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('dek')
                            ->label('Standfirst / dek')
                            ->helperText('The one-line summary shown under the headline.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('body')
                            ->required()
                            ->rows(12)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Article image')
                            ->helperText('Every upload is resized to a fixed 1600×900 automatically - crop to the part of the photo you want kept before saving, since anything outside the crop box is cut at this step (the site itself never re-crops it after that).')
                            ->image()
                            ->disk('public')
                            ->directory('news-images')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9'])
                            ->imageResizeTargetWidth('1600')
                            ->imageResizeTargetHeight('900')
                            ->imageResizeMode('cover')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('category')
                            ->options([
                                'match-report' => 'Match report',
                                'transfers' => 'Transfers',
                                'injury' => 'Injury news',
                                'analysis' => 'Analysis',
                                'club-news' => 'Club news',
                            ])
                            ->required(),
                        Forms\Components\Select::make('source')
                            ->options([
                                'ai' => 'Morphie',
                                'human' => 'Human-written',
                            ])
                            ->required()
                            ->default('ai'),
                        Forms\Components\Select::make('league_id')
                            ->relationship('league', 'name')
                            ->label('League')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('team_id')
                            ->relationship('team', 'name')
                            ->label('Team')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('author')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Editorial status')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_review' => 'Pending review',
                                'published' => 'Published',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Toggle::make('is_pinned')
                            ->label('Pin to homepage')
                            ->helperText('Pinned + published articles appear in the homepage hero, newest-pinned first. Only takes effect once the article is Published - up to 4 show at a time.'),
                        Forms\Components\Select::make('reviewed_by')
                            ->relationship('reviewer', 'name')
                            ->label('Reviewed by')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'rejected'),
                    ]),

                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\ToggleColumn::make('is_pinned')
                    ->label('Pinned')
                    ->tooltip('Pinned + published articles show in the homepage hero'),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\TextColumn::make('source')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'ai' ? 'Morphie' : 'Human-written')
                    ->color(fn (string $state) => $state === 'ai' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'pending_review' => 'warning',
                        'published' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('league.name')
                    ->label('League')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('author')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending review',
                        'published' => 'Published',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'match-report' => 'Match report',
                        'transfers' => 'Transfers',
                        'injury' => 'Injury news',
                        'analysis' => 'Analysis',
                        'club-news' => 'Club news',
                    ]),
                Tables\Filters\TernaryFilter::make('is_pinned')
                    ->label('Pinned'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve & publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (NewsArticle $record) => $record->status === 'pending_review')
                    ->action(function (NewsArticle $record) {
                        $record->approve(auth()->user());
                        Notification::make()
                            ->title('Article published')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (NewsArticle $record) => $record->status === 'pending_review')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for rejection')
                            ->required(),
                    ])
                    ->action(function (NewsArticle $record, array $data) {
                        $record->reject(auth()->user(), $data['rejection_reason']);
                        Notification::make()
                            ->title('Article rejected')
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsArticles::route('/'),
            'create' => Pages\CreateNewsArticle::route('/create'),
            'edit' => Pages\EditNewsArticle::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending_review')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
