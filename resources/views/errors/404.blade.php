@extends('layouts.site')

@section('title', 'Page Not Found | The Soccer Goals')
@section('meta_description', "The page you're looking for doesn't exist or has moved. Find today's scores, leagues and news from The Soccer Goals homepage.")
@section('robots', 'noindex, follow')

@section('content')

  <section class="wrap" style="padding:96px 20px 120px;text-align:center;max-width:640px;">
    <div class="eyebrow" style="color:var(--ink-muted);">Error 404</div>
    <h1 style="font-size:clamp(2rem,1.6rem + 1.6vw,2.75rem);line-height:1.1;margin-top:10px;">This page doesn't exist</h1>
    <p style="color:var(--ink-muted);font-size:16px;line-height:1.65;margin-top:16px;">
      The match, article or page you followed a link to isn't here anymore - it may have been an old link, or the content has since moved. Nothing else on the site is affected.
    </p>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:32px;">
      <a href="{{ route('home') }}" class="btn btn-accent">Go to Homepage</a>
      <a href="{{ route('today.index') }}" class="btn btn-ghost">Today's Scores</a>
      <a href="{{ route('leagues.index') }}" class="btn btn-ghost">All Leagues</a>
      <a href="{{ route('news.index') }}" class="btn btn-ghost">Latest News</a>
    </div>
  </section>

@endsection
