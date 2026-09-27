/*
##############################################################################################
# seasonalBackground.js version 1.0.0
#  Picks the page body background photo for the current meteorological season at
#  the station's location, swapping seasons for the southern hemisphere, or
#  leaves the plain theme colour when the installer's "Page background" answer
#  was 'solid'.
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
#
#  Sets <html data-bg-season="winter|spring|summer|autumn">; header.css maps each
#  value to img/seasons/<season>.jpg. With 'solid' the attribute is removed.
#
#  Station latitude and the background choice come from the "meta" block of
#  jsondata/archive.json (latitude from weewx.conf's [Station], page_background
#  from [DivumWXCards]). Both are cached in localStorage so the right background
#  is shown at once on later page loads. Until the first successful read, a
#  seasonal photo for the northern hemisphere is assumed. The season is
#  re-checked hourly and whenever the tab becomes visible, so a page left open
#  (e.g. a kiosk) changes over on its own.
*/
(function () {
  'use strict';

  var LAT_CACHE_KEY = 'divumwxStationLatitude';
  var MODE_CACHE_KEY = 'divumwxPageBackground';
  var RECHECK_MS = 60 * 60 * 1000;
  var NORTHERN_SEASON_BY_MONTH = [
    'winter', 'winter', 'spring', 'spring', 'spring', 'summer',
    'summer', 'summer', 'autumn', 'autumn', 'autumn', 'winter'
  ];
  var SOUTHERN_SWAP = { winter: 'summer', summer: 'winter', spring: 'autumn', autumn: 'spring' };

  // Resolve paths against this script's own location so pages in any folder work.
  var scriptSrc = (document.currentScript && document.currentScript.src) || window.location.href;
  var archiveUrl = new URL('jsondata/archive.json', scriptSrc).href;

  function readCache(key) {
    try { return localStorage.getItem(key); } catch (e) { return null; }
  }

  function writeCache(key, value) {
    try { localStorage.setItem(key, String(value)); } catch (e) { /* storage unavailable */ }
  }

  var cachedLat = parseFloat(readCache(LAT_CACHE_KEY));
  var stationLat = isFinite(cachedLat) ? cachedLat : null;
  var mode = readCache(MODE_CACHE_KEY) === 'solid' ? 'solid' : 'seasonal';

  function seasonFor(date, lat) {
    var northern = NORTHERN_SEASON_BY_MONTH[date.getMonth()];
    return (lat == null || lat >= 0) ? northern : SOUTHERN_SWAP[northern];
  }

  function apply() {
    var root = document.documentElement;
    if (mode === 'solid') {
      root.removeAttribute('data-bg-season');
      return;
    }
    var season = seasonFor(new Date(), stationLat);
    if (root.getAttribute('data-bg-season') !== season) {
      root.setAttribute('data-bg-season', season);
    }
  }

  function refreshSettings() {
    if (!window.fetch) return;
    fetch(archiveUrl, { cache: 'no-store' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        var meta = (data && data.meta) || {};
        var lat = parseFloat(meta.latitude);
        if (isFinite(lat)) {
          stationLat = lat;
          writeCache(LAT_CACHE_KEY, lat);
        }
        if (meta.page_background === 'solid' || meta.page_background === 'seasonal') {
          mode = meta.page_background;
          writeCache(MODE_CACHE_KEY, mode);
        }
        apply();
      })
      .catch(function (e) {
        console.warn('seasonalBackground: could not read jsondata/archive.json —', e.message);
      });
  }

  apply();
  refreshSettings();
  setInterval(apply, RECHECK_MS);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) apply();
  });
})();
