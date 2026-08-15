@extends('layouts.site')

@section('title', 'Fixtures — All 5 Leagues | The Soccer Goals')
@section('meta_description', 'Upcoming fixtures for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1. Pick a league to see its full match schedule.')
@section('meta_keywords', 'football fixtures, upcoming matches, match schedule, Premier League fixtures, La Liga fixtures, Serie A fixtures, Bundesliga fixtures, Ligue 1 fixtures')
@section('canonical', route('fixtures.index'))
@section('og_title', 'Fixtures — All 5 Leagues | The Soccer Goals')
@section('og_description', 'Upcoming fixtures for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1.')

@section('content')
  @include('partials.league-picker', [
      'eyebrow' => 'All 5 Leagues · ' . ($leagues->first()->season ?? '2026-27') . ' Season',
      'pageTitle' => 'Fixtures',
      'heroMeta' => 'Select a competition to see its upcoming match schedule.',
      'introText' => 'Choose a league below to see every upcoming fixture, with kick-off time, venue and a full match preview for each game.',
      'destinationRoute' => 'fixtures.show',
      'ctaLabel' => 'View Fixtures',
  ])
@endsection
