<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class League extends Model
{
    use HasFactory;

    /** Leagues pinned to the front of a "pick a league" listing page, in this exact order. */
    private const PINNED_FIRST = ['premier-league', 'la-liga', 'saudi-pro-league'];

    /** Leagues pushed to the back, after everything else. */
    private const PINNED_LAST = ['bundesliga'];

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

    /**
     * Reorders an already-fetched leagues collection for a "pick a
     * league" listing page (fixtures, results, points tables): the
     * PINNED_FIRST leagues lead in that exact order, PINNED_LAST leagues
     * trail after everything else, and every other league keeps whatever
     * order it arrived in (stable sort, PHP 8+) - one shared definition
     * so every listing page agrees on this order.
     *
     * @param  Collection<int, League>  $leagues
     * @return Collection<int, League>
     */
    public static function sortForPicker(Collection $leagues): Collection
    {
        return $leagues->sortBy(function (League $league) {
            $firstPos = array_search($league->slug, self::PINNED_FIRST, true);

            if ($firstPos !== false) {
                return $firstPos;
            }

            if (in_array($league->slug, self::PINNED_LAST, true)) {
                return 100;
            }

            return 50;
        })->values();
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
