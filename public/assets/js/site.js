
(function(){
  "use strict";

  var header = document.getElementById('siteHeader');
  window.addEventListener('scroll', function(){
    header.classList.toggle('is-scrolled', window.scrollY > 4);
    backToTop.classList.toggle('is-visible', window.scrollY > 700);
  }, { passive: true });

  // Mega menus
  var megaItems = Array.prototype.slice.call(document.querySelectorAll('[data-mega]'));
  function closeAllMega(except){
    megaItems.forEach(function(li){
      if (li !== except){
        li.classList.remove('open');
        var btn = li.querySelector('.mega-trigger');
        if (btn) btn.setAttribute('aria-expanded','false');
      }
    });
  }
  megaItems.forEach(function(li){
    var btn = li.querySelector('.mega-trigger');
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      var isOpen = li.classList.contains('open');
      closeAllMega(null);
      li.classList.toggle('open', !isOpen);
      btn.setAttribute('aria-expanded', String(!isOpen));
    });
  });
  document.addEventListener('click', function(){ closeAllMega(null); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeAllMega(null); });

  // Mobile drawer
  var hamburgerBtn = document.getElementById('hamburgerBtn');
  var navDrawer = document.getElementById('navDrawer');
  var drawerOverlay = document.getElementById('drawerOverlay');
  var drawerCloseBtn = document.getElementById('drawerCloseBtn');
  function openDrawer(){
    navDrawer.classList.add('is-open');
    drawerOverlay.classList.add('is-visible');
    hamburgerBtn.setAttribute('aria-expanded','true');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer(){
    navDrawer.classList.remove('is-open');
    drawerOverlay.classList.remove('is-visible');
    hamburgerBtn.setAttribute('aria-expanded','false');
    document.body.style.overflow = '';
  }
  hamburgerBtn.addEventListener('click', openDrawer);
  drawerCloseBtn.addEventListener('click', closeDrawer);
  drawerOverlay.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeDrawer(); });

  // Theme toggle. The blocking script in <head> already applied a saved
  // preference (or left the attribute unset, meaning "follow the OS")
  // before this file loads, so this just has to figure out which mode is
  // actually showing right now and keep the button's aria state in sync.
  var themeToggle = document.getElementById('themeToggle');
  function currentTheme(){
    var explicit = document.documentElement.getAttribute('data-theme');
    if (explicit === 'dark' || explicit === 'light') return explicit;
    return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  }
  function applyTheme(theme){
    document.documentElement.setAttribute('data-theme', theme);
    try { localStorage.setItem('theme', theme); } catch (e) {}
    themeToggle.setAttribute('aria-pressed', String(theme === 'dark'));
    themeToggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
  }
  applyTheme(currentTheme());
  themeToggle.addEventListener('click', function(){
    applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
  });

  // League tabs (match center) - purely presentational toggle
  var leagueTabs = document.querySelectorAll('.league-tabs .tab-btn');
  leagueTabs.forEach(function(tab){
    tab.addEventListener('click', function(){
      leagueTabs.forEach(function(t){ t.setAttribute('aria-selected','false'); });
      tab.setAttribute('aria-selected','true');
    });
  });

  // Standings table tabs
  var ttabs = document.querySelectorAll('.ttab');
  ttabs.forEach(function(tab){
    tab.addEventListener('click', function(){
      var target = tab.getAttribute('data-table');
      ttabs.forEach(function(t){ t.setAttribute('aria-selected', String(t === tab)); });
      document.querySelectorAll('.standings-panel').forEach(function(panel){
        panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
      });
    });
  });


  // Cookie bar
  var cookieBar = document.getElementById('cookieBar');
  if (!sessionStorage.getItem('ninety-cookie-ack')) {
    cookieBar.classList.add('is-visible');
  }
  function ackCookie(){
    cookieBar.classList.remove('is-visible');
    sessionStorage.setItem('ninety-cookie-ack','1');
  }
  document.getElementById('cookieAccept').addEventListener('click', ackCookie);
  document.getElementById('cookieManage').addEventListener('click', ackCookie);

  // Back to top
  var backToTop = document.getElementById('backToTop');
  backToTop.addEventListener('click', function(){
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Celebration confetti: fires once on a single target element, used across
  // the Results list (headline result), a single match's final-result page,
  // and a team's page when their most recent result was a win. Kept to one
  // moment per page rather than one per card, so it reads as a highlight.
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function spawnConfetti(targetEl, delay) {
    if (!targetEl || reduceMotion) return;
    var confettiColors = ['#1552C0', '#0E9F6E', '#D6363C', '#F5B301', '#FFFFFF'];
    setTimeout(function () {
      for (var i = 0; i < 18; i++) {
        var piece = document.createElement('span');
        piece.className = 'confetti-piece';
        piece.style.background = confettiColors[i % confettiColors.length];
        var angle = Math.random() * Math.PI * 2;
        var distance = 34 + Math.random() * 30;
        var x = Math.cos(angle) * distance;
        var y = Math.sin(angle) * distance - 10;
        piece.style.setProperty('--confetti-end', 'translate(' + x.toFixed(0) + 'px,' + y.toFixed(0) + 'px)');
        piece.style.setProperty('--confetti-rot', (Math.random() * 360).toFixed(0) + 'deg');
        piece.style.animationDelay = (Math.random() * 0.12).toFixed(2) + 's';
        targetEl.appendChild(piece);
        piece.addEventListener('animationend', function () { this.remove(); });
      }
    }, delay);
  }

  // 1) Results list page — headline (top) result only.
  var resultsGrid = document.querySelector('.celebrate-results');
  if (resultsGrid) {
    var firstResultCard = resultsGrid.querySelector('.match-card');
    spawnConfetti(firstResultCard && firstResultCard.querySelector('.team-score.winning'), 550);
  }

  // 2) A single match's final-result page — its one winning score.
  var matchCelebrate = document.querySelector('.celebrate-match');
  if (matchCelebrate) {
    spawnConfetti(matchCelebrate.querySelector('.team-score.winning'), 550);
  }

  // 3) Team page — only rendered with this class server-side when the
  //    team's most recent result was a win.
  var teamWinCelebrate = document.querySelector('.celebrate-team-win');
  if (teamWinCelebrate) {
    spawnConfetti(teamWinCelebrate.querySelector('.rs-score'), 350);
  }
})();

