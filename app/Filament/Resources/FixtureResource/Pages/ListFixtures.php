<?php

namespace App\Filament\Resources\FixtureResource\Pages;

use App\Filament\Resources\FixtureResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListFixtures extends ListRecords
{
    protected static string $resource = FixtureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
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
