<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Filament\Support\SeoFields;
use App\Models\MatchFixture;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResultResource extends Resource
{
    protected static ?string $model = MatchFixture::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Results';

    protected static ?string $modelLabel = 'result';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'final');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Match')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('league_id')
                            ->relationship('league', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('matchday')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('home_team_id')
                            ->label('Home team')
                            ->relationship('homeTeam', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('away_team_id')
                            ->label('Away team')
                            ->relationship('awayTeam', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\DateTimePicker::make('kickoff_at')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('venue')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Final score')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('home_score')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('away_score')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                    ]),

                Forms\Components\Section::make('Match story')
                    ->schema([
                        Forms\Components\Textarea::make('match_report')
                            ->label('')
                            ->rows(5),
                    ]),

                Forms\Components\Section::make('Match statistics')
                    ->schema([
                        Forms\Components\Fieldset::make('Possession')
                            ->schema([
                                Forms\Components\TextInput::make('stats.possession.home')->label('Home %')->numeric()->suffix('%'),
                                Forms\Components\TextInput::make('stats.possession.away')->label('Away %')->numeric()->suffix('%'),
                            ])->columns(2),
                        Forms\Components\Fieldset::make('Shots')
                            ->schema([
                                Forms\Components\TextInput::make('stats.shots.home')->label('Home')->numeric(),
                                Forms\Components\TextInput::make('stats.shots.away')->label('Away')->numeric(),
                                Forms\Components\TextInput::make('stats.shots_on_target.home')->label('On target (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.shots_on_target.away')->label('On target (away)')->numeric(),
                            ])->columns(2),
                        Forms\Components\Fieldset::make('Discipline')
                            ->schema([
                                Forms\Components\TextInput::make('stats.corners.home')->label('Corners (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.corners.away')->label('Corners (away)')->numeric(),
                                Forms\Components\TextInput::make('stats.yellow_cards.home')->label('Yellow (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.yellow_cards.away')->label('Yellow (away)')->numeric(),
                                Forms\Components\TextInput::make('stats.red_cards.home')->label('Red (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.red_cards.away')->label('Red (away)')->numeric(),
                            ])->columns(3),
                    ]),

                Forms\Components\Toggle::make('is_published')
                    ->label('Published (visible on live site)')
                    ->helperText('Off by default. Review the result, then switch this on to make it public.')
                    ->default(false),

                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kickoff_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('league.name')
                    ->label('League')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('homeTeam.name')
                    ->label('Home')
                    ->searchable(),
                Tables\Columns\TextColumn::make('home_score')
                    ->label('')
                    ->formatStateUsing(fn (MatchFixture $record) => "{$record->home_score} - {$record->away_score}"),
                Tables\Columns\TextColumn::make('awayTeam.name')
                    ->label('Away')
                    ->searchable(),
                Tables\Columns\TextColumn::make('matchday')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kickoff_at')
                    ->label('Played')
                    ->dateTime('D j M Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Live')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('league_id')
                    ->relationship('league', 'name')
                    ->label('League'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->actions([
                Tables\Actions\Action::make('togglePublish')
                    ->label(fn (MatchFixture $record) => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (MatchFixture $record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (MatchFixture $record) => $record->is_published ? 'gray' : 'success')
                    ->action(fn (MatchFixture $record) => $record->update(['is_published' => ! $record->is_published])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => MatchFixture::whereIn('id', $records->pluck('id'))->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->action(fn ($records) => MatchFixture::whereIn('id', $records->pluck('id'))->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResults::route('/'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}
