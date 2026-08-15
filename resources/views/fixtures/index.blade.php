@extends('layouts.site')

@php $leagueCount = $leagues->count(); @endphp
@section('title', "Fixtures — All {$leagueCount} Leagues | The Soccer Goals")
@section('meta_description', "Upcoming fixtures across all {$leagueCount} leagues covered by The Soccer Goals. Pick a league to see its full match schedule.")
@section('meta_keywords', 'football fixtures, upcoming matches, match schedule, Premier League fixtures, La Liga fixtures, Serie A fixtures, Bundesliga fixtures, Ligue 1 fixtures')
@section('canonical', route('fixtures.index'))
@section('og_title', "Fixtures — All {$leagueCount} Leagues | The Soccer Goals")
@section('og_description', "Upcoming fixtures across all {$leagueCount} leagues covered by The Soccer Goals.")

@section('content')
  @include('partials.league-picker', [
      'eyebrow' => "All {$leagueCount} Leagues · " . ($leagues->first()->season ?? '2026-27') . ' Season',
      'pageTitle' => 'Fixtures',
      'heroMeta' => 'Select a competition to see its upcoming match schedule.',
      'introText' => 'Choose a league below to see every upcoming fixture, with kick-off time, venue and a full match preview for each game.',
      'destinationRoute' => 'fixtures.show',
      'ctaLabel' => 'View Fixtures',
  ])
@endsection
