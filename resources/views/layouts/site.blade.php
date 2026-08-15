<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'The Soccer Goals — Soccer, Covered.')</title>
<meta name="description" content="@yield('meta_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')">
<meta name="keywords" content="@yield('meta_keywords', 'soccer news, football news, Premier League, La Liga, Serie A, Bundesliga, Ligue 1, fixtures, results, points table, football scores')">
<link rel="canonical" href="@yield('canonical', url()->current())">
<meta property="og:type" content="website">
<meta property="og:site_name" content="The Soccer Goals">
<meta property="og:title" content="@yield('og_title', 'The Soccer Goals — Soccer, Covered.')">
<meta property="og:description" content="@yield('og_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')">
<meta property="og:url" content="@yield('canonical', url()->current())">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('og_title', 'The Soccer Goals — Soccer, Covered.')">
<meta name="twitter:description" content="@yield('og_description', 'The Soccer Goals brings you live scores, fixtures, results and points tables for the Premier League, La Liga, Serie A, Bundesliga and Ligue 1, plus in-depth team news and analysis for every major European club.')">
<link rel="stylesheet" href="{{ asset('assets/css/site.css') }}">
</head>
<body>

<svg style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true" focusable="false">
<defs>
<symbol id="flag-eng" viewBox="0 0 25 15"><rect width="25" height="15" fill="#FFF"/><g fill="#CE1124"><rect width="3" height="15" x="11"/><rect width="25" height="3" y="6"/></g></symbol>
<symbol id="flag-esp" viewBox="0 0 3 2"><rect width="3" height="2" fill="#AA151B"/><rect y="0.5" width="3" height="1" fill="#F1BF00"/></symbol>
<symbol id="flag-ita" viewBox="0 0 3 2"><path fill="#008C45" d="M0 0h1v2H0z"/><path fill="#fff" d="M1 0h1v2H1z"/><path fill="#CD212A" d="M2 0h1v2H2z"/></symbol>
<symbol id="flag-deu" viewBox="0 0 5 3"><path d="M0 0h5v3H0z"/><path fill="#D00" d="M0 1h5v2H0z"/><path fill="#FFCE00" d="M0 2h5v1H0z"/></symbol>
<symbol id="flag-fra" viewBox="0 0 3 2"><path fill="#EC1920" d="M0 0h3v2H0z"/><path fill="#fff" d="M0 0h2v2H0z"/><path fill="#051440" d="M0 0h1v2H0z"/></symbol>
</defs>
</svg>

<a class="skip-link" href="#main">Skip to main content</a>

<div class="cookie-bar" id="cookieBar">
  <p><strong>We value your privacy.</strong> The Soccer Goals and our partners use cookies to personalize content, serve relevant ads and analyze traffic. See our <a href="#" style="color:#fff;text-decoration:underline;">Cookie Policy</a>.</p>
  <div class="cookie-actions">
    <button class="btn btn-outline-light btn-sm" id="cookieManage">Manage</button>
    <button class="btn btn-accent btn-sm" id="cookieAccept">Accept All</button>
  </div>
</div>

<div class="top-bar">
  <div class="wrap top-bar-inner">
    <div class="top-bar-date">Thursday, 13 August 2026 <span class="tb-hide-mobile">&nbsp;&middot;&nbsp;Matchday 1 across Europe</span></div>
    <div class="top-bar-links">
      <a href="#" class="tb-hide-mobile">Advertise</a>
      <a href="#" class="tb-hide-mobile">Help Center</a>
      <span class="tb-divider tb-hide-mobile"></span>
      <a href="#">Sign In</a>
      <a href="#" class="tb-register">Register</a>
      <span class="tb-social">
        <a href="#" aria-label="The Soccer Goals on X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23.3 22h-6.9l-5.4-6.7L4.8 22H1.6l8.2-9.4L1 2h7l4.9 6.1L18.9 2Zm-1.2 18h1.9L7.4 4h-2l12.3 16Z"/></svg></a>
        <a href="#" aria-label="The Soccer Goals on Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
      </span>
    </div>
  </div>
</div>

