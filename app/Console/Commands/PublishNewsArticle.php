<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Standing;
use App\Models\Team;
use App\Services\AiNewsWriter;
use App\Services\NewsGraphicGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Writes one genuinely unique, AI-generated news article via the Anthropic
 * API - grounded in real data already on the site (scores, standings,
 * fixtures), never invented facts - plus an original on-brand graphic, and
 * queues it as pending_review for an editor to approve in the admin panel.
 *
 * Same rules as league:publish-results: natural human-sounding writing, no
 * robotic AI phrasing, and a safe failure mode (nothing is created) if the
 * API call doesn't succeed.
 */
#[Signature('news:generate {category : club-news, transfers, or match-report}')]
#[Description('Write and queue a genuinely unique AI-written news article for review, with an on-brand graphic')]
class PublishNewsArticle extends Command
{
    private const CATEGORIES = ['club-news', 'transfers', 'match-report'];

    public function handle(): int
    {
        $category = $this->argument('category');

        if (! in_array($category, self::CATEGORIES, true)) {
            $this->error('Category must be one of: '.implode(', ', self::CATEGORIES));

            return self::FAILURE;
        }

        $context = match ($category) {
            'club-news' => $this->clubNewsContext(),
            'match-report' => $this->matchReportContext(),
            'transfers' => $this->transfersContext(),
        };

        if ($context === null) {
            $this->info("Nothing new to write about for {$category} right now.");

            return self::SUCCESS;
        }

        $written = app(AiNewsWriter::class)->write($context['prompt']);

        if (! $written) {
            $this->error('AI generation failed - check storage/logs/laravel.log. No article was created.');

            return self::FAILURE;
        }

        $slug = Str::slug($written['title']).'-'.Str::random(6);

        $imagePath = app(NewsGraphicGenerator::class)->generate(
            $slug,
            $context['image']['label'],
            $context['image']['line1'],
            $context['image']['line2'] ?? null,
            $context['image']['big'],
            $context['image']['footer'],
        );

        $article = NewsArticle::create([
            'title' => $written['title'],
            'slug' => $slug,
            'dek' => $written['dek'],
            'body' => $written['body'],
            'image_path' => $imagePath,
            'category' => $category,
            'league_id' => $context['league_id'] ?? null,
            'team_id' => $context['team_id'] ?? null,
            'match_id' => $context['match_id'] ?? null,
            'source' => 'ai',
            'status' => 'pending_review',
            'author' => 'Marcus Ferreira',
            'meta_title' => $written['meta_title'] ?? $written['title'],
            'meta_description' => $written['meta_description'] ?? $written['dek'],
            'meta_keywords' => $written['meta_keywords'] ?? '',
        ]);

        $this->info("Created \"{$article->title}\" ({$category}) - pending review in the admin panel.");

        return self::SUCCESS;
    }

    private function jsonInstructions(int $minParagraphs): string
    {
        return <<<TXT
        Do not name any manager, player, coach, owner, or other real person who was not explicitly given to you in the facts above - not even ones you're confident about from general knowledge. Names change (managers get sacked, players get transferred) and a name you "know" may already be wrong. Refer to teams and squads only ("the visitors", "United's defence"), never named individuals who weren't provided.

        The body is REQUIRED to contain at least {$minParagraphs} separate paragraphs, each a genuine paragraph of multiple sentences, separated by a blank line (two \\n characters). A response with fewer paragraphs than that is incorrect - check your own draft against this count before responding.

        Write like a real person on deadline, not like an AI trying to sound human. Concretely: vary sentence length a lot - mix short, blunt sentences with longer, winding ones, the way people actually talk, instead of settling into a smooth medium-length rhythm. Never use "not only X but also Y". Avoid neatly matched three-item lists. Do not use any of these words or phrases anywhere: moreover, furthermore, delve, tapestry, boasts, showcases, underscores, testament to, realm, seamless, ever-evolving, landscape, game-changer, in today's world, it's worth noting, at the end of the day, in conclusion, overall. If a sentence sounds like it belongs in a press release, rewrite it plainer.

        Respond with ONLY valid JSON (no markdown fences, no commentary before or after) in exactly this shape:
        {"title": "...", "dek": "...", "body": "paragraph one\\n\\nparagraph two\\n\\nparagraph three", "meta_title": "...", "meta_description": "...", "meta_keywords": "comma, separated, keywords"}
        The title should be attractive, simple, and sleek - not clickbait. The dek is a single-sentence subtitle.
        TXT;
    }

    private function matchReportContext(): ?array
    {
        $coveredMatchIds = NewsArticle::where('category', 'match-report')
            ->whereNotNull('match_id')
            ->pluck('match_id');

        $match = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'final')
            ->where('is_published', true)
            ->whereNotIn('id', $coveredMatchIds)
            ->orderByDesc('kickoff_at')
            ->first();

        if (! $match) {
            return null;
        }

        $homeStanding = Standing::where('league_id', $match->league_id)->where('team_id', $match->home_team_id)->first();
        $awayStanding = Standing::where('league_id', $match->league_id)->where('team_id', $match->away_team_id)->first();
        $stats = $match->stats ?? [];

        $home = $match->homeTeam->name;
        $away = $match->awayTeam->name;
        $possessionHome = $stats['possession']['home'] ?? 50;
        $possessionAway = $stats['possession']['away'] ?? 50;
        $shotsHome = $stats['shots']['home'] ?? '-';
        $shotsAway = $stats['shots']['away'] ?? '-';
        $homePosition = $homeStanding?->position ?? 'n/a';
        $homePoints = $homeStanding?->points ?? 0;
        $awayPosition = $awayStanding?->position ?? 'n/a';
        $awayPoints = $awayStanding?->points ?? 0;

