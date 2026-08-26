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
Schedule::command('football-data:sync serie-a')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('football-data:sync ligue-1')->everyFiveMinutes()->withoutOverlapping();

// Saudi Pro League isn't covered by football-data.org at all, so
// API-Football is the primary fixture source here (not just enrichment).
Schedule::command('api-football:sync-fixtures saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();

// Real statistics, event timelines, lineups, and a ratings-based Man of
// the Match from API-Football - football-data.org's tier has none of
// this. Runs a few minutes after the sync above so a match is already
// marked final (and its teams already resolved) before this looks for
// it; only touches matches that don't have real stats yet, so it's cheap
// to run often.
Schedule::command('api-football:sync-stats premier-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats la-liga')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats serie-a')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats ligue-1')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();

// Referee, prediction, coach and (once confirmed, usually ~1h before
// kick-off) lineups for fixtures in the next 7 days - powers the match
// preview page. Only fetches lineups/predictions once per match (checks
// the column is still null first), so running every 5 minutes just
// means a confirmed lineup shows up quickly once it exists, not that
// we re-spend calls on matches already enriched.
Schedule::command('api-football:sync-previews premier-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews la-liga')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews serie-a')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews ligue-1')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();

// Each of these already skips itself when there's nothing new to cover
// (same match/team not re-covered), so running on a schedule never spams
// the review queue - it just writes fresh content when there's genuinely
// something new to write about.
Schedule::command('news:generate match-report')->everyTwoHours()->withoutOverlapping();
Schedule::command('news:generate club-news')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('news:generate transfers')->dailyAt('14:00')->withoutOverlapping();