(function(){
  "use strict";
  // Today's Matches status filter (All/Live/Upcoming/Finished) - purely
  // client-side, filtering match-cards already in the DOM by their real
  // data-status attribute. No extra query, nothing fake to fetch.
  var tabsWrap = document.querySelector('[data-status-tabs]');
  if (!tabsWrap) return;
  var groupsWrap = document.querySelector('[data-status-groups]');
  var tabs = Array.prototype.slice.call(tabsWrap.querySelectorAll('[data-status-filter]'));
  var cards = Array.prototype.slice.call(groupsWrap.querySelectorAll('.match-card[data-status], .match-row[data-status]'));
  var groups = Array.prototype.slice.call(groupsWrap.querySelectorAll('.today-comp-group'));

  tabsWrap.addEventListener('click', function(e){
    var tab = e.target.closest('[data-status-filter]');
    if (!tab) return;
    var filter = tab.getAttribute('data-status-filter');

    tabs.forEach(function(t){
      t.classList.toggle('is-active', t === tab);
      t.setAttribute('aria-selected', String(t === tab));
    });

    cards.forEach(function(card){
      var show = filter === 'all' || card.getAttribute('data-status') === filter;
      card.classList.toggle('is-hidden', !show);
    });

    groups.forEach(function(group){
      var anyVisible = Array.prototype.slice.call(group.querySelectorAll('.match-card[data-status], .match-row[data-status]'))
        .some(function(card){ return !card.classList.contains('is-hidden'); });
      group.classList.toggle('is-empty', !anyVisible);
    });
  });
})();
