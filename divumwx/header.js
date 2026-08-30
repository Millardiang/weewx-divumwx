/*
##############################################################################################
# header.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ---- Seasonal accent mode (shared across every page) ----

var SEASON_ICONS = { winter: '\u2744\uFE0F', spring: '\u{1F338}', summer: '\u{1F33B}', autumn: '\u{1F342}' };
var SEASON_CLASSES = ['season-winter', 'season-spring', 'season-summer', 'season-autumn'];
var NORTHERN_SEASON_BY_MONTH = [
  'winter', 'winter', 'spring', 'spring', 'spring', 'summer',
  'summer', 'summer', 'autumn', 'autumn', 'autumn', 'winter'
];
var SEASON_SWAP_SOUTH = { winter: 'summer', summer: 'winter', spring: 'autumn', autumn: 'spring' };
var DEFAULT_STATION_LAT = 51.94;
function getMeteorologicalSeason(date, lat) {
  var season = NORTHERN_SEASON_BY_MONTH[date.getMonth()];
  return ((typeof lat === 'number' ? lat : DEFAULT_STATION_LAT) >= 0) ? season : SEASON_SWAP_SOUTH[season];
}
function seasonLabel(season) {
  if (season === 'autumn' && (localStorage.getItem('dashboardUnitSystem') || 'uk') === 'us') return 'Fall';
  return season.charAt(0).toUpperCase() + season.slice(1);
}

function applySeasonClass(mode, lat) {
  // Seasonal accent mode retired site-wide (removed on request -- the
  // parchment/tan seasonal tinting it applied to cards, hourly panels,
  // and the active day-strip button was broadly disliked and had also
  // developed a genuine CSS specificity bug where the active day-card
  // and the hourly panel resolved to different colours under a season
  // class). Always a no-op now regardless of what mode is passed --
  // including a stale 'seasonal' value some users may still have saved
  // in localStorage from before this was removed, and regardless of
  // whether a page's own THEME_ORDER array still lists 'seasonal' as a
  // cyclable option. document.body still gets any leftover season-*
  // class stripped off (harmless if already absent) so a page that was
  // showing seasonal tinting before this deploy clears it immediately
  // on next load rather than waiting for something else to remove it.
  //
  // Kept as a real function, not deleted -- every calling page still
  // invokes this positionally and several assign its return value to
  // currentSeason, which already handles null today (every existing
  // non-'seasonal' mode already returned null via the old early-return,
  // so this is an already-exercised code path, not a new one).
  //
  // stationforecast.html defines its OWN local applySeasonClass() that
  // shadows this one on that page specifically -- it needed (and got)
  // the identical no-op treatment applied directly in that file, since
  // it doesn't call through to this shared header.js copy at all.
  document.body.classList.remove.apply(document.body.classList, SEASON_CLASSES);
  return null;
}

var HEADER_UNIT_SYSTEM_LABELS = {
  uk: 'UK (\u00B0C, mph, hPa)',
  us: 'US (\u00B0F, mph, inHg)',
  metric: 'Metric (\u00B0C, km/h, hPa)',
  scandi: 'Scandinavian (\u00B0C, m/s, hPa)',
  canada: 'Canada (\u00B0C, km/h, kPa)',
  aviation: 'Aviation (\u00B0C, kt, mbar)',
  beaufort: 'Beaufort (\u00B0C, Bft, hPa)'
};
function populateSharedUnitOptions(){
  var select = document.getElementById('unitSystem');
  if (!select || select.options.length || typeof SYSTEMS === 'undefined') return;
  Object.keys(SYSTEMS).forEach(function(key){
    var opt = document.createElement('option');
    opt.value = key;
    opt.textContent = HEADER_UNIT_SYSTEM_LABELS[key] || key;
    select.appendChild(opt);
  });
  select.value = localStorage.getItem('dashboardUnitSystem') || 'uk';
}

function initSharedHeader(){
  var navbar = document.getElementById('navbar');
  var navWrap = document.querySelector('.nav-wrap');
  var menuInner = document.querySelector('.menu-inner');
  var menuBtn = document.getElementById('menuBtn');

  populateSharedUnitOptions();

  function getGridColumnCount(){
    var grid = document.querySelector('.wrapper');
    if (!grid) return null;
    var cols = window.getComputedStyle(grid).getPropertyValue('grid-template-columns');
    if (!cols) return null;
    var tracks = cols.trim().split(/\s+/).filter(Boolean);
    return tracks.length || null;
  }

  function updateNavCollapse(){
    var colCount = getGridColumnCount();
    var collapsed = (colCount !== null) ? (colCount <= 3) : (window.innerWidth <= 953);
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

  menuBtn.onclick = function(){
    document.body.classList.toggle('menu-open');
  };

  // Hides whichever nav link points at the page currently being viewed.
  // Compares resolved *pathnames* via the anchor's own .pathname DOM
  // property (browser-resolved, so it works the same for the main
  // navbar's plain relative hrefs like "records.html" and the astronomy
  // navbar's root-relative ones like "/skyfield"), rather than the raw
  // href text against window.location's last segment -- that string
  // comparison silently never matched the root-relative style.
  function normalizePath(path){
    path = path.replace(/\/+$/, '') || '/';
    if (!/\.[a-z0-9]+$/i.test(path)) path += '/index.html';
    return path.toLowerCase();
  }
  var herePath = normalizePath(window.location.pathname);
  document.querySelectorAll('.site-nav-link[href]').forEach(function(a){
    if (normalizePath(a.pathname) === herePath) a.style.display = 'none';
  });
}