/* astro-nav.js -- shared by every page in the astronomy section that
   lives one directory below the main DivumWX site root (currently
   Skyfield and Celestial). Copied once by each skin's own
   [CopyGenerator] copy_once list, same as sky.js/sky.css -- Cheetah
   never touches this file, so it isn't part of what index.html.tmpl
   regenerates each report cycle.

   Two jobs:

   1. Live-syncs this page's theme with the main dashboard's own theme
      setting (toggle/auto/seasonal), on top of the theme the template
      itself already resolved server-side at last report generation.
      Same station coordinates, same auto/seasonal resolution logic as
      the rest of the dashboard.

      Opt-out: a page whose almanac charts are server-rendered SVG with
      their colors baked directly into the markup (both Skyfield's Sky
      page and Celestial's dome/pass-chart) cannot safely repaint those
      charts from a browser-side toggle -- only the surrounding
      CSS-styled chrome would move, leaving a light-plate chart sitting
      inside a dark-plate page (or the reverse) until the next report
      regeneration. Celestial's own realtime_updater.inc goes to real
      lengths to avoid exactly this (a whole reload-on-palette-mismatch
      mechanism, see its own comments) -- fighting that with a second,
      independent live toggle here would reintroduce the very mismatch
      it exists to prevent. A page that can't safely repaint sets
      data-theme-locked="true" on <html>; this script then mirrors
      whatever theme the page already has onto the navbar (so the two
      always agree) instead of live-syncing either to the dashboard.
      The theme button still updates the shared dashboardThemeMode
      preference either way -- other pages navigated to next will
      honour it -- it just won't repaint a locked page itself.

   2. Includes and wires up the astronomy-section navbar
      (astronomyNavbar.html) via the same w3-include-html convention
      the rest of the DivumWX site uses, plus the shared header.js's
      initSharedHeader() for menu-collapse, current-page-link-hiding,
      and unit-option population.

   Loaded with `defer`, so document.body is guaranteed to exist by the
   time this runs -- no separate "before body exists" phase needed the
   way an early, un-deferred <head> script would. */
