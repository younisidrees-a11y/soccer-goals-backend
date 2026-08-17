<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\NewsArticle;
use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Three editorially-written news articles, one per category requested:
 * Club News, Transfer News, and a Match Report. Left as pending_review
 * so an editor approves them before they go live.
 */
class ThreeCategoryNewsSeeder extends Seeder
{
    public function run(): void
    {
        $premierLeague = League::where('slug', 'premier-league')->first();
        $sunderland = Team::where('slug', 'sunderland')->first();
        $palace = Team::where('slug', 'crystal-palace')->first();

        NewsArticle::updateOrCreate(
            ['slug' => 'sunderland-back-in-the-premier-league-2026'],
            [
                'title' => 'Sunderland Are Back - and the Whole City Can Feel It',
                'dek' => "A promotion, a big home win, and a stadium that feels loud again. Sunderland's return to the Premier League is off to the perfect start.",
                'body' => <<<'BODY'
There is a different energy around Sunderland right now, and you do not need to be inside the Stadium of Light to notice it. After years away from the top flight, the club is back in the Premier League, and the opening weekend gave supporters exactly the start they had been hoping for: a 2-1 win over Brentford in front of a crowd that was loud from the first whistle to the last.

Promotion brings pressure as well as excitement, and plenty of newly-promoted teams find the step up tough going in the opening weeks. Sunderland did not look like a team overawed by the occasion. They played with energy, pressed high when they needed to, and gave their fans a performance to match the mood in the stands.

What stood out most was not just the result, but the feeling around the place. Local pubs were full hours before kick-off, replica shirts were everywhere in the city centre, and the walk to the ground had the kind of buzz that only comes around when a club returns to where its supporters feel it belongs. For a fanbase that has waited a long time for this moment, that atmosphere matters just as much as the three points.

There is a long season ahead, and staying in the Premier League will be a far tougher challenge than getting promoted to it. But if the opening weekend is anything to go by, Sunderland look ready to embrace the challenge rather than fear it. The club, and the city behind it, are enjoying being back where they believe they belong.
BODY,
                'image_path' => 'assets/img/news/sunderland-return-club-news-2026.svg',
                'category' => 'club-news',
                'league_id' => $premierLeague?->id,
                'team_id' => $sunderland?->id,
                'match_id' => 10,
                'source' => 'human',
                'status' => 'pending_review',
                'author' => 'Marcus Ferreira',
                'meta_title' => 'Sunderland Are Back in the Premier League | The Soccer Goals',
                'meta_description' => "Sunderland's return to the Premier League started with a home win and a city in full voice - a look at the mood around the club on opening weekend.",
                'meta_keywords' => 'Sunderland, Premier League, promotion, club news, Stadium of Light',
            ]
        );

        NewsArticle::updateOrCreate(
            ['slug' => 'transfer-deadline-day-nerves-2026'],
            [
                'title' => "Deadline Day Nerves: Inside Football's Most Chaotic 24 Hours",
                'dek' => 'Phones buzzing, medicals booked at the last minute, fans refreshing their timelines. Deadline day is unlike anything else in football.',
                'body' => <<<'BODY'
There is nothing quite like the final hours of a transfer window. Deals that have been quietly discussed for weeks suddenly speed up, medicals get booked at short notice, and fans spend the whole day refreshing their phones, hoping for one more signing before the window shuts.

For most clubs, deadline day is not really about big, exciting signings. It is about fine-tuning the squad before the deadline. A back-up goalkeeper might arrive. A versatile defender could be added for cover. A young player may be sent out on loan just to get regular game time. These moves rarely make headlines, but they often matter just as much once the season is in full swing and injuries or suspensions start to bite.

Behind the scenes, deadline day is organised chaos. Agents, club officials, and lawyers all race against the clock to get the paperwork finished in time. Even deals that look certain in the morning can fall apart by the evening if the final details cannot be agreed. It is a stressful day for everyone involved, and just as nerve-wracking for supporters watching from home as it is for the people actually doing the negotiating.

Once the transfer window shuts, the focus shifts fully to results. Every deal made in the past few weeks now gets judged in a completely different way. A signing that looked exciting on deadline day only really matters once that player starts making a difference on the pitch. For now though, the drama of deadline day is a reminder of just how fast the modern transfer market really moves.
BODY,
                'image_path' => 'assets/img/news/transfer-deadline-day-nerves-2026.svg',
                'category' => 'transfers',
                'league_id' => $premierLeague?->id,
                'source' => 'human',
                'status' => 'pending_review',
                'author' => 'Marcus Ferreira',
                'meta_title' => 'Transfer Deadline Day Nerves | The Soccer Goals',
                'meta_description' => "A look at what really happens behind the scenes on transfer deadline day, and why it's one of the most stressful days in football for clubs and fans alike.",
                'meta_keywords' => 'transfer news, deadline day, transfer window, football transfers',
            ]
        );

        NewsArticle::updateOrCreate(
            ['slug' => 'crystal-palace-2-2-west-ham-2026'],
            [
                'title' => 'Crystal Palace and West Ham Share a Four-Goal Thriller at Selhurst Park',
                'dek' => 'The lead changed hands more than once as both sides went for the win rather than settling for a point.',
                'body' => <<<'BODY'
Crystal Palace and West Ham served up an entertaining afternoon at Selhurst Park, sharing four goals in a 2-2 draw that had spells of real quality from both sides. Neither team was content to sit back and protect what they had, and that approach made for one of the more open games of the opening weekend.

The numbers show just how close this game really was. Crystal Palace had a little more of the ball, with 55% possession, but West Ham matched them shot for shot. Palace had 12 attempts on goal, West Ham had 11, and both teams managed three shots on target. This was the kind of game either team could have won, and nobody would have complained if they had.

What made this match so entertaining was the way momentum kept shifting. Just when one team looked to be in control, the other hit back. That pattern carried on right until the final whistle. For anyone watching, it was four goals and a real contest from start to finish. For the players, it was the kind of game that stays with you long after it ends.

A draw was not what either side wanted, but it felt like a fair result. Both teams were evenly matched for most of the game. Crystal Palace and West Ham will be pleased with how they attacked, and both will look to build on that. At the same time, both teams know they need to tighten up at the back if they want performances like this to bring home wins.
BODY,
                'image_path' => 'assets/img/news/crystal-palace-2-2-west-ham-2026.svg',
                'category' => 'match-report',
                'league_id' => $premierLeague?->id,
                'team_id' => $palace?->id,
                'match_id' => 8,
                'source' => 'human',
                'status' => 'pending_review',
                'author' => 'Marcus Ferreira',
                'meta_title' => 'Crystal Palace 2-2 West Ham: Premier League Match Report | The Soccer Goals',
                'meta_description' => 'Crystal Palace and West Ham shared a four-goal draw at Selhurst Park in an entertaining, end-to-end opening-weekend contest.',
                'meta_keywords' => 'Crystal Palace, West Ham, Premier League, match report, Selhurst Park',
            ]
        );
    }
}
