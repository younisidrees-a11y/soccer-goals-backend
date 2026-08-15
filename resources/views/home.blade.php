@extends('layouts.site')

@section('title', 'The Soccer Goals — Soccer, Covered.')
@section('meta_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')
@section('meta_keywords', 'soccer news, football news, Premier League, La Liga, Serie A, Bundesliga, Ligue 1, fixtures, results, points table, football scores')
@section('canonical', route('home'))
@section('og_title', 'The Soccer Goals — Soccer, Covered.')
@section('og_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')

@section('content')

  <section class="hero wrap" aria-labelledby="hero-heading">
    <article class="hero-lead">
      <div class="media media-hero" aria-hidden="true">
        <svg viewBox="0 0 800 450" preserveAspectRatio="xMidYMid slice">
          <line x1="400" y1="0" x2="400" y2="450" stroke="#fff" stroke-opacity=".18" stroke-width="2"/>
          <circle cx="400" cy="225" r="62" fill="none" stroke="#fff" stroke-opacity=".18" stroke-width="2"/>
          <circle cx="400" cy="225" r="3" fill="#fff" fill-opacity=".3"/>
          <path d="M0 90 h110 v150 h-110 z" fill="none" stroke="#fff" stroke-opacity=".16" stroke-width="2"/>
          <path d="M800 90 h-110 v150 h110 z" fill="none" stroke="#fff" stroke-opacity=".16" stroke-width="2"/>
          <circle cx="140" cy="120" r="46" fill="#fff" fill-opacity=".07"/>
          <circle cx="700" cy="340" r="70" fill="#fff" fill-opacity=".06"/>
        </svg>
        <span class="media-tag">Opening Weekend</span>
      </div>
      <div class="eyebrow">Premier League &middot; Season Preview</div>
      <h1 id="hero-heading">Five Storylines to Watch as the Premier League Kicks Off</h1>
      <p class="dek">New signings, a reshuffled top six and a fixture list front-loaded with early collisions &mdash; here's everything worth knowing before the first whistle of the new campaign.</p>
      <div class="byline">By Marcus Ferreira <span class="dot"></span> 13 Aug 2026 <span class="dot"></span> 6 min read</div>
    </article>

    <div class="hero-side">
      <article class="hero-side-card">
        <div class="media" aria-hidden="true">
          <svg viewBox="0 0 200 140" preserveAspectRatio="xMidYMid slice"><circle cx="160" cy="20" r="50" fill="#fff" fill-opacity=".08"/><circle cx="20" cy="120" r="40" fill="#fff" fill-opacity=".08"/></svg>
        </div>
        <div>
          <div class="eyebrow">Transfers</div>
          <h3>Deadline Countdown: Ten Deals Still in Motion</h3>
          <div class="byline">Aug 13 <span class="dot"></span> 4 min</div>
        </div>
      </article>
      <article class="hero-side-card">
        <div class="media" aria-hidden="true">
          <svg viewBox="0 0 200 140" preserveAspectRatio="xMidYMid slice"><rect x="0" y="0" width="200" height="140" fill="none" stroke="#fff" stroke-opacity=".12" stroke-width="3"/><circle cx="100" cy="70" r="26" fill="none" stroke="#fff" stroke-opacity=".18" stroke-width="2"/></svg>
        </div>
        <div>
          <div class="eyebrow">La Liga</div>
          <h3>Bellingham and Yamal Headline a Star-Studded Round One</h3>
          <div class="byline">Aug 13 <span class="dot"></span> 5 min</div>
        </div>
      </article>
      <article class="hero-side-card">
        <div class="media" aria-hidden="true">
          <svg viewBox="0 0 200 140" preserveAspectRatio="xMidYMid slice"><circle cx="50" cy="40" r="60" fill="#fff" fill-opacity=".07"/></svg>
        </div>
        <div>
          <div class="eyebrow">Bundesliga</div>
          <h3>Bayern Look to Reload After a Busy Summer Rebuild</h3>
          <div class="byline">Aug 12 <span class="dot"></span> 4 min</div>
        </div>
      </article>
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

      <section aria-labelledby="matches-heading" id="fixtures">
        <div class="section-head">
          <h2 id="matches-heading">Premier League &middot; Matchday 1 Results</h2>
        </div>

        <div class="match-grid">
          @foreach ($todaysMatches as $m)
          <a href="{{ route('matches.show', $m->id) }}" class="match-card">
            <div class="match-meta"><span class="match-comp"><svg class="flag" role="img" aria-label="England flag"><use href="#flag-eng"></use></svg>{{ $m->venue }}</span><span class="match-status">Full-Time</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->homeTeam->crest_code }}" role="img" aria-label="{{ $m->homeTeam->full_name }} badge"></span><span class="team-name">{{ $m->homeTeam->name }}</span></div><span class="team-score{{ $m->home_score > $m->away_score ? ' winning' : '' }}">{{ $m->home_score }}</span></div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->awayTeam->crest_code }}" role="img" aria-label="{{ $m->awayTeam->full_name }} badge"></span><span class="team-name">{{ $m->awayTeam->name }}</span></div><span class="team-score{{ $m->away_score > $m->home_score ? ' winning' : '' }}">{{ $m->away_score }}</span></div>
            </div>
            <div class="match-venue">{{ $m->venue }}</div>
          </a>
          @endforeach
        </div>
      </section>

      <section aria-labelledby="news-heading">
        <div class="section-head">
          <h2 id="news-heading">Latest News</h2>
          <a href="{{ route('news.index') }}" class="section-link">View all news
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
        <div class="news-grid">
          @forelse ($latestNews as $article)
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
            <a href="{{ route('news.show', $article->slug) }}"><div class="media" aria-hidden="true"><svg viewBox="0 0 200 150"><circle cx="160" cy="20" r="60" fill="#fff" fill-opacity=".08"/></svg></div></a>
            <span class="cat-tag {{ $catTag[0] }}">{{ $catTag[1] }}</span>
            <a href="{{ route('news.show', $article->slug) }}"><h3>{{ $article->title }}</h3></a>
            <p class="dek">{{ $article->dek }}</p>
          </article>
          @empty
          <p>No published stories yet &mdash; check back soon.</p>
          @endforelse
        </div>
      </section>

      <section aria-labelledby="fx-results-heading" id="results">
        <div class="section-head"><h2 id="fx-results-heading">Fixtures &amp; Results</h2></div>
        <div class="two-col">
          <div class="list-panel">
            <h3>Upcoming Fixtures</h3>
            @foreach ($upcomingFixtures as $fx)
            <a href="{{ route('matches.show', $fx->id) }}" class="fixture-row"><span class="fx-date">{{ strtoupper($fx->kickoff_at->format('D')) }}<br>{{ $fx->kickoff_at->format('j M') }}</span><span class="fx-teams"><span class="fx-team"><span class="crest crest-{{ $fx->homeTeam->crest_code }}" role="img" aria-label="{{ $fx->homeTeam->full_name }} badge"></span>{{ $fx->homeTeam->name }}</span><span class="vs">vs</span><span class="fx-team"><span class="crest crest-{{ $fx->awayTeam->crest_code }}" role="img" aria-label="{{ $fx->awayTeam->full_name }} badge"></span>{{ $fx->awayTeam->name }}</span></span><span class="fx-time"><span class="dot-waiting" aria-hidden="true"></span>{{ $fx->kickoff_at->format('H:i') }}</span></a>
            @endforeach
          </div>
          <div class="list-panel">
            <h3>Recent Results</h3>
            @foreach ($recentResults as $r)
            <a href="{{ route('matches.show', $r->id) }}" class="result-row"><span class="fx-teams"><span class="fx-team"><span class="crest crest-{{ $r->homeTeam->crest_code }}" role="img" aria-label="{{ $r->homeTeam->full_name }} badge"></span>{{ $r->homeTeam->name }}</span><span class="vs">vs</span><span class="fx-team"><span class="crest crest-{{ $r->awayTeam->crest_code }}" role="img" aria-label="{{ $r->awayTeam->full_name }} badge"></span>{{ $r->awayTeam->name }}</span></span><span class="rs-score">{{ $r->home_score }}&ndash;{{ $r->away_score }}</span></a>
            @endforeach
          </div>
        </div>
      </section>

      <div class="ad-slot ad-native">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">Native in-content unit</span>
      </div>

      <section aria-labelledby="history-heading" class="history-feature">
        <div class="history-grid">
          <div class="media" aria-hidden="true">
            <svg viewBox="0 0 400 320" preserveAspectRatio="xMidYMid slice">
              <circle cx="200" cy="160" r="90" fill="none" stroke="#fff" stroke-opacity=".16" stroke-width="2"/>
              <circle cx="80" cy="60" r="40" fill="#fff" fill-opacity=".07"/>
              <circle cx="330" cy="260" r="55" fill="#fff" fill-opacity=".06"/>
            </svg>
            <span class="media-tag">League History</span>
          </div>
          <div>
            <div class="eyebrow">Long Read</div>
            <h2 id="history-heading" style="font-size:clamp(1.4rem,1.15rem + 1vw,1.9rem);margin-top:8px;">The Evolution of the Premier League</h2>
            <p class="pull-quote">From a single satellite broadcast deal to the most-watched domestic league on the planet &mdash; the story of how English football rebuilt itself.</p>
            <p style="color:var(--ink-muted);font-size:14.5px;line-height:1.65;max-width:58ch;">Three decades on, the competition's global footprint, revenue and quality of play have transformed beyond recognition. We trace the rule changes, broadcast deals and iconic seasons that built the modern game.</p>
            <a href="{{ route('leagues.show', 'premier-league') }}" class="btn btn-ghost" style="margin-top:18px;">Read the Full Feature</a>
          </div>
        </div>
      </section>

    </div>

    <aside class="sidebar" aria-label="Sidebar">
      <div class="widget table-widget" id="tables">
        <div class="widget-head">
          <h2>Points Table</h2>
          <div class="table-tabs" role="tablist" aria-label="Select league table">
            <button class="ttab is-active" data-table="pl" aria-selected="true"><svg class="flag" role="img" aria-label="England flag"><use href="#flag-eng"></use></svg>PL</button>
            <button class="ttab" data-table="laliga" aria-selected="false"><svg class="flag" role="img" aria-label="Spain flag"><use href="#flag-esp"></use></svg>La Liga</button>
          </div>
        </div>

        <div class="standings-panel is-active" data-panel="pl">
          <table class="standings">
            <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>Pts</th></tr></thead>
            <tbody>
              @foreach ($plStandings as $s)
              <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>{{ $s->team->name }}</a></td><td>{{ $s->played }}</td><td>{{ $s->won }}</td><td>{{ $s->drawn }}</td><td>{{ $s->lost }}</td><td class="pts">{{ $s->points }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="standings-panel" data-panel="laliga">
          <table class="standings">
            <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>Pts</th></tr></thead>
            <tbody>
              @foreach ($laLigaStandings as $s)
              <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>{{ $s->team->name }}</a></td><td>{{ $s->played }}</td><td>{{ $s->won }}</td><td>{{ $s->drawn }}</td><td>{{ $s->lost }}</td><td class="pts">{{ $s->points }}</td></tr>
              @endforeach
            </tbody>
          </table>
        </div>

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

  <section class="promo-band">
    <div class="wrap promo-inner">
      <div>
        <h2>Never miss a moment of the season.</h2>
        <p>Get the Soccer Goals app for live score alerts, personalized team feeds and match-day push notifications.</p>
      </div>
      <form class="promo-form" onsubmit="return false;">
        <input type="email" placeholder="Enter your email" required aria-label="Email address">
        <button class="btn btn-accent" type="submit">Get the App</button>
      </form>
    </div>
  </section>

@endsection
