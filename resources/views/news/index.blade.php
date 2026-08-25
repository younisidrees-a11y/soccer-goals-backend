@extends('layouts.site')

@section('title', 'News — The Soccer Goals')
@section('meta_description', 'The latest football news across match reports, transfers, analysis, injury updates and club news for the English Premier League, Spanish La Liga, Serie A, Bundesliga and Ligue 1.')
@section('meta_keywords', 'football news, soccer news, match reports, transfer news, football analysis, injury updates')
@section('canonical', route('news.index'))
@section('og_title', 'News — The Soccer Goals')
@section('og_description', 'The latest football news across match reports, transfers, analysis, injury updates and club news.')

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">News</span>
      </div>
      <div class="league-hero-inner">
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">Coverage</div>
          <h1 class="league-hero-title">News</h1>
          <div class="league-hero-meta">Match reports, transfers, analysis and more from every major league.</div>
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
    <div class="league-tabs" role="tablist" aria-label="News categories" style="flex-wrap:wrap;margin-bottom:32px;">
      @foreach ($categories as $cat)
      <a href="{{ route('news.category', $cat['slug']) }}" class="tab-btn" role="tab" aria-selected="false">{{ $cat['label'] }}@if($cat['count'] > 0) ({{ $cat['count'] }})@endif</a>
      @endforeach
    </div>

    <div class="section-head"><h2>Latest News</h2></div>
    <div class="news-grid">
      @forelse ($latest as $article)
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
        <a href="{{ route('news.show', $article->slug) }}"><div class="media" aria-hidden="true">
          @if($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" loading="lazy">
          @else
            <svg viewBox="0 0 200 150"><circle cx="160" cy="20" r="60" fill="#fff" fill-opacity=".08"/></svg>
          @endif
        </div></a>
        <span class="cat-tag {{ $catTag[0] }}">{{ $catTag[1] }}</span>
        <a href="{{ route('news.show', $article->slug) }}"><h3>{{ $article->title }}</h3></a>
        <p class="dek">{{ $article->dek }}</p>
      </article>
      @empty
      <p style="color:var(--ink-faint);">No published stories yet &mdash; check back soon.</p>
      @endforelse
    </div>
  </div>

@endsection
