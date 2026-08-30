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
// Sped back up from every 30 minutes to every 10, for the same reason
// as the fixtures block above - the account is genuinely on 7,500/day,
// not the 100/day this was throttled for. Kept at 10 rather than
// matching fixtures' 5-minute cadence since this runs across all 13
// leagues x 2 commands (stats + previews below) - a real, if unlikely,
// worst case of 144 runs/day x 13 leagues = ~1,872/day per command
// stacks with everything else below it, so this stays a notch more
// conservative until real usage is confirmed.
Schedule::command('api-football:sync-stats premier-league')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats la-liga')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats serie-a')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats ligue-1')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats bundesliga')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats saudi-pro-league')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats liga-mx')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats super-lig')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats mls')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats championship')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats scottish-premiership')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats eredivisie')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-stats primeira-liga')->everyTenMinutes()->withoutOverlapping();

// Referee, prediction, coach and (once confirmed, usually ~1h before
// kick-off) lineups for fixtures in the next 7 days - powers the match
// preview page. Only fetches lineups/predictions once per match (checks
// the column is still null first), so most runs are free once a league
// is caught up. Same 7,500/day headroom and same conservative-for-now
// 10-minute cadence as sync-stats above - a lineup confirmed a few
// minutes late is a non-issue for a preview page read hours ahead of
// kick-off, so there's no real upside to matching fixtures' 5-minute
// pace here specifically.
Schedule::command('api-football:sync-previews premier-league')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews la-liga')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews serie-a')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews ligue-1')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews bundesliga')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews saudi-pro-league')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews liga-mx')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews super-lig')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews mls')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews championship')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews scottish-premiership')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews eredivisie')->everyTenMinutes()->withoutOverlapping();
Schedule::command('api-football:sync-previews primeira-liga')->everyTenMinutes()->withoutOverlapping();

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

// Each of these already skips itself when there's nothing new to cover
// (same match/team not re-covered), so running on a schedule never spams
// the review queue - it just writes fresh content when there's genuinely
// something new to write about.
Schedule::command('news:generate match-report')->everyTwoHours()->withoutOverlapping();
Schedule::command('news:generate club-news')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('news:generate transfers')->dailyAt('14:00')->withoutOverlapping();
