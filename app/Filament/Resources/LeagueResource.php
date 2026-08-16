<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeagueResource\Pages;
use App\Filament\Resources\LeagueResource\RelationManagers;
use App\Filament\Support\SeoFields;
use App\Models\League;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeagueResource extends Resource
{
    protected static ?string $model = League::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('country')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('flag_code')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('season')
                    ->required()
                    ->maxLength(20)
                    ->default('2026-27'),
                Forms\Components\TextInput::make('total_matchdays')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('about_text')
                    ->label('About this league')
                    ->helperText('Shown under "About the [League]" on the public league page. One paragraph per line.')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->label('Published (visible on live site)')
                    ->helperText('Off by default. Review the league, then switch this on to make it public.')
                    ->default(false),

                SeoFields::section(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flag_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('season')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_matchdays')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Live')
                    ->boolean()
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
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->actions([
                Tables\Actions\Action::make('togglePublish')
                    ->label(fn (League $record) => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (League $record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (League $record) => $record->is_published ? 'gray' : 'success')
                    ->action(fn (League $record) => $record->update(['is_published' => ! $record->is_published])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => League::whereIn('id', $records->pluck('id'))->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->action(fn ($records) => League::whereIn('id', $records->pluck('id'))->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListLeagues::route('/'),
            'create' => Pages\CreateLeague::route('/create'),
            'edit' => Pages\EditLeague::route('/{record}/edit'),
        ];
    }
}
