<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id', 'name', 'full_name', 'slug', 'external_id', 'api_football_id', 'crest_code', 'color_hex',
        'stadium', 'stadium_capacity', 'manager', 'manager_facts', 'manager_bio', 'manager_photo_path',
        'founded_year', 'history_essay', 'honours_facts',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /** Resolves manager_photo_path to a real URL whether it's a static asset path or a Filament storage-disk upload. */
    public function getManagerPhotoUrlAttribute(): ?string
    {
        if (! $this->manager_photo_path) {
            return null;
        }

        if (Str::startsWith($this->manager_photo_path, ['http://', 'https://', '/'])) {
            return $this->manager_photo_path;
        }

        if (Storage::disk('public')->exists($this->manager_photo_path)) {
            return Storage::disk('public')->url($this->manager_photo_path);
        }

        return asset($this->manager_photo_path);
    }

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
