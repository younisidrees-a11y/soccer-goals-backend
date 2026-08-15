@extends('layouts.site')

@section('title', 'Results — All 5 Leagues | The Soccer Goals')
@section('meta_description', 'Latest results for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1. Pick a league to see every final score.')
@section('meta_keywords', 'football results, final scores, match results, Premier League results, La Liga results, Serie A results, Bundesliga results, Ligue 1 results')
@section('canonical', route('results.index'))
@section('og_title', 'Results — All 5 Leagues | The Soccer Goals')
@section('og_description', 'Latest results for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1.')

@section('content')
  @include('partials.league-picker', [
      'eyebrow' => 'All 5 Leagues · ' . ($leagues->first()->season ?? '2026-27') . ' Season',
      'pageTitle' => 'Results',
      'heroMeta' => 'Select a competition to see its latest final scores.',
      'introText' => 'Choose a league below to see every result so far this season, with the full match report and stats for each game.',
      'destinationRoute' => 'results.show',
      'ctaLabel' => 'View Results',
  ])
@endsection
