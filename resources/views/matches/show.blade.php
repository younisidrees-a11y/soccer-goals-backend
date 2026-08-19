@extends('layouts.site')

@php
  $title = $match->homeTeam->name . ' vs ' . $match->awayTeam->name;
  $isFinal = $match->status === 'final';
  $isLive = $match->status === 'live';
  $year = $match->kickoff_at->format('Y');
  $kickoffTime = $match->kickoff_at->format('H:i');
  $dateLong = $match->kickoff_at->format('j M Y');

  if ($isFinal) {
      $score = "{$match->home_score}-{$match->away_score}";
      $goalDiff = abs($match->home_score - $match->away_score);
      $isDraw = $match->home_score === $match->away_score;
      $winner = $isDraw ? null : ($match->home_score > $match->away_score ? $match->homeTeam->name : $match->awayTeam->name);

      $resultPhrase = $isDraw
          ? 'Match Ends in a Draw'
          : "{$winner} Win by {$goalDiff} " . Str::plural('Goal', $goalDiff);

      $defaultTitle = "{$title} {$score} Match Result {$year}: {$resultPhrase} | The Soccer Goals";

      $defaultDescription = $isDraw
          ? "{$title} ended {$score} at {$match->venue} on {$dateLong}. Full-time result, match report and stats from this {$match->league->name} {$match->league->season} clash."
          : "{$title} {$score}: {$winner} win by {$goalDiff} " . Str::plural('goal', $goalDiff) . " at {$match->venue} on {$dateLong}. Full match report, stats and final score from this {$match->league->name} {$match->league->season} fixture.";

      $defaultKeywords = "{$match->homeTeam->name}, {$match->awayTeam->name}, {$title}, {$score}, match result, final score, {$match->league->name}, {$year}"
          . ($isDraw ? ', draw' : ", {$winner} win");
  } else {
      $defaultTitle = "{$title} Live Match {$year}: Going to Play on {$dateLong} at {$kickoffTime} | The Soccer Goals";

      $defaultDescription = "{$match->homeTeam->name} host {$match->awayTeam->name} live at {$match->venue} on {$dateLong}, kick-off {$kickoffTime}. Team news, form and match preview for this {$match->league->name} {$match->league->season} fixture.";

      $defaultKeywords = "{$match->homeTeam->name}, {$match->awayTeam->name}, {$title}, live match, {$match->league->name}, fixture {$year}, kick off time, match preview";
  }
@endphp

