/*
##############################################################################################
# astro-nav.js version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

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

      resolved = document.documentElement.className.indexOf('theme-light') >= 0 ? 'light' : 'dark';
    } else {
      resolved = effectiveTheme(getThemeMode());

      document.documentElement.classList.remove('theme-dark', 'theme-light');
      document.documentElement.classList.add('theme-' + resolved);
    }

    document.body.classList.remove('light', 'dark');
    document.body.classList.add(resolved);
    return resolved;
  }
  function pollIsDayForTheme() {
    if (THEME_LOCKED) return;
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

  function fixDivumwxNavLinks(navHost) {
    if (!navHost) return;
    var DEEP_DIRS = ['skyfield', 'celestial'];
    var segments = window.location.pathname.split('/').filter(Boolean);
    if (segments.length && segments[segments.length - 1].indexOf('.') !== -1) {
      segments.pop();
    }
    if (segments.length && DEEP_DIRS.indexOf(segments[segments.length - 1]) !== -1) {
      segments.pop();
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

  function clipDomeLines(){
    document.querySelectorAll('.sec-dome svg, #dome-wrap svg').forEach(function(svg){
      var circles = svg.querySelectorAll('circle');
      if (!circles.length) return;

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

      svg.querySelectorAll('line, path').forEach(function(el){
        if (el.closest('clipPath')) return;
        if (el.getAttribute('clip-path') !== clipUrl) el.setAttribute('clip-path', clipUrl);
      });
    });
  }

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

  clipDomeLines();
  var domeHost = document.querySelector('.sec-dome, #dome-wrap');
  if (domeHost && window.MutationObserver) {
    new MutationObserver(clipDomeLines).observe(domeHost, { childList: true, subtree: true });
  }
})();