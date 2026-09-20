/*
##############################################################################################
# header.js version 1.0.0
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