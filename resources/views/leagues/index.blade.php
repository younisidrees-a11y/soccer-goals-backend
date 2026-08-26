@extends('layouts.site')

@php
  $leagueCount = $leagues->count();
  $clubCount = $leagues->sum('teams_count');
@endphp

@section('title', "All Leagues — {$leagueCount} Competitions, {$clubCount} Clubs | The Soccer Goals")
@section('meta_description', "Every league covered by The Soccer Goals in one place: {$leagueCount} competitions and {$clubCount} real clubs worldwide, plus today's matches across all of them.")
@section('meta_keywords', 'football leagues, all leagues, soccer competitions, club directory, today\'s football matches, Premier League, La Liga, Serie A, Bundesliga, Ligue 1, Saudi Pro League, Liga MX, Süper Lig, MLS')
@section('canonical', route('leagues.index'))
@section('og_title', "All Leagues — {$leagueCount} Competitions, {$clubCount} Clubs | The Soccer Goals")
@section('og_description', "Every league covered by The Soccer Goals in one place: {$leagueCount} competitions and {$clubCount} real clubs worldwide.")

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">All Leagues</span>
      </div>
      <div class="league-hero-inner" style="align-items:flex-start;">
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">Worldwide Coverage</div>
          <h1 class="league-hero-title">Every League, One Page</h1>
          <div class="league-hero-meta">{{ $leagueCount }} competitions &middot; {{ $clubCount }} real clubs &middot; updated live from the database</div>
        </div>
      </div>

      <div class="league-jump" role="navigation" aria-label="Jump to a league">
        @foreach ($leagues as $l)
        <a href="#league-{{ $l->slug }}" class="league-jump-chip"><svg class="flag" role="img" aria-label="{{ $l->country }} flag"><use href="#flag-{{ $l->flag_code }}"></use></svg>{{ $l->name }}</a>
        @endforeach
      </div>
    </div>
  </section>

  <div class="wrap">
    <div class="ad-slot ad-leaderboard">
      <span class="ad-eyebrow">Advertisement</span>
      <span class="ad-size">728 &times; 90 &middot; AdSense unit</span>
    </div>
  </div>

  <div class="wrap" style="padding-block:8px 48px;">

    <section aria-labelledby="today-heading" style="margin-bottom:44px;">
      <div class="section-head">
        <h2 id="today-heading">Today's Matches</h2>
        <span class="section-link" style="color:var(--ink-faint);font-weight:600;">{{ now()->format('l, j F Y') }}</span>
      </div>

      @if($todaysMatches->isEmpty())
        <p style="color:var(--ink-faint);font-size:14.5px;">No matches kicking off today across any covered league &mdash; check the jump links above for each league's full fixture list.</p>
      @else
        <div class="match-grid">
          @foreach ($todaysMatches as $tm)
          @php
            $tmShowScore = $tm->isFinal() || ($tm->isLive() && $tm->home_score !== null);
            $tmStatusLabel = $tm->isFinal() ? 'Full-Time' : ($tm->isLive() ? 'LIVE' : $tm->kickoff_at->format('H:i').' UTC');
          @endphp
          <a href="{{ $tm->prettyUrl() }}" class="match-card">
            <div class="match-meta"><span class="match-comp">{{ $tm->league->name }}@if($tm->venue) &middot; {{ $tm->venue }}@endif</span><span class="match-status{{ $tm->isLive() ? ' is-live' : '' }}">@unless($tm->isFinal() || $tm->isLive())<span class="dot-waiting" aria-hidden="true"></span>@endunless{{ $tmStatusLabel }}</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $tm->homeTeam->crest_code }}" role="img" aria-label="{{ $tm->homeTeam->full_name }} badge"></span><span class="team-name">{{ $tm->homeTeam->name }}</span></div>@if($tmShowScore)<span class="team-score{{ $tm->isFinal() && $tm->home_score > $tm->away_score ? ' winning' : '' }}">{{ $tm->home_score }}</span>@endif</div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $tm->awayTeam->crest_code }}" role="img" aria-label="{{ $tm->awayTeam->full_name }} badge"></span><span class="team-name">{{ $tm->awayTeam->name }}</span></div>@if($tmShowScore)<span class="team-score{{ $tm->isFinal() && $tm->away_score > $tm->home_score ? ' winning' : '' }}">{{ $tm->away_score }}</span>@endif</div>
            </div>
          </a>
          @endforeach
        </div>
      @endif
    </section>

    @foreach ($leagues as $league)
    <section aria-labelledby="league-{{ $league->slug }}-heading" id="league-{{ $league->slug }}" class="league-block">
      <div class="league-block-head">
        <span class="league-block-flag"><svg viewBox="0 0 25 15"><use href="#flag-{{ $league->flag_code }}"></use></svg></span>
        <div>
          <h2 id="league-{{ $league->slug }}-heading">{{ $league->name }}</h2>
          <p>{{ $league->country }} &middot; {{ $league->teams_count }} clubs &middot; {{ $league->season }} season</p>
        </div>
        <a href="{{ route('leagues.show', $league->slug) }}" class="btn btn-ghost btn-sm league-block-cta">Table &amp; Fixtures
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>

      <div class="team-directory-grid">
        @foreach ($league->teams as $team)
        <a href="{{ route('teams.show', $team->slug) }}" class="team-card">
          <span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge"></span>
          <span class="team-card-body">
            <span class="team-card-name">{{ $team->name }}</span>
            <span class="team-card-meta">{{ $team->founded_year ? 'Est. '.$team->founded_year : ($team->stadium ?: $league->name) }}</span>
          </span>
        </a>
        @endforeach
      </div>
    </section>
    @endforeach

  </div>

@endsection
