<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Team;
use App\Services\AiFactChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * One-off, read-only, site-wide sweep of every AI-written text field -
 * match reports, halftime reports, pre-match previews, live commentary,
 * news articles, club history/manager bios - checking each against real
 * data already on the site rather than reading it and trusting it's fine.
 * Makes no changes; use content:fix-audit-issues (which reuses these same
 * checks) to act on what this finds.
 */
class AuditContent extends Command
{
    protected $signature = 'content:audit {--limit=8 : Example rows to print per issue category}';

    protected $description = 'Scan every AI-written field site-wide for factual mismatches and leftover AI-tone issues';

    private const BANNED_WORDS = [
        'moreover', 'furthermore', 'delve', 'tapestry', 'boasts', 'showcases', 'underscores',
        'testament to', 'realm', 'seamless', 'ever-evolving', 'landscape', 'game-changer',
        "in today's world", "it's worth noting", 'at the end of the day', 'in conclusion', 'overall,',
    ];

    private int $exampleLimit;

    public function handle(): int
    {
        $this->exampleLimit = (int) $this->option('limit');

        $this->auditMatchReports();
        $this->auditHalftimeReports();
        $this->auditPreviews();
        $this->auditCommentary();
        $this->auditNewsArticles();
        $this->auditClubContent();

        return self::SUCCESS;
    }

    private function auditMatchReports(): void
    {
        $this->line('');
        $this->info('=== Match reports (matches.match_report) ===');

        $checked = 0;
        $missing = 0;
        $wrongScore = 0;
        $wrongDay = 0;
        $bannedWord = 0;
        $matchdayWord = 0;
        $tooShort = 0;
        $examples = [];

        MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'final')
            ->where('is_published', true)
            ->chunkById(200, function ($matches) use (&$checked, &$missing, &$wrongScore, &$wrongDay, &$bannedWord, &$matchdayWord, &$tooShort, &$examples) {
                foreach ($matches as $match) {
                    $checked++;

                    if (blank($match->match_report)) {
                        $missing++;

                        continue;
                    }

                    $report = $match->match_report;
                    $label = "#{$match->id} {$match->homeTeam->name} {$match->home_score}-{$match->away_score} {$match->awayTeam->name} ({$match->league->name}, {$match->kickoff_at->format('j M Y')})";

                    if (! AiFactChecker::containsScore($report, $match->home_score, $match->away_score)) {
                        $wrongScore++;
                        $this->addExample($examples, 'wrong_score', $label);
                    }

                    if ($this->hasWrongDay($report, $match->kickoff_at)) {
                        $wrongDay++;
                        $this->addExample($examples, 'wrong_day', $label.' - report says '.$this->foundWrongDay($report, $match->kickoff_at).', real day is '.$match->kickoff_at->format('l'));
                    }

                    if ($word = $this->findBannedWord($report)) {
                        $bannedWord++;
                        $this->addExample($examples, 'banned_word', $label." - contains \"{$word}\"");
                    }

                    if (Str::contains(Str::lower($report), 'matchday')) {
                        $matchdayWord++;
                        $this->addExample($examples, 'matchday_word', $label);
                    }

                    if (str_word_count($report) < 40) {
                        $tooShort++;
                        $this->addExample($examples, 'too_short', $label.' - only '.str_word_count($report).' words');
                    }
                }
            });

