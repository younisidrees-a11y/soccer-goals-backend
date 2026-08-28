@extends('layouts.site')

@section('title', 'The Soccer Goals — Soccer, Covered.')
@section('meta_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the English Premier League, Spanish La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')
@section('meta_keywords', 'soccer news, football news, English Premier League, Spanish La Liga, Serie A, Bundesliga, Ligue 1, fixtures, results, points table, football scores')
@section('canonical', route('home'))
@section('og_title', 'The Soccer Goals — Soccer, Covered.')
@section('og_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the English Premier League, Spanish La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')

@section('content')

  <div class="wrap">
    <div class="ad-slot ad-leaderboard">
      <span class="ad-eyebrow">Advertisement</span>
      <span class="ad-size">728 &times; 90 &middot; AdSense unit</span>
    </div>
  </div>

  @if($spotMatch)
  @php
    $spotWinner = $spotMatch->home_score > $spotMatch->away_score ? 'home' : ($spotMatch->away_score > $spotMatch->home_score ? 'away' : null);
    // Real article available: use its real headline/dek/byline, link to
    // the article. No article yet: generate the headline straight from
    // the match's own real score - never invented prose - and link to
    // the match page instead of an article that doesn't exist.
    if ($spotlight) {
        $spotHref = route('news.show', $spotlight->slug);
        $spotTag = 'Match Report';
        $spotHeadline = $spotlight->title;
        $spotDek = $spotlight->dek;
        $spotByline = trim(($spotlight->author ?? '').' · '.$spotlight->published_at?->format('j M Y'), ' ·');
        $spotCta = 'Read Match Report';
    } else {
        $spotHref = $spotMatch->prettyUrl();
        $spotTag = 'Full-Time';
        if ($spotWinner) {
            $winnerTeam = $spotWinner === 'home' ? $spotMatch->homeTeam : $spotMatch->awayTeam;
            $loserTeam = $spotWinner === 'home' ? $spotMatch->awayTeam : $spotMatch->homeTeam;
            $winnerScore = $spotWinner === 'home' ? $spotMatch->home_score : $spotMatch->away_score;
            $loserScore = $spotWinner === 'home' ? $spotMatch->away_score : $spotMatch->home_score;
            $spotHeadline = "{$winnerTeam->name} Beat {$loserTeam->name} {$winnerScore}-{$loserScore}";
        } else {
            $spotHeadline = "{$spotMatch->homeTeam->name} and {$spotMatch->awayTeam->name} Draw {$spotMatch->home_score}-{$spotMatch->away_score}";
        }
        $spotDek = 'Full-time in the '.$spotMatch->league->name.($spotMatch->venue ? ' at '.$spotMatch->venue : '').'.';
        $spotByline = $spotMatch->kickoff_at->format('j M Y');
        $spotCta = 'View Match';
    }
  @endphp
  <div class="wrap" style="margin-top:20px;">
    <a href="{{ $spotHref }}" class="spotlight"@if($spotMatch->homeTeam->color_hex) style="--spot-a:{{ $spotMatch->homeTeam->color_hex }};"@endif>
      <div class="spotlight-body">
        <span class="spotlight-tag"><span class="dot" aria-hidden="true"></span>{{ $spotTag }} &middot; {{ $spotMatch->league->name }}</span>
        <div class="spotlight-score">
          <div class="spot-team"><span class="crest crest-{{ $spotMatch->homeTeam->crest_code }}" role="img" aria-label="{{ $spotMatch->homeTeam->full_name }} badge"></span><span class="spot-team-name">{{ $spotMatch->homeTeam->name }}</span></div>
          <span class="spot-num{{ $spotWinner === 'home' ? ' is-winner' : '' }}">{{ $spotMatch->home_score }}</span>
          <span class="spot-sep">&ndash;</span>
          <span class="spot-num{{ $spotWinner === 'away' ? ' is-winner' : '' }}">{{ $spotMatch->away_score }}</span>
          <div class="spot-team"><span class="crest crest-{{ $spotMatch->awayTeam->crest_code }}" role="img" aria-label="{{ $spotMatch->awayTeam->full_name }} badge"></span><span class="spot-team-name">{{ $spotMatch->awayTeam->name }}</span></div>
        </div>
        <h2>{{ $spotHeadline }}</h2>
        <p class="spotlight-dek">{{ $spotDek }}</p>
        <p class="spotlight-byline">{{ $spotByline }}</p>
      </div>
      <span class="spotlight-cta">{{ $spotCta }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
    </a>
  </div>
  @endif

  <div class="wrap">
    <div class="dash">
      <div>
        <div class="section-head">
          <h2>{{ $isToday ? "Today's Football" : 'Football on '.$selectedDate->format('j F') }}</h2>
          <a href="{{ route('today.index') }}" class="section-link">Full schedule
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>

        <div class="date-row">
          <a href="{{ route('home', ['date' => $selectedDate->copy()->subDay()->toDateString()]) }}" class="date-nav-btn" aria-label="Previous day">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6"/></svg>
          </a>
          @foreach ($dateStrip as $day)
          @php $dayIsToday = $day->isToday(); $dayIsSelected = $day->isSameDay($selectedDate); @endphp
          <a href="{{ $dayIsToday ? route('home') : route('home', ['date' => $day->toDateString()]) }}" class="date-chip{{ $dayIsSelected ? ' is-selected' : '' }}{{ $dayIsToday ? ' is-today' : '' }}">
            <span>{{ $dayIsToday ? 'TODAY' : strtoupper($day->format('D')) }}</span>
            <span class="d">{{ $day->format('j') }}</span>
          </a>
          @endforeach
          <a href="{{ route('home', ['date' => $selectedDate->copy()->addDay()->toDateString()]) }}" class="date-nav-btn" aria-label="Next day">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
          </a>
        </div>

        @if($todaysMatches->isEmpty())
        <p style="color:var(--ink-faint);font-size:14.5px;">No matches {{ $isToday ? 'kicking off today' : 'on '.$selectedDate->format('j F') }} across any covered league &mdash; check <a href="{{ route('fixtures.index') }}">upcoming fixtures</a> instead.</p>
        @else
        @php
          $todayHasLive = $todaysMatches->contains(fn ($m) => $m->isLive());
          $todayHasUpcoming = $todaysMatches->contains(fn ($m) => ! $m->isFinal() && ! $m->isLive());
          $todayHasFinished = $todaysMatches->contains(fn ($m) => $m->isFinal());
        @endphp
        <div class="status-tabs" role="tablist" aria-label="Filter today's matches by status" data-status-tabs>
          <button class="status-tab is-active" data-status-filter="all" role="tab" aria-selected="true">All</button>
          @if($todayHasLive)<button class="status-tab" data-status-filter="live" role="tab" aria-selected="false">Live</button>@endif
          @if($todayHasUpcoming)<button class="status-tab" data-status-filter="scheduled" role="tab" aria-selected="false">Upcoming</button>@endif
          @if($todayHasFinished)<button class="status-tab" data-status-filter="final" role="tab" aria-selected="false">Finished</button>@endif
        </div>

        <div data-status-groups>
          @foreach ($todaysMatchesByLeague as $leagueMatches)
          @php
            $grpLeague = $leagueMatches->first()->league;
            $grpColor = $leagueColors[$grpLeague->slug] ?? null;
          @endphp
          <div class="today-comp-group"@if($grpColor) style="--comp-color:{{ $grpColor }};"@endif>
            <div class="today-comp-head">
              <div class="today-comp-head-left">
                <svg class="flag" role="img" aria-label="{{ $grpLeague->country }} flag"><use href="#flag-{{ $grpLeague->flag_code }}"></use></svg>
                <a href="{{ route('leagues.show', $grpLeague->slug) }}" class="today-comp-name">{{ $grpLeague->name }}</a>
              </div>
            </div>
            <div class="match-list">
              @foreach ($leagueMatches as $m)
              @php
                $mShowScore = $m->isFinal() || ($m->isLive() && $m->home_score !== null);
                $mStatusLabel = $m->isFinal() ? 'FT' : ($m->isLive() ? 'LIVE' : $m->kickoff_at->format('H:i'));
                $mStatusKey = $m->isFinal() ? 'final' : ($m->isLive() ? 'live' : 'scheduled');
              @endphp
              <a href="{{ $m->prettyUrl() }}" class="match-row" data-status="{{ $mStatusKey }}">
                <div class="match-row-teams">
                  <div class="match-row-team"><span class="crest crest-{{ $m->homeTeam->crest_code }}" role="img" aria-label="{{ $m->homeTeam->full_name }} badge"></span><span class="match-row-team-name">{{ $m->homeTeam->name }}</span></div>
                  <div class="match-row-team"><span class="crest crest-{{ $m->awayTeam->crest_code }}" role="img" aria-label="{{ $m->awayTeam->full_name }} badge"></span><span class="match-row-team-name">{{ $m->awayTeam->name }}</span></div>
                </div>
                <div class="match-row-side">
                  @if($mShowScore)
                  <div class="match-row-score"><span>{{ $m->home_score }}</span><span>{{ $m->away_score }}</span></div>
                  @endif
                  <span class="match-row-status{{ $m->isLive() ? ' is-live' : '' }}">@if($m->isLive())<span class="dot" aria-hidden="true"></span>@endif{{ $mStatusLabel }}</span>
                </div>
              </a>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>

      <aside class="sidebar" aria-label="Sidebar">
        <div class="widget">
          <div class="widget-head"><h2>Trending Now</h2></div>
          @if($latestNews->isEmpty())
          <p style="color:var(--ink-faint);font-size:13px;">No published stories yet &mdash; check back soon.</p>
          @else
          <ul class="trending-list">
            @foreach ($latestNews->take(4) as $i => $article)
            <li>
              <span class="trend-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
              <div class="trend-text">
                <a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a>
                <div class="trend-meta">{{ $article->category_label }} &middot; {{ $article->published_at?->format('j M') }}</div>
              </div>
            </li>
            @endforeach
          </ul>
          @endif
        </div>

        <div class="ad-slot ad-mpu">
          <span class="ad-eyebrow">Advertisement</span>
          <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
        </div>

        @php $topScorerLeague = $leagueTables[$defaultLeagueIndex]['league'] ?? null; @endphp
        @if($topScorerLeague)
        <div class="widget">
          <div class="widget-head"><h2>Top Scorer</h2></div>
          <p style="font-size:12px;color:var(--ink-faint);margin:-8px 0 12px;">{{ $topScorerLeague->name }}</p>
          @if($topScorer)
          <div class="top-scorer-row">
            <span class="crest crest-{{ $topScorer->team->crest_code }}" role="img" aria-label="{{ $topScorer->team->full_name }} badge"></span>
            <div>
              <a href="{{ $topScorer->prettyUrl() }}" class="top-scorer-name">{{ $topScorer->name }}</a>
              <div class="top-scorer-meta">{{ $topScorer->goals }} {{ Str::plural('goal', $topScorer->goals) }} &middot; {{ $topScorer->team->name }}</div>
            </div>
          </div>
          @else
          <p style="color:var(--ink-faint);font-size:13px;">No scoring data yet this season.</p>
          @endif
        </div>
        @endif
      </aside>
    </div>
  </div>

  <div class="wrap">
    <div class="section-head"><h2>Popular Competitions</h2></div>
    <div class="comp-strip">
      @foreach ($popularLeagues as $pl)
      <a href="{{ route('leagues.show', $pl['league']->slug) }}" class="comp-tile"@if($pl['color']) style="--comp-color:{{ $pl['color'] }};"@endif>
        <svg class="flag" role="img" aria-label="{{ $pl['league']->country }} flag"><use href="#flag-{{ $pl['league']->flag_code }}"></use></svg>
        <span class="comp-tile-name">{{ $pl['league']->name }}</span>
        <span class="comp-tile-meta">{{ $pl['league']->country }}</span>
        <span class="comp-tile-status{{ $pl['liveCount'] > 0 ? ' has-live' : '' }}"><span class="dot" aria-hidden="true"></span>{{ $pl['statusLabel'] }}</span>
      </a>
      @endforeach
    </div>
  </div>

  <div class="wrap content-grid" style="grid-template-columns:1fr;">
    <div class="content-main">

      <section aria-labelledby="fx-results-heading" id="results">
        <div class="section-head"><h2 id="fx-results-heading">Fixtures &amp; Results</h2></div>
        <div class="two-col">
          <div class="list-panel">
            <h3>Upcoming Fixtures</h3>
            @foreach ($upcomingFixtures as $fx)
            <a href="{{ $fx->prettyUrl() }}" class="fixture-row"><span class="fx-date">{{ strtoupper($fx->kickoff_at->format('D')) }}<br>{{ $fx->kickoff_at->format('j M') }}</span><span class="fx-teams"><span class="fx-team"><span class="crest crest-{{ $fx->homeTeam->crest_code }}" role="img" aria-label="{{ $fx->homeTeam->full_name }} badge"></span>{{ $fx->homeTeam->name }}</span><span class="vs">vs</span><span class="fx-team"><span class="crest crest-{{ $fx->awayTeam->crest_code }}" role="img" aria-label="{{ $fx->awayTeam->full_name }} badge"></span>{{ $fx->awayTeam->name }}</span></span><span class="fx-time"><span class="dot-waiting" aria-hidden="true"></span>{{ $fx->kickoff_at->format('H:i') }}</span></a>
            @endforeach
          </div>
          <div class="list-panel">
            <h3>Recent Results</h3>
            @foreach ($recentResults as $r)
            <a href="{{ $r->prettyUrl() }}" class="result-row"><span class="fx-teams"><span class="fx-team"><span class="crest crest-{{ $r->homeTeam->crest_code }}" role="img" aria-label="{{ $r->homeTeam->full_name }} badge"></span>{{ $r->homeTeam->name }}</span><span class="vs">vs</span><span class="fx-team"><span class="crest crest-{{ $r->awayTeam->crest_code }}" role="img" aria-label="{{ $r->awayTeam->full_name }} badge"></span>{{ $r->awayTeam->name }}</span></span><span class="rs-score">{{ $r->home_score }}&ndash;{{ $r->away_score }}</span></a>
            @endforeach
          </div>
        </div>
      </section>

      <div class="ad-slot ad-native">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">Native in-content unit</span>
      </div>

      <section aria-labelledby="standings-heading" id="tables">
        <div class="section-head"><h2 id="standings-heading">Standings Snapshot</h2></div>
        <div class="widget table-widget">
          <div class="table-tabs" role="tablist" aria-label="Select league table" style="flex-wrap:wrap;margin-bottom:14px;">
            @foreach ($leagueTables as $i => $t)
            <button class="ttab{{ $i === $defaultLeagueIndex ? ' is-active' : '' }}" data-table="home-lt-{{ $t['league']->slug }}" aria-selected="{{ $i === $defaultLeagueIndex ? 'true' : 'false' }}"><svg class="flag" role="img" aria-label="{{ $t['league']->country }} flag"><use href="#flag-{{ $t['league']->flag_code }}"></use></svg>{{ $t['league']->name }}</button>
            @endforeach
          </div>

          @foreach ($leagueTables as $i => $t)
          <div class="standings-panel{{ $i === $defaultLeagueIndex ? ' is-active' : '' }}" data-panel="home-lt-{{ $t['league']->slug }}">
            @unless ($t['hasStarted'])
            <p style="font-size:12.5px;color:var(--ink-muted);margin:0 0 10px;">Season hasn&rsquo;t kicked off yet &mdash; table will fill in once matches are played.</p>
            @endunless
            <table class="standings">
              <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>W</th><th>D</th><th>L</th><th>Pts</th></tr></thead>
              <tbody>
                @foreach ($t['standings'] as $s)
                <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>{{ $s->team->name }}</a></td><td>{{ $s->played }}</td><td>{{ $s->won }}</td><td>{{ $s->drawn }}</td><td>{{ $s->lost }}</td><td class="pts">{{ $s->points }}</td></tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endforeach

          <div class="table-legend">
            <span class="legend-item"><span class="legend-dot ucl"></span>Champions League</span>
            <span class="legend-item"><span class="legend-dot rel"></span>Relegation</span>
          </div>
        </div>
      </section>

      <section aria-labelledby="news-heading">
        <div class="section-head">
          <h2 id="news-heading">Latest Football News</h2>
          <a href="{{ route('news.index') }}" class="section-link">View all news
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
        @if($latestNews->isEmpty())
        <p style="color:var(--ink-faint);font-size:14.5px;">No published stories yet &mdash; check back soon.</p>
        @else
        @php
          $newsFeat = $latestNews->first();
          $newsMed = $latestNews->slice(1, 2);
          $newsTimeline = $latestNews->slice(3, 4);
        @endphp
        <div class="news-feature-grid">
          <a href="{{ route('news.show', $newsFeat->slug) }}" class="news-feat">
            <div class="media" aria-hidden="true">
              @if($newsFeat->image_url)
                <img src="{{ $newsFeat->image_url }}" alt="{{ $newsFeat->title }}" loading="lazy">
              @else
                <svg viewBox="0 0 200 150"><circle cx="160" cy="20" r="60" fill="#fff" fill-opacity=".08"/></svg>
              @endif
            </div>
            <span class="cat-tag {{ $newsFeat->category_badge_class }}">{{ $newsFeat->category_label }}</span>
            <h3>{{ $newsFeat->title }}</h3>
            <p class="news-dek">{{ $newsFeat->dek }}</p>
            <span class="news-meta">{{ $newsFeat->author }} &middot; {{ $newsFeat->published_at?->format('j M') }}</span>
          </a>

          @foreach ($newsMed as $nm)
          <a href="{{ route('news.show', $nm->slug) }}" class="news-med">
            <span class="cat-tag {{ $nm->category_badge_class }}">{{ $nm->category_label }}</span>
            <h3>{{ $nm->title }}</h3>
            <p class="news-dek">{{ $nm->dek }}</p>
          </a>
          @endforeach

          @if($newsTimeline->isNotEmpty())
          <div class="news-timeline">
            @foreach ($newsTimeline as $nt)
            @php
              $ntHours = (int) ($nt->published_at?->diffInHours(now()) ?? 0);
              $ntRel = $ntHours < 1 ? (int) $nt->published_at->diffInMinutes(now()).'m' : ($ntHours < 24 ? $ntHours.'h' : (int) $nt->published_at->diffInDays(now()).'d');
            @endphp
            <a href="{{ route('news.show', $nt->slug) }}" class="tl-item">
              <span class="tl-time">{{ $ntRel }}</span>
              <span class="tl-title">{{ $nt->title }}</span>
            </a>
            @endforeach
          </div>
          @endif
        </div>
        @endif
      </section>

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
            <h2 id="history-heading" style="font-size:clamp(1.4rem,1.15rem + 1vw,1.9rem);margin-top:8px;">The Evolution of the English Premier League</h2>
            <p class="pull-quote">From a single satellite broadcast deal to the most-watched domestic league on the planet &mdash; the story of how English football rebuilt itself.</p>
            <p style="color:var(--ink-muted);font-size:14.5px;line-height:1.65;max-width:58ch;">Three decades on, the competition's global footprint, revenue and quality of play have transformed beyond recognition. We trace the rule changes, broadcast deals and iconic seasons that built the modern game.</p>
            <a href="{{ route('leagues.show', 'premier-league') }}" class="btn btn-ghost" style="margin-top:18px;">Read the Full Feature</a>
          </div>
        </div>
      </section>

      <section aria-labelledby="transfers-heading">
        <div class="section-head">
          <h2 id="transfers-heading">Transfer Center</h2>
          <a href="{{ route('news.category', 'transfers') }}" class="section-link">View all transfers
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
        {{-- Real published transfer-news articles in a card/list shell,
             not the mockup's Player/From/To/Status table - that table
             used invented sample names and its own markup flagged it as
             a placeholder with no backing data model. This is the same
             shell treatment with real content instead. --}}
        <div class="transfer-card">
          @forelse ($transferNews as $article)
          <a href="{{ route('news.show', $article->slug) }}" class="transfer-row">
            <span class="cat-tag {{ $article->category_badge_class }}">{{ $article->category_label }}</span>
            <span class="transfer-row-title">{{ $article->title }}</span>
            <span class="transfer-row-date">{{ $article->published_at?->format('j M') }}</span>
          </a>
          @empty
          <p class="transfer-card-empty">No transfer news published yet &mdash; check back soon.</p>
          @endforelse
        </div>
      </section>

      <div class="two-col" style="align-items:start;">
        <div class="widget newsletter-widget">
          <h2>The Daily Briefing</h2>
          <p>Every score, every storyline, every morning &mdash; straight to your inbox.</p>
          <form class="nl-form" onsubmit="return false;">
            <input type="email" placeholder="you@email.com" required aria-label="Email address">
            <button class="btn btn-accent btn-block" type="submit">Sign Up Free</button>
          </form>
          <p class="nl-fine">No spam. Unsubscribe anytime.</p>
        </div>
        <div class="ad-slot ad-skyscraper" style="min-height:250px;">
          <span class="ad-eyebrow">Advertisement</span>
          <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
        </div>
      </div>

    </div>
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
