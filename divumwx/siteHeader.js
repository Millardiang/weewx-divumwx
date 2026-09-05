/*
##############################################################################################
# siteHeader.js version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== siteHeader.js =====================
//
// The ONE shared script behind every DivumWX page's navbar, regardless
// of which navbar file that page includes (navbar.html for the main
// site, astronomyNavbar.html for the astronomy sub-section) and
// regardless of what else is on the page. Every function here is
// deliberately written to no-op cleanly when an element it looks for
// isn't present -- astronomyNavbar.html has no unit selector, for
// instance, and populateUnitSelector() below simply does nothing on a
// page that includes it, rather than needing two different versions of
// this file for two different navbars.
//
// This file replaces what used to be four to five independently
// maintained copies of the same logic (index.html's own inline script,
// header.js, and separate near-identical copies inline in
// constellations.html/meteorShowers.html/records.html) -- that
// duplication is *why* previous fixes kept missing pages: a bug fixed in
// one copy stayed broken in the others until someone happened to notice
// and go find every copy by hand. Every page now loads this ONE file;
// fixing something here fixes it everywhere at once.
//
// A page using this file needs, in order:
//   <script src="cardI18n.js"></script>      (or the full cardsBundleNew.js,
//                                              on pages that already load it
//                                              for weather cards -- either
//                                              defines window.DivumWXI18N)
//   <script src="siteHeader.js"></script>
//   <div class="site-header-include" w3-include-html="navbar.html"></div>
//   (or w3-include-html="astronomyNavbar.html" for the astronomy section)
//   ... then, in the page's own script:
//   includeHTML(() => {
//     initSharedHeader();
//     // + whatever else this specific page needs on navbar-ready
//   });

// ---------------------------------------------------------------------
// includeHTML -- fetches every [w3-include-html] element's referenced
// file and injects it as that element's innerHTML, then fires callback
// once every include has settled (success or failure -- a failed
// include degrades to an empty element, not a stuck page).
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
// fixDivumwxNavLinks -- astronomyNavbar.html ships with its links
// hardcoded to "/divumwx/..." (the common deployment: DivumWX nested
// under a "divumwx" subdirectory of a shared HTML_ROOT). That's wrong
// for a dedicated docroot where HTML_ROOT IS the DivumWX root (no
// "/divumwx" segment at all), and wrong for any deployment where the
// subdirectory is named something other than "divumwx". Rather than
// hardcoding one mode, this derives the actual DivumWX root from the
// CURRENT page's own URL and rewrites the navbar's links to match,
// whatever that root actually is. Safe to call on every page regardless
// of which navbar file it includes: navbar.html's own links are plain
// relative paths, not "/divumwx/...", so this selector simply matches
// nothing there -- no need to skip calling it based on which navbar a
// page happens to use.
//
// Works for pages at the DivumWX root (astronomy.html,
// constellations.html, etc) and pages exactly one level below it in a
// known-named subdirectory (skyfield/, celestial/) -- stripping a
// trailing filename segment, then a trailing "deep directory" segment
// if present, always leaves exactly the DivumWX root's own path,
// regardless of what (if anything) precedes it.
// ---------------------------------------------------------------------
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

// ---------------------------------------------------------------------
// initSharedHeader -- moves #navbar into the correct spot for the
// current viewport width (inline in .nav-wrap when there's room, into
// the hamburger menu's .menu-inner when there isn't), wires up the menu
// toggle, and hides whichever nav link points at the current page. Then
// hands off to populateLanguageSelector()/translateNavbar() below --
// unconditionally, since both are safe no-ops if this page's navbar
// doesn't have a language selector or DivumWXI18N hasn't loaded yet
// (see those functions' own comments for how they recover once it has).
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

  populateUnitSelector();
  populateLanguageSelector();
  translateNavbar();
}
// Handles trailing slashes and directory-root URLs ("/foo/" ->
// "/foo/index.html") so a link comparison works correctly regardless of
// whether the current page's URL has an explicit filename -- a plain
// `.pathname.split('/').pop()` comparison (this file's own earlier,
// simpler version) got this wrong for exactly that case.
function normalizePath(path) {
  path = path.replace(/\/+$/, '') || '/';
  if (!/\.[a-z0-9]+$/i.test(path)) path += '/index.html';
  return path.toLowerCase();
}

// ---------------------------------------------------------------------
// populateUnitSelector / broadcastUnitSystem -- the unit-system dropdown.
// A no-op on any page whose navbar has no #unitSystem (astronomyNavbar.html).
//
// Named NAV_UNIT_KEY/NAV_UNIT_LABELS (not the shorter UNIT_KEY/UNIT_LABELS
// used in earlier per-page copies of this code) because at least one page
// (charts-d3.html) already has its own, completely unrelated top-level
// UNIT_LABELS -- a per-data-type chart-axis-label map, nothing to do with
// the navbar dropdown's option text. That's a `const`, and a later
// `var UNIT_LABELS` from this file sharing the same page would be a hard
// SyntaxError (a var can't coexist with a const of the same name in the
// same scope) that silently takes out charts-d3.html's entire script
// block. Scoping this file's own globals with a distinct prefix avoids
// the whole class of collision rather than hoping no page ever reuses
// the generic name -- which one already had, found only by literally
// trying to run both scripts together, not by inspecting either file
// alone.
// ---------------------------------------------------------------------
var NAV_UNIT_KEY = 'dashboardUnitSystem';
var NAV_UNIT_LABELS = {
  uk: 'UK (\u00B0C, mph, hPa)',
  us: 'US (\u00B0F, mph, inHg)',
  metric: 'Metric (\u00B0C, km/h, hPa)',
  scandi: 'Scandinavian (\u00B0C, m/s, hPa)',
  canada: 'Canada (\u00B0C, km/h, kPa)',
  aviation: 'Aviation (\u00B0C, kt, mbar)',
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
// populateLanguageSelector / translateNavbar -- see index.html's
// original versions of these two functions for the full design
// rationale (timing, why the label shows the current language rather
// than a static caption, why translateNavbar() is guarded to run once).
// Identical behaviour here, just living in one shared place instead of
// copied into every page that needs it.
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
// Registered at file load time, unconditionally -- works even if
// DivumWXI18N's own script tag hasn't finished loading yet when this
// line runs (addEventListener just registers interest in a future
// event, it doesn't need the event's source to exist yet).
window.addEventListener('i18nready', function(){
  populateLanguageSelector();
  translateNavbar();
});
