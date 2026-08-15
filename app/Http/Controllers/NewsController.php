<?php

namespace App\Http\Controllers;

use App\Models\MatchFixture;
use App\Models\NewsArticle;

class NewsController extends Controller
{
    private const CATEGORIES = [
        'match-report' => 'Match Reports',
        'transfers' => 'Transfer News',
        'analysis' => 'Analysis & Tactics',
        'injury' => 'Injury Updates',
        'club-news' => 'Club News',
    ];

    public function index()
    {
        $counts = NewsArticle::published()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = collect(self::CATEGORIES)->map(fn ($label, $key) => [
            'slug' => $key,
            'label' => $label,
            'count' => $counts[$key] ?? 0,
        ])->values();

        $latest = NewsArticle::with(['league', 'team'])
            ->published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        return view('news.index', compact('categories', 'latest'));
    }

    public function category(string $category)
    {
        abort_unless(array_key_exists($category, self::CATEGORIES), 404);

        $label = self::CATEGORIES[$category];

        $articles = NewsArticle::with(['league', 'team'])
            ->published()
            ->where('category', $category)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('news.category', compact('category', 'label', 'articles'));
    }

    public function show(string $slug)
    {
        $article = NewsArticle::with(['league', 'team'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $categoryLabel = self::CATEGORIES[$article->category] ?? 'News';

        $related = NewsArticle::published()
            ->where('id', '!=', $article->id)
            ->where(function ($q) use ($article) {
                $q->where('category', $article->category)
                    ->orWhere('team_id', $article->team_id);
            })
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        $tickerMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(7)
            ->get();

        return view('news.show', compact('article', 'categoryLabel', 'related', 'tickerMatches'));
    }

    public static function categories(): array
    {
        return self::CATEGORIES;
    }
}
