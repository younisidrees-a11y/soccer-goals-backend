<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id', 'name', 'full_name', 'slug', 'crest_code', 'color_hex',
        'stadium', 'stadium_capacity', 'manager', 'founded_year', 'history_essay',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_published',
    ];

    protected $casts = [
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

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(MatchFixture::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(MatchFixture::class, 'away_team_id');
    }

    public function standing(): HasMany
    {
        return $this->hasMany(Standing::class);
    }

    public function topScorers()
    {
        return $this->players()->orderByDesc('goals')->limit(5)->get();
    }
}
