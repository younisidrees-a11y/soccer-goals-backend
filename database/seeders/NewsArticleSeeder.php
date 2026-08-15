<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\League;
use App\Models\NewsArticle;
use App\Models\Team;
use Illuminate\Database\Seeder;

class NewsArticleSeeder extends Seeder
{
    public function run(): void
    {
        $pl = League::where('slug', 'premier-league')->first();
        $liverpool = Team::where('slug', 'liverpool')->first();
        $mci = Team::where('slug', 'manchester-city')->first();
        $editor = AdminUser::first();

        // One of each workflow state, so the review queue has something to show on day one.
        NewsArticle::updateOrCreate(['slug' => 'salah-double-liverpool-bournemouth-sample'], [
            'title' => 'Salah Double Sends Liverpool Cruising Past Bournemouth',
            'dek' => 'A dominant Anfield opener as the champions began their title defence in ominous form.',
            'body' => 'Liverpool opened their Premier League title defence with a comfortable 3-0 win over Bournemouth at Anfield, Mohamed Salah scoring twice either side of a Cody Gakpo strike...',
            'category' => 'match-report',
            'league_id' => $pl?->id,
            'team_id' => $liverpool?->id,
            'source' => 'ai',
            'status' => 'pending_review',
            'author' => 'AI Draft, reviewed by Editorial Admin',
        ]);

        NewsArticle::updateOrCreate(['slug' => 'city-derby-week-win-sample'], [
            'title' => "City Edge Chelsea in Derby-Week Thriller",
            'dek' => 'A tight five-goal contest at the Etihad opens the season with a statement win.',
            'body' => 'Manchester City began their season with a hard-fought 2-1 win over Chelsea, Phil Foden and Erling Haaland on target in a game that swung on a single second-half moment...',
            'category' => 'match-report',
            'league_id' => $pl?->id,
            'team_id' => $mci?->id,
            'source' => 'ai',
            'status' => 'pending_review',
            'author' => 'AI Draft',
        ]);

        NewsArticle::updateOrCreate(['slug' => 'transfer-rumour-unverified-sample'], [
            'title' => 'Unverified Transfer Rumour Flagged by the Drafting Model',
            'dek' => 'An example of a draft an editor chose not to publish.',
            'body' => 'A speculative transfer story drafted from a low-confidence source, held back pending stronger sourcing...',
            'category' => 'transfers',
            'source' => 'ai',
            'status' => 'rejected',
            'author' => 'AI Draft',
            'reviewed_by' => $editor?->id,
            'reviewed_at' => now()->subHours(2),
            'rejection_reason' => 'Source not confirmed by a second outlet.',
        ]);
    }
}