        $this->reportCounts([
            'Checked' => $checked,
            'Missing a report entirely' => $missing,
            "Doesn't state the real score" => $wrongScore,
            'Names the wrong day of the week' => $wrongDay,
            'Contains a banned AI-tone word' => $bannedWord,
            'Says "matchday"' => $matchdayWord,
            'Suspiciously short (<40 words)' => $tooShort,
        ], $examples);
    }

    private function auditHalftimeReports(): void
    {
        $this->line('');
        $this->info('=== Halftime reports (matches.halftime_report) ===');

        $checked = 0;
        $bannedWord = 0;
        $matchdayWord = 0;
        $examples = [];

        MatchFixture::with(['homeTeam', 'awayTeam'])
            ->whereNotNull('halftime_report')
            ->chunkById(200, function ($matches) use (&$checked, &$bannedWord, &$matchdayWord, &$examples) {
                foreach ($matches as $match) {
                    $checked++;
                    $label = "#{$match->id} {$match->homeTeam->name} vs {$match->awayTeam->name}";

                    if ($word = $this->findBannedWord($match->halftime_report)) {
                        $bannedWord++;
                        $this->addExample($examples, 'banned_word', $label." - contains \"{$word}\"");
                    }

                    if (Str::contains(Str::lower($match->halftime_report), 'matchday')) {
                        $matchdayWord++;
                        $this->addExample($examples, 'matchday_word', $label);
                    }
                }
            });

        $this->reportCounts([
            'Checked' => $checked,
            'Contains a banned AI-tone word' => $bannedWord,
            'Says "matchday"' => $matchdayWord,
        ], $examples);
    }

    private function auditPreviews(): void
    {
        $this->line('');
        $this->info('=== Pre-match previews (home_preview_note / away_preview_note) ===');

        $checked = 0;
        $wrongDay = 0;
        $bannedWord = 0;
        $matchdayWord = 0;
        $examples = [];

        MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where(fn ($q) => $q->whereNotNull('home_preview_note')->orWhereNotNull('away_preview_note'))
            ->chunkById(200, function ($matches) use (&$checked, &$wrongDay, &$bannedWord, &$matchdayWord, &$examples) {
                foreach ($matches as $match) {
                    $checked++;
                    $label = "#{$match->id} {$match->homeTeam->name} vs {$match->awayTeam->name} ({$match->kickoff_at->format('j M Y')})";
                    $combined = trim(($match->home_preview_note ?? '').' '.($match->away_preview_note ?? ''));

                    if ($combined === '') {
                        continue;
                    }

                    if ($this->hasWrongDay($combined, $match->kickoff_at)) {
                        $wrongDay++;
                        $this->addExample($examples, 'wrong_day', $label.' - says '.$this->foundWrongDay($combined, $match->kickoff_at).', real day is '.$match->kickoff_at->format('l'));
                    }

                    if ($word = $this->findBannedWord($combined)) {
                        $bannedWord++;
                        $this->addExample($examples, 'banned_word', $label." - contains \"{$word}\"");
                    }

                    if (Str::contains(Str::lower($combined), 'matchday')) {
                        $matchdayWord++;
                        $this->addExample($examples, 'matchday_word', $label);
                    }
                }
            });

        $this->reportCounts([
            'Checked' => $checked,
            'Names the wrong day of the week' => $wrongDay,
            'Contains a banned AI-tone word' => $bannedWord,
            'Says "matchday"' => $matchdayWord,
        ], $examples);
    }

    private function auditCommentary(): void
    {
        $this->line('');
        $this->info('=== Live commentary (matches.commentary) ===');

        $matchesChecked = 0;
        $linesChecked = 0;
        $bannedWord = 0;
        $matchdayWord = 0;
        $unverifiedEvent = 0;
        $examples = [];

        MatchFixture::with(['homeTeam', 'awayTeam'])
            ->whereNotNull('commentary')
            ->chunkById(100, function ($matches) use (&$matchesChecked, &$linesChecked, &$bannedWord, &$matchdayWord, &$unverifiedEvent, &$examples) {
                foreach ($matches as $match) {
                    $lines = $match->commentary ?? [];

                    if (empty($lines)) {
                        continue;
                    }

                    $matchesChecked++;
                    $realEventMinutes = collect($match->events ?? [])->pluck('minute')->map(fn ($m) => (int) $m)->all();
                    $label = "#{$match->id} {$match->homeTeam->name} vs {$match->awayTeam->name}";

                    foreach ($lines as $line) {
                        $text = is_array($line) ? ($line['text'] ?? '') : (string) $line;
                        $minute = is_array($line) ? (int) ($line['minute'] ?? -1) : -1;

                        if ($text === '') {
                            continue;
                        }

                        $linesChecked++;

                        if ($word = $this->findBannedWord($text)) {
                            $bannedWord++;
                            $this->addExample($examples, 'banned_word', "{$label} @ {$minute}' - contains \"{$word}\"");
                        }

                        if (Str::contains(Str::lower($text), 'matchday')) {
                            $matchdayWord++;
                            $this->addExample($examples, 'matchday_word', "{$label} @ {$minute}'");
                        }

                        // No real event within a minute of this line, but it
                        // uses goal/card/sub language - the same check
                        // AiLiveCommentaryWriter now runs before saving,
                        // applied retroactively to lines written before
                        // that check existed.
                        $hasNearbyRealEvent = collect($realEventMinutes)->contains(fn ($m) => abs($m - $minute) <= 1);

                        if (! $hasNearbyRealEvent && AiFactChecker::containsUnverifiedEventClaim($text)) {
                            $unverifiedEvent++;
                            $this->addExample($examples, 'unverified_event', "{$label} @ {$minute}' - \"{$text}\"");
                        }
                    }
                }
            });

        $this->reportCounts([
            'Matches with commentary' => $matchesChecked,
            'Lines checked' => $linesChecked,
            'Lines with a banned AI-tone word' => $bannedWord,
            'Lines saying "matchday"' => $matchdayWord,
            'Lines claiming an event with no nearby real event' => $unverifiedEvent,
        ], $examples);
    }

    private function auditNewsArticles(): void
    {
        $this->line('');
        $this->info('=== News articles (source=ai, published) ===');

        $checked = 0;
        $wrongScore = 0;
        $wrongDay = 0;
        $bannedWord = 0;
        $matchdayWord = 0;
        $tooShort = 0;
        $unresolvedMatchReport = 0;
        $examples = [];

        NewsArticle::with('match')
            ->where('source', 'ai')
            ->where('status', 'published')
            ->whereNotNull('body')
            ->chunkById(200, function ($articles) use (&$checked, &$wrongScore, &$wrongDay, &$bannedWord, &$matchdayWord, &$tooShort, &$unresolvedMatchReport, &$examples) {
                foreach ($articles as $article) {
                    $checked++;
                    $label = "#{$article->id} \"{$article->title}\"";
                    $combined = $article->title.' '.$article->dek.' '.$article->body;

                    if ($word = $this->findBannedWord($combined)) {
                        $bannedWord++;
                        $this->addExample($examples, 'banned_word', $label." - contains \"{$word}\"");
                    }

                    if (Str::contains(Str::lower($combined), 'matchday')) {
                        $matchdayWord++;
                        $this->addExample($examples, 'matchday_word', $label);
                    }

                    if (str_word_count($article->body) < 100) {
                        $tooShort++;
                        $this->addExample($examples, 'too_short', $label.' - only '.str_word_count($article->body).' words');
                    }

                    if ($article->category !== 'match-report') {
                        continue;
                    }

                    if (! $article->match) {
                        $unresolvedMatchReport++;
                        $this->addExample($examples, 'unresolved_match', $label.' - match-report category but match_id does not resolve to a real match, cannot verify score/day at all');

                        continue;
                    }

                    if (! AiFactChecker::containsScore($article->body, $article->match->home_score, $article->match->away_score)) {
                        $wrongScore++;
                        $this->addExample($examples, 'wrong_score', $label." - real score is {$article->match->home_score}-{$article->match->away_score}");
                    }

                    if ($this->hasWrongDay($combined, $article->match->kickoff_at)) {
                        $wrongDay++;
                        $this->addExample($examples, 'wrong_day', $label.' - says '.$this->foundWrongDay($combined, $article->match->kickoff_at).', real day is '.$article->match->kickoff_at->format('l'));
                    }
                }
            });

        $this->reportCounts([
            'Checked' => $checked,
            'Match-report articles with no resolvable real match' => $unresolvedMatchReport,
            "Match-report articles that don't state the real score" => $wrongScore,
            'Names the wrong day of the week' => $wrongDay,
            'Contains a banned AI-tone word' => $bannedWord,
            'Says "matchday"' => $matchdayWord,
            'Suspiciously short (<100 words)' => $tooShort,
        ], $examples);
    }

    private function auditClubContent(): void
    {
        $this->line('');
        $this->info('=== Club history & manager bios (teams.history_essay / manager_bio) ===');

        $checked = 0;
        $bannedWord = 0;
        $matchdayWord = 0;
        $examples = [];

        Team::where(fn ($q) => $q->whereNotNull('history_essay')->orWhereNotNull('manager_bio'))
            ->chunkById(200, function ($teams) use (&$checked, &$bannedWord, &$matchdayWord, &$examples) {
                foreach ($teams as $team) {
                    $checked++;
                    $combined = trim(($team->history_essay ?? '').' '.($team->manager_bio ?? ''));

                    if ($combined === '') {
                        continue;
                    }

                    if ($word = $this->findBannedWord($combined)) {
                        $bannedWord++;
                        $this->addExample($examples, 'banned_word', "{$team->name} - contains \"{$word}\"");
                    }

                    if (Str::contains(Str::lower($combined), 'matchday')) {
                        $matchdayWord++;
                        $this->addExample($examples, 'matchday_word', $team->name);
                    }
                }
            });

        $this->reportCounts([
            'Checked' => $checked,
            'Contains a banned AI-tone word' => $bannedWord,
            'Says "matchday"' => $matchdayWord,
        ], $examples);
    }

    private function hasWrongDay(string $text, $realDate): bool
    {
        return $this->foundWrongDay($text, $realDate) !== null;
    }

    private function foundWrongDay(string $text, $realDate): ?string
    {
        $realDay = $realDate->format('l');

        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
            if ($day === $realDay) {
                continue;
            }

            if (preg_match('/\b'.$day.'\b/', $text)) {
                return $day;
            }
        }

        return null;
    }

    private function findBannedWord(string $text): ?string
    {
        $lower = Str::lower($text);

        foreach (self::BANNED_WORDS as $word) {
            if (str_contains($lower, $word)) {
                return $word;
            }
        }

        return null;
    }

    private function addExample(array &$examples, string $key, string $line): void
    {
        $examples[$key] ??= [];

        if (count($examples[$key]) < $this->exampleLimit) {
            $examples[$key][] = $line;
        }
    }

    private function reportCounts(array $counts, array $examples): void
    {
        foreach ($counts as $label => $count) {
            $this->line(sprintf('%-55s %d', $label, $count));
        }

        foreach ($examples as $key => $lines) {
            $this->line('  -- '.str_replace('_', ' ', $key).' examples --');

            foreach ($lines as $line) {
                $this->line('     '.$line);
            }
        }
    }
}
