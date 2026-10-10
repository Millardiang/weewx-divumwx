/*
##############################################################################################
# siteHeader.js version 1.0.2
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== siteHeader.js =====================

// ---------------------------------------------------------------------

// ---------------------------------------------------------------------
function includeHTML(callback) {
  var elements = document.querySelectorAll('[w3-include-html]');
  var pending = elements.length;
  if (pending === 0) { if (callback) callback(); return; }
  elements.forEach(function(el){
    var file = el.getAttribute('w3-include-html');
    fetch(file, { cache: 'no-store' })
      .then(function(res){ if (!res.ok) throw new Error('HTTP ' + res.status); return res.text(); })
      .then(function(html){ el.innerHTML = html; el.removeAttribute('w3-include-html'); })
      .catch(function(e){ console.warn('siteHeader: include failed for', file, '\u2014', e.message); el.innerHTML = ''; })
      .finally(function(){ pending--; if (pending === 0 && callback) callback(); });
  });
}

// ---------------------------------------------------------------------

// ---------------------------------------------------------------------
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

// ---------------------------------------------------------------------

// ---------------------------------------------------------------------
function initSharedHeader(){
  fixDivumwxNavLinks(document.querySelector('.site-header-include') || document.querySelector('.site-navbar-include'));

  var navbar = document.getElementById('navbar');
  var navWrap = document.querySelector('.nav-wrap');
  var menuInner = document.querySelector('.menu-inner');
  var menuBtn = document.getElementById('menuBtn');

  function updateNavCollapse(){
    if (!navbar || !navWrap || !menuInner || !menuBtn) return;
    var collapsed = Math.max(1, Math.floor((window.innerWidth - 20) / 319)) <= 3;
    if (collapsed && navbar.parentElement !== menuInner) {
      navbar.classList.add('site-nav--menu');
      menuInner.appendChild(navbar);
    } else if (!collapsed && navbar.parentElement !== navWrap) {
      navbar.classList.remove('site-nav--menu');
      navWrap.insertBefore(navbar, menuBtn);
    }
    menuBtn.style.display = collapsed ? 'flex' : 'none';
  }
  updateNavCollapse();
  window.addEventListener('resize', updateNavCollapse);

  if (menuBtn) {
    menuBtn.onclick = function(){
      document.body.classList.toggle('menu-open');
    };
  }

  var here = normalizePath(window.location.pathname);
  document.querySelectorAll('.site-nav-link[href]').forEach(function(a){
    if (normalizePath(a.pathname) === here) a.style.display = 'none';
  });

  revealOptionalNavLinks(here);

  populateUnitSelector();
  populateLanguageSelector();
  translateNavbar();
}

// ---------------------------------------------------------------------
// Optional nav links (e.g. Skyfield / Celestial on astronomyNavbar.html)
// ship with the `hidden` attribute and data-astro-optional. Each one is
// only shown once its target (e.g. ./skyfield/index.html, already
// rewritten to the real DivumWX root by fixDivumwxNavLinks) is confirmed
// to exist on the server. The link for the page currently being viewed
// stays hidden, matching the rest of the navbar.
// ---------------------------------------------------------------------
function revealOptionalNavLinks(here) {
  document.querySelectorAll('a[data-astro-optional][href]').forEach(function(a){
    if (here && normalizePath(a.pathname) === here) return;
    var url = a.href;
    var show = function(){ a.hidden = false; };
    fetch(url, { method: 'HEAD', cache: 'no-store' })
      .then(function(res){
        if (res.ok) return show();
        // Some servers refuse HEAD; fall back to a GET before giving up.
        if (res.status === 405 || res.status === 501) {
          return fetch(url, { cache: 'no-store' }).then(function(r){ if (r.ok) show(); });
        }
      })
      .catch(function(){ /* not installed / unreachable -- keep hidden */ });
  });
}

