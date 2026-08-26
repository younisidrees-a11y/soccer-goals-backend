@extends('layouts.site')

@section('title', 'About Us & Editorial Standards | The Soccer Goals')
@section('meta_description', 'How The Soccer Goals sources its data, uses AI in its editorial process, and handles corrections — plus how to get in touch.')
@section('meta_keywords', 'about the soccer goals, editorial standards, corrections policy, contact')
@section('canonical', route('about'))
@section('og_title', 'About Us & Editorial Standards | The Soccer Goals')
@section('og_description', 'How The Soccer Goals sources its data, uses AI in its editorial process, and handles corrections.')

@section('content')

  <div class="wrap" style="padding-block:32px 64px;max-width:760px;">
    <h1 style="font-size:clamp(1.8rem,1.4rem + 1.6vw,2.5rem);">About The Soccer Goals</h1>
    <p style="font-size:16px;color:var(--ink-muted);margin-top:12px;line-height:1.7;">
      The Soccer Goals covers results, fixtures, points tables and club news across Europe's top leagues —
      the English Premier League, Spanish La Liga, Serie A, Bundesliga, Ligue 1 and the Saudi Pro League.
    </p>

    <h2 id="editorial-process" style="margin-top:40px;font-size:1.4rem;">Our Editorial Process</h2>
    <p style="margin-top:12px;line-height:1.75;color:var(--ink);">
      Every score, statistic, lineup and match event on this site comes from licensed sports-data providers,
      not from guesswork. We do not publish a result, a stat, or a scoreline that hasn't come from a real
      data feed.
    </p>
    <p style="margin-top:14px;line-height:1.75;color:var(--ink);">
      Club history, honours and biographical facts about managers are researched and cross-checked against
      public reference sources before they're used in any article.
    </p>

    <h2 style="margin-top:32px;font-size:1.4rem;">How We Use AI</h2>
    <p style="margin-top:12px;line-height:1.75;color:var(--ink);">
      Some articles on this site — match reports and statistical write-ups in particular — are drafted with
      AI assistance. The AI is only ever given verified facts to work from (the real score, the real
      statistics, the real Man of the Match rating) and is explicitly instructed not to invent names, quotes,
      or events. It is a drafting tool, not a source of facts.
    </p>
    <p style="margin-top:14px;line-height:1.75;color:var(--ink);">
      Every article, whether AI-assisted or written directly by an editor, goes through a review step before
      it is published. We disclose this openly because we think readers deserve to know how their news is
      made, not because we think it's a weakness — the standard for accuracy is the same either way.
    </p>

    <h2 style="margin-top:32px;font-size:1.4rem;">Corrections</h2>
    <p style="margin-top:12px;line-height:1.75;color:var(--ink);">
      We correct errors as soon as we're made aware of them. If you spot something wrong on this site — a
      score, a stat, a name, anything — please tell us and we'll fix it promptly.
    </p>

    <h2 id="contact" style="margin-top:32px;font-size:1.4rem;">Contact</h2>
    <p style="margin-top:12px;line-height:1.75;color:var(--ink);">
      For corrections, editorial questions, or anything else:
      <a href="mailto:editorial@thesoccergoals.com" style="color:var(--accent);text-decoration:underline;">editorial@thesoccergoals.com</a>
    </p>
  </div>

@endsection
