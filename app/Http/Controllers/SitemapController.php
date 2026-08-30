<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

/**
 * Two sitemaps, kept deliberately separate because they serve different
 * purposes: the general sitemap tells Google what exists at all, while
 * the news sitemap follows Google News' own protocol - only articles
 * published in the last 48 hours, in the exact <news:news> shape Google
 * News requires. Mixing them (or letting old articles linger in the news
 * one) is a common reason sites get rejected from Google News.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'hourly', 'priority' => '1.0'],
            ['loc' => route('today.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => route('news.index'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => route('fixtures.index'), 'changefreq' => 'hourly', 'priority' => '0.8'],
            ['loc' => route('results.index'), 'changefreq' => 'hourly', 'priority' => '0.8'],
            ['loc' => route('tables.index'), 'changefreq' => 'hourly', 'priority' => '0.8'],
        ];

        foreach (NewsController::categories() as $slug => $label) {
            $urls[] = ['loc' => route('news.category', $slug), 'changefreq' => 'hourly', 'priority' => '0.7'];
        }

        foreach (League::published()->get() as $league) {
            $urls[] = ['loc' => route('leagues.show', $league->slug), 'changefreq' => 'daily', 'priority' => '0.8'];
            $urls[] = ['loc' => route('fixtures.show', $league->slug), 'changefreq' => 'hourly', 'priority' => '0.7'];
            $urls[] = ['loc' => route('results.show', $league->slug), 'changefreq' => 'hourly', 'priority' => '0.7'];
        }

        foreach (Team::published()->get() as $team) {
            $urls[] = ['loc' => route('teams.show', $team->slug), 'changefreq' => 'daily', 'priority' => '0.6'];
        }

        foreach (NewsArticle::published()->latest('published_at')->get() as $article) {
            $urls[] = [
                'loc' => route('news.show', $article->slug),
                'lastmod' => ($article->updated_at ?? $article->published_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return $this->xmlResponse($this->buildUrlset($urls));
    }

    public function news(): Response
    {
        $articles = NewsArticle::published()
            ->where('published_at', '>=', now()->subHours(48))
            ->latest('published_at')
            ->limit(1000)
            ->get();

        $items = $articles->map(function (NewsArticle $article) {
            return [
                'loc' => route('news.show', $article->slug),
                'news' => [
                    'publication_name' => 'The Soccer Goals',
                    'publication_language' => 'en',
                    'publication_date' => $article->published_at->toIso8601String(),
                    'title' => $article->title,
                ],
            ];
        });

        return $this->xmlResponse($this->buildNewsUrlset($items));
    }

    /**
     * Every published match had no sitemap presence at all before this -
     * thousands of real result/fixture pages discoverable only by
     * internal-link crawling rather than being told about directly. Kept
     * as its own sitemap file (like the news one) rather than folded into
     * the general sitemap, since it's the single largest URL set on the
     * site.
     */
    public function matches(): Response
    {
        $urls = MatchFixture::published()
            ->orderByDesc('kickoff_at')
            ->get()
            ->map(fn (MatchFixture $match) => [
                'loc' => $match->prettyUrl(),
                'lastmod' => ($match->updated_at ?? $match->kickoff_at)->toAtomString(),
                'changefreq' => $match->status === 'final' ? 'monthly' : 'hourly',
                'priority' => $match->status === 'final' ? '0.5' : '0.7',
            ]);

        return $this->xmlResponse($this->buildUrlset($urls->all()));
    }

    /** Same gap as matches() - every real player profile, previously absent from any sitemap. */
    public function players(): Response
    {
        $urls = Player::whereHas('team', fn ($q) => $q->published())
            ->get()
            ->map(fn (Player $player) => [
                'loc' => $player->prettyUrl(),
                'changefreq' => 'weekly',
                'priority' => '0.4',
            ]);

        return $this->xmlResponse($this->buildUrlset($urls->all()));
    }

    private function buildUrlset(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->esc($url['loc'])."</loc>\n";
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>'.$this->esc($url['lastmod'])."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$this->esc($url['changefreq'])."</changefreq>\n";
            $xml .= '    <priority>'.$this->esc($url['priority'])."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function buildNewsUrlset(iterable $items): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">'."\n";

        foreach ($items as $item) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->esc($item['loc'])."</loc>\n";
            $xml .= "    <news:news>\n";
            $xml .= "      <news:publication>\n";
            $xml .= '        <news:name>'.$this->esc($item['news']['publication_name'])."</news:name>\n";
            $xml .= '        <news:language>'.$this->esc($item['news']['publication_language'])."</news:language>\n";
            $xml .= "      </news:publication>\n";
            $xml .= '      <news:publication_date>'.$this->esc($item['news']['publication_date'])."</news:publication_date>\n";
            $xml .= '      <news:title>'.$this->esc($item['news']['title'])."</news:title>\n";
            $xml .= "    </news:news>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
