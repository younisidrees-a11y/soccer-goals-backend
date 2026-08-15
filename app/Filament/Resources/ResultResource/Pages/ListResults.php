<?php

namespace App\Filament\Resources\ResultResource\Pages;

use App\Filament\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListResults extends ListRecords
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn ($query) => $query->where('is_published', true)),
            'unpublished' => Tab::make('Unpublished')
                ->modifyQueryUsing(fn ($query) => $query->where('is_published', false)),
        ];
    }
}
