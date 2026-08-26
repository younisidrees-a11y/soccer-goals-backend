@extends('layouts.site')

@section('title', $team->meta_title ?: $team->full_name . ' — Fixtures, Squad, Table & News | The Soccer Goals')
@section('meta_description', $team->meta_description ?: $team->full_name . ' fixtures, results, squad list and latest news for the ' . $team->league->season . ' season, plus their current ' . $team->league->name . ' table position.')
@section('meta_keywords', $team->meta_keywords ?: $team->name . ', ' . $team->full_name . ', ' . $team->league->name . ', football club, squad, fixtures, results' . ($team->stadium ? ', ' . $team->stadium : '') . ($team->manager ? ', ' . $team->manager : ''))
@section('canonical', route('teams.show', $team->slug))
@section('og_title', $team->meta_title ?: $team->full_name . ' — Fixtures, Squad, Table & News | The Soccer Goals')
@section('og_description', $team->meta_description ?: $team->full_name . ' fixtures, results, squad list and latest news for the ' . $team->league->season . ' season, plus their current ' . $team->league->name . ' table position.')

@php
  $ordinal = function (int $n) {
      return $n . match (true) {
          in_array($n % 100, [11, 12, 13]) => 'th',
          $n % 10 === 1 => 'st',
          $n % 10 === 2 => 'nd',
          $n % 10 === 3 => 'rd',
          default => 'th',
      };
  };
  $lastResult = $recentMatches->first();
  $form = null;
  if ($lastResult) {
      $wasHome = $lastResult->home_team_id === $team->id;
      $forGoals = $wasHome ? $lastResult->home_score : $lastResult->away_score;
      $againstGoals = $wasHome ? $lastResult->away_score : $lastResult->home_score;
      $form = $forGoals > $againstGoals ? 'w' : ($forGoals < $againstGoals ? 'l' : 'd');
  }
@endphp

