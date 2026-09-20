/*
##############################################################################################
# siteHeader.js version 1.0.0
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

  populateUnitSelector();
  populateLanguageSelector();
  translateNavbar();
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