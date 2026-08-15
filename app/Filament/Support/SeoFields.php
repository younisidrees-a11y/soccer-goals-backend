<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class SeoFields
{
    /** Reusable "SEO" form section: title, description, keywords/tags. */
    public static function section(): Section
    {
        return Section::make('SEO')
            ->description('Overrides the auto-generated page title, description and tags used by search engines. Leave blank to use the site default.')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('meta_title')
                    ->label('SEO title')
                    ->maxLength(255)
                    ->helperText('Shown in the browser tab and search results. Falls back to the page\'s default title if left blank.'),
                Textarea::make('meta_description')
                    ->label('Meta description')
                    ->rows(2)
                    ->maxLength(500)
                    ->helperText('The summary shown under the title in search results. Aim for under 160 characters.'),
                TextInput::make('meta_keywords')
                    ->label('Tags / keywords')
                    ->maxLength(255)
                    ->helperText('Comma-separated, e.g. "premier league, liverpool, football news".'),
            ]);
    }
}
