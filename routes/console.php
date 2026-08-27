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
Schedule::command('football-data:sync bundesliga')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('football-data:sync championship')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('football-data:sync eredivisie')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('football-data:sync primeira-liga')->everyFiveMinutes()->withoutOverlapping();

// Saudi Pro League, Liga MX, Süper Lig and MLS aren't covered by
// football-data.org at all, so API-Football is the primary fixture
// source for these (not just enrichment).
Schedule::command('api-football:sync-fixtures saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures liga-mx')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures super-lig')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures mls')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures scottish-premiership')->everyFiveMinutes()->withoutOverlapping();

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
Schedule::command('api-football:sync-stats bundesliga')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats liga-mx')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats super-lig')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats mls')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats championship')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats scottish-premiership')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats eredivisie')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats primeira-liga')->everyFiveMinutes()->withoutOverlapping();

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
Schedule::command('api-football:sync-previews bundesliga')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews liga-mx')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews super-lig')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews mls')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews championship')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews scottish-premiership')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews eredivisie')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews primeira-liga')->everyFiveMinutes()->withoutOverlapping();

// Minute-by-minute AI live commentary - piloting on two leagues only
// (League::live_commentary_enabled). Runs every minute rather than every
// five like everything else above, since "live commentary" that's five
// minutes behind isn't live - but each run is a cheap no-op the instant
// there's no live match in the league, so this is safe to leave on
// even when nothing's being played.
// appendOutputTo: unlike every other scheduled command here, this one's
// own info/warn output (e.g. "Wrote 0 commentary lines" or "AI commentary
// generation failed") was going nowhere - the scheduler discards command
// output by default, so a silent failure every minute for an entire live
// match left zero trace anywhere. This is the only reliable signal for
// diagnosing why a match ended up with no commentary after the fact.
Schedule::command('api-football:sync-commentary la-liga')->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path('logs/commentary.log'));
Schedule::command('api-football:sync-commentary saudi-pro-league')->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path('logs/commentary.log'));

// Each of these already skips itself when there's nothing new to cover
// (same match/team not re-covered), so running on a schedule never spams
// the review queue - it just writes fresh content when there's genuinely
// something new to write about.
Schedule::command('news:generate match-report')->everyTwoHours()->withoutOverlapping();
Schedule::command('news:generate club-news')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('news:generate transfers')->dailyAt('14:00')->withoutOverlapping();