function normalizePath(path) {
  path = path.replace(/\/+$/, '') || '/';
  if (!/\.[a-z0-9]+$/i.test(path)) path += '/index.html';
  return path.toLowerCase();
}

// ---------------------------------------------------------------------

// ---------------------------------------------------------------------
var NAV_UNIT_KEY = 'dashboardUnitSystem';
var NAV_UNIT_LABELS = {
  uk: 'UK (\u00B0C, mph, hPa)',
  us: 'US (\u00B0F, mph, inHg)',
  metric: 'Metric (\u00B0C, km/h, hPa)',
  scandi: 'Scandinavian (\u00B0C, m/s, hPa)',
  canada: 'Canada (\u00B0C, km/h, kPa)',
  icao: 'ICAO (\u00B0C, kt, hPa, NM, ft)',
  beaufort: 'Beaufort (\u00B0C, Bft, hPa)'
};
function populateUnitSelector() {
  var select = document.getElementById('unitSystem');
  if (!select) return;
  if (typeof SYSTEMS === 'undefined') {
    console.warn('siteHeader: units.js not loaded \u2014 unit selector disabled');
    select.disabled = true;
    return;
  }
  if (!select.options.length) {
    Object.keys(SYSTEMS).forEach(function(key){
      var opt = document.createElement('option');
      opt.value = key;
      opt.textContent = NAV_UNIT_LABELS[key] || key;
      select.appendChild(opt);
    });
    select.value = localStorage.getItem(NAV_UNIT_KEY) || 'uk';
    select.onchange = function(){
      localStorage.setItem(NAV_UNIT_KEY, select.value);
      broadcastUnitSystem();
      if (typeof getThemeMode === 'function' && typeof applyDashboardTheme === 'function' && getThemeMode() === 'seasonal') applyDashboardTheme();
    };
  }
  broadcastUnitSystem();
}
function broadcastUnitSystem() {
  var select = document.getElementById('unitSystem');
  var system = select ? select.value : (localStorage.getItem(NAV_UNIT_KEY) || 'uk');
  window.dispatchEvent(new CustomEvent('unitsystemchange', {
    detail: { system: system, config: (typeof SYSTEMS !== 'undefined' ? SYSTEMS[system] : null) }
  }));
}

// ---------------------------------------------------------------------

// ---------------------------------------------------------------------
function populateLanguageSelector() {
  var select = document.getElementById('languageSystem');
  var flagImg = document.getElementById('languageFlag');
  var label = document.getElementById('languageLabel');
  if (!select || typeof DivumWXI18N === 'undefined') return;
  if (!select.options.length) {
    DivumWXI18N.getAvailableLanguages().forEach(function(code){
      var opt = document.createElement('option');
      opt.value = code;
      var emoji = DivumWXI18N.getLanguageFlagEmoji(code);
      opt.textContent = (emoji ? emoji + ' ' : '') + DivumWXI18N.getLanguageName(code);
      select.appendChild(opt);
    });
    select.onchange = function(){
      DivumWXI18N.setLanguage(select.value);
    };
  }
  var current = DivumWXI18N.getLanguage();
  select.value = current;
  if (flagImg) {
    var url = DivumWXI18N.getLanguageFlagUrl(current);
    if (url) { flagImg.src = url; flagImg.style.display = ''; }
    else { flagImg.style.display = 'none'; }
  }
  if (label) label.textContent = DivumWXI18N.getLanguageName(current);
}
var _navbarTranslated = false;
function translateNavbar() {
  if (_navbarTranslated || typeof DivumWXI18N === 'undefined') return;
  _navbarTranslated = true;
  document.querySelectorAll('[data-i18n]').forEach(function(el){
    DivumWXI18N.applyLabel(el, el.getAttribute('data-i18n'));
  });
  document.querySelectorAll('[data-i18n-attr]').forEach(function(el){
    var spec = el.getAttribute('data-i18n-attr');
    var sep = spec.indexOf(':');
    if (sep === -1) return;
    var attrs = spec.slice(0, sep).split(',');
    var key = spec.slice(sep + 1);
    attrs.forEach(function(attr){ DivumWXI18N.applyAttr(el, attr.trim(), key); });
  });
}