@section('title', $match->meta_title ?: $defaultTitle)
@section('meta_description', $match->meta_description ?: $defaultDescription)
@section('meta_keywords', $match->meta_keywords ?: $defaultKeywords)
@section('canonical', route('matches.show', $match->id))
@section('og_title', $match->meta_title ?: $defaultTitle)
@section('og_description', $match->meta_description ?: $defaultDescription)

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#B9CBDA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('leagues.show', $match->league->slug) }}" style="color:#B9CBDA;">{{ $match->league->name }}</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $title }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $match->league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB8FF;">{{ $match->league->name }}@if($match->venue) &middot; {{ $match->venue }}@endif</div>
          <h1 class="league-hero-title">{{ $title }}</h1>
          <div class="league-hero-meta">
            {{ $match->kickoff_at->format('D j M Y') }} &middot;
            @if($isFinal) Full-Time
            @elseif($isLive && $match->halftime_published_at) LIVE &middot; {{ $match->home_score_ht }}-{{ $match->away_score_ht }} at half-time
            @elseif($isLive) LIVE &middot; first half underway
            @else {{ $match->kickoff_at->format('H:i') }} kick-off
            @endif
          </div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">Date</div>
          <div class="stat-value">{{ $match->kickoff_at->format('D j M Y') }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">{{ $isFinal ? 'Status' : ($isLive ? 'Status' : 'Kick-off') }}</div>
          <div class="stat-value">
            @if($isFinal) Full-Time
            @elseif($isLive) <span class="dot-waiting" aria-hidden="true"></span> LIVE
            @else <span class="dot-waiting" aria-hidden="true"></span>{{ $match->kickoff_at->format('H:i') }}
            @endif
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Venue</div>
          <div class="stat-value" style="font-size:15px;">{{ $match->venue ?? 'TBC' }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Result</div>
          <div class="stat-value">
            @if($isFinal) {{ $match->home_score }}-{{ $match->away_score }}
            @elseif($isLive && $match->halftime_published_at) {{ $match->home_score_ht }}-{{ $match->away_score_ht }} (HT)
            @elseif($isLive) In progress
            @else Not yet played
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="wrap">
    <div class="ad-slot ad-leaderboard">
      <span class="ad-eyebrow">Advertisement</span>
      <span class="ad-size">728 &times; 90 &middot; AdSense unit</span>
    </div>
  </div>

  <div class="wrap content-grid">
    <div class="content-main">

      @if($isLive && $match->halftime_report)
        <section class="detail-closing" aria-labelledby="live-heading">
          <h2 id="live-heading">Live Update &middot; Half-Time</h2>
          <p>{{ $match->halftime_report }}</p>
        </section>
      @endif

      @if($isFinal)

        <section class="detail-closing" aria-labelledby="report-heading">
          <h2 id="report-heading">Match Report</h2>
          <p>{{ $match->match_report }}</p>
        </section>

      @else

        <section aria-labelledby="preview-heading">
          <div class="section-head"><h2 id="preview-heading">Match Preview</h2></div>
          <div style="display:grid;gap:14px;grid-template-columns:1fr;">
            <div class="widget">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge" style="width:28px;height:31px;"></span>
                <strong>{{ $match->homeTeam->name }}</strong>
                @if($homeStanding)<span style="font-size:12.5px;color:var(--ink-faint);">&middot; {{ $homeStanding->position }}{{ match(true){ in_array($homeStanding->position % 100, [11,12,13]) => 'th', $homeStanding->position % 10 === 1 => 'st', $homeStanding->position % 10 === 2 => 'nd', $homeStanding->position % 10 === 3 => 'rd', default => 'th' } }} &middot; {{ $homeStanding->points }} pts</span>@endif
              </div>
              <p style="font-size:14px;color:var(--ink-muted);line-height:1.6;margin:0;">{{ $match->home_preview_note }}</p>
            </div>
            <div class="widget">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge" style="width:28px;height:31px;"></span>
                <strong>{{ $match->awayTeam->name }}</strong>
                @if($awayStanding)<span style="font-size:12.5px;color:var(--ink-faint);">&middot; {{ $awayStanding->position }}{{ match(true){ in_array($awayStanding->position % 100, [11,12,13]) => 'th', $awayStanding->position % 10 === 1 => 'st', $awayStanding->position % 10 === 2 => 'nd', $awayStanding->position % 10 === 3 => 'rd', default => 'th' } }} &middot; {{ $awayStanding->points }} pts</span>@endif
              </div>
              <p style="font-size:14px;color:var(--ink-muted);line-height:1.6;margin:0;">{{ $match->away_preview_note }}</p>
            </div>
          </div>
        </section>

      @endif

      <section aria-labelledby="matchup-heading" style="margin-top:32px;">
        <div class="section-head"><h2 id="matchup-heading">The Matchup</h2></div>
        <div class="match-grid{{ $isFinal ? ' celebrate-match' : '' }}">
          <div class="match-card" style="grid-column:1/-1;">
            <div class="match-meta"><span class="match-comp">{{ $match->league->name }}@if($match->venue) &middot; {{ $match->venue }}@endif</span><span class="match-status">{{ $isFinal ? 'Full-Time' : '' }}@unless($isFinal)<span class="dot-waiting" aria-hidden="true"></span>{{ $match->kickoff_at->format('D j M Y, H:i') }}@endunless</span></div>
            <div class="match-teams">
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge"></span><span class="team-name">{{ $match->homeTeam->name }} <span style="color:var(--ink-faint);font-weight:500;">(Home)</span></span></div>@if($isFinal)<span class="team-score{{ $match->home_score > $match->away_score ? ' winning' : '' }}">{{ $match->home_score }}</span>@endif</div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge"></span><span class="team-name">{{ $match->awayTeam->name }} <span style="color:var(--ink-faint);font-weight:500;">(Away)</span></span></div>@if($isFinal)<span class="team-score{{ $match->away_score > $match->home_score ? ' winning' : '' }}">{{ $match->away_score }}</span>@endif</div>
            </div>
          </div>
        </div>
      </section>

      @if($isFinal)

        @php
          $isDraw = $match->home_score === $match->away_score;
          $bridgeSeed = hexdec(substr(md5('bridge-' . $match->id), 0, 6));
          $possHome = $match->stats['possession']['home'] ?? null;
          $shotsHome = $match->stats['shots']['home'] ?? null;
          $shotsAway = $match->stats['shots']['away'] ?? null;

          if ($isDraw) {
              $possLine = $possHome
                  ? "Neither team could really take control of the ball for long, with {$match->homeTeam->name} holding {$possHome}% possession, close enough to an even split to explain why the game never settled into one side's favour."
                  : "It was an even contest for large parts of the afternoon, with neither team able to take control of the game for long.";

              $bridgeTemplates = [
                  "{$match->homeTeam->name} and {$match->awayTeam->name} drew {$match->home_score}-{$match->away_score} at {$match->venue}, in a game that could easily have finished either way. {$possLine} Both sides had spells on top without ever pulling clear, and the shot count on the day backs up just how tight things were. A draw is rarely the result either team sets out to get, but on this occasion it was a fair reflection of ninety minutes where chances were shared roughly evenly. Below is a closer look at exactly how the numbers from the match broke down.",
                  "It finished {$match->home_score}-{$match->away_score} between {$match->homeTeam->name} and {$match->awayTeam->name} at {$match->venue}, a scoreline that summed up a game short on clear separation between the two sides. {$possLine} There were spells of pressure from both teams, but not enough to turn a share of the points into all three. Days like this are part of a long season, and how each side responds in their next fixture will say plenty about their form heading forward. Here is a closer look at how the two teams compared statistically.",
              ];
          } else {
              $winner = $match->home_score > $match->away_score ? $match->homeTeam->name : $match->awayTeam->name;
              $loser = $match->home_score > $match->away_score ? $match->awayTeam->name : $match->homeTeam->name;
              $winnerIsHome = $match->home_score > $match->away_score;

              $possLine = $possHome
                  ? ($winnerIsHome
                      ? "{$winner} also had the better share of the ball, holding {$possHome}% possession, which usually goes hand in hand with the extra control a winning side tends to show."
                      : "Even without the extra share of possession, which finished {$possHome}% in favour of {$loser}, {$winner} made the most of the chances that mattered and did enough to see the game out.")
                  : "";

              $shotsLine = ($shotsHome !== null && $shotsAway !== null)
                  ? "The shot count told a similar story, with {$shotsHome} attempts from {$match->homeTeam->name} against {$shotsAway} from {$match->awayTeam->name} over the course of the game."
                  : "";

              $bridgeTemplates = [
                  "{$winner} beat {$loser} {$match->home_score}-{$match->away_score} at {$match->venue}, and the numbers from the match back up how the game played out. {$possLine} {$shotsLine} {$loser} had their moments and did not make it easy from start to finish, but ultimately came up short on the day. Small margins like this one tend to add up over a season, and results such as this are often what separates teams in the table further down the line. Here is a closer look at exactly how the two sides compared, statistically.",
                  "{$match->homeTeam->name} {$match->home_score}-{$match->away_score} {$match->awayTeam->name} was the final score at {$match->venue}, in a game where {$winner} did enough across the ninety minutes to deserve the three points. {$possLine} {$shotsLine} It was not always comfortable for {$winner}, and {$loser} pushed for a way back into the game, but the result stood. Games like this can carry real weight later in the season, so this is a scoreline worth remembering. Below is a closer look at how the match broke down, statistically.",
              ];
          }

          $bridgeParagraph = trim(preg_replace('/\s+/', ' ', $bridgeTemplates[$bridgeSeed % count($bridgeTemplates)]));
        @endphp

        <section aria-labelledby="summary-heading" style="margin-top:32px;">
          <div class="section-head"><h2 id="summary-heading">Match Summary</h2></div>
          <p style="font-size:15px;line-height:1.7;color:var(--ink);max-width:68ch;">{{ $bridgeParagraph }}</p>
        </section>

        @if($match->stats)
        <section aria-labelledby="stats-heading" style="margin-top:32px;">
          <div class="section-head"><h2 id="stats-heading">Match Statistics</h2></div>
          <p style="font-size:12.5px;color:var(--ink-faint);margin-top:-8px;margin-bottom:16px;">{{ $match->homeTeam->name }} (home) vs {{ $match->awayTeam->name }} (away)</p>
          <div class="stat-bullets">
            @php $poss = $match->stats['possession'] ?? null; @endphp
            @if($poss)
            <div class="stat-bullet-row">
              <span class="stat-bullet-label">Possession</span>
              <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ $poss['home'] }}%;"></span></span>
              <span class="stat-bullet-value">{{ $poss['home'] }}%</span>
            </div>
            @endif
            @foreach (['shots' => 'Shots', 'shots_on_target' => 'Shots on Target', 'corners' => 'Corners', 'fouls' => 'Fouls'] as $key => $label)
              @if(isset($match->stats[$key]))
              @php
                $homeVal = $match->stats[$key]['home'];
                $awayVal = $match->stats[$key]['away'];
                $total = $homeVal + $awayVal ?: 1;
              @endphp
              <div class="stat-bullet-row">
                <span class="stat-bullet-label">{{ $label }}</span>
                <span class="stat-bullet-track"><span class="stat-bullet-fill" style="width:{{ round($homeVal / $total * 100) }}%;"></span></span>
                <span class="stat-bullet-value">{{ $homeVal }}&ndash;{{ $awayVal }}</span>
              </div>
              @endif
            @endforeach
          </div>
        </section>
        @endif

        @if($homeNext || $awayNext)
        @php
          $aheadSeed = hexdec(substr(md5('ahead-' . $match->id), 0, 6));

          $aheadIntros = [
              "does not have long to wait before getting back out onto the pitch",
              "will already be turning their attention to what comes next",
              "have a quick chance to build on this result",
          ];
          $homeAheadIntro = $aheadIntros[$aheadSeed % count($aheadIntros)];
          $awayAheadIntro = $aheadIntros[($aheadSeed + 1) % count($aheadIntros)];

          $nextLinkText = fn ($next) => "{$next->homeTeam->name} vs {$next->awayTeam->name} going to play at {$next->kickoff_at->format('j F Y')} in {$next->league->name} {$next->league->season}";
        @endphp

        <section aria-labelledby="ahead-heading" style="margin-top:32px;">
          <div class="section-head"><h2 id="ahead-heading">Looking Ahead</h2></div>
          <div style="font-size:15px;line-height:1.7;color:var(--ink);max-width:68ch;">
            @if($homeNext)
            <p>
              {{ $match->homeTeam->name }} {{ $homeAheadIntro }}. Their next test comes {{ $homeNext->home_team_id === $match->homeTeam->id ? 'at home to' : 'away at' }} {{ $homeNext->home_team_id === $match->homeTeam->id ? $homeNext->awayTeam->name : $homeNext->homeTeam->name }}, and how they respond after this result will be worth watching. You can follow the build-up and everything you need to know when
              <a href="{{ route('matches.show', $homeNext->id) }}" style="color:var(--accent);text-decoration:underline;">{{ $nextLinkText($homeNext) }}</a>.
            </p>
            @else
            <p>{{ $match->homeTeam->name }} do not have a fixture confirmed yet, so their next test is still to be set.</p>
            @endif
            @if($awayNext)
            <p>
              {{ $match->awayTeam->name }}, meanwhile, {{ $awayAheadIntro }}. They are next in action {{ $awayNext->home_team_id === $match->awayTeam->id ? 'at home to' : 'away at' }} {{ $awayNext->home_team_id === $match->awayTeam->id ? $awayNext->awayTeam->name : $awayNext->homeTeam->name }}, a game that will give an early sense of how they carry this result forward. Full details are here:
              <a href="{{ route('matches.show', $awayNext->id) }}" style="color:var(--accent);text-decoration:underline;">{{ $nextLinkText($awayNext) }}</a>.
            </p>
            @else
            <p>{{ $match->awayTeam->name }} do not have a fixture confirmed yet, so their next test is still to be set.</p>
            @endif
          </div>
        </section>
        @endif

        <section aria-labelledby="next-heading" style="margin-top:32px;">
          <div class="section-head"><h2 id="next-heading">What's Next</h2></div>
          <div style="display:grid;gap:14px;grid-template-columns:1fr;">
            @foreach ([[$match->homeTeam, $homeNext], [$match->awayTeam, $awayNext]] as [$team, $next])
            <div class="widget" style="display:flex;align-items:center;gap:12px;">
              <span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" style="width:32px;height:35px;"></span>
              <div style="flex:1;">
                <strong>{{ $team->name }}</strong>
                <div style="font-size:13px;color:var(--ink-faint);margin-top:2px;">
                  @if($next)
                    Next: vs {{ $next->home_team_id === $team->id ? $next->awayTeam->name : $next->homeTeam->name }} ({{ $next->home_team_id === $team->id ? 'H' : 'A' }}) &middot; {{ $next->venue }} &middot; {{ $next->kickoff_at->format('D j M') }}
                  @else
                    No further fixtures scheduled.
                  @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </section>

      @endif

    </div>

    <aside class="sidebar" aria-label="Sidebar">
      <div class="widget">
        <h2 style="margin-bottom:14px;">This Fixture</h2>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <a href="{{ route('teams.show', $match->homeTeam->slug) }}" class="btn btn-ghost btn-block">{{ $match->homeTeam->name }} Team Page</a>
          <a href="{{ route('teams.show', $match->awayTeam->slug) }}" class="btn btn-ghost btn-block">{{ $match->awayTeam->name }} Team Page</a>
          <a href="{{ route('leagues.show', $match->league->slug) }}" class="btn btn-ghost btn-block">{{ $match->league->name }} Table &amp; Results</a>
        </div>
      </div>

      <div class="ad-slot ad-mpu">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 250 &middot; AdSense unit</span>
      </div>

      <div class="ad-slot ad-skyscraper">
        <span class="ad-eyebrow">Advertisement</span>
        <span class="ad-size">300 &times; 600 &middot; AdSense unit</span>
      </div>
    </aside>
  </div>

@endsection
