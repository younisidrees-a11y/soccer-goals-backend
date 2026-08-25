@extends('layouts.site')

@php $leagueCount = $leagues->count(); @endphp
@section('title', "Results — All {$leagueCount} Leagues | The Soccer Goals")
@section('meta_description', "Latest results across all {$leagueCount} leagues covered by The Soccer Goals. Pick a league to see every final score.")
@section('meta_keywords', 'football results, final scores, match results, English Premier League results, Spanish La Liga results, Serie A results, Bundesliga results, Ligue 1 results')
@section('canonical', route('results.index'))
@section('og_title', "Results — All {$leagueCount} Leagues | The Soccer Goals")
@section('og_description', "Latest results across all {$leagueCount} leagues covered by The Soccer Goals.")

@section('content')
  @include('partials.league-picker', [
      'eyebrow' => "All {$leagueCount} Leagues · " . ($leagues->first()->season ?? '2026-27') . ' Season',
      'pageTitle' => 'Results',
      'heroMeta' => 'Select a competition to see its latest final scores.',
      'introText' => 'Choose a league below to see every result so far this season, with the full match report and stats for each game.',
      'destinationRoute' => 'results.show',
      'ctaLabel' => 'View Results',
  ])
@endsection
