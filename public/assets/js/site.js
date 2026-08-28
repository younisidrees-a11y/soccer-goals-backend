
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

(function(){
  "use strict";
  // The ticker track scrolls (overflow-x:auto) but its scrollbar is
  // hidden and a plain vertical mouse wheel doesn't move horizontal
  // content on most browsers without Shift held - so on desktop there
  // was no visible or discoverable way to reach anything past the first
  // handful of chips. This translates a normal wheel gesture into
  // horizontal movement, and wires the two arrow buttons, so the strip
  // is actually scrollable with an ordinary mouse, not just touch.
  var track = document.querySelector('[data-ticker-track]');
  if (!track) return;

  track.addEventListener('wheel', function(e){
    if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return; // already horizontal (trackpad) - let it through
    e.preventDefault();
    track.scrollBy({ left: e.deltaY, behavior: 'auto' });
  }, { passive: false });

  Array.prototype.slice.call(document.querySelectorAll('[data-ticker-scroll]')).forEach(function(btn){
    btn.addEventListener('click', function(){
      var dir = parseInt(btn.getAttribute('data-ticker-scroll'), 10) || 1;
      track.scrollBy({ left: dir * Math.round(track.clientWidth * 0.8), behavior: 'smooth' });
    });
  });
})();

(function(){
  "use strict";
  // Header icon panels (Favorites, Live Now): click-to-open/close,
  // closes on outside click or Escape - same interaction pattern as the
  // mega menus above, just a smaller panel.
  var wraps = Array.prototype.slice.call(document.querySelectorAll('[data-panel]'));
  if (!wraps.length) return;

  function closeAll(except){
    wraps.forEach(function(w){
      if (w === except) return;
      w.classList.remove('is-open');
      var t = w.querySelector('[data-panel-toggle]');
      if (t) t.setAttribute('aria-expanded', 'false');
    });
  }

  wraps.forEach(function(wrap){
    var toggle = wrap.querySelector('[data-panel-toggle]');
    if (!toggle) return;
    toggle.addEventListener('click', function(e){
      e.stopPropagation();
      var open = wrap.classList.contains('is-open');
      closeAll(null);
      wrap.classList.toggle('is-open', !open);
      toggle.setAttribute('aria-expanded', String(!open));
    });
  });

  document.addEventListener('click', function(){ closeAll(null); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeAll(null); });
})();

(function(){
  "use strict";
  // Favorite teams: real, working, no account needed - a plain
  // localStorage list of {slug,name,crest}. The heart on a team page
  // writes to it; the header panel reads and renders it. Deliberately
  // storing the team's own name/crest at favorite-time rather than just
  // an id, so the header panel never needs a second lookup or a big
  // client-side team table just to render itself.
  var STORAGE_KEY = 'favoriteTeams';

  function readFavorites(){
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      var parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) { return []; }
  }

  function writeFavorites(list){
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(list)); } catch (e) {}
  }

  function isFavorited(slug){
    return readFavorites().some(function(f){ return f.slug === slug; });
  }

  function toggleFavorite(slug, name, crest){
    var list = readFavorites();
    var idx = list.findIndex(function(f){ return f.slug === slug; });
    if (idx === -1) { list.push({ slug: slug, name: name, crest: crest }); }
    else { list.splice(idx, 1); }
    writeFavorites(list);
    return idx === -1; // true = now favorited
  }

  function renderFavoritesList(){
    var container = document.querySelector('[data-favorites-list]');
    if (!container) return;
    var list = readFavorites();
    if (!list.length) {
      container.innerHTML = '<p class="header-panel-empty">No favorite teams yet &mdash; tap the heart on any team page to add one.</p>';
      return;
    }
    container.innerHTML = list.map(function(f){
      var safeName = String(f.name).replace(/[&<>"']/g, function(c){
        return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
      });
      var safeCrest = String(f.crest || '').replace(/[^a-z0-9-]/gi, '');
      return '<a href="/teams/' + encodeURIComponent(f.slug) + '" class="header-panel-item">' +
        '<span class="crest crest-' + safeCrest + '" role="img" aria-label="' + safeName + ' badge" style="width:18px;height:20px;"></span>' +
        '<span class="header-panel-item-text">' + safeName + '</span>' +
        '<button type="button" class="header-panel-remove" data-fav-remove="' + f.slug + '" aria-label="Remove ' + safeName + ' from favorites">&times;</button>' +
        '</a>';
    }).join('');
  }

  // Team-page toggle button
  var toggleBtn = document.querySelector('[data-fav-toggle]');
  if (toggleBtn) {
    var slug = toggleBtn.getAttribute('data-fav-slug');
    var name = toggleBtn.getAttribute('data-fav-name');
    var crest = toggleBtn.getAttribute('data-fav-crest');
    function reflectToggle(){
      var fav = isFavorited(slug);
      toggleBtn.classList.toggle('is-favorited', fav);
      toggleBtn.setAttribute('aria-pressed', String(fav));
      toggleBtn.setAttribute('aria-label', (fav ? 'Remove ' : 'Add ') + name + (fav ? ' from' : ' to') + ' favorites');
    }
    reflectToggle();
    toggleBtn.addEventListener('click', function(){
      toggleFavorite(slug, name, crest);
      reflectToggle();
      renderFavoritesList();
    });
  }

  renderFavoritesList();

  // Remove button inside the header panel (event delegation - the list
  // is re-rendered on every change, so a static listener would go stale)
  var favoritesList = document.querySelector('[data-favorites-list]');
  if (favoritesList) {
    favoritesList.addEventListener('click', function(e){
      var removeBtn = e.target.closest('[data-fav-remove]');
      if (!removeBtn) return;
      e.preventDefault();
      e.stopPropagation();
      var list = readFavorites().filter(function(f){ return f.slug !== removeBtn.getAttribute('data-fav-remove'); });
      writeFavorites(list);
      renderFavoritesList();
      if (toggleBtn && toggleBtn.getAttribute('data-fav-slug') === removeBtn.getAttribute('data-fav-remove')) {
        toggleBtn.classList.remove('is-favorited');
        toggleBtn.setAttribute('aria-pressed', 'false');
      }
    });
  }
})();
