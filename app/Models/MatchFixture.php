<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MatchFixture extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'league_id', 'external_id', 'api_football_fixture_id', 'home_team_id', 'away_team_id', 'matchday', 'kickoff_at',
        'venue', 'referee', 'home_preview_note', 'away_preview_note', 'preview_published_at',
        'status', 'home_score', 'away_score', 'home_score_ht', 'away_score_ht',
        'match_report', 'halftime_report', 'halftime_published_at', 'stats', 'events', 'lineups', 'motm', 'prediction', 'commentary',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_published',
    ];

    protected $casts = [
        'kickoff_at' => 'datetime',
        'preview_published_at' => 'datetime',
        'halftime_published_at' => 'datetime',
        'stats' => 'array',
        'events' => 'array',
        'lineups' => 'array',
        'motm' => 'array',
        'prediction' => 'array',
        'commentary' => 'array',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    /** A fixture is upcoming until it's been reported as final. */
    public function isFixture(): bool
    {
        return $this->status !== 'final';
    }

    public function scopeFixtures($query)
    {
        return $query->where('status', '!=', 'final');
    }

    public function scopeResults($query)
    {
        return $query->where('status', 'final');
    }

    /** The descriptive part of this match's pretty URL, e.g. "al-faisaly-vs-al-fateh-football-match". */
    public function seoSlug(): string
    {
        return Str::slug("{$this->homeTeam->name}-vs-{$this->awayTeam->name}-football-match");
    }

    /** The canonical human-readable URL for this match: /matches/{id}/{month}/{home}-vs-{away}-football-match. The bare /matches/{id} form still works (see routes/web.php) and 301s here. */
    public function prettyUrl(): string
    {
        return route('matches.show', [
            'match' => $this->id,
            'month' => $this->kickoff_at->format('m'),
            'slug' => $this->seoSlug(),
        ]);
    }

    /** Win/draw/loss from the home team's perspective, or null if not played. */
    public function homeResult(): ?string
    {
        if (! $this->isFinal() || $this->home_score === null) {
            return null;
        }

        return match (true) {
            $this->home_score > $this->away_score => 'win',
            $this->home_score < $this->away_score => 'loss',
            default => 'draw',
        };
    }
}
