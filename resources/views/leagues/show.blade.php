@extends('layouts.site')

@section('title', $league->meta_title ?: $league->name . ' — Table, Fixtures, Results & News | The Soccer Goals')
@section('meta_description', $league->meta_description ?: $league->name . ' table, fixtures, results and team news for the ' . $league->season . ' season. Full ' . $league->name . ' coverage on The Soccer Goals.')
@section('meta_keywords', $league->meta_keywords ?: $league->name . ', ' . $league->country . ' football, football table, standings, fixtures, results, live scores, ' . $league->season . ' season, ' . $league->name . ' news')
@section('canonical', route('leagues.show', $league->slug))
@section('og_title', $league->meta_title ?: $league->name . ' — Table, Fixtures, Results & News | The Soccer Goals')
@section('og_description', $league->meta_description ?: $league->name . ' table, fixtures, results and team news for the ' . $league->season . ' season. Full ' . $league->name . ' coverage on The Soccer Goals.')

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#9299AA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('leagues.index') }}" style="color:#9299AA;">Leagues</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $league->name }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB4FF;">{{ $league->country }} &middot; {{ $league->season }} Season</div>
          <h1 class="league-hero-title">{{ $league->name }}</h1>
          <div class="league-hero-meta">{{ $league->teams_count }} clubs &middot; {{ $league->season }} season</div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">Top Scorer</div>
          <div class="stat-value">
            @if($topScorer)
              <a href="{{ $topScorer->prettyUrl() }}" style="color:inherit;display:flex;align-items:center;gap:8px;"><span class="crest crest-{{ $topScorer->team->crest_code }}" role="img" aria-label="{{ $topScorer->team->full_name }} badge"></span>{{ $topScorer->name }} &middot; {{ $topScorer->goals }}</a>
            @else
              TBC
            @endif
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-label">League Leader</div>
          <div class="stat-value">
            @if($leader)
              <span class="crest crest-{{ $leader->team->crest_code }}" role="img" aria-label="{{ $leader->team->full_name }} badge"></span>{{ $leader->team->name }}
            @else
              TBC
            @endif
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Leader's Points</div>
          <div class="stat-value">{{ $leader->points ?? 0 }} pts</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Next Fixture</div>
          <div class="stat-value">{{ $nextFixture?->kickoff_at->format('D, j M') ?? 'TBC' }}</div>
        </div>
      </div>
    </div>
  </section>

  <div class="wrap">
    <div class="ad-slot ad-leaderboard">
      <span class="ad-eyebrow">Advertisement</span>
      <span class="ad-size">728 &times; 90 &middot; AdSense unit</span>
    </div>
  </div>

  <div class="wrap content-grid">
    <div class="content-main">

      <section aria-labelledby="history-heading-1" class="essay-block" id="about">
        <div class="essay-part-tag">League History</div>
        <h2 id="history-heading-1">About the {{ $league->name }}</h2>
        <p class="lede">The {{ $league->name }} is {{ $league->country }}'s top professional football division, contested by {{ $league->teams_count }} clubs across {{ $league->total_matchdays }} rounds every season.</p>
        @if($matchesTotal > 0)
        <p style="font-size:13.5px;color:var(--ink-faint);">{{ $matchesPlayed }} of {{ $matchesTotal }} matches played this season &middot; {{ $matchesPlayed > 0 ? round($matchesPlayed / $matchesTotal * 100) : 0 }}% complete.</p>
        @endif
        @if($league->about_text)
          @foreach (explode("\n", $league->about_text) as $paragraph)
            @continue(trim($paragraph) === '')
            <p>{{ trim($paragraph) }}</p>
          @endforeach
        @endif
      </section>

      <div class="ad-slot ad-native">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">Native in-content unit</span>
      </div>

      <section aria-labelledby="teams-heading" id="teams">
        <div class="section-head"><h2 id="teams-heading">{{ $league->name }} Teams List</h2></div>
        <div class="team-directory-grid">
          @foreach ($standings as $s)
          <a href="{{ route('teams.show', $s->team->slug) }}" class="team-card"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge"></span><span class="team-card-body"><span class="team-card-name">{{ $s->team->name }}</span><span class="team-card-meta">{{ $s->position }}{{ match(true){ in_array($s->position % 100, [11,12,13]) => 'th', $s->position % 10 === 1 => 'st', $s->position % 10 === 2 => 'nd', $s->position % 10 === 3 => 'rd', default => 'th' } }} &middot; {{ $s->points }} {{ Str::plural('pt', $s->points) }}</span></span></a>
          @endforeach
        </div>
      </section>

      <section aria-labelledby="fixtures-heading" id="fixtures" style="margin-top:32px;">
        <div class="section-head"><h2 id="fixtures-heading">Upcoming Fixtures</h2></div>

        @if($upcomingFixtures->isNotEmpty())
        <div class="match-grid">
          @foreach ($upcomingFixtures as $m)
          <a href="{{ $m->prettyUrl() }}" class="match-card">
            <div class="match-meta"><span class="match-comp">{{ $league->name }} &middot; {{ $m->venue }}</span><span class="match-status">{{ $m->kickoff_at->format('D j M, H:i') }}</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->homeTeam->crest_code }}" role="img" aria-label="{{ $m->homeTeam->full_name }} badge"></span><span class="team-name">{{ $m->homeTeam->name }}</span></div></div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->awayTeam->crest_code }}" role="img" aria-label="{{ $m->awayTeam->full_name }} badge"></span><span class="team-name">{{ $m->awayTeam->name }}</span></div></div>
            </div>
            <div class="match-venue">{{ $m->venue }}</div>
          </a>
          @endforeach
        </div>
        @else
        <p class="achievements-empty">No fixtures currently scheduled for the {{ $league->name }}.</p>
        @endif
      </section>

      <section aria-labelledby="results-heading" id="results" style="margin-top:32px;">
        <div class="section-head"><h2 id="results-heading">Latest Results</h2></div>

        @if($latestResults->isNotEmpty())
        <div class="match-grid">
          @foreach ($latestResults as $m)
          <a href="{{ $m->prettyUrl() }}" class="match-card">
            <div class="match-meta"><span class="match-comp">{{ $league->name }} &middot; {{ $m->venue }}</span><span class="match-status">Full-Time</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->homeTeam->crest_code }}" role="img" aria-label="{{ $m->homeTeam->full_name }} badge"></span><span class="team-name">{{ $m->homeTeam->name }}</span></div><span class="team-score{{ $m->home_score > $m->away_score ? ' winning' : '' }}">{{ $m->home_score }}</span></div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->awayTeam->crest_code }}" role="img" aria-label="{{ $m->awayTeam->full_name }} badge"></span><span class="team-name">{{ $m->awayTeam->name }}</span></div><span class="team-score{{ $m->away_score > $m->home_score ? ' winning' : '' }}">{{ $m->away_score }}</span></div>
            </div>
            <div class="match-venue">{{ $m->venue }}</div>
          </a>
          @endforeach
        </div>
        @else
        <p class="achievements-empty">No matches played yet this season &mdash; check back once the {{ $league->name }} kicks off.</p>
        @endif
      </section>

      @php
        $hasAnyAchievement = collect($achievements)->filter()->isNotEmpty();
      @endphp
      <section aria-labelledby="achievements-heading" id="achievements" style="margin-top:32px;">
        <div class="section-head"><h2 id="achievements-heading">Top Achievements</h2></div>
        <p style="font-size:13.5px;color:var(--ink-faint);margin-top:-8px;margin-bottom:18px;">Real superlatives from this season's {{ $league->name }} results &mdash; not a prediction, just what's actually happened on the pitch.</p>

        @if($hasAnyAchievement)
        <div class="achievements-grid">

          @if($achievements['bestAttack'])
          <div class="achievement-card" style="--ach-color:var(--accent);--ach-soft:var(--accent-soft);">
            <span class="achievement-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4.3"/><circle cx="12" cy="12" r="1"/></svg></span>
            <span class="achievement-label">Best Attack</span>
            <span class="achievement-team"><span class="crest crest-{{ $achievements['bestAttack']->team->crest_code }}" role="img" aria-label="{{ $achievements['bestAttack']->team->full_name }} badge"></span>{{ $achievements['bestAttack']->team->name }}</span>
            <span class="achievement-value">{{ $achievements['bestAttack']->goals_for }} goals scored</span>
            <span class="achievement-detail">The division's most prolific attack so far this season.</span>
          </div>
          @endif

          @if($achievements['bestDefense'])
          <div class="achievement-card" style="--ach-color:var(--accent-2);--ach-soft:var(--accent-2-soft);">
            <span class="achievement-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5 19 6.5V11c0 5.2-3 8.6-7 9.8-4-1.2-7-4.6-7-9.8V6.5L12 3.5Z"/></svg></span>
            <span class="achievement-label">Best Defense</span>
            <span class="achievement-team"><span class="crest crest-{{ $achievements['bestDefense']->team->crest_code }}" role="img" aria-label="{{ $achievements['bestDefense']->team->full_name }} badge"></span>{{ $achievements['bestDefense']->team->name }}</span>
            <span class="achievement-value">{{ $achievements['bestDefense']->goals_against }} conceded</span>
            <span class="achievement-detail">The tightest defensive record in the league right now.</span>
          </div>
          @endif

          @if($achievements['mostWins'])
          <div class="achievement-card" style="--ach-color:var(--accent);--ach-soft:var(--accent-soft);">
            <span class="achievement-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M8 4h8v3.2a4 4 0 0 1-8 0V4Z"/><path d="M8 5H5.2A2.8 2.8 0 0 0 8 7.6V5Z"/><path d="M16 5h2.8A2.8 2.8 0 0 1 16 7.6V5Z"/><path d="M11 11.5h2V15h-2z"/><path d="M9 19.5h6v1.3H9z"/><path d="M9.7 16.5h4.6l.5 3H9.2l.5-3Z"/></svg></span>
            <span class="achievement-label">Most Wins</span>
            <span class="achievement-team"><span class="crest crest-{{ $achievements['mostWins']->team->crest_code }}" role="img" aria-label="{{ $achievements['mostWins']->team->full_name }} badge"></span>{{ $achievements['mostWins']->team->name }}</span>
            <span class="achievement-value">{{ $achievements['mostWins']->won }} {{ Str::plural('win', $achievements['mostWins']->won) }}</span>
            <span class="achievement-detail">No side has won more matches this season.</span>
          </div>
          @endif

          @if($achievements['biggestWin'])
          @php
            $bw = $achievements['biggestWin'];
            $bwWinner = $bw->home_score > $bw->away_score ? $bw->homeTeam : $bw->awayTeam;
            $bwLoser = $bw->home_score > $bw->away_score ? $bw->awayTeam : $bw->homeTeam;
            $bwMargin = abs($bw->home_score - $bw->away_score);
          @endphp
          <div class="achievement-card" style="--ach-color:var(--live);--ach-soft:var(--live-soft);">
            <span class="achievement-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M13 2 5 14h5.5L9.5 22 19 10h-5.5L13 2Z"/></svg></span>
            <span class="achievement-label">Biggest Win</span>
            <span class="achievement-team"><span class="crest crest-{{ $bwWinner->crest_code }}" role="img" aria-label="{{ $bwWinner->full_name }} badge"></span>{{ $bwWinner->name }}</span>
            <span class="achievement-value">Beat {{ $bwLoser->name }} by {{ $bwMargin }}</span>
            <span class="achievement-detail"><a href="{{ $bw->prettyUrl() }}" style="color:inherit;text-decoration:underline;">{{ $bw->homeTeam->name }} {{ $bw->home_score }}&ndash;{{ $bw->away_score }} {{ $bw->awayTeam->name }}</a></span>
          </div>
          @endif

          @if($achievements['longestUnbeaten'])
          <div class="achievement-card" style="--ach-color:var(--accent-2);--ach-soft:var(--accent-2-soft);">
            <span class="achievement-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2.5c.9 3-2.6 4.4-2.6 8a2.6 2.6 0 0 0 5.2 0c0-1-.6-1.7-.6-1.7.9 2.5 2.5 3.6 2.5 6.3a4.5 4.5 0 0 1-9 0c0-5.6 3-6.6 4.5-12.6Z"/></svg></span>
            <span class="achievement-label">Longest Unbeaten Run</span>
            <span class="achievement-team"><span class="crest crest-{{ $achievements['longestUnbeaten']['team']->crest_code }}" role="img" aria-label="{{ $achievements['longestUnbeaten']['team']->full_name }} badge"></span>{{ $achievements['longestUnbeaten']['team']->name }}</span>
            <span class="achievement-value">{{ $achievements['longestUnbeaten']['best'] }} {{ Str::plural('game', $achievements['longestUnbeaten']['best']) }} unbeaten</span>
            <span class="achievement-detail">Longest current-season run without a loss.</span>
          </div>
          @endif

        </div>
        @else
        <p class="achievements-empty">Achievements will appear here once {{ $league->name }} matches have been played this season.</p>
        @endif
      </section>

      <section aria-labelledby="table-heading" id="table" style="margin-top:32px;">
        <div class="section-head"><h2 id="table-heading">{{ $league->name }} Points Table</h2></div>

        @if($league->table_intro)
        <p class="lede" style="margin-bottom:18px;">{{ $league->table_intro }}</p>
        @endif

        <div class="table-scroll">
          <table class="standings standings-full">
            <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>GF</th><th>GA</th><th>GD</th><th>Pts</th></tr></thead>
            <tbody>
              @foreach ($standings as $s)
              <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>{{ $s->team->name }}</a></td><td>{{ $s->played }}</td><td>{{ $s->won }}</td><td>{{ $s->drawn }}</td><td>{{ $s->lost }}</td><td>{{ $s->goals_for }}</td><td>{{ $s->goals_against }}</td><td>{{ $s->goal_difference > 0 ? '+' : '' }}{{ $s->goal_difference }}</td><td class="pts">{{ $s->points }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="table-legend" style="margin-top:12px;">
          <span class="legend-item"><span class="legend-dot ucl"></span>Champions League</span>
          <span class="legend-item"><span class="legend-dot rel"></span>Relegation</span>
        </div>

        @if($league->table_closing)
        <p style="margin-top:18px;color:var(--ink-muted);font-size:14.5px;line-height:1.65;">{{ $league->table_closing }}</p>
        @endif
      </section>

      <section aria-labelledby="news-heading" style="margin-top:32px;">
        <div class="section-head">
          <h2 id="news-heading">{{ $league->name }} News</h2>
        </div>
        <div class="news-grid">
          @forelse ($news as $article)
          @php
            $catTag = match ($article->category) {
                'match-report' => ['cat-report', 'Match Report'],
                'transfers' => ['cat-transfers', 'Transfers'],
                'analysis' => ['cat-analysis', 'Analysis'],
                'injury' => ['cat-report', 'Injury Update'],
                default => ['cat-opinion', 'Club News'],
            };
          @endphp
          <article class="news-card">
            <a href="{{ route('news.show', $article->slug) }}"><div class="media" aria-hidden="true"><svg viewBox="0 0 200 150"><rect width="200" height="150" fill="none" stroke="#fff" stroke-opacity=".12" stroke-width="3"/></svg></div></a>
            <span class="cat-tag {{ $catTag[0] }}">{{ $catTag[1] }}</span>
            <a href="{{ route('news.show', $article->slug) }}"><h3>{{ $article->title }}</h3></a>
            <p class="dek">{{ $article->dek }}</p>
          </article>
          @empty
          <p>No published {{ $league->name }} stories yet &mdash; check back soon.</p>
          @endforelse
        </div>
      </section>

    </div>

    <aside class="sidebar" aria-label="Sidebar">
      <div class="widget table-widget" id="tables">
        <div class="widget-head">
          <h2>{{ $league->name }} Table</h2>
        </div>

        <table class="standings">
          <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>Pts</th></tr></thead>
          <tbody>
            @foreach ($standings as $s)
            <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>{{ $s->team->name }}</a></td><td>{{ $s->played }}</td><td>{{ $s->won }}</td><td>{{ $s->drawn }}</td><td>{{ $s->lost }}</td><td class="pts">{{ $s->points }}</td></tr>
            @endforeach
          </tbody>
        </table>

        <div class="table-legend">
          <span class="legend-item"><span class="legend-dot ucl"></span>Champions League</span>
          <span class="legend-item"><span class="legend-dot rel"></span>Relegation</span>
        </div>
      </div>

      <div class="ad-slot ad-mpu">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
      </div>

      <div class="widget newsletter-widget">
        <h2>The Daily Briefing</h2>
        <p>Every score, every storyline, every morning &mdash; straight to your inbox.</p>
        <form class="nl-form" onsubmit="return false;">
          <input type="email" placeholder="you@email.com" required aria-label="Email address">
          <button class="btn btn-accent btn-block" type="submit">Sign Up Free</button>
        </form>
        <p class="nl-fine">No spam. Unsubscribe anytime.</p>
      </div>

      <div class="ad-slot ad-skyscraper">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 600 &middot; AdSense unit</span>
      </div>
    </aside>
  </div>

@endsection
