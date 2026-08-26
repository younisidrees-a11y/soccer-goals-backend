@extends('layouts.site')

@section('title', $league->name . ' Results ' . $league->season . ' | The Soccer Goals')
@section('meta_description', 'Every ' . $league->name . ' result so far this season, with full match reports and stats for each game.')
@section('meta_keywords', $league->name . ' results, ' . $league->name . ' scores, final scores, ' . $league->country . ' football results')
@section('canonical', route('results.show', $league->slug))
@section('og_title', $league->name . ' Results ' . $league->season . ' | The Soccer Goals')
@section('og_description', 'Every ' . $league->name . ' result so far this season.')

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('results.index') }}" style="color:#B9CBDA;">Results</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $league->name }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">{{ $league->country }} &middot; {{ $league->season }} Season</div>
          <h1 class="league-hero-title">{{ $league->name }} Results</h1>
          <div class="league-hero-meta">{{ $results->total() }} {{ Str::plural('result', $results->total()) }} so far</div>
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
      <h2>Full-Time Results</h2>
      <a href="{{ route('leagues.show', $league->slug) }}" class="section-link">{{ $league->name }} table
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    <div class="match-grid celebrate-results">
      @forelse ($results as $r)
      <a href="{{ $r->prettyUrl() }}" class="match-card">
        <div class="match-meta"><span class="match-comp">{{ $league->name }} &middot; {{ $r->venue }}</span><span class="match-status">Full-Time</span></div>
        <div class="match-teams">
          <div class="match-team"><div class="team-id"><span class="crest crest-{{ $r->homeTeam->crest_code }}" role="img" aria-label="{{ $r->homeTeam->full_name }} badge"></span><span class="team-name">{{ $r->homeTeam->name }}</span></div><span class="team-score{{ $r->home_score > $r->away_score ? ' winning' : '' }}">{{ $r->home_score }}</span></div>
          <div class="match-team"><div class="team-id"><span class="crest crest-{{ $r->awayTeam->crest_code }}" role="img" aria-label="{{ $r->awayTeam->full_name }} badge"></span><span class="team-name">{{ $r->awayTeam->name }}</span></div><span class="team-score{{ $r->away_score > $r->home_score ? ' winning' : '' }}">{{ $r->away_score }}</span></div>
        </div>
        <div class="match-venue">{{ $r->homeTeam->name }} played {{ $r->awayTeam->name }} at {{ $r->venue }}@if($r->referee), referee {{ $r->referee }}@endif, on {{ $r->kickoff_at->format('j F Y') }}.</div>
      </a>
      @empty
      <p style="color:var(--ink-faint);">No results yet.</p>
      @endforelse
    </div>

    @include('partials.pagination', ['paginator' => $results])
  </div>

@endsection
