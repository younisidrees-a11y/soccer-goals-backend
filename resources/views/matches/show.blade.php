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

      $defaultTitle = "{$match->league->name} {$title} Match {$year} Result Score {$score}";

      $defaultDescription = $isDraw
          ? "{$title} ended {$score} at {$match->venue} on {$dateLong}. Full-time result, match report and stats from this {$match->league->name} {$match->league->season} clash."
          : "{$title} {$score}: {$winner} win by {$goalDiff} " . Str::plural('goal', $goalDiff) . " at {$match->venue} on {$dateLong}. Full match report, stats and final score from this {$match->league->name} {$match->league->season} fixture.";

      $defaultKeywords = "{$match->homeTeam->name}, {$match->awayTeam->name}, {$title}, {$score}, match result, final score, {$match->league->name}, {$year}"
          . ($isDraw ? ', draw' : ", {$winner} win");
  } else {
      // One shared title for every not-yet-final fixture - scheduled
      // (kickoff still to come) and live (already underway) alike. This
      // is accurate either way: "Kick Off Time" names a real fact of the
      // match whether it's upcoming or already happened, and "Live
      // Score" becomes true the moment the match actually starts -
      // unlike the earlier per-status title, description/keywords still
      // differ below since those genuinely say different things (a
      // live score right now vs. a pre-match preview).
      $defaultTitle = "{$title} Match {$year} Live Score, Kick Off Time | The Soccer Goals";

      $defaultDescription = $isLive
          ? "{$title} live now at {$match->venue}, currently {$match->home_score}-{$match->away_score}. Live score, commentary and stats as this {$match->league->name} {$match->league->season} match happens."
          : "{$match->homeTeam->name} face {$match->awayTeam->name} at {$match->venue} on {$dateLong}, kick-off {$kickoffTime}. Team news, form and match preview for this {$match->league->name} {$match->league->season} fixture.";

      $defaultKeywords = $isLive
          ? "{$match->homeTeam->name}, {$match->awayTeam->name}, {$title}, live score, live match, {$match->league->name}, {$year}"
          : "{$match->homeTeam->name}, {$match->awayTeam->name}, {$title}, match preview, {$match->league->name}, fixture {$year}, kick off time";
  }
@endphp

@section('title', $match->meta_title ?: $defaultTitle)
@section('meta_description', $match->meta_description ?: $defaultDescription)
@section('meta_keywords', $match->meta_keywords ?: $defaultKeywords)
@section('canonical', $match->prettyUrl())
@section('og_title', $match->meta_title ?: $defaultTitle)
@section('og_description', $match->meta_description ?: $defaultDescription)

