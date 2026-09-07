/*
##############################################################################################
# stationTime.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

(function(){
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var REFRESH_MS = 5 * 60 * 1000;

  var tz = null;

  function browserTZ(){
    try {
      return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    } catch (e){
      return 'UTC';
    }
  }

  function getTZ(){
    return tz || browserTZ();
  }

  function applyMeta(data){
    var m = data && data.meta;
    if (m && m.timezone && m.timezone !== tz) {
      // Fires on the first successful fetch (tz goes from null to a real
      // value -- the common case, closing the race window below) AND on
      // any later refresh where the station's configured timezone
      // actually changes, so a long-lived open tab corrects itself too.
      // Deliberately NOT fired on every 5-minute refresh unconditionally
      // -- most of them are a no-op reapplication of the same value, and
      // firing a global event every 5 minutes for nothing would just
      // train every listener to ignore it.
      tz = m.timezone;
      window.dispatchEvent(new CustomEvent('stationtimeready', { detail: { tz: tz } }));
    }
  }

  function refresh(){
    return fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(applyMeta)
      .catch(function(e){
        console.warn('stationTime: timezone fetch failed —', e.message);
      });
  }

  refresh();
  setInterval(refresh, REFRESH_MS);

  window.StationTime = { getTZ: getTZ, refresh: refresh };
})();