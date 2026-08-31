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
//
// UPDATE: the 100-request/day quota this block was originally throttled
// for was wrong for this account - confirmed live against API-Football's
// own /status endpoint on 30 Aug 2026: this key is on the Pro plan,
// 7,500 requests/day (only 3 used that day, by mid-afternoon). The
// 100/day assumption is what caused real staleness - four MLS matches
// (Houston Dynamo vs San Jose Earthquakes and others, all kicked off at
// 00:30 UTC on 30 Aug) sat stuck on "scheduled" for 13+ hours because
// this sync hadn't run successfully since the quota was exhausted on 27
// Aug (Celta Vigo vs Osasuna commentary burning it before this even
// got a turn) and never recovered on its own.
//
// Restored to every 5 minutes - same cadence as the football-data.org
// leagues above, comfortably inside 7,500/day even at 1 call/run x 5
// leagues x 288 runs = ~1,440/day worst case. Not pushed further than
// that in one pass since the exact real cost per run (vs. this
// worst-case estimate) hasn't been observed yet - check
// api-football:status-check (or the /status endpoint directly) after a
// few days of real traffic before tightening this further.
Schedule::command('api-football:sync-fixtures saudi-pro-league')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures liga-mx')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures super-lig')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures mls')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-fixtures scottish-premiership')->everyFiveMinutes()->withoutOverlapping();

// Real statistics, event timelines, lineups, and a ratings-based Man of
// the Match from API-Football - football-data.org's tier has none of
// this. Runs a few minutes after the sync above so a match is already
// marked final (and its teams already resolved) before this looks for
// it; only touches matches that don't have real stats yet, so most runs
// make zero real API calls once a league is caught up.
//
// UPDATE 31 Aug 2026: every-10-minutes here (plus previews below at the
// same cadence, plus 3-league live commentary) turned out to genuinely
// exhaust the real 7,500/day quota by 23:14 UTC that day (confirmed
// live: 7,470/7,500 used) - the earlier "~1,872/day worst case per
// command" estimate this comment used to cite was wrong in practice,
// not just theory. That's what left Barcelona vs Rayo Vallecano (and
// Celta Vigo, Famalicão, two Süper Lig matches) enrichment-less for
// real - api-football:status-check and matches:check-stale both
// correctly caught and logged it before anyone noticed on the live
// site, which is exactly what they're for. Dialed back to every 20
// minutes (still 33% faster than the original every-30) to leave real
// daily headroom instead of running the quota to the edge every day.
Schedule::command('api-football:sync-stats premier-league')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats la-liga')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats serie-a')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats ligue-1')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats bundesliga')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats saudi-pro-league')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats liga-mx')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats super-lig')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats mls')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats championship')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats scottish-premiership')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats eredivisie')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-stats primeira-liga')->cron('*/20 * * * *')->withoutOverlapping();

// Referee, prediction, coach and (once confirmed, usually ~1h before
// kick-off) lineups for fixtures in the next 7 days - powers the match
// preview page. Only fetches lineups/predictions once per match (checks
// the column is still null first), so most runs are free once a league
// is caught up. Dialed back to every 20 minutes alongside sync-stats
// above for the same real reason (see that comment) - a lineup
// confirmed a few minutes later is a non-issue for a preview page read
// hours ahead of kick-off anyway.
Schedule::command('api-football:sync-previews premier-league')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews la-liga')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews serie-a')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews ligue-1')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews bundesliga')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews saudi-pro-league')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews liga-mx')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews super-lig')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews mls')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews championship')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews scottish-premiership')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews eredivisie')->cron('*/20 * * * *')->withoutOverlapping();
Schedule::command('api-football:sync-previews primeira-liga')->cron('*/20 * * * *')->withoutOverlapping();

// Real early warning against a repeat of the incident documented above
// (four MLS matches stuck on "scheduled" for 13+ hours because the
// quota was exhausted and nothing surfaced that until a human noticed
// stale data on the live site) - logs a warning the moment usage
// crosses 85% of the daily limit, hours before anything actually stops
// working. Checking every 2 hours is enough resolution to catch a
// climbing trend without spending meaningful quota on the check itself
// (this endpoint doesn't count against the daily limit).
Schedule::command('api-football:status-check')->everyTwoHours()->withoutOverlapping()->appendOutputTo(storage_path('logs/api-football-status.log'));

// Catches the symptom directly, independent of the cause - any
// published match still "scheduled" 3+ hours past its real kickoff,
// whatever the reason (quota, an API-Football outage, a sync bug).
Schedule::command('matches:check-stale')->hourly()->withoutOverlapping()->appendOutputTo(storage_path('logs/api-football-status.log'));

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
// Added MLS to the pilot - the 2-league limit was originally chosen to
// conserve API quota under the wrong 100/day assumption (confirmed:
// this account is on 7,500/day). MLS can have several matches live at
// once (a bigger league than La Liga/Saudi Pro League), so real usage
// here is worth watching via api-football:status-check, but there's
// genuine headroom for it.
Schedule::command('api-football:sync-commentary mls')->everyMinute()->withoutOverlapping()->appendOutputTo(storage_path('logs/commentary.log'));

// Each of these already skips itself when there's nothing new to cover
// (same match/team not re-covered), so running on a schedule never spams
// the review queue - it just writes fresh content when there's genuinely
// something new to write about.
Schedule::command('news:generate match-report')->everyTwoHours()->withoutOverlapping();
Schedule::command('news:generate club-news')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('news:generate transfers')->dailyAt('14:00')->withoutOverlapping();
