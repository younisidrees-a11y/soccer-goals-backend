<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Filament\Resources\PlayerResource\RelationManagers;
use App\Models\Player;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Players';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('position')
                    ->options([
                        'Goalkeeper' => 'Goalkeeper',
                        'Centre-Back' => 'Centre-Back',
                        'Left-Back' => 'Left-Back',
                        'Right-Back' => 'Right-Back',
                        'Midfielder' => 'Midfielder',
                        'Defensive Mid.' => 'Defensive Mid.',
                        'Attacking Mid.' => 'Attacking Mid.',
                        'Winger' => 'Winger',
                        'Forward' => 'Forward',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('shirt_number')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(99),
                Forms\Components\TextInput::make('nationality')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_captain')
                    ->inline(false),
                Forms\Components\TextInput::make('goals')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('assists')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shirt_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nationality')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_captain')
                    ->boolean(),
                Tables\Columns\TextColumn::make('goals')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assists')
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
                Tables\Filters\SelectFilter::make('team_id')
                    ->relationship('team', 'name')
                    ->label('Team')
                    ->searchable(),
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
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
        ];
    }
}
