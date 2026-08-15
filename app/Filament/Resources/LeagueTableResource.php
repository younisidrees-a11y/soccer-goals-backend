<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeagueTableResource\Pages;
use App\Filament\Resources\LeagueTableResource\RelationManagers;
use App\Models\League;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeagueTableResource extends Resource
{
    protected static ?string $model = League::class;

    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';

    protected static ?string $navigationLabel = 'Points Tables';

    protected static ?string $modelLabel = 'league table';

    protected static ?string $slug = 'points-tables';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make($form->getRecord()?->name ?? 'League')
                    ->description('Pick which league\'s points table you\'re managing. Choose the league, then write the content that surrounds its standings on the public site.')
                    ->schema([
                        Forms\Components\Select::make('league_select')
                            ->label('League')
                            ->options(fn () => League::query()->orderBy('name')->pluck('name', 'id'))
                            ->default(fn ($record) => $record?->id)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('To manage a different league\'s table, go back and pick it from the list.'),
                    ]),

                Forms\Components\Section::make('Before the Table')
                    ->description('Shown as an intro paragraph above the standings on the public points table page.')
                    ->schema([
                        Forms\Components\Textarea::make('table_intro')
                            ->label('')
                            ->rows(4),
                    ]),

                Forms\Components\Section::make('After the Table')
                    ->description('Shown as closing analysis below the standings on the public points table page.')
                    ->schema([
                        Forms\Components\Textarea::make('table_closing')
                            ->label('')
                            ->rows(6),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Select a League')
            ->description('Choose which league\'s points table you want to view or edit.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('League')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('country'),
                Tables\Columns\TextColumn::make('teams_count')
                    ->label('Teams')
                    ->counts('teams'),
                Tables\Columns\TextColumn::make('leader')
                    ->label('Current Leader')
                    ->state(function (League $record) {
                        $leader = $record->standings()->with('team')->orderBy('position')->first();

                        return $leader ? "{$leader->team->name} ({$leader->points} pts)" : '—';
                    }),
                Tables\Columns\IconColumn::make('has_content')
                    ->label('Content set')
                    ->boolean()
                    ->state(fn (League $record) => filled($record->table_intro) && filled($record->table_closing)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Manage Table'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StandingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeagueTables::route('/'),
            'edit' => Pages\EditLeagueTable::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
