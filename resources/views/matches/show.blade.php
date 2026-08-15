@extends('layouts.site')

@php
  $title = $match->homeTeam->name . ' vs ' . $match->awayTeam->name;
  $isFinal = $match->status === 'final';
  $year = $match->kickoff_at->format('Y');
  $kickoffTime = $match->kickoff_at->format('H:i');
  $dateLong = $match->kickoff_at->format('j M Y');

  $defaultTitle = $isFinal
      ? "{$title} {$match->home_score}-{$match->away_score} — {$year} Match Result & Report | The Soccer Goals"
      : "{$title} — {$year} Fixture, Match Kick-off {$kickoffTime} | The Soccer Goals";

  $defaultDescription = $isFinal
      ? "{$title} {$match->home_score}-{$match->away_score}: full match report, stats and result from {$match->venue}, {$dateLong}. {$match->league->name} {$match->league->season} season."
      : "{$match->homeTeam->name} host {$match->awayTeam->name} at {$match->venue} on {$dateLong}, kick-off {$kickoffTime}. Team news, form and match preview. {$match->league->name} {$match->league->season} season.";

  $defaultKeywords = "{$match->homeTeam->name}, {$match->awayTeam->name}, {$title}, {$match->league->name}"
      . ($isFinal ? ', match report, final score, result' : ', fixture, match preview, kick off time');
@endphp