(function(){
  var STATION_LAT = 51.94;
  var STATION_LON = -0.987;
  var THEME_KEY = 'dashboardThemeMode';
  var LOOP_JSON_URL = '../jsondata/loop.json';
  var ARCHIVE_JSON_URL = '../jsondata/archive.json';
  var ALMANAC_JSON_URL = '../jsondata/almanac.json';
  var NAVBAR_URL = '../astronomyNavbar.html';
  var POLL_MS = 60 * 1000;
  var lastIsDay = null;
  var THEME_LOCKED = document.documentElement.getAttribute('data-theme-locked') === 'true';

  function getSunTimes(date, lat, lon) {
    var rad = Math.PI / 180;
    var msPerDay = 86400000;
    var J1970 = 2440588, J2000 = 2451545;
    var toJulian = function(d){ return d.valueOf() / msPerDay - 0.5 + J1970; };
    var fromJulian = function(j){ return new Date((j + 0.5 - J1970) * msPerDay); };
    var toDays = function(d){ return toJulian(d) - J2000; };
    var obliquity = rad * 23.4397;
    var meanAnomaly = function(d){ return rad * (357.5291 + 0.98560028 * d); };
    var eclipticLon = function(M){
      var C = rad * (1.9148 * Math.sin(M) + 0.02 * Math.sin(2 * M) + 0.0003 * Math.sin(3 * M));
      var perihelion = rad * 102.9372;
      return M + C + perihelion + Math.PI;
    };
    var declination = function(L){ return Math.asin(Math.sin(L) * Math.sin(obliquity)); };
    var julianCycle = function(d, lw){ return Math.round(d - 0.0009 - lw / (2 * Math.PI)); };
    var approxTransit = function(Ht, lw, n){ return 0.0009 + (Ht + lw) / (2 * Math.PI) + n; };
    var solarTransitJ = function(ds, M, L){ return J2000 + ds + 0.0053 * Math.sin(M) - 0.0069 * Math.sin(2 * L); };
    var hourAngle = function(h, phi, dec){ return Math.acos((Math.sin(h) - Math.sin(phi) * Math.sin(dec)) / (Math.cos(phi) * Math.cos(dec))); };
    var lw = rad * -lon, phi = rad * lat;
    var d = toDays(date);
    var n = julianCycle(d, lw);
    var ds = approxTransit(0, lw, n);
    var M = meanAnomaly(ds);
    var L = eclipticLon(M);
    var dec = declination(L);
    var Jnoon = solarTransitJ(ds, M, L);
    var h0 = -0.833 * rad;
    var H = hourAngle(h0, phi, dec);
    if (isNaN(H)) return null;
    var Jset = solarTransitJ(approxTransit(H, lw, n), M, L);
    var Jrise = Jnoon - (Jset - Jnoon);
    return { sunrise: fromJulian(Jrise), sunset: fromJulian(Jset) };
  }
  function getThemeMode() {
    var stored = localStorage.getItem(THEME_KEY);
    return ['dark', 'light', 'auto'].indexOf(stored) > -1 ? stored : 'auto';
  }
  function effectiveTheme(mode) {
    if (mode === 'light' || mode === 'dark') return mode;
    // This page pair has no seasonal palette of its own (just the one
    // light/dark pair) -- 'seasonal' resolves to light here, same
    // fallback the rest of the dashboard's own non-seasonal pages use.
    if (mode === 'seasonal') return 'light';
    if (lastIsDay != null) return lastIsDay ? 'light' : 'dark';
    var now = new Date();
    var sun = getSunTimes(now, STATION_LAT, STATION_LON);
    if (!sun) return 'light';
    return (now >= sun.sunrise && now < sun.sunset) ? 'light' : 'dark';
  }
  function applyTheme() {
    var resolved;
    if (THEME_LOCKED) {
      // Never touch <html>'s own theme class -- mirror whatever the
      // page was generated with, so the navbar and the page's baked
      // SVG charts can never disagree.
      resolved = document.documentElement.className.indexOf('theme-light') >= 0 ? 'light' : 'dark';
    } else {
      resolved = effectiveTheme(getThemeMode());
      // The template itself already sets class="theme-$theme" server-side
      // at generation time (theme-dark or theme-light) -- sky.css only
      // ever defines a :root.theme-light override (dark is simply the
      // absence of it), so leaving a stale theme-dark class alongside a
      // newly-added theme-light would be harmless in practice, but it's
      // still misleading markup. Manage the pair properly instead.
      document.documentElement.classList.remove('theme-dark', 'theme-light');
      document.documentElement.classList.add('theme-' + resolved);
    }
    // header.css (the shared astronomy-section navbar) keys its own
    // dark/light rules off body.dark/body.light, not this page's own
    // theme-light convention -- both need to move together so the
    // navbar and the page content stay visually in sync.
    document.body.classList.remove('light', 'dark');
    document.body.classList.add(resolved);
    return resolved;
  }
  function pollIsDayForTheme() {
    if (THEME_LOCKED) return;  // nothing for a live poll to act on
    fetch(LOOP_JSON_URL + '?_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(j){
        var o = (j && j.observations) || {};
        if (o.isDay != null) {
          lastIsDay = o.isDay;
          if (getThemeMode() === 'auto') applyTheme();
        }
      })
      .catch(function(e){ console.warn('astro-nav: loop.json poll failed --', e.message); });
  }

  function includeHTML(callback) {
    var elements = document.querySelectorAll('[w3-include-html]');
    var pending = elements.length;
    if (pending === 0) { if (callback) callback(); return; }
    elements.forEach(function(el){
      var file = el.getAttribute('w3-include-html');
      fetch(file, { cache: 'no-store' })
        .then(function(res){ if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
        .then(function(html){ el.innerHTML = html; el.removeAttribute('w3-include-html'); })
        .catch(function(e){ console.warn('astro-nav: include failed for', file, '--', e.message); el.innerHTML = ''; })
        .finally(function(){ pending--; if (pending === 0 && callback) callback(); });
    });
  }

  // astronomyNavbar.html ships with its links hardcoded to "/divumwx/..."
  // (the common case: DivumWX nested under a "divumwx" subdirectory of
  // a shared HTML_ROOT). That's wrong for the other supported deployment
  // mode -- a dedicated docroot where HTML_ROOT IS the DivumWX root, so
  // there's no "/divumwx" segment in the URL at all -- and would also be
  // wrong for any deployment where the subdirectory happens to be named
  // something other than "divumwx". Rather than hardcoding one mode and
  // needing a manual re-fix if the deployment differs, this derives the
  // actual DivumWX root from the CURRENT page's own URL and rewrites the
  // navbar's links to match, whatever that root actually is.
  //
  // This works because every page that includes astronomyNavbar.html is
  // either AT the DivumWX root (astronomy.html, constellations.html,
  // meteorShowers.html, visualisations.html) or exactly one level below
  // it, in a known-named subdirectory (skyfield/, celestial/) -- so
  // stripping a trailing filename segment, then a trailing "deep
  // directory" segment if present, always leaves exactly the DivumWX
  // root's own path, regardless of what (if anything) precedes it.
  function fixDivumwxNavLinks(navHost) {
    if (!navHost) return;
    var DEEP_DIRS = ['skyfield', 'celestial'];
    var segments = window.location.pathname.split('/').filter(Boolean);
    if (segments.length && segments[segments.length - 1].indexOf('.') !== -1) {
      segments.pop(); // trailing filename, e.g. "index.html"
    }
    if (segments.length && DEEP_DIRS.indexOf(segments[segments.length - 1]) !== -1) {
      segments.pop(); // trailing "skyfield" or "celestial"
    }
    var root = segments.length ? '/' + segments.join('/') : '';
    navHost.querySelectorAll('a[href^="/divumwx/"], a[href="/divumwx"]').forEach(function(a){
      var href = a.getAttribute('href');
      a.setAttribute('href', root + href.slice('/divumwx'.length));
    });
  }

  function setStationTitle() {
    fetch(ARCHIVE_JSON_URL + '?_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(data){
        var loc = data && data.meta && data.meta.station_location;
        var el = document.querySelector('.brand-text');
        if (loc && el) el.textContent = loc;
      })
      .catch(function(e){ console.warn('astro-nav: station_location fetch failed --', e.message); });
  }

  // Clips the dome's constellation-figure lines to its own circular
  // boundary. A CSS clip-path targeting the <line>/<path> elements
  // directly (an earlier attempt at this) didn't work in practice --
  // per the CSS Masking spec, clip-path on an SVG child element
  // defaults to that element's own "fill-box" as the reference frame,
  // not the shared SVG coordinate space a hardcoded circle(296px at
  // 340px 348px) assumed. This uses SVG's own native clipping instead
  // (the clip-path *presentation attribute*, not the CSS property),
  // which has always resolved in the SVG's local user-space coordinates
  // by default (clipPathUnits="userSpaceOnUse") -- no reference-box
  // ambiguity to get wrong. It also reads the background circle's own
  // real cx/cy/r straight out of the DOM rather than a hardcoded guess,
  // so it's correct regardless of what size dome actually renders.
  function clipDomeLines(){
    document.querySelectorAll('.sec-dome svg, #dome-wrap svg').forEach(function(svg){
      var circles = svg.querySelectorAll('circle');
      if (!circles.length) return;
      // The background is the largest-radius circle in the dome --
      // every star/planet marker is much smaller than the dome itself.
      var bg = null, bgR = -1;
      circles.forEach(function(c){
        var r = parseFloat(c.getAttribute('r')) || 0;
        if (r > bgR) { bgR = r; bg = c; }
      });
      if (!bg) return;
      var cx = bg.getAttribute('cx'), cy = bg.getAttribute('cy'), r = bg.getAttribute('r');
      if (cx == null || cy == null || r == null) return;

      var svgNS = 'http://www.w3.org/2000/svg';
      var defs = svg.querySelector('defs');
      if (!defs) { defs = document.createElementNS(svgNS, 'defs'); svg.insertBefore(defs, svg.firstChild); }

      // Idempotent: reuses an existing clip def rather than creating a
      // new one each call (this function re-runs on every dome DOM
      // change via the MutationObserver below, including changes it
      // just made itself -- appending a fresh <clipPath> every time
      // would itself be a childList mutation on the observed subtree,
      // triggering the observer again, forever).
      var clipCircle = defs.querySelector('clipPath.dome-clip circle');
      var clipPath = defs.querySelector('clipPath.dome-clip');
      if (!clipPath) {
        clipPath = document.createElementNS(svgNS, 'clipPath');
        clipPath.setAttribute('class', 'dome-clip');
        clipPath.setAttribute('id', 'domeClip-' + Math.random().toString(36).slice(2));
        clipCircle = document.createElementNS(svgNS, 'circle');
        clipPath.appendChild(clipCircle);
        defs.appendChild(clipPath);
      }
      clipCircle.setAttribute('cx', cx);
      clipCircle.setAttribute('cy', cy);
      clipCircle.setAttribute('r', r);
      var clipUrl = 'url(#' + clipPath.getAttribute('id') + ')';

      // Only <line> and <path> -- stars/planets (circle) and labels
      // (text) are deliberately left unclipped, matching that those are
      // fine to overflow. Skips anything already correctly clipped
      // (same idempotency reason as above) and anything inside the clip
      // definition itself.
      svg.querySelectorAll('line, path').forEach(function(el){
        if (el.closest('clipPath')) return;
        if (el.getAttribute('clip-path') !== clipUrl) el.setAttribute('clip-path', clipUrl);
      });
    });
  }

  // Refines the DivumWX moon disc (if this page has one -- currently just
  // Skyfield's header) with the exact phase angle and rotation from
  // almanac.json, once it loads. The template's own server-side Cheetah
  // computation (see index.html.tmpl) already renders a correctly-phased,
  // unrotated disc for the first paint -- almanac.json already has both
  // values as plain numbers (SkyfieldLoopData's own almanac.moon.ecliptic_angle
  // and almanac.moon.parallactic_angle, in the exact convention
  // moonDisc.js expects), computed and verified once already, so this
  // sidesteps entirely the guessing this took via $almanac Cheetah tags
  // (ValueHelper vs. bare float, degrees vs. radians -- see the template's
  // own history on this). render() clears its container each call, so
  // calling it again here after the server-side first paint is safe.
  function updateMoonDiscFromAlmanac() {
    var mount = document.getElementById('moonDiscMount');
    if (!mount || typeof DivumWXMoonDisc === 'undefined') return;
    fetch(ALMANAC_JSON_URL + '?_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(alm){
        var phasex = alm['almanac.moon.ecliptic_angle'];
        var tiltDeg = alm['almanac.moon.parallactic_angle'];
        if (typeof phasex !== 'number' || isNaN(phasex)) return;
        DivumWXMoonDisc.render(mount, {
          phasex: phasex,
          tiltDeg: (typeof tiltDeg === 'number' && !isNaN(tiltDeg)) ? tiltDeg : 0,
          size: 60
        });
      })
      .catch(function(e){ console.warn('astro-nav: almanac.json moon disc update failed --', e.message); });
  }

  applyTheme();
  pollIsDayForTheme();
  if (!THEME_LOCKED) {
    setInterval(pollIsDayForTheme, POLL_MS);
    setInterval(function(){
      var m = getThemeMode();
      if (m === 'auto' || m === 'seasonal') applyTheme();
    }, POLL_MS);
    window.addEventListener('storage', function(e){
      if (e.key === THEME_KEY) applyTheme();
    });
  }

  var navHost = document.querySelector('.site-header-include');
  if (navHost && !navHost.getAttribute('w3-include-html')) navHost.setAttribute('w3-include-html', NAVBAR_URL);

  includeHTML(function(){
    fixDivumwxNavLinks(navHost);
    applyTheme();
    if (typeof initSharedHeader === 'function') initSharedHeader();

    var themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
      var THEME_ORDER = ['dark', 'light', 'auto'];
      var THEME_ICONS = { auto: '\u{1F313}', light: '\u2600\uFE0F', dark: '\u{1F319}', seasonal: '\u{1F342}' };
      function refreshThemeBtn(){
        var mode = getThemeMode();
        themeBtn.textContent = THEME_ICONS[mode] || THEME_ICONS.auto;
        themeBtn.title = mode.charAt(0).toUpperCase() + mode.slice(1)
          + (THEME_LOCKED ? ' (applies next visit -- this page\u2019s own plate is fixed at report generation)' : '');
      }
      themeBtn.onclick = function(){
        // Always updates the shared preference, even when locked --
        // other pages navigated to next will honour it; only this
        // page's own repaint is skipped (see applyTheme()).
        var next = THEME_ORDER[(THEME_ORDER.indexOf(getThemeMode()) + 1) % THEME_ORDER.length];
        localStorage.setItem(THEME_KEY, next);
        applyTheme();
        refreshThemeBtn();
      };
      refreshThemeBtn();
    }

    setStationTitle();
    updateMoonDiscFromAlmanac();
  });

  // Runs once immediately (covers Skyfield's server-rendered dome, which
  // never changes after load, and Celestial's own first paint), then
  // re-runs automatically whenever the dome's contents change --
  // Celestial's own realtime_updater.inc periodically replaces the
  // dome's fragment wholesale with freshly-fetched markup (to animate
  // the sky moving between report cycles), which would silently carry
  // fresh, unclipped lines right back in on the next refresh if this
  // only ran once at load.
  clipDomeLines();
  var domeHost = document.querySelector('.sec-dome, #dome-wrap');
  if (domeHost && window.MutationObserver) {
    new MutationObserver(clipDomeLines).observe(domeHost, { childList: true, subtree: true });
  }
})();