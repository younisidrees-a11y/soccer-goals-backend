@extends('layouts.site')

@php
  $team = $player->team;
  $league = $team->league;
  $stats = $player->stats ?? [];
@endphp

@section('title', $player->name . ' — ' . $team->name . ' Player Profile, Stats & Bio | The Soccer Goals')
@section('meta_description', $player->meta_description ?: $player->name . ', ' . $player->position . ' for ' . $team->name . ' in the ' . $league->name . '. Real stats, bio and career info.')
@section('meta_keywords', $player->name . ', ' . $team->name . ', ' . $league->name . ', ' . $player->position . ', football player, player profile, player stats')
@section('canonical', $player->prettyUrl())
@section('og_title', $player->name . ' — ' . $team->name . ' Player Profile | The Soccer Goals')
@section('og_description', $player->name . ', ' . $player->position . ' for ' . $team->name . ' in the ' . $league->name . '.')
@if($player->photo_url)
@section('og_image', $player->photo_url)
@endif

@section('content')

  <section class="team-hero" style="--team-color:{{ $team->color_hex }};">
    <div class="wrap">
      <div class="breadcrumb" style="color:rgba(255,255,255,.6);">
        <a href="{{ route('home') }}" style="color:rgba(255,255,255,.85);">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('teams.show', $team->slug) }}" style="color:rgba(255,255,255,.85);">{{ $team->name }}</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $player->name }}</span>
      </div>
      <div class="team-hero-inner">
        @if($player->photo_url)
          <img src="{{ $player->photo_url }}" alt="{{ $player->name }}" class="team-hero-crest" style="border-radius:50%;object-fit:cover;background:rgba(255,255,255,.12);">
        @else
          <div class="team-hero-crest" style="border-radius:50%;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:28px;">{{ Str::of($player->name)->explode(' ')->map(fn($w) => mb_substr($w,0,1))->join('') }}</div>
        @endif
        <div>
          <div class="team-hero-badge">
            <span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" style="width:16px;height:16px;"></span>
            {{ $team->name }} &middot; {{ $league->name }}
          </div>
          <h1 class="team-hero-title">{{ $player->name }}@if($player->is_captain) <span style="font-size:0.5em;vertical-align:middle;">(C)</span>@endif</h1>
          <div class="team-hero-meta">
            <span>{{ $player->position }}@if($player->shirt_number) &middot; #{{ $player->shirt_number }}@endif</span>
            @if($player->nationality)<span>{{ $player->nationality }}</span>@endif
            @if($player->age)<span>Age {{ $player->age }}</span>@endif
            @if($player->injured)<span style="color:var(--live);font-weight:700;">Currently Injured</span>@endif
          </div>
        </div>
      </div>

      @if(!empty($stats))
      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">Appearances</div>
          <div class="stat-value">{{ $stats['appearances'] ?? '—' }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Goals</div>
          <div class="stat-value">{{ $player->goals }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Assists</div>
          <div class="stat-value">{{ $player->assists }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Avg. Rating</div>
          <div class="stat-value">{{ $stats['rating'] ?? '—' }}</div>
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

      <section aria-labelledby="bio-heading" class="essay-block">
        <h2 id="bio-heading">Biography</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:0;">
          @if($player->birth_date)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Date of Birth</span><strong>{{ $player->birth_date->format('j F Y') }} ({{ $player->age }} years old)</strong></li>
          @endif
          @if($player->birth_place || $player->birth_country)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Place of Birth</span><strong>{{ trim(($player->birth_place ?? '') . (($player->birth_place && $player->birth_country) ? ', ' : '') . ($player->birth_country ?? '')) }}</strong></li>
          @endif
          @if($player->nationality)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Nationality</span><strong>{{ $player->nationality }}</strong></li>
          @endif
          @if($player->height)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Height</span><strong>{{ $player->height }}</strong></li>
          @endif
          @if($player->weight)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Weight</span><strong>{{ $player->weight }}</strong></li>
          @endif
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Position</span><strong>{{ $player->position }}</strong></li>
          @if($player->shirt_number)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Shirt Number</span><strong>#{{ $player->shirt_number }}</strong></li>
          @endif
          <li style="display:flex;justify-content:space-between;padding:10px 0;"><span style="color:var(--ink-faint);">Current Club</span><strong><a href="{{ route('teams.show', $team->slug) }}" style="color:var(--team-color);">{{ $team->name }}</a></strong></li>
        </ul>
      </section>

      @if(!empty($stats))
      <section aria-labelledby="stats-heading" class="essay-block" style="margin-top:28px;">
        <h2 id="stats-heading">Season Stats {{ $league->season }}</h2>
        <div class="team-directory-grid" style="grid-template-columns:repeat(2,1fr);">
          @foreach ([
              'Minutes Played' => $stats['minutes'] ?? null,
              'Shots (On Target)' => isset($stats['shots_total']) ? $stats['shots_total'].' ('.($stats['shots_on_target'] ?? 0).')' : null,
              'Passes (Accuracy)' => isset($stats['passes_total']) ? $stats['passes_total'].($stats['passes_accuracy'] ? ' ('.$stats['passes_accuracy'].'%)' : '') : null,
              'Key Passes' => $stats['passes_key'] ?? null,
              'Tackles' => $stats['tackles_total'] ?? null,
              'Interceptions' => $stats['interceptions'] ?? null,
              'Duels Won' => isset($stats['duels_total']) ? ($stats['duels_won'] ?? 0).' / '.$stats['duels_total'] : null,
              'Dribbles (Success)' => isset($stats['dribbles_attempts']) ? $stats['dribbles_attempts'].' ('.($stats['dribbles_success'] ?? 0).')' : null,
              'Fouls Drawn' => $stats['fouls_drawn'] ?? null,
              'Fouls Committed' => $stats['fouls_committed'] ?? null,
              'Yellow Cards' => $stats['yellow_cards'] ?? null,
              'Red Cards' => $stats['red_cards'] ?? null,
              'Saves' => $stats['saves'] ?? null,
          ] as $label => $value)
            @continue($value === null)
            <div style="padding:14px 16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);">
              <div style="font-size:11px;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.04em;font-weight:700;">{{ $label }}</div>
              <div style="font-family:var(--font-display);font-size:19px;font-weight:700;margin-top:4px;">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      </section>
      @endif

      @if(!empty($player->trophies))
      <section aria-labelledby="trophies-heading" class="essay-block" style="margin-top:28px;">
        <h2 id="trophies-heading">Trophies &amp; Honours</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:10px;">
          @foreach ($player->trophies as $t)
          <li style="display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid var(--border);">
            <span style="font-weight:600;">{{ $t['league'] }} <span style="color:var(--ink-faint);font-weight:400;">({{ $t['country'] }})</span></span>
            <span style="color:var(--ink-muted);text-align:right;">{{ $t['season'] }} &middot; {{ $t['place'] }}</span>
          </li>
          @endforeach
        </ul>
      </section>
      @endif

      @if(!empty($player->transfers))
      <section aria-labelledby="transfers-heading" class="essay-block" style="margin-top:28px;">
        <h2 id="transfers-heading">Transfer History</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:10px;">
          @foreach ($player->transfers as $t)
          <li style="display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid var(--border);flex-wrap:wrap;">
            <span>{{ $t['from'] ?? 'Unattached' }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align:middle;margin:0 4px;"><path d="M5 12h14M13 6l6 6-6 6"/></svg> <strong>{{ $t['to'] ?? '—' }}</strong></span>
            <span style="color:var(--ink-faint);text-align:right;">{{ \Carbon\Carbon::parse($t['date'])->format('j M Y') }}@if($t['type']) &middot; {{ $t['type'] }}@endif</span>
          </li>
          @endforeach
        </ul>
      </section>
      @endif

      @if($team->stadium)
      <section aria-labelledby="venue-heading" class="essay-block" style="margin-top:28px;">
        <h2 id="venue-heading">Home Ground: {{ $team->stadium }}</h2>
        @if($team->venue_image_url)
        <div class="media" style="margin-bottom:16px;max-width:480px;aspect-ratio:16/10;"><img src="{{ $team->venue_image_url }}" alt="{{ $team->stadium }}" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-md);"></div>
        @endif
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:0;">
          @if($team->venue_city)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">City</span><strong>{{ $team->venue_city }}</strong></li>
          @endif
          @if($team->venue_address)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Address</span><strong>{{ $team->venue_address }}</strong></li>
          @endif
          @if($team->stadium_capacity)
          <li style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);"><span style="color:var(--ink-faint);">Capacity</span><strong>{{ $team->stadium_capacity }}</strong></li>
          @endif
          @if($team->venue_surface)
          <li style="display:flex;justify-content:space-between;padding:10px 0;"><span style="color:var(--ink-faint);">Surface</span><strong>{{ $team->venue_surface }}</strong></li>
          @endif
        </ul>
      </section>
      @endif

    </div>

    <aside class="sidebar" aria-label="Sidebar" style="--team-color:{{ $team->color_hex }};">
      <div class="widget">
        <div class="widget-head"><h2>{{ $team->name }} Squad</h2></div>
        <div style="display:flex;flex-direction:column;gap:2px;">
          @foreach ($team->players()->orderBy('shirt_number')->take(10)->get() as $mate)
          <a href="{{ $mate->prettyUrl() }}" style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--ink);text-decoration:none;{{ $mate->id === $player->id ? 'font-weight:700;color:var(--team-color);' : '' }}">
            <span style="width:22px;color:var(--ink-faint);font-family:var(--font-display);font-weight:700;font-size:12px;">{{ $mate->shirt_number ?? '-' }}</span>
            {{ $mate->name }}
          </a>
          @endforeach
        </div>
        <a href="{{ route('teams.show', $team->slug) }}#squad" class="section-link" style="margin-top:14px;display:inline-flex;">Full squad
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>

      <div class="ad-slot ad-mpu">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
      </div>
    </aside>
  </div>

@endsection
