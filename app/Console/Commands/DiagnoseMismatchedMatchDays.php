<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;

/**
 * One-off diagnostic: the AI match-report prompt never included the real
 * kickoff date until now, so any published article that mentions a day of
 * the week ("...win over Villarreal on Sunday") was a pure guess - right
 * only by chance (~1 in 7). Cross-checks every weekday name actually
 * present in each article's body against the real match's actual kickoff
 * weekday (only possible for articles with match_id set). Read-only -
 * makes no changes.
 */
class DiagnoseMismatchedMatchDays extends Command
{
    protected $signature = 'diagnose:mismatched-match-days';

    protected $description = 'Find published articles whose body names the wrong day of the week for their match';

    private const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function handle(): int
    {
        $articles = NewsArticle::with('match')
            ->where('source', 'ai')
            ->where('status', 'published')
            ->whereNotNull('match_id')
            ->whereNotNull('body')
            ->get();

        $checked = 0;
        $mismatches = 0;

        foreach ($articles as $article) {
            if (! $article->match || ! $article->match->kickoff_at) {
                continue;
            }

            $checked++;
            $realDay = $article->match->kickoff_at->format('l');

            foreach (self::WEEKDAYS as $mentionedDay) {
                if ($mentionedDay === $realDay) {
                    continue;
                }

                if (preg_match('/\b'.$mentionedDay.'\b/', $article->body)) {
                    $mismatches++;
                    $this->line("Article {$article->id}: \"{$article->title}\"");
                    $this->line("  body says {$mentionedDay}, real match was played {$realDay} ({$article->match->kickoff_at->format('j F Y')})");
                    break;
                }
            }
        }

        $this->info("Checked {$checked} article(s) with a resolvable match, found {$mismatches} with a wrong day of the week.");

        return self::SUCCESS;
    }
}