window.addEventListener('i18nready', function(){
  populateLanguageSelector();
  translateNavbar();
});

// ---------------------------------------------------------------------
// Full-screen toggle (#fullscreenToggle in navbar.html / astronomyNavbar.html)
//
// The choice is remembered in localStorage so that it applies to every
// page. Browsers always leave full screen when a new page loads, and only
// allow it to be entered from a user action, so on each new page the site
// returns to full screen on the visitor's first click, tap or key press.
// Pressing the button again, or leaving full screen with Esc, turns it off.
// ---------------------------------------------------------------------
(function(){
  var FS_KEY = 'dashboardFullscreen';
  var root = document.documentElement;
  var unloading = false;

  function fsSupported() {
    return !!(document.fullscreenEnabled || document.webkitFullscreenEnabled);
  }
  function fsActive() {
    return !!(document.fullscreenElement || document.webkitFullscreenElement);
  }
  function enterFs() {
    try {
      if (root.requestFullscreen) {
        var p = root.requestFullscreen({ navigationUI: 'hide' });
        if (p && p.catch) p.catch(function(e){ console.warn('siteHeader: full screen refused —', e.message); });
      } else if (root.webkitRequestFullscreen) {
        root.webkitRequestFullscreen();
      }
    } catch (e) { console.warn('siteHeader: full screen refused —', e.message); }
  }
  function exitFs() {
    if (document.exitFullscreen) {
      var p = document.exitFullscreen();
      if (p && p.catch) p.catch(function(){});
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
  function getPref() {
    try { return localStorage.getItem(FS_KEY) === '1'; } catch (e) { return false; }
  }
  function setPref(on) {
    try { localStorage.setItem(FS_KEY, on ? '1' : '0'); } catch (e) {}
  }
  function syncButton() {
    var btn = document.getElementById('fullscreenToggle');
    if (btn) btn.setAttribute('aria-pressed', fsActive() ? 'true' : 'false');
  }

  if (!fsSupported()) {
    root.classList.add('no-fullscreen');
    return;
  }

  // Button: event delegation, because the navbar is injected after load
  document.addEventListener('click', function(e){
    var btn = e.target.closest && e.target.closest('#fullscreenToggle');
    if (!btn) return;
    if (fsActive()) {
      setPref(false);
      exitFs();
    } else {
      setPref(true);
      enterFs();
    }
  });

  // Keep the button in step, and treat leaving full screen (e.g. Esc)
  // as turning it off -- unless it happened because the page is unloading.
  function onFsChange() {
    syncButton();
    if (!fsActive() && !unloading) setPref(false);
  }
  document.addEventListener('fullscreenchange', onFsChange);
  document.addEventListener('webkitfullscreenchange', onFsChange);
  window.addEventListener('pagehide', function(){ unloading = true; });
  window.addEventListener('beforeunload', function(){ unloading = true; });
  window.addEventListener('pageshow', function(){ unloading = false; syncButton(); });

  // Resume full screen on this page at the first user interaction
  function resume(e) {
    if (e.type === 'keydown' && e.key === 'Escape') return;
    if (e.target && e.target.closest && e.target.closest('#fullscreenToggle')) { stop(); return; }
    stop();
    if (getPref() && !fsActive()) enterFs();
  }
  function stop() {
    document.removeEventListener('click', resume, true);
    document.removeEventListener('keydown', resume, true);
    document.removeEventListener('touchend', resume, true);
  }
  if (getPref()) {
    document.addEventListener('click', resume, true);
    document.addEventListener('keydown', resume, true);
    document.addEventListener('touchend', resume, true);
  }

  window.addEventListener('i18nready', syncButton);
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', syncButton);
  } else {
    syncButton();
  }
})();
