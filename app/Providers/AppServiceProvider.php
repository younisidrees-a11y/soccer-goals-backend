<?php

namespace App\Providers;

use App\Models\MatchFixture;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.site', function ($view) {
            $view->with('hasLiveMatch', MatchFixture::where('status', 'live')->where('is_published', true)->exists());
            $view->with('siteSettings', SiteSetting::current());
            $view->with('currentMatchday', MatchFixture::published()->where('status', 'final')->max('matchday') ?? 1);
        });
    }
}
