<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'external_code', 'api_football_id', 'country', 'flag_code', 'season', 'total_matchdays',
        'about_text', 'table_intro', 'table_closing',
        'meta_title', 'meta_description', 'meta_keywords',
        'is_published', 'live_commentary_enabled',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'live_commentary_enabled' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchFixture::class);
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class)->orderBy('position');
    }

    public function newsArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }
}
