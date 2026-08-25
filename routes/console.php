<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Real fixtures/results/standings from football-data.org - replaced the
// old fictional match simulator (matches:progress) once the site switched
// to tracking real results for these two leagues. Deliberately NOT
// running matches:progress alongside this: if the real sync ever lagged,
// the old simulator could "helpfully" fabricate a fictional score for a
// match that's actually just waiting on real data.
Schedule::command('football-data:sync premier-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('football-data:sync la-liga')->everyFiveMinutes()->withoutOverlapping();

// Each of these already skips itself when there's nothing new to cover
// (same match/team not re-covered), so running on a schedule never spams
// the review queue - it just writes fresh content when there's genuinely
// something new to write about.
Schedule::command('news:generate match-report')->everyTwoHours()->withoutOverlapping();
Schedule::command('news:generate club-news')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('news:generate transfers')->dailyAt('14:00')->withoutOverlapping();
