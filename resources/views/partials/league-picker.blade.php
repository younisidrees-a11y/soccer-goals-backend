{{--
  Shared "pick a league" landing section.
  Expects: $eyebrow, $pageTitle, $heroMeta, $introText, $destinationRoute, $ctaLabel
  $destinationRoute: a route name accepting a league slug, e.g. 'fixtures.show'
--}}
<section class="league-hero">
  <div class="wrap">
    <div class="breadcrumb" style="color:#8FA6BA;">
      <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
      <span style="color:#fff;">{{ $pageTitle }}</span>
    </div>
    <div class="league-hero-inner">
      <div>
        <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">{{ $eyebrow }}</div>
        <h1 class="league-hero-title">{{ $pageTitle }}</h1>
        <div class="league-hero-meta">{{ $heroMeta }}</div>
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

<div class="wrap" style="padding-block:36px 64px;max-width:920px;">
  <p style="font-size:16px;color:var(--ink-muted);margin-bottom:24px;">{{ $introText }}</p>
  <div class="selector-grid">
    @foreach ($leagues as $league)
    @php
      $flagVb = match ($league->flag_code) {
          'eng' => '0 0 25 15',
          'deu' => '0 0 5 3',
          default => '0 0 3 2',
      };
    @endphp
    <a href="{{ route($destinationRoute, $league->slug) }}" class="selector-card">
      <span class="selector-card-flag"><svg viewBox="{{ $flagVb }}"><use href="#flag-{{ $league->flag_code }}"></use></svg></span>
      <span class="selector-card-body">
        <span class="selector-card-name">{{ $league->name }}</span>
        <span class="selector-card-meta">{{ $league->country }} &middot; {{ $league->teams_count }} clubs &middot; {{ $ctaLabel }}</span>
      </span>
      <svg class="selector-card-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
    @endforeach
  </div>
</div>
