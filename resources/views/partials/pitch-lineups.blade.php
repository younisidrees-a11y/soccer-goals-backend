{{--
  Expects: $lineups (the match's parsed lineups array, 2 teams), $homeTeam, $awayTeam
  (models, used to match sides by api_football_id and pull real team colours).
  Positions players using API-Football's own "row:col" grid field - row 1 is
  always the goalkeeper, higher rows are more advanced positions. Column order
  within a row is used purely as a left-to-right sort key.
--}}
@php
  $safeColorPitch = fn (?string $hex) => $hex && preg_match('/^#[0-9a-fA-F]{3,6}$/', $hex) ? $hex : '#1552C0';
  $contrastColorPitch = function (?string $hex) {
      $hex = ltrim($hex ?: '#1552C0', '#');
      if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
      if (strlen($hex) !== 6) { return '#FFFFFF'; }
      [$r, $g, $b] = array_map(fn ($h) => hexdec($h), str_split($hex, 2));
      $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

      return $luminance > 0.6 ? '#0E2233' : '#FFFFFF';
  };

  $sides = [];
  foreach ($lineups as $teamLineup) {
      $isHomeSide = $teamLineup['team_id'] === $homeTeam->api_football_id;
      $team = $isHomeSide ? $homeTeam : $awayTeam;
      $color = $safeColorPitch($team->color_hex);
      $textColor = $contrastColorPitch($color);

      $byRow = collect($teamLineup['start_xi'])->groupBy(fn ($p) => (int) explode(':', $p['grid'])[0]);
      $maxRow = $byRow->keys()->max() ?: 1;

      $positioned = [];
      foreach ($byRow as $row => $players) {
          $sorted = $players->sortBy(fn ($p) => (int) explode(':', $p['grid'])[1])->values();
          $count = $sorted->count();
          $depth = $maxRow > 1 ? ($row - 1) / ($maxRow - 1) : 0;

          foreach ($sorted as $i => $p) {
              $yPct = ($i + 1) / ($count + 1) * 100;
              $xPct = $isHomeSide ? (8 + $depth * 40) : (92 - $depth * 40);
              $positioned[] = ['player' => $p, 'x' => $xPct, 'y' => $yPct];
          }
      }

      $sides[] = [
          'is_home' => $isHomeSide,
          'team' => $team,
          'formation' => $teamLineup['formation'],
          'coach' => $teamLineup['coach'],
          'color' => $color,
          'text_color' => $textColor,
          'players' => $positioned,
      ];
  }

  usort($sides, fn ($a, $b) => $b['is_home'] <=> $a['is_home']);
@endphp

<div class="pitch-legend">
  @foreach($sides as $side)
  <div class="pitch-legend-item" style="--side-color:{{ $side['color'] }};">
    <span class="crest crest-{{ $side['team']->crest_code }}" role="img" aria-label="{{ $side['team']->full_name }} badge"></span>
    <strong>{{ $side['team']->name }}</strong>
    <span class="pitch-formation">{{ $side['formation'] }}</span>
  </div>
  @endforeach
</div>

<div class="pitch-wrap">
  <div class="pitch-halfway"></div>
  <div class="pitch-circle"></div>
  <div class="pitch-box pitch-box-left"></div>
  <div class="pitch-box pitch-box-right"></div>

  @foreach($sides as $side)
    @foreach($side['players'] as $p)
    <div class="pitch-player" style="left:{{ $p['x'] }}%;top:{{ $p['y'] }}%;">
      <div class="pitch-player-tooltip">{{ $p['player']['name'] }} &middot; {{ $p['player']['position'] }}</div>
      @include('partials.jersey', ['color' => $side['color'], 'number' => $p['player']['number'], 'textColor' => $side['text_color']])
    </div>
    @endforeach
  @endforeach
</div>

@foreach($sides as $side)
<div class="pitch-coach-line">Coach: <strong>{{ $side['coach'] ?? $side['team']->manager ?? 'TBC' }}</strong> ({{ $side['team']->name }})</div>
@endforeach
