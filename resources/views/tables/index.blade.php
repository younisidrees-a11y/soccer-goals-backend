@extends('layouts.site')

@section('title', 'Points Tables — All 5 Leagues | The Soccer Goals')
@section('meta_description', 'Live standings for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1. Pick a league to see its full points table.')
@section('meta_keywords', 'football table, standings, points table, Premier League table, La Liga table, Serie A table, Bundesliga table, Ligue 1 table')
@section('canonical', route('tables.index'))
@section('og_title', 'Points Tables — All 5 Leagues | The Soccer Goals')
@section('og_description', 'Live standings for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1.')

@section('content')
  @include('partials.league-picker', [
      'eyebrow' => 'All 5 Leagues · ' . ($leagues->first()->season ?? '2026-27') . ' Season',
      'pageTitle' => 'Points Tables',
      'heroMeta' => 'Select a competition to see its full standings.',
      'introText' => 'Choose a league below to see the complete points table, updated after every matchday.',
      'destinationRoute' => 'leagues.show',
      'ctaLabel' => 'View Table',
  ])
@endsection
