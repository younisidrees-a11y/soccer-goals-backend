@extends('layouts.site')

@php $leagueCount = $leagues->count(); @endphp
@section('title', "Points Tables — All {$leagueCount} Leagues | The Soccer Goals")
@section('meta_description', "Live standings across all {$leagueCount} leagues covered by The Soccer Goals. Pick a league to see its full points table.")
@section('meta_keywords', 'football table, standings, points table, English Premier League table, Spanish La Liga table, Serie A table, Bundesliga table, Ligue 1 table')
@section('canonical', route('tables.index'))
@section('og_title', "Points Tables — All {$leagueCount} Leagues | The Soccer Goals")
@section('og_description', "Live standings across all {$leagueCount} leagues covered by The Soccer Goals.")

@section('content')
  @include('partials.league-picker', [
      'eyebrow' => "All {$leagueCount} Leagues · " . ($leagues->first()->season ?? '2026-27') . ' Season',
      'pageTitle' => 'Points Tables',
      'heroMeta' => 'Select a competition to see its full standings.',
      'introText' => 'Choose a league below to see the complete points table, updated after every round.',
      'destinationRoute' => 'leagues.show',
      'ctaLabel' => 'View Table',
  ])
@endsection
