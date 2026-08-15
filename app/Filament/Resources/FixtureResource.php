<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FixtureResource\Pages;
use App\Filament\Support\SeoFields;
use App\Models\MatchFixture;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FixtureResource extends Resource
{
    protected static ?string $model = MatchFixture::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Fixtures';

    protected static ?string $modelLabel = 'fixture';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', '!=', 'final');
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
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('matchday')
                            ->required()
                            ->numeric(),
                        Forms\Components\Select::make('home_team_id')
                            ->label('Home team')
                            ->relationship('homeTeam', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('away_team_id')
                            ->label('Away team')
                            ->relationship('awayTeam', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('kickoff_at')
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('venue')
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Pre-match preview')
                    ->description('A short paragraph of context for each side, shown on the fixture before kick-off.')
                    ->schema([
                        Forms\Components\Textarea::make('home_preview_note')
                            ->label('Home team history / context')
                            ->rows(3),
                        Forms\Components\Textarea::make('away_preview_note')
                            ->label('Away team history / context')
                            ->rows(3),
                    ]),

                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kickoff_at')
            ->columns([
                Tables\Columns\TextColumn::make('league.name')
                    ->label('League')
                    ->sortable(),
                Tables\Columns\TextColumn::make('homeTeam.name')
                    ->label('Home'),
                Tables\Columns\TextColumn::make('awayTeam.name')
                    ->label('Away'),
                Tables\Columns\TextColumn::make('matchday')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kickoff_at')
                    ->label('Kick-off')
                    ->dateTime('D j M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('venue')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'live' ? 'warning' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('league_id')
                    ->relationship('league', 'name')
                    ->label('League'),
                Tables\Filters\SelectFilter::make('matchday')
                    ->options(fn () => \App\Models\MatchFixture::query()
                        ->where('status', '!=', 'final')
                        ->distinct()
                        ->orderBy('matchday')
                        ->pluck('matchday', 'matchday')
                        ->toArray()),
            ])
            ->actions([
                Tables\Actions\Action::make('reportResult')
                    ->label('Report result')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('home_score')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('away_score')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\Textarea::make('match_report')
                            ->label('Match story')
                            ->rows(4)
                            ->required(),
                        Forms\Components\Fieldset::make('Stats')
                            ->schema([
                                Forms\Components\TextInput::make('stats.possession.home')->label('Possession % (home)')->numeric()->suffix('%'),
                                Forms\Components\TextInput::make('stats.possession.away')->label('Possession % (away)')->numeric()->suffix('%'),
                                Forms\Components\TextInput::make('stats.shots.home')->label('Shots (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.shots.away')->label('Shots (away)')->numeric(),
                                Forms\Components\TextInput::make('stats.shots_on_target.home')->label('On target (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.shots_on_target.away')->label('On target (away)')->numeric(),
                                Forms\Components\TextInput::make('stats.corners.home')->label('Corners (home)')->numeric(),
                                Forms\Components\TextInput::make('stats.corners.away')->label('Corners (away)')->numeric(),
                            ])->columns(2),
                    ])
                    ->action(function (MatchFixture $record, array $data) {
                        $record->update([
                            'status' => 'final',
                            'home_score' => $data['home_score'],
                            'away_score' => $data['away_score'],
                            'match_report' => $data['match_report'],
                            'stats' => $data['stats'] ?? null,
                        ]);
                        Notification::make()
                            ->title('Result recorded')
                            ->body('This fixture has moved into Results.')
                            ->success()
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
            'index' => Pages\ListFixtures::route('/'),
            'create' => Pages\CreateFixture::route('/create'),
            'edit' => Pages\EditFixture::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}