        $prompt = <<<PROMPT
        You are a football news editor writing for a sports website. Write a genuinely original, natural, human-sounding news article about the following completed match. Avoid robotic AI phrasing (no "furthermore", "it is important to note", no bullet points), and avoid stock phrases that would feel repeated across many articles.

        Match: {$home} {$match->home_score}-{$match->away_score} {$away}
        Competition: {$match->league->name}
        Venue: {$match->venue}
        Possession: {$home} {$possessionHome}% - {$possessionAway}% {$away}
        Shots: {$home} {$shotsHome} - {$shotsAway} {$away}
        {$home} current league position: {$homePosition} ({$homePoints} points)
        {$away} current league position: {$awayPosition} ({$awayPoints} points)

        Write a 4-5 paragraph match report (roughly 350-450 words). Cover how the match unfolded, what the result means for both teams' league position, and a forward-looking closing line. Only use the facts given above - do not invent goal scorers, cards, or incidents not implied by the numbers.

        {$this->jsonInstructions(4)}
        PROMPT;

        return [
            'prompt' => $prompt,
            'league_id' => $match->league_id,
            'team_id' => $match->home_team_id,
            'match_id' => $match->id,
            'image' => [
                'label' => 'MATCH REPORT',
                'line1' => $home,
                'line2' => $away,
                'big' => "{$match->home_score}-{$match->away_score}",
                'footer' => Str::upper("The Soccer Goals · {$match->league->name} {$match->league->season}"),
            ],
        ];
    }

    private function clubNewsContext(): ?array
    {
        $recentlyCovered = NewsArticle::where('category', 'club-news')
            ->where('created_at', '>=', now()->subDays(3))
            ->pluck('team_id');

        $team = Team::published()
            ->whereNotIn('id', $recentlyCovered)
            ->where(function ($q) {
                $q->whereHas('homeMatches', fn ($qq) => $qq->where('status', 'final'))
                    ->orWhereHas('awayMatches', fn ($qq) => $qq->where('status', 'final'));
            })
            ->inRandomOrder()
            ->first();

        if (! $team) {
            return null;
        }

        $standing = Standing::where('league_id', $team->league_id)->where('team_id', $team->id)->first();

        $lastMatch = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'final')
            ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
            ->orderByDesc('kickoff_at')
            ->first();

        $nextMatch = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('status', '!=', 'final')
            ->where('is_published', true)
            ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
            ->orderBy('kickoff_at')
            ->first();

        $lastResultLine = $lastMatch
            ? "{$lastMatch->homeTeam->name} {$lastMatch->home_score}-{$lastMatch->away_score} {$lastMatch->awayTeam->name}"
            : 'no matches played yet';

        $nextFixtureLine = $nextMatch
            ? "{$nextMatch->homeTeam->name} vs {$nextMatch->awayTeam->name} on {$nextMatch->kickoff_at->format('j F Y')}"
            : 'fixture list not yet published';

        $prompt = <<<PROMPT
        You are a football news editor writing a club news update for a sports website. Write a genuinely original, natural, human-sounding piece, in plain simple English, avoiding robotic AI phrasing.

        Team: {$team->name} ({$team->full_name})
        Competition: {$team->league->name}
        Current league position: {$standing?->position} with {$standing?->points} points from {$standing?->played} games
        Most recent result: {$lastResultLine}
        Next fixture: {$nextFixtureLine}

        Write a 3-4 paragraph club news article (roughly 280-350 words) about how {$team->name}'s season is going right now - form, mood around the club, what's coming up. Only use the facts given above - do not invent transfers, injuries, or manager changes.

        {$this->jsonInstructions(3)}
        PROMPT;

        return [
            'prompt' => $prompt,
            'league_id' => $team->league_id,
            'team_id' => $team->id,
            'match_id' => $lastMatch?->id,
            'image' => [
                'label' => 'CLUB NEWS',
                'line1' => $team->name,
                'line2' => null,
                'big' => $standing ? "#{$standing->position}" : '',
                'footer' => Str::upper("The Soccer Goals · {$team->league->name}"),
            ],
        ];
    }

    private function transfersContext(): array
    {
        $angles = [
            'the emotional rollercoaster of transfer deadline day for fans, players, and club staff',
            'how a transfer rumour actually starts and spreads before anything is confirmed',
            'what happens inside a football club during the final week of a transfer window',
            'why most transfer business is quiet squad tweaks rather than blockbuster signings',
            'how modern transfer negotiations actually get done behind the scenes',
        ];

        $angle = $angles[array_rand($angles)];

        $prompt = <<<PROMPT
        You are a football news editor writing a general transfer-window feature for a sports website. Write a genuinely original, natural, human-sounding piece, in plain simple English, avoiding robotic AI phrasing.

        Angle for this piece: {$angle}

        Important: this is a general-interest feature about how transfer windows work, not a report of any specific real transfer. Do not name any specific real player transfer, deal, or fee - keep it general and observational, the way a football writer would explain the process to fans.

        Write a 3-4 paragraph article (roughly 280-350 words).

        {$this->jsonInstructions(3)}
        PROMPT;

        return [
            'prompt' => $prompt,
            'league_id' => null,
            'team_id' => null,
            'match_id' => null,
            'image' => [
                'label' => 'TRANSFER NEWS',
                'line1' => 'Transfer',
                'line2' => 'Window',
                'big' => '',
                'footer' => 'THE SOCCER GOALS · TRANSFER DESK',
            ],
        ];
    }
}
