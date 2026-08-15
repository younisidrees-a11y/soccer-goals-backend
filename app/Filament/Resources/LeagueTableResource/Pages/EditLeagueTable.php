<?php

namespace App\Filament\Resources\LeagueTableResource\Pages;

use App\Filament\Resources\LeagueTableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeagueTable extends EditRecord
{
    protected static string $resource = LeagueTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPublic')
                ->label('View public page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('leagues.show', $this->record->slug))
                ->openUrlInNewTab(),
        ];
    }
}
