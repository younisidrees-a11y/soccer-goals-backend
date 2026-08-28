@extends('layouts.site')

@section('title', $article->meta_title ?: $article->title . ' | The Soccer Goals')
@section('meta_description', $article->meta_description ?: $article->dek)
@section('meta_keywords', $article->meta_keywords ?: $article->title . ', ' . $categoryLabel . ($article->team ? ', ' . $article->team->name : '') . ($article->league ? ', ' . $article->league->name : ''))
@section('canonical', route('news.show', $article->slug))
@section('og_title', $article->meta_title ?: $article->title)
@section('og_description', $article->meta_description ?: $article->dek)
@section('og_type', 'article')
@section('og_image', $article->image_url ?: asset('apple-touch-icon.png'))

@php
  $publishedAt = $article->published_at ?? $article->created_at;
  $modifiedAt = $article->updated_at ?? $publishedAt;
  $newsArticleSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'NewsArticle',
      'headline' => \Illuminate\Support\Str::limit($article->title, 110, ''),
      'description' => $article->meta_description ?: $article->dek,
      'image' => [$article->image_url ?: asset('apple-touch-icon.png')],
      'datePublished' => $publishedAt->toIso8601String(),
      'dateModified' => $modifiedAt->toIso8601String(),
      'author' => $article->author
          ? [['@type' => 'Person', 'name' => $article->author]]
          : [['@type' => 'Organization', 'name' => 'The Soccer Goals']],
      'publisher' => [
          '@type' => 'Organization',
          'name' => 'The Soccer Goals',
          'logo' => [
              '@type' => 'ImageObject',
              'url' => asset('apple-touch-icon.png'),
              'width' => 180,
              'height' => 180,
          ],
      ],
      'mainEntityOfPage' => [
          '@type' => 'WebPage',
          '@id' => route('news.show', $article->slug),
      ],
      'articleSection' => $categoryLabel,
      'inLanguage' => 'en',
  ];

  if ($article->meta_keywords) {
      $newsArticleSchema['keywords'] = $article->meta_keywords;
  }
@endphp