<header class="site-header" id="siteHeader">
  <div class="wrap header-inner">
    <a href="{{ route('home') }}" class="brand" aria-label="The Soccer Goals home">
      <span class="brand-badge" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round"><path d="M3 4h18M3 4v15M21 4v15M3 8.2h4.2M3 12h4.2M3 15.8h4.2M21 8.2h-4.2M21 12h-4.2M21 15.8h-4.2"/></svg></span>
      <span class="brand-word">
        <span class="brand-name">Soccer Goals</span>
        <span class="brand-tag">Soccer, Covered.</span>
      </span>
    </a>

    <nav class="main-nav" aria-label="Primary">
      <ul>
        <li><a class="nav-link" href="{{ route('home') }}">Home</a></li>

        <li class="has-mega" data-mega>
          <button class="nav-link mega-trigger" aria-expanded="false">Leagues
            <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 4l4 4 4-4"/></svg>
          </button>
          <div class="mega-panel">
            <div class="mega-col">
              <div class="mega-col-title">Top 5 Leagues</div>
              <ul>
                <li><a href="{{ route('leagues.show', 'premier-league') }}"><svg class="flag" role="img" aria-label="England flag"><use href="#flag-eng"></use></svg>Premier League</a></li>
                <li><a href="{{ route('leagues.show', 'la-liga') }}"><svg class="flag" role="img" aria-label="Spain flag"><use href="#flag-esp"></use></svg>La Liga</a></li>
                <li><a href="{{ route('leagues.show', 'serie-a') }}"><svg class="flag" role="img" aria-label="Italy flag"><use href="#flag-ita"></use></svg>Serie A</a></li>
                <li><a href="{{ route('leagues.show', 'bundesliga') }}"><svg class="flag" role="img" aria-label="Germany flag"><use href="#flag-deu"></use></svg>Bundesliga</a></li>
                <li><a href="{{ route('leagues.show', 'ligue-1') }}"><svg class="flag" role="img" aria-label="France flag"><use href="#flag-fra"></use></svg>Ligue 1</a></li>
              </ul>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">European Cups</div>
              <ul>
                <li><a href="#">Champions League</a></li>
                <li><a href="#">Europa League</a></li>
                <li><a href="#">Conference League</a></li>
                <li><a href="#">Nations League</a></li>
              </ul>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Quick Links</div>
              <ul>
                <li><a href="{{ route('tables.index') }}">Points Tables</a></li>
                <li><a href="{{ route('fixtures.index') }}">Fixtures</a></li>
                <li><a href="{{ route('results.index') }}">Results</a></li>
                <li><a href="#">Top Scorers</a></li>
                <li><a href="#">Team Directory</a></li>
              </ul>
            </div>
            <div class="mega-feature">
              <span class="eyebrow">Deadline Day</span>
              <h4>Transfer Tracker: every deal, live</h4>
              <p>Follow every signing before the window slams shut.</p>
              <a href="#" class="btn btn-accent btn-sm">Open Tracker</a>
            </div>
          </div>
        </li>

        <li class="has-mega" data-mega>
          <button class="nav-link mega-trigger" aria-expanded="false">News
            <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 4l4 4 4-4"/></svg>
          </button>
          <div class="mega-panel">
            <div class="mega-col">
              <div class="mega-col-title">Categories</div>
              <ul>
                <li><a href="{{ route('news.category', 'match-report') }}">Match Reports</a></li>
                <li><a href="{{ route('news.category', 'transfers') }}">Transfer News</a></li>
                <li><a href="{{ route('news.category', 'analysis') }}">Analysis &amp; Tactics</a></li>
                <li><a href="{{ route('news.category', 'club-news') }}">Club News</a></li>
                <li><a href="{{ route('news.category', 'injury') }}">Injury Updates</a></li>
              </ul>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">By League</div>
              <ul>
                <li><a href="{{ route('leagues.show', 'premier-league') }}">Premier League News</a></li>
                <li><a href="{{ route('leagues.show', 'la-liga') }}">La Liga News</a></li>
                <li><a href="{{ route('leagues.show', 'serie-a') }}">Serie A News</a></li>
                <li><a href="{{ route('leagues.show', 'bundesliga') }}">Bundesliga News</a></li>
              </ul>
            </div>
            <div class="mega-col">
              <div class="mega-col-title">Just In</div>
              <ul>
                <li><a href="#">City edge Chelsea in five-goal thriller</a></li>
                <li><a href="#">Yamal starts as Barca open title defense</a></li>
                <li><a href="#">Napoli confirm deal for Belgian winger</a></li>
              </ul>
            </div>
            <div class="mega-feature">
              <span class="eyebrow">Editor's Pick</span>
              <h4>The Evolution of the Premier League</h4>
              <p>From first whistle to global game &mdash; the long read.</p>
              <a href="#" class="btn btn-accent btn-sm">Read Feature</a>
            </div>
          </div>
        </li>

        <li><a class="nav-link" href="{{ route('fixtures.index') }}">Fixtures</a></li>
        <li><a class="nav-link" href="{{ route('results.index') }}">Results</a></li>
        <li><a class="nav-link" href="{{ route('tables.index') }}">Tables</a></li>
        <li><a class="nav-link" href="#">Transfers</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <button class="icon-btn" aria-label="Search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      </button>
      <button class="btn btn-accent">Subscribe</button>
      <button class="hamburger" id="hamburgerBtn" aria-label="Open menu" aria-expanded="false" aria-controls="navDrawer">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="nav-drawer" id="navDrawer" aria-label="Mobile navigation">
  <div class="drawer-inner">
    <div class="drawer-top">
      <span class="brand-name" style="font-size:19px;">Menu</span>
      <button class="drawer-close" id="drawerCloseBtn" aria-label="Close menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>
    <label class="drawer-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input type="search" placeholder="Search teams, players, news">
    </label>

    <div class="drawer-links">
      <a href="{{ route('home') }}">Home</a>
      <details class="drawer-group">
        <summary>Leagues <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 4l4 4 4-4"/></svg></summary>
        <div class="drawer-sublist">
          <a href="{{ route('leagues.show', 'premier-league') }}"><svg class="flag" role="img" aria-label="England flag"><use href="#flag-eng"></use></svg>Premier League</a>
          <a href="{{ route('leagues.show', 'la-liga') }}"><svg class="flag" role="img" aria-label="Spain flag"><use href="#flag-esp"></use></svg>La Liga</a>
          <a href="{{ route('leagues.show', 'serie-a') }}"><svg class="flag" role="img" aria-label="Italy flag"><use href="#flag-ita"></use></svg>Serie A</a>
          <a href="{{ route('leagues.show', 'bundesliga') }}"><svg class="flag" role="img" aria-label="Germany flag"><use href="#flag-deu"></use></svg>Bundesliga</a>
          <a href="{{ route('leagues.show', 'ligue-1') }}"><svg class="flag" role="img" aria-label="France flag"><use href="#flag-fra"></use></svg>Ligue 1</a>
          <a href="#">Champions League</a>
        </div>
      </details>
      <details class="drawer-group">
        <summary>News <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 4l4 4 4-4"/></svg></summary>
        <div class="drawer-sublist">
          <a href="{{ route('news.category', 'match-report') }}">Match Reports</a>
          <a href="{{ route('news.category', 'transfers') }}">Transfer News</a>
          <a href="{{ route('news.category', 'analysis') }}">Analysis &amp; Tactics</a>
          <a href="{{ route('news.category', 'club-news') }}">Club News</a>
        </div>
      </details>
      <a href="{{ route('fixtures.index') }}">Fixtures</a>
      <a href="{{ route('results.index') }}">Results</a>
      <a href="{{ route('tables.index') }}">Tables</a>
      <a href="#">Transfers</a>
    </div>

    <div class="drawer-cta">
      <a href="#" class="btn btn-ghost btn-block">Sign In</a>
      <a href="#" class="btn btn-accent btn-block">Subscribe</a>
    </div>
  </div>
