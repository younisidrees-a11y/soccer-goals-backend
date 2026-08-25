@extends('layouts.site')

@section('title', $label . ' | The Soccer Goals')
@section('meta_description', 'The latest ' . strtolower($label) . ' from the Premier League, Spanish La Liga, Serie A, Bundesliga and Ligue 1.')
@section('meta_keywords', strtolower($label) . ', football news, soccer news')
@section('canonical', route('news.category', $category))
@section('og_title', $label . ' | The Soccer Goals')
@section('og_description', 'The latest ' . strtolower($label) . ' from every major league.')

@php
  $allCategories = \App\Http\Controllers\NewsController::categories();
@endphp

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('news.index') }}" style="color:#B9CBDA;">News</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $label }}</span>
      </div>
      <div class="league-hero-inner">
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">Coverage</div>
          <h1 class="league-hero-title">{{ $label }}</h1>
          <div class="league-hero-meta">{{ $articles->total() }} {{ Str::plural('story', $articles->total()) }}</div>
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
      @foreach ($allCategories as $slug => $catLabel)
      <a href="{{ route('news.category', $slug) }}" class="tab-btn" role="tab" aria-selected="{{ $slug === $category ? 'true' : 'false' }}">{{ $catLabel }}</a>
      @endforeach
    </div>

    <div class="news-grid">
      @forelse ($articles as $article)
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
      <p style="color:var(--ink-faint);">No {{ strtolower($label) }} published yet &mdash; check back soon.</p>
      @endforelse
    </div>

    @include('partials.pagination', ['paginator' => $articles])
  </div>

@endsection