@section('head_extra')
<meta property="article:published_time" content="{{ $publishedAt->toIso8601String() }}">
<meta property="article:modified_time" content="{{ $modifiedAt->toIso8601String() }}">
<meta property="article:section" content="{{ $categoryLabel }}">
@if($article->author)
<meta property="article:author" content="{{ $article->author }}">
@endif
<script type="application/ld+json">
{!! json_encode($newsArticleSchema, JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

@section('content')

  <div class="wrap" style="padding-top:24px;">
    <div class="breadcrumb">
      <a href="{{ route('home') }}">Home</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
      <a href="{{ route('news.index') }}">News</a>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
      <a href="{{ route('news.category', $article->category) }}">{{ $categoryLabel }}</a>
    </div>
  </div>

  <div class="wrap content-grid">
    <div class="content-main">

      <article>
        @php
          $catTag = match ($article->category) {
              'match-report' => ['cat-report', 'Match Report'],
              'transfers' => ['cat-transfers', 'Transfers'],
              'analysis' => ['cat-analysis', 'Analysis'],
              'injury' => ['cat-report', 'Injury Update'],
              default => ['cat-opinion', 'Club News'],
          };
        @endphp
        <span class="cat-tag {{ $catTag[0] }}">{{ $catTag[1] }}</span>
        <h1 style="font-size:clamp(1.6rem,1.2rem + 1.6vw,2.2rem);margin-top:12px;">{{ $article->title }}</h1>
        <p class="dek" style="font-size:16px;color:var(--ink-muted);margin-top:10px;">{{ $article->dek }}</p>
        <div class="byline" style="margin-top:14px;">
          By {{ $article->author ?: 'The Soccer Goals' }}
          <span class="dot"></span>
          <time datetime="{{ $publishedAt->toIso8601String() }}">{{ $publishedAt->format('j M Y') }}</time>
          @if($article->team)
          <span class="dot"></span>
          <a href="{{ route('teams.show', $article->team->slug) }}" style="color:inherit;text-decoration:underline;">{{ $article->team->name }}</a>
          @endif
          @if($article->league)
          <span class="dot"></span>
          <a href="{{ route('leagues.show', $article->league->slug) }}" style="color:inherit;text-decoration:underline;">{{ $article->league->name }}</a>
          @endif
        </div>

        @if($article->image_url)
        <div class="media media-hero" style="margin-top:22px;">
          <img src="{{ $article->image_url }}" alt="{{ $article->title }}">
        </div>
        @endif

        <div style="margin-top:24px;font-size:15.5px;line-height:1.75;color:var(--ink);max-width:68ch;">
          @foreach (explode("\n", $article->body) as $paragraph)
            @continue(trim($paragraph) === '')
            <p style="margin-bottom:16px;">{{ trim($paragraph) }}</p>
          @endforeach
        </div>

        @if($article->match)
        <a href="{{ $article->match->prettyUrl() }}" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-top:28px;padding:16px 20px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-lg);text-decoration:none;color:inherit;">
          <span style="display:flex;align-items:center;gap:10px;font-size:14.5px;">
            <span class="crest crest-{{ $article->match->homeTeam->crest_code }}" role="img" aria-label="{{ $article->match->homeTeam->full_name }} badge"></span>
            <strong>{{ $article->match->homeTeam->name }}</strong>
            @if($article->match->isFinal())
              <span style="font-weight:800;font-variant-numeric:tabular-nums;">{{ $article->match->home_score }}&ndash;{{ $article->match->away_score }}</span>
            @else
              <span style="color:var(--ink-faint);">vs</span>
            @endif
            <strong>{{ $article->match->awayTeam->name }}</strong>
            <span class="crest crest-{{ $article->match->awayTeam->crest_code }}" role="img" aria-label="{{ $article->match->awayTeam->full_name }} badge"></span>
          </span>
          <span class="btn btn-accent btn-sm">Full Match Report &amp; Stats
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </span>
        </a>
        @endif
      </article>

      @if($related->isNotEmpty())
      <section aria-labelledby="related-heading" style="margin-top:40px;">
        <div class="section-head"><h2 id="related-heading">Related Stories</h2></div>
        <div class="news-grid">
          @foreach ($related as $rel)
          @php
            $relTag = match ($rel->category) {
                'match-report' => ['cat-report', 'Match Report'],
                'transfers' => ['cat-transfers', 'Transfers'],
                'analysis' => ['cat-analysis', 'Analysis'],
                'injury' => ['cat-report', 'Injury Update'],
                default => ['cat-opinion', 'Club News'],
            };
          @endphp
          <article class="news-card">
            <a href="{{ route('news.show', $rel->slug) }}"><div class="media" aria-hidden="true">
              @if($rel->image_url)
                <img src="{{ $rel->image_url }}" alt="{{ $rel->title }}" loading="lazy">
              @else
                <svg viewBox="0 0 200 150"><circle cx="160" cy="20" r="60" fill="#fff" fill-opacity=".08"/></svg>
              @endif
            </div></a>
            <span class="cat-tag {{ $relTag[0] }}">{{ $relTag[1] }}</span>
            <a href="{{ route('news.show', $rel->slug) }}"><h3>{{ $rel->title }}</h3></a>
            <p class="dek">{{ $rel->dek }}</p>
          </article>
          @endforeach
        </div>
      </section>
      @endif

    </div>

    <aside class="sidebar" aria-label="Sidebar">
      <div class="widget">
        <h2 style="margin-bottom:14px;">More News</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <a href="{{ route('news.index') }}" class="btn btn-ghost btn-block">All News</a>
          <a href="{{ route('news.category', $article->category) }}" class="btn btn-ghost btn-block">More {{ $categoryLabel }}</a>
        </div>
      </div>

      <div class="ad-slot ad-mpu">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
      </div>

      <div class="widget newsletter-widget">
        <h2>The Daily Briefing</h2>
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
