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
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="#" style="color:#B9CBDA;">Leagues</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $league->name }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">{{ $league->country }} &middot; {{ $league->season }} Season</div>
          <h1 class="league-hero-title">{{ $league->name }}</h1>
          <div class="league-hero-meta">{{ $league->teams_count }} clubs &middot; {{ $league->season }} season &middot; Live from the database</div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">Matchday</div>
          <div class="stat-value">1 of {{ $league->total_matchdays }}</div>
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
          <div class="stat-label">Next Matchday</div>
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

      <section aria-labelledby="table-heading" id="table">
        <div class="section-head"><h2 id="table-heading">{{ $league->name }} Table</h2></div>

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

      @php
        $top5 = $standings->take(5);
        $maxPoints = $top5->max('points') ?: 1;
        $maxGoals = $top5->max('goals_for') ?: 1;
        $maxGd = $top5->max('goal_difference') ?: 1;
      @endphp
      @if($top5->isNotEmpty())
      <section aria-labelledby="stats-heading" style="margin-top:32px;">
        <div class="section-head"><h2 id="stats-heading">Top 5 &mdash; Season Snapshot</h2></div>
        <p style="font-size:13.5px;color:var(--ink-faint);margin-top:-8px;margin-bottom:16px;">How the league's top five compare on points, goals scored and goal difference so far.</p>

        <h3 style="font-family:var(--font-display);font-size:15px;font-weight:600;margin-bottom:10px;">Points</h3>
        <div class="stat-bullets" style="margin-bottom:24px;">
          @foreach ($top5 as $s)
          <div class="stat-bullet-row">
            <span class="stat-bullet-label">{{ $s->team->name }}</span>
            <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ round($s->points / $maxPoints * 100) }}%;"></span></span>
            <span class="stat-bullet-value">{{ $s->points }}</span>
          </div>
          @endforeach
        </div>

        <h3 style="font-family:var(--font-display);font-size:15px;font-weight:600;margin-bottom:10px;">Goals Scored</h3>
        <div class="stat-bullets" style="margin-bottom:24px;">
          @foreach ($top5 as $s)
          <div class="stat-bullet-row">
            <span class="stat-bullet-label">{{ $s->team->name }}</span>
            <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ round($s->goals_for / $maxGoals * 100) }}%;"></span></span>
            <span class="stat-bullet-value">{{ $s->goals_for }}</span>
          </div>
          @endforeach
        </div>

        <h3 style="font-family:var(--font-display);font-size:15px;font-weight:600;margin-bottom:10px;">Goal Difference</h3>
        <div class="stat-bullets">
          @foreach ($top5 as $s)
          <div class="stat-bullet-row">
            <span class="stat-bullet-label">{{ $s->team->name }}</span>
            <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ $maxGd > 0 ? round(max($s->goal_difference, 0) / $maxGd * 100) : 0 }}%;"></span></span>
            <span class="stat-bullet-value">{{ $s->goal_difference > 0 ? '+' : '' }}{{ $s->goal_difference }}</span>
          </div>
          @endforeach
        </div>
      </section>
      @endif

      <section aria-labelledby="matches-heading" id="fixtures">
        <div class="section-head">
          <h2 id="matches-heading">Matchday 1 &mdash; {{ $matchdayOneResults->contains(fn ($m) => $m->isFinal()) ? 'Full Results' : 'Fixtures' }}</h2>
        </div>

        <div class="match-grid">
          @foreach ($matchdayOneResults as $m)
          <a href="{{ route('matches.show', $m->id) }}" class="match-card">
            <div class="match-meta"><span class="match-comp">{{ $league->name }} &middot; {{ $m->venue }}</span><span class="match-status">{{ $m->isFinal() ? 'Full-Time' : $m->kickoff_at->format('D j M, H:i') }}</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->homeTeam->crest_code }}" role="img" aria-label="{{ $m->homeTeam->full_name }} badge"></span><span class="team-name">{{ $m->homeTeam->name }}</span></div>@if($m->isFinal())<span class="team-score{{ $m->home_score > $m->away_score ? ' winning' : '' }}">{{ $m->home_score }}</span>@endif</div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->awayTeam->crest_code }}" role="img" aria-label="{{ $m->awayTeam->full_name }} badge"></span><span class="team-name">{{ $m->awayTeam->name }}</span></div>@if($m->isFinal())<span class="team-score{{ $m->away_score > $m->home_score ? ' winning' : '' }}">{{ $m->away_score }}</span>@endif</div>
            </div>
            <div class="match-venue">{{ $m->venue }}</div>
          </a>
          @endforeach
        </div>
      </section>

      <section aria-labelledby="history-heading-1" class="essay-block">
        <div class="essay-part-tag">League History</div>
        <h2 id="history-heading-1">About the {{ $league->name }}</h2>
        <p class="lede">The {{ $league->name }} is {{ $league->country }}'s top professional football division, contested by {{ $league->teams_count }} clubs across {{ $league->total_matchdays }} matchdays every season.</p>
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
        <div class="section-head"><h2 id="teams-heading">Team Directory</h2></div>
        <div class="team-directory-grid">
          @foreach ($standings as $s)
          <a href="{{ route('teams.show', $s->team->slug) }}" class="team-card"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge"></span><span class="team-card-body"><span class="team-card-name">{{ $s->team->name }}</span><span class="team-card-meta">{{ $s->position }}{{ match(true){ in_array($s->position % 100, [11,12,13]) => 'th', $s->position % 10 === 1 => 'st', $s->position % 10 === 2 => 'nd', $s->position % 10 === 3 => 'rd', default => 'th' } }} &middot; {{ $s->points }} {{ Str::plural('pt', $s->points) }}</span></span></a>
          @endforeach
        </div>
      </section>

      <section aria-labelledby="news-heading">
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
        <h2>The Matchday Briefing</h2>
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
