@extends('layouts.site')

@php $todayLabel = now()->format('l, j F Y'); @endphp
@section('title', "Today's Matches — {$todayLabel} | The Soccer Goals")
@section('meta_description', "Every match kicking off today, {$todayLabel}, across all leagues covered by The Soccer Goals — live scores, kick-off times and full-time results in one place.")
@section('meta_keywords', "today's football matches, live scores, {$todayLabel} fixtures, football schedule today")
@section('canonical', route('today.index'))
@section('og_title', "Today's Matches — {$todayLabel} | The Soccer Goals")
@section('og_description', "Every match kicking off today across all leagues covered by The Soccer Goals.")

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#9299AA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">Today's Matches</span>
      </div>
      <div class="league-hero-inner" style="align-items:flex-start;">
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB4FF;">{{ $todayLabel }}</div>
          <h1 class="league-hero-title">Today's Matches</h1>
          <div class="league-hero-meta">{{ $matches->total() }} {{ Str::plural('match', $matches->total()) }} today across every covered league</div>
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
      <h2>Full Schedule</h2>
      <a href="{{ route('fixtures.index') }}" class="section-link">Upcoming fixtures
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    @if($matches->isEmpty())
    <p style="color:var(--ink-faint);font-size:14.5px;">No matches kicking off today across any covered league &mdash; check <a href="{{ route('fixtures.index') }}">upcoming fixtures</a> instead.</p>
    @else
    <div class="match-grid">
      @foreach ($matches as $m)
      @php
        $mShowScore = $m->isFinal() || ($m->isLive() && $m->home_score !== null);
        $mStatusLabel = $m->isFinal() ? 'Full-Time' : ($m->isLive() ? 'LIVE' : $m->kickoff_at->format('H:i').' UTC');
      @endphp
      <a href="{{ $m->prettyUrl() }}" class="match-card">
        <div class="match-meta"><span class="match-comp">{{ $m->league->name }} &middot; {{ $m->venue }}</span><span class="match-status{{ $m->isLive() ? ' is-live' : '' }}">@unless($m->isFinal() || $m->isLive())<span class="dot-waiting" aria-hidden="true"></span>@endunless{{ $mStatusLabel }}</span></div>
        <div class="match-teams">
          <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->homeTeam->crest_code }}" role="img" aria-label="{{ $m->homeTeam->full_name }} badge"></span><span class="team-name">{{ $m->homeTeam->name }}</span></div>@if($mShowScore)<span class="team-score{{ $m->isFinal() && $m->home_score > $m->away_score ? ' winning' : '' }}">{{ $m->home_score }}</span>@endif</div>
          <div class="match-team"><div class="team-id"><span class="crest crest-{{ $m->awayTeam->crest_code }}" role="img" aria-label="{{ $m->awayTeam->full_name }} badge"></span><span class="team-name">{{ $m->awayTeam->name }}</span></div>@if($mShowScore)<span class="team-score{{ $m->isFinal() && $m->away_score > $m->home_score ? ' winning' : '' }}">{{ $m->away_score }}</span>@endif</div>
        </div>
        <div class="match-venue">{{ $m->venue }}</div>
      </a>
      @endforeach
    </div>

    @include('partials.pagination', ['paginator' => $matches])
    @endif
  </div>

@endsection
