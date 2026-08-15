<?php

namespace App\Filament\Resources\LeagueTableResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StandingsRelationManager extends RelationManager
{
    protected static string $relationship = 'standings';

    protected static ?string $title = 'Standings';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('zone')
                    ->options([
                        'ucl' => 'Champions League zone',
                        'rel' => 'Relegation zone',
                        'none' => 'Mid-table',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('played')->label('P')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('won')->label('W')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('drawn')->label('D')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('lost')->label('L')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('goals_for')->label('GF')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('goals_against')->label('GA')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('goal_difference')->label('GD')->required()->numeric()->default(0),
                Forms\Components\TextInput::make('points')->label('Pts')->required()->numeric()->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('position')
            ->defaultSort('position')
            ->columns([
                Tables\Columns\TextColumn::make('position')->label('Pos')->sortable(),
                Tables\Columns\TextColumn::make('team.name')->label('Team')->searchable(),
                Tables\Columns\TextColumn::make('played')->label('P'),
                Tables\Columns\TextColumn::make('won')->label('W'),
                Tables\Columns\TextColumn::make('drawn')->label('D'),
                Tables\Columns\TextColumn::make('lost')->label('L'),
                Tables\Columns\TextColumn::make('goals_for')->label('GF'),
                Tables\Columns\TextColumn::make('goals_against')->label('GA'),
                Tables\Columns\TextColumn::make('goal_difference')->label('GD'),
                Tables\Columns\TextColumn::make('points')->label('Pts')->weight('bold'),
                Tables\Columns\TextColumn::make('zone')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'ucl' => 'success',
                        'rel' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
}
