<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'country', 'flag_code', 'season', 'total_matchdays',
        'table_intro', 'table_closing',
        'meta_title', 'meta_description', 'meta_keywords',
    ];

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