@section('title', $match->meta_title ?: $defaultTitle)
@section('meta_description', $match->meta_description ?: $defaultDescription)
@section('meta_keywords', $match->meta_keywords ?: $defaultKeywords)
@section('canonical', route('matches.show', $match->id))
@section('og_title', $match->meta_title ?: $defaultTitle)
@section('og_description', $match->meta_description ?: $defaultDescription)

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('leagues.show', $match->league->slug) }}" style="color:#B9CBDA;">{{ $match->league->name }}</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $title }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $match->league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">{{ $match->league->name }}@if($match->venue) &middot; {{ $match->venue }}@endif</div>
          <h1 class="league-hero-title">{{ $title }}</h1>
          <div class="league-hero-meta">{{ $match->kickoff_at->format('D j M Y') }} &middot; {{ $isFinal ? 'Full-Time' : $match->kickoff_at->format('H:i') . ' kick-off' }}</div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">Date</div>
          <div class="stat-value">{{ $match->kickoff_at->format('D j M Y') }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">{{ $isFinal ? 'Status' : 'Kick-off' }}</div>
          <div class="stat-value">{{ $isFinal ? 'Full-Time' : '' }}@unless($isFinal)<span class="dot-waiting" aria-hidden="true"></span>{{ $match->kickoff_at->format('H:i') }}@endunless</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Venue</div>
          <div class="stat-value" style="font-size:15px;">{{ $match->venue ?? 'TBC' }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Result</div>
          <div class="stat-value">{{ $isFinal ? $match->home_score . '-' . $match->away_score : 'Not yet played' }}</div>
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

      @if($isFinal)

        <section class="detail-closing" aria-labelledby="report-heading">
          <h2 id="report-heading">Match Report</h2>
          <p>{{ $match->match_report }}</p>
        </section>

      @else

        <section aria-labelledby="preview-heading">
          <div class="section-head"><h2 id="preview-heading">Match Preview</h2></div>
          <div style="display:grid;gap:14px;grid-template-columns:1fr;">
            <div class="widget">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge" style="width:28px;height:31px;"></span>
                <strong>{{ $match->homeTeam->name }}</strong>
                @if($homeStanding)<span style="font-size:12.5px;color:var(--ink-faint);">&middot; {{ $homeStanding->position }}{{ match(true){ in_array($homeStanding->position % 100, [11,12,13]) => 'th', $homeStanding->position % 10 === 1 => 'st', $homeStanding->position % 10 === 2 => 'nd', $homeStanding->position % 10 === 3 => 'rd', default => 'th' } }} &middot; {{ $homeStanding->points }} pts</span>@endif
              </div>
              <p style="font-size:14px;color:var(--ink-muted);line-height:1.6;margin:0;">{{ $match->home_preview_note }}</p>
            </div>
            <div class="widget">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge" style="width:28px;height:31px;"></span>
                <strong>{{ $match->awayTeam->name }}</strong>
                @if($awayStanding)<span style="font-size:12.5px;color:var(--ink-faint);">&middot; {{ $awayStanding->position }}{{ match(true){ in_array($awayStanding->position % 100, [11,12,13]) => 'th', $awayStanding->position % 10 === 1 => 'st', $awayStanding->position % 10 === 2 => 'nd', $awayStanding->position % 10 === 3 => 'rd', default => 'th' } }} &middot; {{ $awayStanding->points }} pts</span>@endif
              </div>
              <p style="font-size:14px;color:var(--ink-muted);line-height:1.6;margin:0;">{{ $match->away_preview_note }}</p>
            </div>
          </div>
        </section>

      @endif

      <section aria-labelledby="matchup-heading" style="margin-top:32px;">
        <div class="section-head"><h2 id="matchup-heading">The Matchup</h2></div>
        <div class="match-grid{{ $isFinal ? ' celebrate-match' : '' }}">
          <div class="match-card" style="grid-column:1/-1;">
            <div class="match-meta"><span class="match-comp">{{ $match->league->name }}@if($match->venue) &middot; {{ $match->venue }}@endif</span><span class="match-status">{{ $isFinal ? 'Full-Time' : '' }}@unless($isFinal)<span class="dot-waiting" aria-hidden="true"></span>{{ $match->kickoff_at->format('D j M Y, H:i') }}@endunless</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge"></span><span class="team-name">{{ $match->homeTeam->name }} <span style="color:var(--ink-faint);font-weight:500;">(Home)</span></span></div>@if($isFinal)<span class="team-score{{ $match->home_score > $match->away_score ? ' winning' : '' }}">{{ $match->home_score }}</span>@endif</div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge"></span><span class="team-name">{{ $match->awayTeam->name }} <span style="color:var(--ink-faint);font-weight:500;">(Away)</span></span></div>@if($isFinal)<span class="team-score{{ $match->away_score > $match->home_score ? ' winning' : '' }}">{{ $match->away_score }}</span>@endif</div>
            </div>
          </div>
        </div>
      </section>

      @if($isFinal)

        @if($match->stats)
        <section aria-labelledby="stats-heading" style="margin-top:32px;">
          <div class="section-head"><h2 id="stats-heading">Match Statistics</h2></div>
          <p style="font-size:12.5px;color:var(--ink-faint);margin-top:-8px;margin-bottom:16px;">{{ $match->homeTeam->name }} (home) vs {{ $match->awayTeam->name }} (away)</p>
          <div class="stat-bullets">
            @php $poss = $match->stats['possession'] ?? null; @endphp
            @if($poss)
            <div class="stat-bullet-row">
              <span class="stat-bullet-label">Possession</span>
              <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ $poss['home'] }}%;"></span></span>
              <span class="stat-bullet-value">{{ $poss['home'] }}%</span>
            </div>
            @endif
            @foreach (['shots' => 'Shots', 'shots_on_target' => 'Shots on Target', 'corners' => 'Corners', 'fouls' => 'Fouls'] as $key => $label)
              @if(isset($match->stats[$key]))
              @php
                $homeVal = $match->stats[$key]['home'];
                $awayVal = $match->stats[$key]['away'];
                $total = $homeVal + $awayVal ?: 1;
              @endphp
              <div class="stat-bullet-row">
                <span class="stat-bullet-label">{{ $label }}</span>
                <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ round($homeVal / $total * 100) }}%;"></span></span>
                <span class="stat-bullet-value">{{ $homeVal }}&ndash;{{ $awayVal }}</span>
              </div>
              @endif
            @endforeach
          </div>
        </section>
        @endif

        <section aria-labelledby="next-heading" style="margin-top:32px;">
          <div class="section-head"><h2 id="next-heading">What's Next</h2></div>
          <div style="display:grid;gap:14px;grid-template-columns:1fr;">
            @foreach ([[$match->homeTeam, $homeNext], [$match->awayTeam, $awayNext]] as [$team, $next])
            <div class="widget" style="display:flex;align-items:center;gap:12px;">
              <span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" style="width:32px;height:35px;"></span>
              <div style="flex:1;">
                <strong>{{ $team->name }}</strong>
                <div style="font-size:13px;color:var(--ink-faint);margin-top:2px;">
                  @if($next)
                    Next: vs {{ $next->home_team_id === $team->id ? $next->awayTeam->name : $next->homeTeam->name }} ({{ $next->home_team_id === $team->id ? 'H' : 'A' }}) &middot; {{ $next->venue }} &middot; {{ $next->kickoff_at->format('D j M') }}
                  @else
                    No further fixtures scheduled.
                  @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </section>

      @endif

    </div>

    <aside class="sidebar" aria-label="Sidebar">
      <div class="widget">
        <h2 style="margin-bottom:14px;">This Fixture</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <a href="{{ route('teams.show', $match->homeTeam->slug) }}" class="btn btn-ghost btn-block">{{ $match->homeTeam->name }} Team Page</a>
          <a href="{{ route('teams.show', $match->awayTeam->slug) }}" class="btn btn-ghost btn-block">{{ $match->awayTeam->name }} Team Page</a>
          <a href="{{ route('leagues.show', $match->league->slug) }}" class="btn btn-ghost btn-block">{{ $match->league->name }} Table &amp; Results</a>
        </div>
      </div>

      <div class="ad-slot ad-mpu">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
      </div>

      <div class="ad-slot ad-skyscraper">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 600 &middot; AdSense unit</span>
      </div>
    </aside>
  </div>

@endsection
