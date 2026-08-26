@extends('layouts.site')

@section('title', $league->name . ' Fixtures ' . $league->season . ' | The Soccer Goals')
@section('meta_description', 'Every upcoming ' . $league->name . ' fixture for the ' . $league->season . ' season, with kick-off time, venue and match preview for each game.')
@section('meta_keywords', $league->name . ' fixtures, ' . $league->name . ' schedule, upcoming matches, ' . $league->country . ' football fixtures')
@section('canonical', route('fixtures.show', $league->slug))
@section('og_title', $league->name . ' Fixtures ' . $league->season . ' | The Soccer Goals')
@section('og_description', 'Every upcoming ' . $league->name . ' fixture for the ' . $league->season . ' season.')

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('fixtures.index') }}" style="color:#B9CBDA;">Fixtures</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $league->name }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">{{ $league->country }} &middot; {{ $league->season }} Season</div>
          <h1 class="league-hero-title">{{ $league->name }} Fixtures</h1>
          <div class="league-hero-meta">{{ $fixtures->total() }} upcoming {{ Str::plural('fixture', $fixtures->total()) }}</div>
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

  <div class="wrap" style="padding-block:36px 64px;">
    <div class="section-head">
      <h2>Upcoming Fixtures</h2>
      <a href="{{ route('leagues.show', $league->slug) }}" class="section-link">{{ $league->name }} table
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    <div class="match-grid">
      @forelse ($fixtures as $fx)
      <a href="{{ route('matches.show', $fx->id) }}" class="match-card">
        <div class="match-meta"><span class="match-comp">{{ $league->name }} &middot; {{ $fx->venue }}</span><span class="match-status{{ $fx->isLive() ? ' is-live' : '' }}">@if($fx->isLive())LIVE@else<span class="dot-waiting" aria-hidden="true"></span>{{ $fx->kickoff_at->format('D j M, H:i') }}@endif</span></div>
        <div class="match-teams">
          @php $fxShowLiveScore = $fx->isLive() && $fx->home_score !== null; @endphp
          <div class="match-team"><div class="team-id"><span class="crest crest-{{ $fx->homeTeam->crest_code }}" role="img" aria-label="{{ $fx->homeTeam->full_name }} badge"></span><span class="team-name">{{ $fx->homeTeam->name }}</span></div>@if($fxShowLiveScore)<span class="team-score">{{ $fx->home_score }}</span>@endif</div>
          <div class="match-team"><div class="team-id"><span class="crest crest-{{ $fx->awayTeam->crest_code }}" role="img" aria-label="{{ $fx->awayTeam->full_name }} badge"></span><span class="team-name">{{ $fx->awayTeam->name }}</span></div>@if($fxShowLiveScore)<span class="team-score">{{ $fx->away_score }}</span>@endif</div>
        </div>
        <div class="match-venue">{{ $fx->homeTeam->name }} play {{ $fx->awayTeam->name }} at {{ $fx->venue }}@if($fx->referee), referee {{ $fx->referee }}@endif, kicking off {{ $fx->kickoff_at->format('j F Y') }} at {{ $fx->kickoff_at->format('H:i') }} UTC.</div>
      </a>
      @empty
      <p style="color:var(--ink-faint);">No upcoming fixtures scheduled.</p>
      @endforelse
    </div>

    @include('partials.pagination', ['paginator' => $fixtures])
  </div>

@endsection
