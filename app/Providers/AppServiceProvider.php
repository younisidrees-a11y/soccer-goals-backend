<?php

namespace App\Providers;

use App\Models\MatchFixture;
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
        });
    }
}
