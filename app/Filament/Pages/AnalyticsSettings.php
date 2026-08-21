<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Lets an admin paste analytics/tracking scripts (Google Analytics, Google
 * Tag Manager, Meta Pixel, etc.) once here, instead of needing a code
 * change and deploy every time a tracking snippet changes. The scripts are
 * injected into every public page - see SiteSetting::current() and
 * layouts/site.blade.php.
 */
class AnalyticsSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Analytics & Tracking';

    protected static ?string $navigationGroup = 'Settings';

    protected static string $view = 'filament.pages.analytics-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->only(['analytics_head_code', 'analytics_body_code']));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Analytics & tracking codes')
                    ->description('Paste tracking scripts here - Google Analytics, Google Tag Manager, Meta Pixel, and similar. They go live on every page of the site immediately after saving, no deploy needed. Only paste scripts from services you trust, since this runs on every visitor\'s browser.')
                    ->schema([
                        Forms\Components\Textarea::make('analytics_head_code')
                            ->label('Head scripts')
                            ->helperText('Placed just before </head> on every page. This is where most tracking snippets go (GA4 gtag.js, Meta Pixel base code, etc.) - paste the full <script> tag(s) exactly as the provider gives them to you.')
                            ->rows(10)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('analytics_body_code')
                            ->label('Body scripts (optional)')
                            ->helperText('Placed just after the opening <body> tag. Only needed for snippets that specifically require this, like the Google Tag Manager <noscript> fallback.')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $setting = SiteSetting::firstOrCreate(['id' => 1]);
        $setting->update($this->form->getState());

        Notification::make()
            ->title('Analytics settings saved')
            ->success()
            ->send();
    }
}
