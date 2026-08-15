<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Filament\Support\SeoFields;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('league_id')
                    ->relationship('league', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('crest_code')
                    ->required()
                    ->maxLength(10),
                Forms\Components\ColorPicker::make('color_hex')
                    ->required()
                    ->default('#1552C0'),
                Forms\Components\TextInput::make('stadium')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stadium_capacity')
                    ->maxLength(20),
                Forms\Components\TextInput::make('manager')
                    ->maxLength(255),
                Forms\Components\TextInput::make('founded_year')
                    ->numeric(),
                Forms\Components\Textarea::make('history_essay')
                    ->columnSpanFull(),

                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('league.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('crest_code')
                    ->searchable(),
                Tables\Columns\ColorColumn::make('color_hex')
                    ->label('Color'),
                Tables\Columns\TextColumn::make('stadium')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stadium_capacity')
                    ->searchable(),
                Tables\Columns\TextColumn::make('manager')
                    ->searchable(),
                Tables\Columns\TextColumn::make('founded_year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('league_id')
                    ->relationship('league', 'name')
                    ->label('League'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
