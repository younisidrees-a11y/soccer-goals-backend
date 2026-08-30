<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Player extends Model
{
    use HasFactory;

    /**
     * Shared with TeamController and MatchController so a squad list
     * groups the same way everywhere it's shown on the site (team pages,
     * and pre-match fixture pages).
     */
    public const POSITION_GROUPS = [
        'Goalkeepers' => ['Goalkeeper'],
        'Defenders' => ['Centre-Back', 'Left-Back', 'Right-Back', 'Defender'],
        'Midfielders' => ['Midfielder', 'Attacking Mid.', 'Defensive Mid.'],
        'Forwards' => ['Forward', 'Winger'],
    ];

    protected $fillable = [
        'team_id', 'api_football_id', 'name', 'position', 'shirt_number', 'nationality',
        'birth_date', 'birth_place', 'birth_country', 'height', 'weight', 'injured', 'photo_url',
        'is_captain', 'goals', 'assists', 'stats', 'trophies', 'transfers',
        'meta_description',
    ];

    protected $casts = [
        'is_captain' => 'boolean',
        'injured' => 'boolean',
        'birth_date' => 'date',
        'stats' => 'array',
        'trophies' => 'array',
        'transfers' => 'array',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function seoSlug(): string
    {
        return Str::slug($this->name);
    }

    public function prettyUrl(): string
    {
        return route('players.show', ['player' => $this->id, 'slug' => $this->seoSlug()]);
    }

    /**
     * @param  Collection<int, Player>  $squad
     * @return array<string, Collection<int, Player>>
     */
    public static function groupByPosition(Collection $squad): array
    {
        $grouped = [];

        foreach (self::POSITION_GROUPS as $label => $positions) {
            $grouped[$label] = $squad->filter(fn (Player $p) => in_array($p->position, $positions));
        }

        return $grouped;
    }
}
