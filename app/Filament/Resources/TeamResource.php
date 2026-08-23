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
                    ->label('Head coach name')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('manager_photo_path')
                    ->label('Head coach photo (front-facing)')
                    ->image()
                    ->disk('public')
                    ->directory('manager-photos')
                    ->visibility('public')
                    ->imageEditor()
                    ->helperText('Use a real, correctly-identified photo of this specific person - a source like Wikipedia/Wikimedia Commons is a safe bet since those photos are usually free-licensed and tied to the right individual.'),
                Forms\Components\Textarea::make('manager_facts')
                    ->label('Head coach facts (real, verified facts only)')
                    ->helperText('Short verified facts only - e.g. nationality, when appointed, one or two notable achievements. Leave blank for a minimal, safe bio with just the name and club. This is what the AI writer below is allowed to state as fact about a real, currently-employed person.')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('manager_bio')
                    ->label('Head coach bio (written from the facts above)')
                    ->helperText('Run "php artisan teams:write-history {slug}" to generate this from the name and facts above - or write it by hand.')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('founded_year')
                    ->numeric(),
                Forms\Components\Textarea::make('honours_facts')
                    ->label('Trophies & honours (real, verified facts only)')
                    ->helperText('Plain factual lines, one per competition - e.g. "Premier League: 6 (2011-12, 2013-14, 2017-18, 2018-19, 2020-21, 2021-22)" and "FA Cup: 7". Verify against a reliable source before saving - this is what the AI writer below is allowed to state as fact, so an error here becomes a false claim on the live site.')
                    ->rows(6)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('history_essay')
                    ->label('Club history (written from the facts above)')
                    ->helperText('Fill in Founded and Trophies & honours above, then run "php artisan teams:write-history {slug}" to generate this automatically - or write it by hand.')
                    ->rows(6)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->label('Published (visible on live site)')
                    ->helperText('Off by default. Review the team, then switch this on to make it public.')
                    ->default(false),

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
                Tables\Filters\SelectFilter::make('league_id')
                    ->relationship('league', 'name')
                    ->label('League'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
            ])
            ->actions([
                Tables\Actions\Action::make('togglePublish')
                    ->label(fn (Team $record) => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (Team $record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Team $record) => $record->is_published ? 'gray' : 'success')
                    ->action(fn (Team $record) => $record->update(['is_published' => ! $record->is_published])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => Team::whereIn('id', $records->pluck('id'))->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->action(fn ($records) => Team::whereIn('id', $records->pluck('id'))->update(['is_published' => false]))
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