@section('content')

  <section class="league-hero">
    <div class="wrap">
      <div class="breadcrumb" style="color:#8FA6BA;">
        <a href="{{ route('home') }}" style="color:#9299AA;">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <a href="{{ route('leagues.show', $match->league->slug) }}" style="color:#9299AA;">{{ $match->league->name }}</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
        <span style="color:#fff;">{{ $title }}</span>
      </div>
      <div class="league-hero-inner">
        <span class="league-hero-flag" aria-hidden="true"><svg viewBox="0 0 25 15"><use href="#flag-{{ $match->league->flag_code }}"></use></svg></span>
        <div>
          <div class="league-hero-eyebrow eyebrow" style="color:#8FB4FF;">{{ $match->league->name }}@if($match->venue) &middot; {{ $match->venue }}@endif</div>
          <h1 class="league-hero-title">{{ $title }}</h1>
          <div class="league-hero-meta">
            {{ $match->kickoff_at->format('D j M Y') }} &middot;
            @if($isFinal) Full-Time
            @elseif($isLive && $match->home_score !== null) LIVE &middot; {{ $match->home_score }}-{{ $match->away_score }}
            @elseif($isLive) LIVE &middot; just underway
            @else {{ $match->kickoff_at->format('H:i') }} kick-off
            @endif
          </div>
        </div>
      </div>

      <div class="stat-strip">
        <div class="stat-item">
          <div class="stat-label">Kick-off</div>
          <div class="stat-value" style="font-size:15px;">
            {{ $match->kickoff_at->format('D j M Y') }}
            @if($isFinal) &middot; Full-Time
            @elseif($isLive) &middot; <span class="dot-waiting" aria-hidden="true"></span> LIVE
            @else &middot; {{ $match->kickoff_at->format('H:i') }} UTC
            @endif
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Venue</div>
          <div class="stat-value" style="font-size:15px;">{{ $match->venue ?? 'TBC' }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Referee</div>
          <div class="stat-value" style="font-size:15px;">{{ $match->referee ?? 'TBC' }}</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Result</div>
          <div class="stat-value">
            @if($isFinal) {{ $match->home_score }}-{{ $match->away_score }}
            @elseif($isLive && $match->home_score !== null) {{ $match->home_score }}-{{ $match->away_score }} (LIVE)
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

      {{-- Real commentary data doesn't disappear once the match ends - it
           used to only render while $isLive was true, which hid a match's
           entire commentary feed the moment it went final. Same feed,
           heading just drops the "Live" framing (and pulsing dot) once
           there's nothing live left to claim. --}}
      @if(($isLive || $isFinal) && ! empty($match->commentary))
        <section class="live-commentary" aria-labelledby="commentary-heading">
          <div class="section-head"><h2 id="commentary-heading">@if($isLive)<span class="dot-live" aria-hidden="true"></span> Live Commentary @else Match Commentary @endif</h2></div>
          <div class="commentary-feed">
            @foreach (array_reverse($match->commentary) as $entry)
            <div class="commentary-line">
              <span class="commentary-minute">{{ $entry['minute'] }}'</span>
              <p>{{ $entry['text'] }}</p>
            </div>
            @endforeach
          </div>
        </section>
      @endif

      @if($isFinal)

        @php
          $shownTypes = ['Goal', 'Card', 'subst'];
          $shownEvents = $match->events ? array_values(array_filter($match->events, fn ($e) => in_array($e['type'], $shownTypes))) : [];
        @endphp
        @if(count($shownEvents))
        @php
          $runningHome = 0;
          $runningAway = 0;
        @endphp
        <section aria-labelledby="timeline-heading">
          <div class="section-head"><h2 id="timeline-heading">Match Events</h2></div>
          <div class="table-scroll">
            <table class="standings">
              <thead><tr><th>Min</th><th class="th-team">Team</th><th>Card</th><th class="th-team">Event</th><th>Score</th></tr></thead>
              <tbody>
                @foreach($shownEvents as $event)
                  @php
                    $isHome = $event['team_id'] === $match->homeTeam->api_football_id;
                    $team = $isHome ? $match->homeTeam : $match->awayTeam;
                    $isRed = str_contains($event['detail'], 'Red');
                    $isGoal = $event['type'] === 'Goal';
                    $isSub = $event['type'] === 'subst';
                    $isCard = $event['type'] === 'Card';

                    if ($isGoal) {
                        $isHome ? $runningHome++ : $runningAway++;
                    }

                    if ($isSub) {
                        $description = "Substitution — {$event['player']} on" . ($event['assist'] ? ", {$event['assist']} off" : '');
                    } elseif ($isCard) {
                        $description = $event['player'];
                    } else {
                        $description = "Goal — {$event['player']}" . ($event['assist'] ? " (assist: {$event['assist']})" : '');
                    }
                  @endphp
                  <tr>
                    <td>{{ $event['minute'] }}'</td>
                    <td class="team-td"><span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" style="width:18px;height:20px;"></span> {{ $team->name }}</td>
                    <td>@if($isCard)<span class="card-chip {{ $isRed ? 'card-chip-red' : 'card-chip-yellow' }}" aria-label="{{ $isRed ? 'Red card' : 'Yellow card' }}"></span>@endif</td>
                    <td style="text-align:left;">{{ $description }}</td>
                    <td class="pts">{{ $runningHome }}-{{ $runningAway }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </section>
        @endif

        <section aria-label="Match Report">
          <p style="font-size:15px;line-height:1.7;color:var(--ink);">{{ $match->match_report }}</p>
        </section>

      @else

        @php
          $previewSafeColor = fn (?string $hex) => $hex && preg_match('/^#[0-9a-fA-F]{3,6}$/', $hex) ? $hex : '#1240C4';
          $previewHomeColor = $previewSafeColor($match->homeTeam->color_hex);
          $previewAwayColor = $previewSafeColor($match->awayTeam->color_hex);
          $ordinal = fn ($n) => $n.(in_array($n % 100, [11, 12, 13]) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th'));
        @endphp

        <section aria-labelledby="preview-heading">
          <div class="section-head"><h2 id="preview-heading">Match Preview</h2></div>
          <div style="display:grid;gap:14px;grid-template-columns:1fr;">
            <div class="widget">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge" style="width:28px;height:31px;"></span>
                <strong>{{ $match->homeTeam->name }}</strong>
                @if($homeStanding)<span style="font-size:12.5px;color:var(--ink-faint);">&middot; {{ $ordinal($homeStanding->position) }} &middot; {{ $homeStanding->points }} pts</span>@endif
              </div>
              <p style="font-size:14px;color:var(--ink-muted);line-height:1.6;margin:0;">{{ $match->home_preview_note }}</p>
            </div>
            <div class="widget">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge" style="width:28px;height:31px;"></span>
                <strong>{{ $match->awayTeam->name }}</strong>
                @if($awayStanding)<span style="font-size:12.5px;color:var(--ink-faint);">&middot; {{ $ordinal($awayStanding->position) }} &middot; {{ $awayStanding->points }} pts</span>@endif
              </div>
              <p style="font-size:14px;color:var(--ink-muted);line-height:1.6;margin:0;">{{ $match->away_preview_note }}</p>
            </div>
          </div>
        </section>

        @if($match->prediction)
        <section aria-labelledby="prediction-heading">
          <div class="section-head"><h2 id="prediction-heading">Who Will Win?</h2></div>
          <div class="widget">
            <div class="stat-compare-row" style="grid-template-columns:44px 1fr 44px;">
              <span class="stat-compare-val" style="color:{{ $previewHomeColor }};">{{ $match->prediction['home_pct'] }}%</span>
              <div class="stat-compare-mid">
                <div class="stat-compare-label">Home &middot; Draw ({{ $match->prediction['draw_pct'] }}%) &middot; Away</div>
                <div class="stat-compare-track">
                  <span class="stat-compare-fill" style="width:{{ $match->prediction['home_pct'] }}%;background:{{ $previewHomeColor }};"></span>
                  <span class="stat-compare-fill" style="width:{{ $match->prediction['draw_pct'] }}%;background:var(--ink-faint);"></span>
                  <span class="stat-compare-fill" style="width:{{ $match->prediction['away_pct'] }}%;background:{{ $previewAwayColor }};"></span>
                </div>
              </div>
              <span class="stat-compare-val" style="color:{{ $previewAwayColor }};">{{ $match->prediction['away_pct'] }}%</span>
            </div>
            <p style="font-size:12.5px;color:var(--ink-faint);margin-top:14px;line-height:1.6;">
              This is our view based on both teams' recent form and head-to-head history — not a guarantee, and not betting advice.
            </p>
          </div>
        </section>
        @endif

        @if($homeLastMatch || $awayLastMatch)
        <section aria-labelledby="lastgame-heading">
          <div class="section-head"><h2 id="lastgame-heading">Last Time Out</h2></div>
          <div class="lineup-grid">
            @foreach ([[$match->homeTeam, $homeLastMatch, $previewHomeColor], [$match->awayTeam, $awayLastMatch, $previewAwayColor]] as [$team, $last, $sideColor])
            <div class="widget">
              <div class="events-team-head" style="--side-color:{{ $sideColor }};margin-bottom:12px;padding-bottom:10px;border-bottom:2px solid var(--side-color);display:flex;align-items:center;gap:9px;">
                <span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" style="width:24px;height:26px;"></span>
                <strong>{{ $team->name }}</strong>
              </div>
              @if($last)
                @php
                  $wasHome = $last->home_team_id === $team->id;
                  $opponent = $wasHome ? $last->awayTeam : $last->homeTeam;
                  $ownScore = $wasHome ? $last->home_score : $last->away_score;
                  $oppScore = $wasHome ? $last->away_score : $last->home_score;
                  $resultWord = $ownScore > $oppScore ? 'Won' : ($ownScore < $oppScore ? 'Lost' : 'Drew');
                @endphp
                <a href="{{ $last->prettyUrl() }}" style="color:inherit;text-decoration:none;">
                  <strong>{{ $resultWord }}</strong> {{ $ownScore }}-{{ $oppScore }} {{ $wasHome ? 'vs' : 'at' }} {{ $opponent->name }}
                </a>
                @if($last->stats)
                <div style="margin-top:10px;font-size:12.5px;color:var(--ink-faint);display:flex;gap:14px;flex-wrap:wrap;">
                  @if(isset($last->stats['possession']))<span>Possession: {{ $wasHome ? $last->stats['possession']['home'] : $last->stats['possession']['away'] }}%</span>@endif
                  @if(isset($last->stats['shots']))<span>Shots: {{ $wasHome ? $last->stats['shots']['home'] : $last->stats['shots']['away'] }}</span>@endif
                </div>
                @endif
              @else
              <p style="font-size:13px;color:var(--ink-faint);">No previous match on record yet.</p>
              @endif
            </div>
            @endforeach
          </div>
        </section>
        @endif

        <section aria-labelledby="coaches-heading">
          <div class="section-head"><h2 id="coaches-heading">The Coaches</h2></div>
          <div class="lineup-grid">
            @foreach ([[$match->homeTeam, $previewHomeColor], [$match->awayTeam, $previewAwayColor]] as [$team, $sideColor])
            <div class="widget" style="display:flex;align-items:center;gap:14px;">
              <img src="{{ $team->manager_photo_url ?? asset('apple-touch-icon.png') }}" alt="{{ $team->manager ?? 'Coach' }}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid {{ $sideColor }};flex-shrink:0;">
              <div>
                <div style="font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--ink);">{{ $team->manager ?: 'Not yet confirmed' }}</div>
                <div style="font-size:12.5px;color:var(--ink-faint);margin-top:2px;">
                  {{ $team->name }}
                  @if($team->coach_nationality) &middot; {{ $team->coach_nationality }} @endif
                  @if($team->coach_age) &middot; {{ $team->coach_age }} yrs @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </section>

        <section aria-labelledby="lineups-preview-heading">
          <div class="section-head"><h2 id="lineups-preview-heading">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }} Lineups</h2></div>
          @if($match->lineups && count($match->lineups) === 2)
            @include('partials.pitch-lineups', ['lineups' => $match->lineups, 'homeTeam' => $match->homeTeam, 'awayTeam' => $match->awayTeam])
          @else
            <div class="lineups-unavailable">Lineups haven't been confirmed yet — teams usually announce their starting XI shortly before kick-off. Check back closer to {{ $match->kickoff_at->format('H:i') }} UTC on {{ $match->kickoff_at->format('j F') }}.</div>

            {{-- Real squad lists for both teams, so this fixture page has
                 genuine content while the actual starting XI is still
                 unknown - same squad data and layout as each team's own
                 squad page. --}}
            @foreach ([[$match->homeTeam, $homeSquadByPosition], [$match->awayTeam, $awaySquadByPosition]] as [$squadTeam, $squadByPosition])
            <div class="fixture-squad" style="--team-color:{{ $squadTeam->color_hex }};">
              <div class="fixture-squad-head">
                <span class="crest crest-{{ $squadTeam->crest_code }}" role="img" aria-label="{{ $squadTeam->full_name }} badge"></span>
                <h3><a href="{{ route('teams.show', $squadTeam->slug) }}">{{ $squadTeam->name }}</a> Squad</h3>
              </div>

              @if(collect($squadByPosition)->every(fn ($group) => $group->isEmpty()))
                <p class="squad-empty">No squad list published for {{ $squadTeam->name }} yet.</p>
              @else
                @foreach ($squadByPosition as $label => $players)
                  @if($players->isNotEmpty())
                  <div class="squad-position-title">{{ $label }}</div>
                  <div class="squad-grid">
                    @foreach ($players as $p)
                    <a href="{{ $p->prettyUrl() }}" class="player-card{{ $p->is_captain ? ' is-captain' : '' }}" style="text-decoration:none;color:inherit;">
                      @if($p->photo_url)
                        <img src="{{ $p->photo_url }}" alt="{{ $p->name }}" class="player-photo" loading="lazy">
                      @else
                        <span class="player-photo player-photo-fallback">{{ $p->shirt_number ?? '?' }}</span>
                      @endif
                      <div><div class="player-name">{{ $p->name }}@if($p->is_captain) <span class="cap-tag">(C)</span>@endif</div><div class="player-role">{{ $p->position }}@if($p->shirt_number) &middot; #{{ $p->shirt_number }}@endif</div></div>
                    </a>
                    @endforeach
                  </div>
                  @endif
                @endforeach
              @endif
            </div>
            @endforeach
          @endif
        </section>

        @if($homeNextTwo->isNotEmpty() || $awayNextTwo->isNotEmpty())
        <section aria-labelledby="upcoming-heading">
          <div class="section-head"><h2 id="upcoming-heading">Coming Up Next</h2></div>
          <div class="lineup-grid">
            @foreach ([[$match->homeTeam, $homeNextTwo, $previewHomeColor], [$match->awayTeam, $awayNextTwo, $previewAwayColor]] as [$team, $upcoming, $sideColor])
            <div class="widget">
              <div style="display:flex;align-items:center;gap:9px;margin-bottom:12px;padding-bottom:10px;border-bottom:2px solid {{ $sideColor }};">
                <span class="crest crest-{{ $team->crest_code }}" role="img" aria-label="{{ $team->full_name }} badge" style="width:24px;height:26px;"></span>
                <strong>{{ $team->name }}</strong>
              </div>
              @forelse ($upcoming as $fx)
                @php $opp = $fx->home_team_id === $team->id ? $fx->awayTeam : $fx->homeTeam; @endphp
                <a href="{{ $fx->prettyUrl() }}" style="display:flex;justify-content:space-between;gap:8px;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--ink);text-decoration:none;">
                  <span>{{ $fx->home_team_id === $team->id ? 'vs' : 'at' }} {{ $opp->name }}</span>
                  <span style="color:var(--ink-faint);">{{ $fx->kickoff_at->format('j M, H:i') }}</span>
                </a>
              @empty
                <p style="font-size:13px;color:var(--ink-faint);">No further fixtures scheduled yet.</p>
              @endforelse
            </div>
            @endforeach
          </div>
        </section>
        @endif

      @endif

      <section aria-labelledby="matchup-heading" style="margin-top:0;">
        <div class="section-head"><h2 id="matchup-heading">The Matchup</h2></div>
        <div class="match-grid{{ $isFinal ? ' celebrate-match' : '' }}">
          <div class="match-card" style="grid-column:1/-1;">
            <div class="match-meta">
              <span class="match-comp">{{ $match->league->name }}@if($match->venue) &middot; {{ $match->venue }}@endif</span>
              <span class="match-status{{ $isLive ? ' is-live' : '' }}">
                @if($isFinal)
                  Full-Time
                @elseif($isLive)
                  LIVE
                @else
                  <span class="dot-waiting" aria-hidden="true"></span>{{ $match->kickoff_at->format('D j M Y, H:i') }}
                @endif
              </span>
            </div>
            <div class="match-teams">
              @php $showLiveScore = $isLive && $match->home_score !== null; @endphp
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge"></span><span class="team-name">{{ $match->homeTeam->name }} <span style="color:var(--ink-faint);font-weight:500;">(Home)</span></span></div>@if($isFinal)<span class="team-score{{ $match->home_score > $match->away_score ? ' winning' : '' }}">{{ $match->home_score }}</span>@elseif($showLiveScore)<span class="team-score">{{ $match->home_score }}</span>@endif</div>
              <div class="match-team"><div class="team-id"><span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge"></span><span class="team-name">{{ $match->awayTeam->name }} <span style="color:var(--ink-faint);font-weight:500;">(Away)</span></span></div>@if($isFinal)<span class="team-score{{ $match->away_score > $match->home_score ? ' winning' : '' }}">{{ $match->away_score }}</span>@elseif($showLiveScore)<span class="team-score">{{ $match->away_score }}</span>@endif</div>
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

        <section aria-label="Match Summary" style="margin-top:0;">
          <p style="font-size:15px;line-height:1.7;color:var(--ink);">{{ $bridgeParagraph }}</p>
        </section>

        @php
          $contrastColor = function (?string $hex) {
              $hex = ltrim($hex ?: '#1240C4', '#');
              if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
              if (strlen($hex) !== 6) { return '#FFFFFF'; }
              [$r, $g, $b] = array_map(fn ($h) => hexdec($h), str_split($hex, 2));
              $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

              return $luminance > 0.6 ? '#12151B' : '#FFFFFF';
          };
          $safeColor = fn (?string $hex) => $hex && preg_match('/^#[0-9a-fA-F]{3,6}$/', $hex) ? $hex : '#1240C4';
        @endphp

        @if($match->motm)
        @php $motmColor = $safeColor($match->motm['team_id'] === $match->homeTeam->api_football_id ? $match->homeTeam->color_hex : $match->awayTeam->color_hex); @endphp
        <section aria-labelledby="motm-heading" style="margin-top:0;">
          <div class="section-head"><h2 id="motm-heading">Man of the Match</h2></div>
          <div class="motm-card" style="--motm-color:{{ $motmColor }};">
            <div class="motm-photo-wrap">
              <img class="motm-photo" src="{{ $match->motm['photo'] }}" alt="{{ $match->motm['name'] }}" loading="lazy">
              <span class="motm-rating-badge">{{ number_format($match->motm['rating'], 1) }}</span>
            </div>
            <div class="motm-body">
              <div class="motm-eyebrow">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.6 6.6 7.1.5-5.5 4.5 1.9 6.9L12 16.7 5.9 20.5l1.9-6.9-5.5-4.5 7.1-.5z"/></svg>
                Man of the Match
              </div>
              <div class="motm-name">{{ $match->motm['name'] }}</div>
              <div class="motm-meta">{{ $match->motm['team_name'] }}@if($match->motm['position']) &middot; {{ $match->motm['position'] }}@endif &middot; highest-rated player on the pitch</div>
            </div>
          </div>
        </section>
        @endif

        @if($match->stats)
        @php
          $statsHomeColor = $safeColor($match->homeTeam->color_hex);
          $statsAwayColor = $safeColor($match->awayTeam->color_hex);
          $statRows = [];
          if ($poss = $match->stats['possession'] ?? null) {
              $statRows[] = ['label' => 'Possession', 'home' => $poss['home'], 'away' => $poss['away'], 'homePct' => $poss['home'], 'suffix' => '%'];
          }
          foreach (['shots' => 'Shots', 'shots_on_target' => 'Shots on Target', 'corners' => 'Corners', 'fouls' => 'Fouls', 'yellow_cards' => 'Yellow Cards'] as $key => $label) {
              if (! isset($match->stats[$key])) {
                  continue;
              }
              $homeVal = $match->stats[$key]['home'];
              $awayVal = $match->stats[$key]['away'];
              $total = ($homeVal + $awayVal) ?: 1;
              $statRows[] = ['label' => $label, 'home' => $homeVal, 'away' => $awayVal, 'homePct' => round($homeVal / $total * 100), 'suffix' => ''];
          }
        @endphp
        <section aria-labelledby="stats-heading" style="margin-top:0;">
          <div class="section-head"><h2 id="stats-heading">Match Statistics</h2></div>
          <div class="stats-compare">
            <div class="stats-compare-head">
              <div class="stats-compare-team" style="--side-color:{{ $statsHomeColor }};"><span class="crest crest-{{ $match->homeTeam->crest_code }}" role="img" aria-label="{{ $match->homeTeam->full_name }} badge"></span>{{ $match->homeTeam->name }}</div>
              <div class="stats-compare-team away" style="--side-color:{{ $statsAwayColor }};">{{ $match->awayTeam->name }}<span class="crest crest-{{ $match->awayTeam->crest_code }}" role="img" aria-label="{{ $match->awayTeam->full_name }} badge"></span></div>
            </div>
            @foreach($statRows as $row)
            <div class="stat-compare-row">
              <span class="stat-compare-val" style="color:{{ $statsHomeColor }};">{{ $row['home'] }}{{ $row['suffix'] }}</span>
              <div class="stat-compare-mid">
                <div class="stat-compare-label">{{ $row['label'] }}</div>
                <div class="stat-compare-track">
                  <span class="stat-compare-fill" style="width:{{ $row['homePct'] }}%;background:{{ $statsHomeColor }};"></span>
                  <span class="stat-compare-fill" style="width:{{ 100 - $row['homePct'] }}%;background:{{ $statsAwayColor }};"></span>
                </div>
              </div>
              <span class="stat-compare-val away" style="color:{{ $statsAwayColor }};">{{ $row['away'] }}{{ $row['suffix'] }}</span>
            </div>
            @endforeach
          </div>
        </section>
        @endif

        @if($match->lineups && count($match->lineups) === 2)
        <section aria-labelledby="lineups-heading" style="margin-top:0;">
          <div class="section-head"><h2 id="lineups-heading">{{ $match->homeTeam->name }} vs {{ $match->awayTeam->name }} Lineups</h2></div>
          {{-- Same pitch-graphic partial as the pre-match preview - it's
               purely a starting-XI layout, nothing about it assumes the
               match hasn't been played yet, so the post-match page was
               showing a plain list instead of this for no real reason. --}}
          @include('partials.pitch-lineups', ['lineups' => $match->lineups, 'homeTeam' => $match->homeTeam, 'awayTeam' => $match->awayTeam])
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

          $nextLinkText = fn ($next) => "{$next->homeTeam->name} vs {$next->awayTeam->name}";
          $nextDetails = fn ($next) => "going to play at {$next->kickoff_at->format('j F Y')} in {$next->league->name} {$next->league->season}";
        @endphp

        <section aria-labelledby="ahead-heading" style="margin-top:0;">
          <div class="section-head"><h2 id="ahead-heading">Looking Ahead</h2></div>
          <div style="font-size:15px;line-height:1.7;color:var(--ink);">
            @if($homeNext)
            <p>
              {{ $match->homeTeam->name }} {{ $homeAheadIntro }}. Their next test comes {{ $homeNext->home_team_id === $match->homeTeam->id ? 'at home to' : 'away at' }} {{ $homeNext->home_team_id === $match->homeTeam->id ? $homeNext->awayTeam->name : $homeNext->homeTeam->name }}, and how they respond after this result will be worth watching. You can follow the build-up and everything you need to know when
              <a href="{{ $homeNext->prettyUrl() }}" style="color:var(--accent);text-decoration:underline;">{{ $nextLinkText($homeNext) }}</a> {{ $nextDetails($homeNext) }}.
            </p>
            @else
            <p>{{ $match->homeTeam->name }} do not have a fixture confirmed yet, so their next test is still to be set.</p>
            @endif
            @if($awayNext)
            <p>
              {{ $match->awayTeam->name }}, meanwhile, {{ $awayAheadIntro }}. They are next in action {{ $awayNext->home_team_id === $match->awayTeam->id ? 'at home to' : 'away at' }} {{ $awayNext->home_team_id === $match->awayTeam->id ? $awayNext->awayTeam->name : $awayNext->homeTeam->name }}, a game that will give an early sense of how they carry this result forward. Full details are here:
              <a href="{{ $awayNext->prettyUrl() }}" style="color:var(--accent);text-decoration:underline;">{{ $nextLinkText($awayNext) }}</a> {{ $nextDetails($awayNext) }}.
            </p>
            @else
            <p>{{ $match->awayTeam->name }} do not have a fixture confirmed yet, so their next test is still to be set.</p>
            @endif
          </div>
        </section>
        @endif

        <section aria-labelledby="next-heading" style="margin-top:0;">
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

      @if($sidebarTable->isNotEmpty())
      <div class="widget table-widget">
        <div class="widget-head">
          <h2>{{ $match->league->name }} Table</h2>
        </div>

        @php
          $sidebarRowColor = fn (?string $hex) => $hex && preg_match('/^#[0-9a-fA-F]{3,6}$/', $hex) ? $hex : null;
        @endphp

        <table class="standings">
          <thead><tr><th></th><th class="th-team">Team</th><th>P</th><th>Pts</th></tr></thead>
          <tbody>
            @foreach ($sidebarTable as $s)
            @php
              $isMatchTeam = in_array($s->team_id, [$match->home_team_id, $match->away_team_id], true);
              $rowColor = $isMatchTeam ? $sidebarRowColor($s->team->color_hex) : null;
            @endphp
            <tr class="{{ $s->zone === 'ucl' ? 'zone-ucl' : ($s->zone === 'rel' ? 'zone-rel' : '') }}"@if($rowColor) style="background:color-mix(in srgb, {{ $rowColor }} 8%, transparent);"@endif><td class="pos">{{ $s->position }}</td><td class="team-td"><a href="{{ route('teams.show', $s->team->slug) }}" class="team-td-inner"><span class="crest crest-{{ $s->team->crest_code }}" role="img" aria-label="{{ $s->team->full_name }} badge" style="width:20px;height:22px;"></span>@if($isMatchTeam)<strong>{{ $s->team->name }}</strong>@else{{ $s->team->name }}@endif</a></td><td>{{ $s->played }}</td><td class="pts">{{ $s->points }}</td></tr>
            @endforeach
          </tbody>
        </table>
        <a href="{{ route('leagues.show', $match->league->slug) }}" class="section-link" style="margin-top:14px;display:inline-flex;">View Full {{ $match->league->name }} Points Table
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
      @endif

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
