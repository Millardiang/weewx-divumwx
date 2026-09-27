/*
##############################################################################################
# seasonalBackground.js version 1.0.0
#  Picks the page body background photo for the current meteorological season at
#  the station's location, swapping seasons for the southern hemisphere.
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
#
#  Sets <html data-bg-season="winter|spring|summer|autumn">; header.css maps each
#  value to img/seasons/<season>.jpg.
#
#  Station latitude comes from jsondata/archive.json (written by WeeWX from
#  weewx.conf). It is cached in localStorage so the right photo is chosen at once
#  on later page loads. Until the first successful read, the northern hemisphere
#  is assumed. The season is re-checked hourly and whenever the tab becomes
#  visible, so a page left open (e.g. a kiosk) changes over on its own.
*/
(function () {
  'use strict';

  var LAT_CACHE_KEY = 'divumwxStationLatitude';
  var RECHECK_MS = 60 * 60 * 1000;
  var NORTHERN_SEASON_BY_MONTH = [
    'winter', 'winter', 'spring', 'spring', 'spring', 'summer',
    'summer', 'summer', 'autumn', 'autumn', 'autumn', 'winter'
  ];
  var SOUTHERN_SWAP = { winter: 'summer', summer: 'winter', spring: 'autumn', autumn: 'spring' };

  // Resolve paths against this script's own location so pages in any folder work.
  var scriptSrc = (document.currentScript && document.currentScript.src) || window.location.href;
  var archiveUrl = new URL('jsondata/archive.json', scriptSrc).href;

  function readCachedLat() {
    try {
      var v = parseFloat(localStorage.getItem(LAT_CACHE_KEY));
      return isFinite(v) ? v : null;
    } catch (e) { return null; }
  }

  function writeCachedLat(lat) {
    try { localStorage.setItem(LAT_CACHE_KEY, String(lat)); } catch (e) { /* storage unavailable */ }
  }

  var stationLat = readCachedLat();

  function seasonFor(date, lat) {
    var northern = NORTHERN_SEASON_BY_MONTH[date.getMonth()];
    return (lat == null || lat >= 0) ? northern : SOUTHERN_SWAP[northern];
  }

  function apply() {
    var season = seasonFor(new Date(), stationLat);
    var root = document.documentElement;
    if (root.getAttribute('data-bg-season') !== season) {
      root.setAttribute('data-bg-season', season);
    }
  }

  function refreshLatitude() {
    if (!window.fetch) return;
    fetch(archiveUrl, { cache: 'no-store' })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (data) {
        var lat = parseFloat(data && data.latitude);
        if (!isFinite(lat)) return;
        stationLat = lat;
        writeCachedLat(lat);
        apply();
      })
      .catch(function (e) {
        console.warn('seasonalBackground: could not read station latitude —', e.message);
      });
  }

  apply();
  refreshLatitude();
  setInterval(apply, RECHECK_MS);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) apply();
  });
})();