@section('content')

  <section class="team-hero" style="--team-color:{{ $team->color_hex }};">
    <div class="wrap">
      <div class="breadcrumb" style="color:rgba(255,255,255,.6);">
        <a href="{{ route('home') }}" style="color:rgba(255,255,255,.85);">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('leagues.show', $team->league->slug) }}" style="color:rgba(255,255,255,.85);">{{ $team->league->name }}</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $team->name }}</span>
      </div>
      <div class="team-hero-inner">
        <span class="team-hero-crest crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" aria-hidden="false"></span>
        <div>
          <div class="team-hero-badge">
            <svg class="flag" role="img" aria-label="{{ $team->league->country }} flag"><use href="#flag-{{ $team->league->flag_code }}"></use></svg>
            {{ $team->league->name }} @if($standing) &middot; {{ $ordinal($standing->position) }} Place @endif
          </div>
          <h1 class="team-hero-title">{{ $team->full_name }}</h1>
          <div class="team-hero-meta">
            @if($team->stadium)
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>{{ $team->stadium }}@if($team->stadium_capacity) &middot; {{ $team->stadium_capacity }} capacity @endif</span>
            @endif
            @if($team->manager)
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>Manager: {{ $team->manager }}</span>
            @endif
            @if($team->founded_year)
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/><circle cx="12" cy="12" r="4"/></svg>Founded {{ $team->founded_year }}</span>
            @endif
          </div>
        </div>
      </div>

      @if($standing)
      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">League Position</div>
          <div class="stat-value">{{ $ordinal($standing->position) }} &middot; {{ $standing->points }} pts</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Goal Difference</div>
          <div class="stat-value">{{ $standing->goal_difference > 0 ? '+' : '' }}{{ $standing->goal_difference }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Form</div>
          <div class="stat-value">@if($form)<span class="form-chip {{ $form }}" style="width:22px;height:22px;">{{ strtoupper($form) }}</span>@else &mdash; @endif</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">{{ $nextFixture?->isLive() ? 'Playing Now' : 'Next Fixture' }}</div>
          <div class="stat-value" style="font-size:15px;">@if($nextFixture?->isLive())<span style="color:var(--live);">LIVE</span>@else{{ $nextFixture?->kickoff_at->format('D, j M') ?? 'TBC' }}@endif</div>
        </div>
      </div>
      @endif
    </div>
  </section>

  <div class="wrap">
    <div class="ad-slot ad-leaderboard">
      <span class="ad-eyebrow">Advertisement</span>
      <span class="ad-size">728 &times; 90 &middot; AdSense unit</span>
    </div>
  </div>

  <div class="wrap content-grid">
    <div class="content-main" style="--team-color:{{ $team->color_hex }};">

      <section aria-labelledby="about-heading" class="essay-block">
        <h2 id="about-heading">About {{ $team->full_name }}</h2>
        @if($team->history_essay)
          @foreach (explode("\n", $team->history_essay) as $paragraph)
            @continue(trim($paragraph) === '')
            <p>{{ trim($paragraph) }}</p>
          @endforeach
        @else
          @php
            $about = $team->full_name . ' compete in the ' . $team->league->name;
            $about .= $team->founded_year ? ', having been founded in ' . $team->founded_year . '.' : '.';
            if ($team->stadium) {
                $about .= ' They play their home matches at ' . $team->stadium;
                $about .= $team->stadium_capacity ? ', which holds ' . $team->stadium_capacity . ' supporters.' : '.';
            }
          @endphp
          <p class="lede">{{ $about }}</p>
        @endif
      </section>

      @if($team->manager)
      <section aria-labelledby="coach-heading" class="essay-block" style="margin-top:28px;">
        <h2 id="coach-heading">Head Coach</h2>
        <div style="display:flex;align-items:flex-start;gap:16px;">
          @if($team->manager_photo_url)
            <img src="{{ $team->manager_photo_url }}" alt="{{ $team->manager }}" width="72" height="72" style="width:72px;height:72px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--team-color);">
          @else
            <div style="width:72px;height:72px;border-radius:50%;background:var(--team-color);flex-shrink:0;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:22px;">{{ Str::of($team->manager)->explode(' ')->map(fn($w) => mb_substr($w,0,1))->join('') }}</div>
          @endif
          <div>
            <p style="font-weight:600;margin:0 0 6px;">{{ $team->manager }}</p>
            @if($team->manager_bio)
              <p style="margin:0;">{{ $team->manager_bio }}</p>
            @else
              <p style="margin:0;color:var(--ink-muted);">Head coach of {{ $team->name }}.</p>
            @endif
          </div>
        </div>
      </section>
      @endif

      @if($team->honours_facts)
      <section aria-labelledby="honours-heading" class="essay-block" style="margin-top:28px;">
        <h2 id="honours-heading">Trophies &amp; Honours</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:10px;">
          @foreach (explode("\n", $team->honours_facts) as $line)
            @continue(trim($line) === '')
            @php [$competition, $detail] = array_pad(explode(':', trim($line), 2), 2, ''); @endphp
            <li style="display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid var(--border);">
              <span style="font-weight:600;">{{ trim($competition) }}</span>
              <span style="color:var(--ink-muted);text-align:right;">{{ trim($detail) }}</span>
            </li>
          @endforeach
        </ul>
      </section>
      @endif

      @if($topScorers->isNotEmpty())
      <section aria-labelledby="scorers-heading" id="scorers" style="--team-color:{{ $team->color_hex }};">
        <div class="section-head">
          <h2 id="scorers-heading">Top Scorers</h2>
          <span class="section-link" style="cursor:default;color:var(--ink-faint);font-weight:600;">Season {{ $team->league->season }}</span>
        </div>
        <table class="scorers-table">
          <thead><tr><th>Player</th><th>Position</th><th class="num">Goals</th><th class="num">Assists</th><th class="num">G+A</th></tr></thead>
          <tbody>
            @foreach ($topScorers as $i => $p)
            <tr>
              <td><div class="player-cell"><span class="player-rank">{{ $i + 1 }}</span>{{ $p->name }}</div></td>
              <td style="color:var(--ink-faint);font-size:12.5px;">{{ $p->position }}</td>
              <td class="num">{{ $p->goals }}</td>
              <td class="num">{{ $p->assists }}</td>
              <td class="num" style="color:var(--team-color);">{{ $p->goals + $p->assists }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </section>
      @endif

      <section aria-labelledby="fixtures-heading" id="fixtures">
        <div class="section-head"><h2 id="fixtures-heading">Fixtures &amp; Results</h2></div>
        <div class="two-col">
          <div class="list-panel">
            <h3>Upcoming</h3>
            @forelse ($upcomingMatches as $fx)
            @php $isHome = $fx->home_team_id === $team->id; $opponent = $isHome ? $fx->awayTeam : $fx->homeTeam; @endphp
            <a href="{{ route('matches.show', $fx->id) }}" class="fixture-row"><span class="fx-date">{{ strtoupper($fx->kickoff_at->format('D')) }}<br>{{ $fx->kickoff_at->format('j M') }}</span><span class="fx-teams">@if($isHome)<span class="fx-team">{{ $team->name }}</span><span class="vs">vs</span><span class="fx-team"><span class="crest crest-{{ $opponent->crest_code }}" role="img" aria-label="{{ $opponent->full_name }} badge"></span>{{ $opponent->name }}</span>@else<span class="fx-team"><span class="crest crest-{{ $opponent->crest_code }}" role="img" aria-label="{{ $opponent->full_name }} badge"></span>{{ $opponent->name }}</span><span class="vs">vs</span><span class="fx-team">{{ $team->name }}</span>@endif</span>@if($fx->isLive())<span class="fx-time" style="color:var(--live);">{{ $fx->home_score !== null ? "{$fx->home_score}\u{2013}{$fx->away_score} " : '' }}LIVE</span>@else<span class="fx-time"><span class="dot-waiting" aria-hidden="true"></span>{{ $fx->kickoff_at->format('H:i') }}</span>@endif</a>
            @empty
            <p style="font-size:13.5px;color:var(--ink-faint);">No upcoming fixtures scheduled yet.</p>
            @endforelse
          </div>
          <div class="list-panel">
            <h3>Recent Results</h3>
            @forelse ($recentMatches as $r)
            @php
              $isHome = $r->home_team_id === $team->id;
              $opponent = $isHome ? $r->awayTeam : $r->homeTeam;
              $isLatestWin = $loop->first && ($isHome ? $r->home_score > $r->away_score : $r->away_score > $r->home_score);
            @endphp
            <a href="{{ route('matches.show', $r->id) }}" class="result-row{{ $isLatestWin ? ' celebrate-team-win' : '' }}"><span class="fx-teams">@if($isHome)<span class="fx-team">{{ $team->name }}</span><span class="vs">vs</span><span class="fx-team"><span class="crest crest-{{ $opponent->crest_code }}" role="img" aria-label="{{ $opponent->full_name }} badge"></span>{{ $opponent->name }}</span>@else<span class="fx-team"><span class="crest crest-{{ $opponent->crest_code }}" role="img" aria-label="{{ $opponent->full_name }} badge"></span>{{ $opponent->name }}</span><span class="vs">vs</span><span class="fx-team">{{ $team->name }}</span>@endif</span><span class="rs-score">{{ $r->home_score }}&ndash;{{ $r->away_score }}</span></a>
            @empty
            <p style="font-size:13.5px;color:var(--ink-faint);">No results yet.</p>
            @endforelse
          </div>
        </div>
      </section>

      <section aria-labelledby="squad-heading" id="squad">
        <div class="section-head"><h2 id="squad-heading">Squad</h2></div>

        @foreach ($squadByPosition as $label => $players)
        @if($players->isNotEmpty())
        <div class="squad-position-title">{{ $label }}</div>
        <div class="squad-grid">
          @foreach ($players as $p)
          <div class="player-card{{ $p->is_captain ? ' is-captain' : '' }}"><span class="player-number">{{ $p->shirt_number }}</span><div><div class="player-name">{{ $p->name }}@if($p->is_captain) <span class="cap-tag">(C)</span>@endif</div><div class="player-role">{{ $p->position }}</div></div></div>
          @endforeach
        </div>
        @endif
        @endforeach
      </section>

      <div class="ad-slot ad-native">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">Native in-content unit</span>
      </div>

      <section aria-labelledby="news-heading">
        <div class="section-head">
          <h2 id="news-heading">{{ $team->name }} News</h2>
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
          <p>No published {{ $team->name }} stories yet &mdash; check back soon.</p>
          @endforelse
        </div>
      </section>

    </div>

    <aside class="sidebar" aria-label="Sidebar" style="--team-color:{{ $team->color_hex }};">
      <div class="widget table-widget" id="tables">
        <div class="widget-head">
          <h2>{{ $team->league->name }} Table</h2>
        </div>

        <table class="standings">
          <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>Pts</th></tr></thead>
          <tbody>
            @foreach ($leagueStandings->take(5) as $s)
            <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"@if($s->team_id === $team->id) style="background:color-mix(in srgb, var(--team-color) 8%, transparent);"@endif><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>@if($s->team_id === $team->id)<strong>{{ $s->team->name }}</strong>@else{{ $s->team->name }}@endif</a></td><td>{{ $s->played }}</td><td class="pts">{{ $s->points }}</td></tr>
            @endforeach
          </tbody>
        </table>
        <a href="{{ route('leagues.show', $team->league->slug) }}" class="section-link" style="margin-top:14px;display:inline-flex;">Full table
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
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