</div>

<div class="live-ticker" aria-label="Live scores ticker">
  <div class="wrap">
    <span class="ticker-label"><span class="dot-live" aria-hidden="true"></span>LIVE</span>
    <div class="ticker-track" tabindex="0">
      @foreach ($tickerMatches ?? [] as $tm)
      <a href="{{ route('matches.show', $tm->id) }}" class="ticker-chip"><span class="ticker-status">FT</span><span class="ticker-teams"><span class="crest crest-{{ $tm->homeTeam->crest_code }}" role="img" aria-label="{{ $tm->homeTeam->full_name }} badge" style="width:15px;height:17px;"></span> <span class="ticker-score">{{ $tm->home_score }}</span>&ndash;<span class="ticker-score">{{ $tm->away_score }}</span> <span class="crest crest-{{ $tm->awayTeam->crest_code }}" role="img" aria-label="{{ $tm->awayTeam->full_name }} badge" style="width:15px;height:17px;"></span></span></a>
      @endforeach
    </div>
  </div>
</div>

<main id="main">
@yield('content')
</main>

<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <a href="{{ route('home') }}" class="brand" aria-label="The Soccer Goals home">
        <span class="brand-badge" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round"><path d="M3 4h18M3 4v15M21 4v15M3 8.2h4.2M3 12h4.2M3 15.8h4.2M21 8.2h-4.2M21 12h-4.2M21 15.8h-4.2"/></svg></span>
        <span class="brand-word">
          <span class="brand-name">Soccer Goals</span>
          <span class="brand-tag">Soccer, Covered.</span>
        </span>
      </a>
      <p>Independent soccer journalism covering every major league, every matchday, worldwide.</p>
      <div class="footer-social">
        <a href="#" aria-label="X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.6 8.7L23.3 22h-6.9l-5.4-6.7L4.8 22H1.6l8.2-9.4L1 2h7l4.9 6.1L18.9 2Z"/></svg></a>
        <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/></svg></a>
        <a href="#" aria-label="YouTube"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l6 3-6 3z" fill="currentColor" stroke="none"/></svg></a>
      </div>
    </div>

    <div class="footer-col">
      <h3>Leagues</h3>
      <ul>
        <li><a href="{{ route('leagues.show', 'premier-league') }}"><svg class="flag" role="img" aria-label="England flag"><use href="#flag-eng"></use></svg>Premier League</a></li>
        <li><a href="{{ route('leagues.show', 'la-liga') }}"><svg class="flag" role="img" aria-label="Spain flag"><use href="#flag-esp"></use></svg>La Liga</a></li>
        <li><a href="{{ route('leagues.show', 'serie-a') }}"><svg class="flag" role="img" aria-label="Italy flag"><use href="#flag-ita"></use></svg>Serie A</a></li>
        <li><a href="{{ route('leagues.show', 'bundesliga') }}"><svg class="flag" role="img" aria-label="Germany flag"><use href="#flag-deu"></use></svg>Bundesliga</a></li>
        <li><a href="{{ route('leagues.show', 'ligue-1') }}"><svg class="flag" role="img" aria-label="France flag"><use href="#flag-fra"></use></svg>Ligue 1</a></li>
        <li><a href="#">Champions League</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Coverage</h3>
      <ul>
        <li><a href="{{ route('fixtures.index') }}">Fixtures</a></li>
        <li><a href="{{ route('results.index') }}">Results</a></li>
        <li><a href="{{ route('tables.index') }}">Points Tables</a></li>
        <li><a href="#">Top Scorers</a></li>
        <li><a href="#">Transfers</a></li>
        <li><a href="#">Team Directory</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Company</h3>
      <ul>
        <li><a href="#">About Us</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Advertise With Us</a></li>
        <li><a href="#">Editorial Standards</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h3>Legal</h3>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Cookie Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Do Not Sell My Info</a></li>
        <li><a href="#">Accessibility</a></li>
      </ul>
    </div>
  </div>

  <div class="wrap footer-bottom">
    <span>&copy; 2026 The Soccer Goals. All rights reserved.</span>
    <div class="footer-legal">
      <a href="#">Privacy</a>
      <a href="#">Cookies</a>
      <a href="#">Terms</a>
      <a href="#">Sitemap</a>
    </div>
  </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Back to top">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<script src="{{ asset('assets/js/site.js') }}" defer></script>
</body>
</html>
