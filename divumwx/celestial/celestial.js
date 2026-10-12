/*
##############################################################################################
# celestial.js version 1.0.0
#  The live Celestial page: countdown row + Geocentric dial (D3 v7).
#
#  Reads ../jsondata/almanac.json, written by SkyfieldLoopData in
#  bin/user/divumwx.py (0.2.0 or later for the celestial.* fields).
#
#  How it stays live (after weewx-celestial's own approach):
#   - The feed is polled every POLL_MS. Each new record (a new
#     current.dateTime.raw) becomes a sample.
#   - Between records, every readout advances at the body's own rate,
#     derived from the last two samples -- for at most a few feed
#     intervals. If the feed goes stale, readouts freeze rather than
#     inventing positions.
#   - "Now" is the SERVER's clock, estimated from each response's HTTP
#     Date header, so a visitor whose own clock is wrong still sees the
#     right countdowns and an honest LIVE/STALE badge.
#
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/
(function(){
  'use strict';

  // Data URLs are resolved from this script's own address, so any page in
  // the site can load it (the Celestial and Skyfield pages both do).
  var BASE = '';
  try { if (document.currentScript && document.currentScript.src) BASE = new URL('.', document.currentScript.src).href; } catch (e) {}
  var ALMANAC_URL = BASE + '../jsondata/almanac.json';
  var ARCHIVE_URL = BASE + '../jsondata/archive.json';
  // Which of this script's panels the page has; each renderer skips its own
  // when absent, so a page can use the feed with only some of them.
  var HAS = {
    live: !!document.getElementById('celLive'),
    chips: !!document.getElementById('celCountdowns'),
    dial: !!document.getElementById('celDial'),
    roster: !!document.getElementById('celRoster')
  };
  var POLL_MS = 5000;
  var TICK_MS = 1000;
  var TRAIL_SECONDS = 3600;
  var TRAIL_MIN_STEP = 20;              // seconds between stored trail points
  var TRAIL_KEY = 'divumwxCelestialTrails.v1';
  var WINDOW_DAYS = 30;                 // windowed chips appear this close to their event
  var JUST_PASSED_S = 600;              // a single-instant chip shows "Now" this long
  var AU_KM = 149597870.7;
  var LY_AU = 63241.077;
  var KM_MI = 0.621371192;

  var BODIES = [
    { key: 'sun',              name: 'Sun',      kind: 'sun'    },
    { key: 'moon',             name: 'Moon',     kind: 'moon'   },
    { key: 'mercury',          name: 'Mercury',  kind: 'planet' },
    { key: 'venus',            name: 'Venus',    kind: 'planet' },
    { key: 'mars',             name: 'Mars',     kind: 'planet' },
    { key: 'jupiter',          name: 'Jupiter',  kind: 'planet' },
    { key: 'saturn',           name: 'Saturn',   kind: 'planet' },
    { key: 'uranus',           name: 'Uranus',   kind: 'planet' },
    { key: 'neptune',          name: 'Neptune',  kind: 'planet' },
    { key: 'pluto',            name: 'Pluto',    kind: 'planet' },
    { key: 'proxima_centauri', name: 'Proxima Centauri', label: 'Proxima', kind: 'star' }
  ];
  var BODY_BY_KEY = {};
  BODIES.forEach(function(b){ BODY_BY_KEY[b.key] = b; });

  // ------------------------------------------------------------------
  // State
  // ------------------------------------------------------------------
  var samples = [];          // last few distinct records: { t, d }
  var skewSamples = [];      // server clock minus client clock, ms
  var skewMs = 0;
  var lastFetchOk = null;    // client ms of the last successful fetch
  var tz = null;
  var stationName = null;
  var trails = loadTrails();
  var focusKey = null;
  var labelSide = {};        // sticky label placement per body
  var geom = null;           // dial geometry, set by layoutDial()

  // ------------------------------------------------------------------
  // Small helpers
  // ------------------------------------------------------------------
  function num(v){ return (typeof v === 'number' && isFinite(v)) ? v : null; }
  function serverNow(){ return (Date.now() + skewMs) / 1000; }
  function median(a){
    if (!a.length) return 0;
    var s = a.slice().sort(function(x, y){ return x - y; });
    return s[Math.floor(s.length / 2)];
  }
  function wrap180(x){ return ((x % 360) + 540) % 360 - 180; }
  function useMiles(){
    try { var u = localStorage.getItem('dashboardUnitSystem') || 'uk'; return u === 'us' || u === 'uk'; }
    catch (e) { return false; }
  }
  function tzOpts(o){
    if (tz) o.timeZone = tz;
    return o;
  }
  function fmtTime(ts, withSeconds){
    var o = { hour: '2-digit', minute: '2-digit', hour12: false };
    if (withSeconds) o.second = '2-digit';
    try { return new Date(ts * 1000).toLocaleTimeString(undefined, tzOpts(o)); }
    catch (e) { delete o.timeZone; return new Date(ts * 1000).toLocaleTimeString(undefined, o); }
  }
  function fmtWhen(ts, now){
    var ahead = ts - now;
    var o;
    if (ahead < 6 * 86400) o = { weekday: 'short', hour: '2-digit', minute: '2-digit', hour12: false };
    else if (ahead < 300 * 86400) o = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit', hour12: false };
    else o = { day: 'numeric', month: 'short', year: 'numeric' };
    try { return new Date(ts * 1000).toLocaleString(undefined, tzOpts(o)); }
    catch (e) { delete o.timeZone; return new Date(ts * 1000).toLocaleString(undefined, o); }
  }
  function monthOf(ts){
    try { return Number(new Date(ts * 1000).toLocaleString('en-GB', tzOpts({ month: 'numeric' }))); }
    catch (e) { return new Date(ts * 1000).getMonth() + 1; }
  }
  function pad2(n){ return (n < 10 ? '0' : '') + n; }
  function fmtCountdown(sec){
    if (sec <= 0) return 'Now';
    sec = Math.floor(sec);
    var d = Math.floor(sec / 86400), h = Math.floor(sec % 86400 / 3600),
        m = Math.floor(sec % 3600 / 60), s = sec % 60;
    if (d > 0) return d + '<small>d</small> ' + pad2(h) + '<small>h</small> ' + pad2(m) + '<small>m</small>';
    if (h > 0) return h + '<small>h</small> ' + pad2(m) + '<small>m</small> ' + pad2(s) + '<small>s</small>';
    return m + '<small>m</small> ' + pad2(s) + '<small>s</small>';
  }
  function fmtAge(sec){
    if (sec < 0) sec = 0;
    if (sec < 90) return Math.round(sec) + ' s ago';
    if (sec < 5400) return Math.round(sec / 60) + ' min ago';
    return Math.round(sec / 3600) + ' h ago';
  }
  var COMPASS = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
  function compass(az){ return COMPASS[Math.round(((az % 360) + 360) % 360 / 22.5) % 16]; }
  var nf0 = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });
  var nf1 = new Intl.NumberFormat(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 });
  var nf2 = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  var nf4 = new Intl.NumberFormat(undefined, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
  var nf5 = new Intl.NumberFormat(undefined, { minimumFractionDigits: 5, maximumFractionDigits: 5 });
  function fmtLinear(km, decimals){
    var miles = useMiles();
    var v = miles ? km * KM_MI : km;
    return (decimals ? nf1.format(v) : nf0.format(v)) + (miles ? ' mi' : ' km');
  }
  function fmtSigned(x, f){ return (x < 0 ? '−' : '+') + f.format(Math.abs(x)); }
  function bodyColor(key){
    var cs = getComputedStyle(document.body);
    var own = cs.getPropertyValue('--b-' + key).trim();
    if (own) return own;
    // A comet or satellite added in weewx.conf without its own colour.
    if (cometsNow().some(function(c){ return c.key === key; })) return cs.getPropertyValue('--b-comet').trim() || '#999';
    if (satellitesNow().some(function(s){ return s.tag === key; })) return cs.getPropertyValue('--b-satellite').trim() || '#999';
    return '#999';
  }

  // Comets and satellites are whatever weewx.conf configures, so they
  // come from the latest record rather than a fixed list.
  function cometsNow(){
    var s = samples[samples.length - 1];
    var list = s && s.d['celestial.comets'];
    return Array.isArray(list) ? list.map(function(c){
      return { key: c.tag, name: c.label, label: c.label, kind: 'comet', designation: c.designation,
               missing: !!c.missing, info: c };
    }) : [];
  }
  function satellitesNow(){
    var s = samples[samples.length - 1];
    var list = s && s.d['celestial.satellites'];
    return Array.isArray(list) ? list : [];
  }
  function allBodies(){ return BODIES.concat(cometsNow()); }

  // A satellite's pass record; 'next_visible_pass' may be the string
  // 'next_pass', meaning the next pass is itself the visible one.
  function passOf(sat, which){
    var p = sat && sat[which];
    if (p === 'next_pass') p = sat.next_pass;
    return (p && typeof p === 'object') ? p : null;
  }
  // Where a satellite is on a pass at time t, from the pass's sampled
  // track: { alt, az, lit } or null outside the pass.
  function trackAt(pass, t){
    var tr = pass && pass.track;
    if (!tr || !tr.length) return null;
    var dt = t - pass.rise;
    if (dt < tr[0][0] || dt > tr[tr.length - 1][0]) return null;
    var i = 1;
    while (i < tr.length - 1 && tr[i][0] < dt) i++;
    var a = tr[i - 1], b = tr[i];
    var f = b[0] > a[0] ? (dt - a[0]) / (b[0] - a[0]) : 0;
    return { alt: a[1] + (b[1] - a[1]) * f,
             az: ((a[2] + wrap180(b[2] - a[2]) * f) % 360 + 360) % 360,
             lit: !!(f < 0.5 ? a[3] : b[3]) };
  }

  // ------------------------------------------------------------------
  // Feed: fetch, clock skew, samples
  // ------------------------------------------------------------------
  function poll(){
    var sent = Date.now();
    fetch(ALMANAC_URL + '?_=' + sent, { cache: 'no-store' })
      .then(function(r){
        if (!r.ok) throw new Error('HTTP ' + r.status);
        var dateHdr = r.headers.get('Date');
        var got = Date.now();
        if (dateHdr) {
          var srv = Date.parse(dateHdr);
          // Date has one-second resolution: +500 ms is its expected midpoint.
          if (!isNaN(srv)) {
            skewSamples.push(srv + 500 - (sent + got) / 2);
            if (skewSamples.length > 9) skewSamples.shift();
            skewMs = median(skewSamples);
            if (Math.abs(skewMs) < 1500) skewMs = 0;   // within Date's own resolution
          }
        }
        return r.json();
      })
      .then(function(d){
        lastFetchOk = Date.now();
        var t = num(d['current.dateTime.raw']);
        if (t == null) return;
        var last = samples[samples.length - 1];
        if (last && last.t === t) { last.d = d; return; }
        if (last && t < last.t) samples = [];          // clock went backwards: start over
        samples.push({ t: t, d: d });
        if (samples.length > 6) samples.shift();
        addTrailPoints(t, d);
        if (samples.length === 1) { onFirstData(); }
        tick();
      })
      .catch(function(e){
        console.warn('celestial: almanac.json fetch failed —', e.message);
        tick();
      });
  }

  function loadMeta(){
    fetch(ARCHIVE_URL + '?_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(a){
        var m = (a && a.meta) || {};
        if (m.timezone) {
          try { new Intl.DateTimeFormat(undefined, { timeZone: m.timezone }); tz = m.timezone; }
          catch (e) { tz = null; }
        }
        if (m.station_location) {
          stationName = m.station_location;
          document.querySelectorAll('[data-station-name]').forEach(function(el){ el.textContent = m.station_location; });
          var st = document.getElementById('celStation');
          if (st) st.textContent = m.station_location;
        }
        tick();
      })
      .catch(function(e){ console.warn('celestial: archive.json fetch failed —', e.message); });
  }

  function feedInterval(){
    var diffs = [];
    for (var i = 1; i < samples.length; i++) diffs.push(samples[i].t - samples[i - 1].t);
    var m = median(diffs);
    return m > 0 ? m : 10;
  }
  function staleAfter(){ return Math.max(45, 4 * feedInterval()); }
  function isStale(now){
    var last = samples[samples.length - 1];
    return !last || (now - last.t) > staleAfter();
  }

  // Seconds to advance the latest sample by to reach "now": zero when the
  // feed is stale, and never more than three feed intervals.
  function advanceFor(now){
    var s1 = samples[samples.length - 1];
    if (!s1 || isStale(now)) return 0;
    return Math.max(0, Math.min(now - s1.t, 3 * feedInterval()));
  }

  // A body's position "now": the latest sample advanced at the rate
  // between the last two samples, for at most three feed intervals.
  function bodyAt(key, now){
    var s1 = samples[samples.length - 1];
    if (!s1) return null;
    var p = 'almanac.' + key + '.';
    var az = num(s1.d[p + 'az']), alt = num(s1.d[p + 'alt']), dist = num(s1.d[p + 'earth_distance']);
    if (az == null || alt == null || dist == null) return null;
    var out = { key: key, az: az, alt: alt, dist: dist, rAz: 0, rAlt: 0, rDist: 0 };
    var s0 = samples[samples.length - 2];
    if (s0) {
      var dt = s1.t - s0.t;
      var az0 = num(s0.d[p + 'az']), alt0 = num(s0.d[p + 'alt']), d0 = num(s0.d[p + 'earth_distance']);
      if (dt > 0 && dt <= 600 && az0 != null && alt0 != null && d0 != null) {
        out.rAz = wrap180(az - az0) / dt;
        out.rAlt = (alt - alt0) / dt;
        out.rDist = (dist - d0) / dt;
      }
    }
    var adv = advanceFor(now);
    out.az = ((az + out.rAz * adv) % 360 + 360) % 360;
    out.alt = alt + out.rAlt * adv;
    out.dist = dist + out.rDist * adv;
    out.up = out.alt > 0;
    return out;
  }

  // ------------------------------------------------------------------
  // Trails (kept per browser for an hour, so a reload doesn't lose them)
  // ------------------------------------------------------------------
  function loadTrails(){
    try {
      var raw = localStorage.getItem(TRAIL_KEY);
      var t = raw ? JSON.parse(raw) : {};
      return (t && typeof t === 'object') ? t : {};
    } catch (e) { return {}; }
  }
  function saveTrails(){
    try { localStorage.setItem(TRAIL_KEY, JSON.stringify(trails)); } catch (e) {}
  }
  function addTrailPoints(t, d){
    allBodies().forEach(function(b){
      var p = 'almanac.' + b.key + '.';
      var az = num(d[p + 'az']), alt = num(d[p + 'alt']), dist = num(d[p + 'earth_distance']);
      if (az == null || dist == null) return;
      var list = trails[b.key] || (trails[b.key] = []);
      var last = list[list.length - 1];
      if (last && t - last[0] < TRAIL_MIN_STEP) return;
      list.push([t, +az.toFixed(3), +(alt || 0).toFixed(3), +dist.toPrecision(7)]);
    });
    Object.keys(trails).forEach(function(k){
      var list = trails[k];
      if (!Array.isArray(list)) { delete trails[k]; return; }
      while (list.length && (t - list[0][0] > TRAIL_SECONDS || list[0][0] > t + 60)) list.shift();
    });
    saveTrails();
  }

  // ------------------------------------------------------------------
  // Countdown chips
  // ------------------------------------------------------------------
  function firstAfter(list, now){
    if (!Array.isArray(list)) return null;
    for (var i = 0; i < list.length; i++) if (num(list[i]) != null && list[i] > now) return list[i];
    return null;
  }

  // The soonest visible satellite pass (one in progress first), or null.
  function soonestVisiblePass(now){
    var best = null;
    satellitesNow().forEach(function(s){
      var vp = passOf(s, 'next_visible_pass');
      if (!vp || num(vp.visible_end) == null || vp.visible_end <= now) return;
      if (!best || vp.visible_start < best.pass.visible_start) best = { sat: s, pass: vp };
    });
    return best;
  }

  function buildChips(d, now){
    var chips = [];
    var sun = bodyAt('sun', now);

    // 0. The next visible satellite pass.
    var vis = soonestVisiblePass(now);
    if (vis) {
      var vp = vis.pass, a = trackAt(vp, vp.visible_start), b = trackAt(vp, vp.visible_end);
      var dirs = (a && b) ? compass(a.az) + ' → ' + compass(b.az) : '';
      var live = vp.visible_start <= now;
      chips.push({
        id: 'sat', kind: 'sat',
        label: vis.sat.label + (live ? ' visible now' : ' visible pass'),
        at: live ? vp.visible_end : vp.visible_start,
        when: live ? 'Until ' + fmtTime(vp.visible_end) : null,
        live: live,
        extra: 'Max ' + Math.round(vp.max_altitude) + '°' + (dirs ? ' · ' + dirs : '')
      });
    }

    // 1. Sunset / sunrise -- rolls itself from the next-few-days lists.
    var nextRise = firstAfter(d['celestial.sun.risings'], now);
    var nextSet = firstAfter(d['celestial.sun.settings'], now);
    if (nextRise == null && num(d['almanac.sun.next_rising.unix_epoch.raw']) > now) nextRise = d['almanac.sun.next_rising.unix_epoch.raw'];
    if (nextSet == null && num(d['almanac.sun.next_setting.unix_epoch.raw']) > now) nextSet = d['almanac.sun.next_setting.unix_epoch.raw'];
    if (nextRise != null || nextSet != null) {
      var isSet = nextSet != null && (nextRise == null || nextSet < nextRise);
      chips.push({
        id: 'sun', kind: 'sun',
        label: isSet ? 'Sunset' : 'Sunrise',
        at: isSet ? nextSet : nextRise,
        extra: sun ? 'Sun at ' + fmtSigned(sun.alt, nf1) + '°' : ''
      });
    }

    // 2. Astronomical darkness.
    var starts = d['celestial.astro_dark.starts'], ends = d['celestial.astro_dark.ends'];
    if (Array.isArray(starts) && Array.isArray(ends)) {
      var ns = firstAfter(starts, now), ne = firstAfter(ends, now);
      if (ns == null && ne == null) {
        chips.push({ id: 'dark', kind: 'dark', label: 'Full darkness', text: 'None', extra: 'Sun stays above −18° for the next few nights' });
      } else if (ne != null && (ns == null || ne < ns)) {
        chips.push({ id: 'dark', kind: 'dark', label: 'Darkness ends', at: ne, extra: 'Astronomical: Sun climbs past −18°' });
      } else {
        chips.push({ id: 'dark', kind: 'dark', label: 'Darkness begins', at: ns, extra: 'Astronomical: Sun sinks past −18°' });
      }
    }

    // 3. Next meteor shower peak.
    var showers = d['celestial.meteor_showers'];
    var shower = null;
    if (Array.isArray(showers)) {
      for (var i = 0; i < showers.length; i++) {
        if (num(showers[i].peak) != null && showers[i].peak > now) { shower = showers[i]; break; }
      }
    }
    if (shower) {
      chips.push({
        id: 'meteor', kind: 'meteor', label: shower.name + ' peak', at: shower.peak,
        extra: 'ZHR ~' + shower.zhr + (num(shower.moon_illumination) != null ? ' · Moon ' + Math.round(shower.moon_illumination) + '% lit' : '')
      });
    } else if (num(d['almanac.next_meteor_shower.peak.unix_epoch.raw']) > now) {
      chips.push({ id: 'meteor', kind: 'meteor', label: (d['almanac.next_meteor_shower.label'] || 'Meteor shower') + ' peak', at: d['almanac.next_meteor_shower.peak.unix_epoch.raw'] });
    }

    // 4. Windowed chips: only within WINDOW_DAYS of the event.
    var windowed = [];
    function single(id, kind, label, at, extra){
      at = num(at);
      if (at == null) return;
      if (at - now > WINDOW_DAYS * 86400 || now - at > JUST_PASSED_S) return;
      windowed.push({ id: id, kind: kind, label: label, at: at, extra: extra || '' });
    }
    var eq = num(d['almanac.next_equinox.unix_epoch.raw']), so = num(d['almanac.next_solstice.unix_epoch.raw']);
    if (eq != null) single('equinox', 'season', (monthOf(eq) <= 6 ? 'March' : 'September') + ' equinox', eq, 'Day and night near equal');
    if (so != null) single('solstice', 'season', (monthOf(so) <= 9 ? 'June' : 'December') + ' solstice', so,
                           (monthOf(so) <= 9) === (stationLat(d) >= 0) ? 'Longest day' : 'Shortest day');
    single('perihelion', 'orbit', 'Earth at perihelion', d['almanac.next_perihelion.unix_epoch.raw'], 'Closest to the Sun');
    single('aphelion', 'orbit', 'Earth at aphelion', d['almanac.next_aphelion.unix_epoch.raw'], 'Farthest from the Sun');
    var smKm = num(d['celestial.supermoon.distance_km']);
    single('supermoon', 'moon', 'Supermoon', d['almanac.next_supermoon.unix_epoch.raw'], smKm != null ? 'Full moon at ' + fmtLinear(smKm) : '');
    if (d['almanac.next_eclipse_kind']) single('eclipse', 'eclipse', String(d['almanac.next_eclipse_kind']), d['almanac.next_eclipse.unix_epoch.raw'], '');
    cometsNow().forEach(function(c){
      if (c.missing) return;
      single('peri-' + c.key, 'comet', c.name + ' at perihelion', d['almanac.' + c.key + '.perihelion.unix_epoch.raw'],
             num(c.info.mag) != null ? 'Now magnitude ' + nf1.format(c.info.mag) : '');
    });
    windowed.sort(function(a, b){ return a.at - b.at; });
    return chips.concat(windowed);
  }

  function renderChips(now){
    if (!HAS.chips) return;
    var last = samples[samples.length - 1];
    var chips = last ? buildChips(last.d, now) : [];
    var sel = d3.select('#celCountdowns').selectAll('.cel-chip').data(chips, function(c){ return c.id; });
    sel.exit().remove();
    var ent = sel.enter().append('div').attr('class', 'cel-chip');
    ent.append('div').attr('class', 'cel-chip-label');
    ent.append('div').attr('class', 'cel-chip-count');
    ent.append('div').attr('class', 'cel-chip-when');
    ent.append('div').attr('class', 'cel-chip-extra');
    var all = ent.merge(sel).order();
    all.attr('data-kind', function(c){ return c.kind; })
       .classed('is-now', function(c){ return c.live || (c.at != null && c.at <= now); });
    all.select('.cel-chip-label').text(function(c){ return c.label; }).attr('title', function(c){ return c.label; });
    all.select('.cel-chip-count').html(function(c){ return c.text != null ? c.text : fmtCountdown(c.at - now); });
    all.select('.cel-chip-when').text(function(c){ return c.when || (c.at != null ? fmtWhen(c.at, now) : ''); });
    all.select('.cel-chip-extra').text(function(c){ return c.extra || ''; });
  }

  function stationLat(d){
    var lat = num(d && d['celestial.station.latitude']);
    return lat == null ? 1 : lat;
  }

  // ------------------------------------------------------------------
  // Geocentric dial
  // ------------------------------------------------------------------
  var svg = d3.select('#celDial');
  var gPlate = svg.append('g').attr('class', 'plate-layer');
  var gTrails = svg.append('g').attr('class', 'trail-layer');
  var gBodies = svg.append('g').attr('class', 'body-layer');
  var gEarth = svg.append('g').attr('class', 'earth-layer');
  var tip = document.getElementById('celTip');

  var measureCtx = document.createElement('canvas').getContext('2d');
  function labelPx(){ return geom && geom.small ? 10.5 : 11.5; }
  function textWidth(s){
    measureCtx.font = '500 ' + labelPx() + 'px Lexend, Helvetica, Arial, sans-serif';
    return measureCtx.measureText(s).width;
  }

  function layoutDial(){
    if (!HAS.dial) return;
    var wrap = document.getElementById('celDialWrap');
    var W = Math.max(280, Math.min(720, wrap.clientWidth || 600));
    var small = W < 460;
    var pad = small ? 22 : 28;
    var R = W / 2 - pad;
    var rEarth = Math.max(9, R * 0.055);
    geom = {
      W: W, cx: W / 2, cy: W / 2, R: R, small: small,
      rEarth: rEarth,
      // Solar system on a log scale; Proxima on its own ring past a break.
      radial: d3.scaleLog().domain([0.0018, 60]).range([rEarth + 12, R * 0.80]).clamp(true),
      rBreak: R * 0.86,
      rStar: R * 0.93
    };
    svg.attr('viewBox', '0 0 ' + W + ' ' + W).attr('width', W).attr('height', W).classed('small', small);
    drawPlate();
  }

  function xy(az, r){
    var a = az * Math.PI / 180;
    return [geom.cx + r * Math.sin(a), geom.cy - r * Math.cos(a)];
  }
  function radiusFor(key, distAU){
    return key === 'proxima_centauri' ? geom.rStar : geom.radial(distAU);
  }

  function drawPlate(){
    var g = geom;
    gPlate.selectAll('*').remove();
    gPlate.append('circle').attr('class', 'plate').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.R);
    // Spokes every 45 degrees.
    for (var a = 0; a < 360; a += 45) {
      var p0 = xy(a, g.rEarth + 6), p1 = xy(a, g.R);
      gPlate.append('line').attr('class', 'spoke').attr('x1', p0[0]).attr('y1', p0[1]).attr('x2', p1[0]).attr('y2', p1[1]);
    }
    // Distance rings.
    var rings = [0.01, 0.1, 1, 10];
    rings.forEach(function(au){
      gPlate.append('circle').attr('class', 'ring').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.radial(au));
    });
    // Scale break, then Proxima's ring.
    gPlate.append('circle').attr('class', 'ring').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.rBreak)
      .attr('stroke-dasharray', '1 5');
    gPlate.append('circle').attr('class', 'ring').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.rStar);
    gPlate.append('circle').attr('class', 'ring ring-outer').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.R);
    // Ring labels, along a bearing between the spokes so they stay clear of them.
    var labelAz = 157.5;
    rings.forEach(function(au){
      var p = xy(labelAz, g.radial(au) + 2);
      gPlate.append('text').attr('class', 'ring-label').attr('x', p[0] + 3).attr('y', p[1] + 3)
        .text(au + ' AU');
    });
    var pb = xy(labelAz, g.rStar + 2);
    gPlate.append('text').attr('class', 'ring-label').attr('x', pb[0] + 3).attr('y', pb[1] + 3).text('4.2 ly');
    // Compass ticks and letters.
    for (var t = 0; t < 360; t += 5) {
      var len = t % 30 === 0 ? 7 : (t % 10 === 0 ? 4 : 2);
      var q0 = xy(t, g.R), q1 = xy(t, g.R - len);
      gPlate.append('line').attr('class', 'tick').attr('x1', q0[0]).attr('y1', q0[1]).attr('x2', q1[0]).attr('y2', q1[1]);
    }
    [['N', 0], ['E', 90], ['S', 180], ['W', 270]].forEach(function(c){
      var p = xy(c[1], g.R + (g.small ? 11 : 15));
      gPlate.append('text').attr('class', 'compass' + (c[0] === 'N' ? ' compass-n' : ''))
        .attr('x', p[0]).attr('y', p[1]).attr('text-anchor', 'middle').attr('dominant-baseline', 'central').text(c[0]);
    });
    // Earth.
    gEarth.selectAll('*').remove();
    gEarth.append('circle').attr('class', 'earth').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.rEarth);
    gEarth.append('circle').attr('class', 'earth-shine')
      .attr('cx', g.cx - g.rEarth * 0.32).attr('cy', g.cy - g.rEarth * 0.32).attr('r', g.rEarth * 0.38);
    // Ring labels and compass letters are obstacles for body labels.
    g.obstacles = [];
    gPlate.selectAll('text.ring-label, text.compass').each(function(){
      var b = this.getBBox();
      g.obstacles.push([b.x - 2, b.y - 1, b.x + b.width + 2, b.y + b.height + 1]);
    });
  }

  // Lit part of the Moon, as a path centred on 0,0 with radius r.
  // k = illuminated fraction; lit limb on the right unless mirrored.
  function moonLitPath(r, k){
    k = Math.max(0, Math.min(1, k));
    // A real 3% crescent is under a pixel wide at this size: draw any
    // visible crescent at least ~6% so it can be seen (and a nearly full
    // Moon at most ~94%, so its dark edge still shows which way it faces).
    if (k > 0.004 && k < 0.06) k = 0.06;
    else if (k < 0.996 && k > 0.94) k = 0.94;
    var rx = Math.abs(1 - 2 * k) * r;
    return 'M0,' + (-r) + ' A' + r + ',' + r + ' 0 0 1 0,' + r
         + ' A' + rx + ',' + r + ' 0 0 ' + (k > 0.5 ? 1 : 0) + ' 0,' + (-r) + 'Z';
  }

  function markRadius(kind){
    if (geom.small) return kind === 'sun' ? 7 : kind === 'moon' ? 6.5 : kind === 'star' ? 5.5 : 4.5;
    return kind === 'sun' ? 8.5 : kind === 'moon' ? 7.5 : kind === 'star' ? 6.5 : 5;
  }

  function placeLabels(items){
    // Greedy: try the sticky side first, then the others, avoiding marks
    // and labels already placed and staying inside the plate.
    var placed = [];
    var marks = items.map(function(it){ var m = it.mr + 3; return [it.x - m, it.y - m, it.x + m, it.y + m]; })
      .concat(geom.obstacles || []);
    function hits(b){
      for (var i = 0; i < placed.length; i++) { var p = placed[i]; if (b[0] < p[2] && b[2] > p[0] && b[1] < p[3] && b[3] > p[1]) return true; }
      for (var j = 0; j < marks.length; j++) { var m = marks[j]; if (b[0] < m[2] && b[2] > m[0] && b[1] < m[3] && b[3] > m[1]) return true; }
      return false;
    }
    // Labels may use the plate's margin, but not run off the drawing.
    function inside(b){
      return b[0] >= 2 && b[1] >= 2 && b[2] <= geom.W - 2 && b[3] <= geom.W - 2;
    }
    items.forEach(function(it){
      var w = textWidth(it.label), h = geom.small ? 12 : 13;
      var boxes = {};
      // Two rings of candidates: hugging the mark, then a step further out.
      [it.mr + 4, it.mr + 13].forEach(function(gap, ring){
        var sfx = ring ? '2' : '';
        boxes['e' + sfx]  = [it.x + gap, it.y - h / 2];
        boxes['w' + sfx]  = [it.x - gap - w, it.y - h / 2];
        boxes['n' + sfx]  = [it.x - w / 2, it.y - gap - h];
        boxes['s' + sfx]  = [it.x - w / 2, it.y + gap];
        boxes['ne' + sfx] = [it.x + gap * 0.7, it.y - gap * 0.7 - h];
        boxes['se' + sfx] = [it.x + gap * 0.7, it.y + gap * 0.7];
        boxes['nw' + sfx] = [it.x - gap * 0.7 - w, it.y - gap * 0.7 - h];
        boxes['sw' + sfx] = [it.x - gap * 0.7 - w, it.y + gap * 0.7];
      });
      // Prefer the side facing away from Earth.
      var near = (it.x >= geom.cx) ? ['e', 'ne', 'se', 'n', 's', 'w', 'nw', 'sw'] : ['w', 'nw', 'sw', 'n', 's', 'e', 'ne', 'se'];
      var outward = near.concat(near.map(function(s){ return s + '2'; }));
      var order = labelSide[it.key] ? [labelSide[it.key]].concat(outward) : outward;
      var chosen = null, fallback = null;
      for (var i = 0; i < order.length; i++) {
        var o = boxes[order[i]];
        var b = [o[0], o[1], o[0] + w, o[1] + h];
        if (!fallback && inside(b)) fallback = { side: order[i], b: b };
        if (inside(b) && !hits(b)) { chosen = { side: order[i], b: b }; break; }
      }
      chosen = chosen || fallback || { side: 'e', b: [boxes.e[0], boxes.e[1], boxes.e[0] + w, boxes.e[1] + h] };
      labelSide[it.key] = chosen.side;
      placed.push(chosen.b);
      it.lx = chosen.b[0];
      it.ly = chosen.b[1] + h - 3;
    });
  }

  function renderDial(now){
    if (!HAS.dial || !geom) return;
    var last = samples[samples.length - 1];
    var d = last ? last.d : {};
    var items = [];
    allBodies().forEach(function(b){
      var p = bodyAt(b.key, now);
      if (!p) return;
      var r = radiusFor(b.key, p.dist);
      var c = xy(p.az, r);
      items.push({ key: b.key, body: b, pos: p, x: c[0], y: c[1], mr: markRadius(b.kind), label: b.label || b.name });
    });
    // A comet's tail points away from the Sun -- here, away from the
    // Sun's mark on the dial.
    var sunItem = items.filter(function(it){ return it.key === 'sun'; })[0];
    items.forEach(function(it){
      if (it.body.kind !== 'comet') return;
      var mag = num(d['almanac.' + it.key + '.mag']);
      it.bright = mag != null && mag <= 6;
      var ang = sunItem ? Math.atan2(it.y - sunItem.y, it.x - sunItem.x) : Math.atan2(it.y - geom.cy, it.x - geom.cx);
      var len = geom.small ? 14 : 19, path = '';
      [-0.32, 0, 0.32].forEach(function(da){
        path += 'M0,0L' + (Math.cos(ang + da) * len * (da ? 0.8 : 1)).toFixed(1) + ',' + (Math.sin(ang + da) * len * (da ? 0.8 : 1)).toFixed(1);
      });
      it.tail = path;
    });
    // Far bodies first so near ones draw on top.
    items.sort(function(a, b){ return b.pos.dist - a.pos.dist; });
    placeLabels(items.slice().sort(function(a, b){ return a.pos.dist - b.pos.dist; }));

    // Trails.
    var line = d3.line();
    var tsel = gTrails.selectAll('path.trail').data(items, function(it){ return it.key; });
    tsel.exit().remove();
    tsel.enter().append('path').attr('class', 'trail').merge(tsel)
      .attr('stroke', function(it){ return bodyColor(it.key); })
      .attr('stroke-dasharray', function(it){ return it.pos.up ? null : '3 3'; })
      .attr('opacity', function(it){ return it.pos.up ? 0.45 : 0.22; })
      .attr('d', function(it){
        var pts = (trails[it.key] || []).filter(function(q){ return q[0] <= now && now - q[0] <= TRAIL_SECONDS; })
          .map(function(q){ return xy(q[1], radiusFor(it.key, q[3])); });
        if (!pts.length) return null;
        pts.push([it.x, it.y]);
        return line(pts);
      });

    // Bodies.
    var waxing = num(d['almanac.next_full_moon.unix_epoch.raw']) != null && num(d['almanac.next_new_moon.unix_epoch.raw']) != null
      ? d['almanac.next_full_moon.unix_epoch.raw'] < d['almanac.next_new_moon.unix_epoch.raw'] : true;
    var mirror = (waxing ? 1 : -1) * (stationLat(d) >= 0 ? 1 : -1);
    var k = (num(d['almanac.moon.phase']) || 0) / 100;

    var sel = gBodies.selectAll('g.body').data(items, function(it){ return it.key; });
    sel.exit().remove();
    var ent = sel.enter().append('g').attr('class', function(it){ return 'body body-' + it.key; });
    ent.each(function(it){
      var g = d3.select(this);
      var col = bodyColor(it.key);
      if (it.body.kind === 'sun') {
        g.append('circle').attr('class', 'sun-glow').attr('r', it.mr * 1.9);
        g.append('circle').attr('class', 'body-mark').attr('r', it.mr);
      } else if (it.body.kind === 'moon') {
        g.append('circle').attr('class', 'moon-dark').attr('r', it.mr);
        g.append('path').attr('class', 'moon-lit');
        g.append('circle').attr('class', 'moon-edge').attr('r', it.mr);
      } else if (it.body.kind === 'star') {
        var s = it.mr + 1.5, w = it.mr * 0.38;
        g.append('path').attr('class', 'body-mark')
          .attr('d', 'M0,' + (-s) + ' L' + w + ',' + (-w) + ' L' + s + ',0 L' + w + ',' + w + ' L0,' + s + ' L' + (-w) + ',' + w + ' L' + (-s) + ',0 L' + (-w) + ',' + (-w) + 'Z');
      } else if (it.body.kind === 'comet') {
        g.append('path').attr('class', 'comet-tail');
        var q = it.mr + 0.5;
        g.append('path').attr('class', 'body-mark comet-mark')
          .attr('d', 'M0,' + (-q) + 'L' + q + ',0L0,' + q + 'L' + (-q) + ',0Z');
      } else {
        g.append('circle').attr('class', 'body-mark').attr('r', it.mr);
      }
      g.append('text').attr('class', 'body-label');
      g.append('circle').attr('class', 'body-hit').attr('r', Math.max(14, it.mr + 8));
      g.on('mouseenter', function(){ setFocus(it.key); })
       .on('mouseleave', function(){ setFocus(null); })
       .on('click', function(ev){ ev.stopPropagation(); setFocus(focusKey === it.key ? null : it.key); });
    });
    var all = ent.merge(sel).order();
    all.classed('below', function(it){ return !it.pos.up; })
       .classed('focus', function(it){ return it.key === focusKey; });
    all.each(function(it){
      var g = d3.select(this);
      var col = bodyColor(it.key);
      g.style('--mark-col', col);
      g.selectAll('.body-mark').attr('transform', 'translate(' + it.x + ',' + it.y + ')').attr('fill', col);
      g.selectAll('.sun-glow, .moon-dark, .moon-edge, .body-hit').attr('cx', it.x).attr('cy', it.y);
      g.select('.sun-glow').attr('fill', col);
      g.select('.moon-lit').attr('d', moonLitPath(it.mr, k))
        .attr('transform', 'translate(' + it.x + ',' + it.y + ') scale(' + mirror + ',1)');
      g.select('.moon-edge').attr('stroke-dasharray', it.pos.up ? null : '2.5 2');
      g.classed('faint', it.body.kind === 'comet' && !it.bright);
      g.select('.comet-tail').attr('d', it.tail || null).attr('stroke', col)
        .attr('transform', 'translate(' + it.x + ',' + it.y + ')');
      g.select('.body-label').attr('x', it.lx).attr('y', it.ly).text(it.label);
      it.node = this;
    });
    if (focusKey) positionTip(items.filter(function(it){ return it.key === focusKey; })[0]);
  }

  function setFocus(key){
    focusKey = key;
    d3.selectAll('#celRoster tbody tr').classed('focus', function(){ return this.getAttribute('data-key') === key; });
    d3.selectAll('#celDial g.body').classed('focus', function(it){ return it.key === key; });
    if (!key) { tip.hidden = true; return; }
    renderDial(serverNow());
  }

  function positionTip(it){
    if (!it) { tip.hidden = true; return; }
    var p = it.pos;
    var scale = (document.getElementById('celDial').getBoundingClientRect().width || geom.W) / geom.W;
    tip.innerHTML = '<b>' + it.body.name + '</b> · ' + (p.up ? 'above the horizon' : 'below the horizon')
      + '<br><span class="k">Bearing</span> ' + nf1.format(p.az) + '° ' + compass(p.az)
      + '<br><span class="k">Altitude</span> ' + fmtSigned(p.alt, nf1) + '°'
      + '<br><span class="k">Distance</span> ' + distanceText(it.key, p.dist).main
      + (it.body.kind === 'comet' ? cometTipLine(it.key) : '');
    tip.style.left = (it.x * scale) + 'px';
    tip.style.top = (it.y * scale) + 'px';
    tip.hidden = false;
  }

  // ------------------------------------------------------------------
  // Roster
  // ------------------------------------------------------------------
  function distanceText(key, au){
    if (key === 'proxima_centauri') {
      return { main: nf4.format(au / LY_AU) + ' ly', sub: nf0.format(au) + ' AU' };
    }
    var km = au * AU_KM;
    if (key === 'moon') return { main: fmtLinear(km, 1), sub: nf2.format(km / 6371) + ' Earth radii' };
    return { main: fmtLinear(km), sub: nf5.format(au) + ' AU' };
  }
  function rateText(key, rDistAU){
    var kms = rDistAU * AU_KM;
    if (!kms || Math.abs(kms) < 0.0005) return '';
    var miles = useMiles();
    var v = Math.abs(miles ? kms * KM_MI : kms);
    // Non-breaking space keeps the figure with its unit if the line wraps.
    return (kms > 0 ? 'receding ' : 'approaching ') + (v >= 10 ? nf1.format(v) : nf2.format(v)) + (miles ? ' mi/s' : ' km/s');
  }

  function cometInfo(key){
    var c = cometsNow().filter(function(x){ return x.key === key; })[0];
    return c ? c.info : null;
  }
  function cometTipLine(key){
    var c = cometInfo(key);
    if (!c) return '';
    return '<br><span class="k">Magnitude</span> ' + (num(c.mag) != null ? nf1.format(c.mag) + (c.mag <= 6 ? ' (naked eye)' : '') : '—')
      + '<br><span class="k">From the Sun</span> ' + nf2.format(c.sun_distance) + ' AU';
  }

  function renderRoster(now){
    if (!HAS.roster) return;
    // Every configured comet gets a row -- one whose elements the MPC
    // no longer publishes is shown as absent rather than left out.
    var rows = allBodies().map(function(b){ return { b: b, p: bodyAt(b.key, now) }; })
      .filter(function(r){ return r.p || r.b.kind === 'comet'; });
    var sel = d3.select('#celRoster tbody').selectAll('tr').data(rows, function(r){ return r.b.key; });
    sel.exit().remove();
    var ent = sel.enter().append('tr').attr('data-key', function(r){ return r.b.key; });
    var who = ent.append('td');
    who.append('span').attr('class', 'who').call(function(s){
      s.append('span').attr('class', 'swatch');
      s.append('span').attr('class', 'nm');
    });
    who.append('span').attr('class', 'state');
    ent.append('td').attr('class', 'num az');
    ent.append('td').attr('class', 'num alt');
    var dc = ent.append('td').attr('class', 'num dist');
    dc.append('span').attr('class', 'odo');
    dc.append('span').attr('class', 'sub');
    dc.append('span').attr('class', 'rate');
    ent.on('mouseenter', function(ev, r){ setFocus(r.b.key); })
       .on('mouseleave', function(){ setFocus(null); });

    var all = ent.merge(sel).order();
    var dash = '—';
    all.classed('below', function(r){ return !r.p || !r.p.up; })
       .classed('comet', function(r){ return r.b.kind === 'comet'; })
       .classed('focus', function(r){ return r.b.key === focusKey; });
    all.select('.swatch').style('--sw', function(r){ return bodyColor(r.b.key); });
    all.select('.nm').text(function(r){ return r.b.name; });
    all.select('.state').text(function(r){
      if (!r.p) {
        var st = r.b.info && r.b.info.status;
        if (!st || st.state === 'pending') return 'Downloading orbit from the MPC…';
        if (st.state === 'error') return 'MPC download failed: ' + (st.error || 'unknown error');
        return 'Not in the MPC’s current comet file';
      }
      var s = r.p.up ? 'Up' : 'Below horizon';
      if (r.b.kind === 'comet') {
        var c = cometInfo(r.b.key);
        if (c && num(c.mag) != null) s += ' · mag ' + nf1.format(c.mag);
      }
      return s;
    });
    all.select('.az').html(function(r){
      if (!r.p) return dash;
      return nf1.format(r.p.az) + '°<span class="sub">' + compass(r.p.az) + '</span>'
        + '<span class="sub alt-inline">alt ' + fmtSigned(r.p.alt, nf1) + '°</span>';
    });
    all.select('.alt').text(function(r){ return r.p ? fmtSigned(r.p.alt, nf1) + '°' : dash; });
    all.select('td.dist .odo').text(function(r){ return r.p ? distanceText(r.b.key, r.p.dist).main : dash; });
    all.select('td.dist .sub').text(function(r){ return r.p ? distanceText(r.b.key, r.p.dist).sub : ''; });
    all.select('td.dist .rate').text(function(r){ return r.p ? rateText(r.b.key, r.p.rDist) : ''; });
  }

  // ------------------------------------------------------------------
  // Live badge
  // ------------------------------------------------------------------
  function renderLive(now){
    if (!HAS.live) return;
    var el = document.getElementById('celLive');
    var label = document.getElementById('celLiveLabel');
    var age = document.getElementById('celLiveAge');
    var last = samples[samples.length - 1];
    if (!last) {
      el.setAttribute('data-state', lastFetchOk === null ? 'wait' : 'stale');
      label.textContent = 'Waiting for data';
      age.textContent = '';
      return;
    }
    var a = now - last.t;
    var stale = a > staleAfter();
    el.setAttribute('data-state', stale ? 'stale' : 'live');
    label.textContent = stale ? 'Stale' : 'Live';
    age.textContent = (stale ? 'updated ' : '') + fmtAge(a);
    var upd = document.getElementById('celUpdated');
    if (upd) upd.textContent = stale
      ? 'The feed has not updated since ' + fmtTime(last.t, true) + ' — readouts are frozen at that moment.'
      : 'Feed updated ' + fmtTime(last.t, true) + '. Between updates, each readout advances at its body’s own rate.';
  }

  // ------------------------------------------------------------------
  // Shared with the page's other panels (dome.js, and the pass chart to come)
  // ------------------------------------------------------------------
  var tickHooks = [];
  window.DivumwxCelestial = {
    BODIES: BODIES,
    now: serverNow,
    latest: function(){ return samples[samples.length - 1] || null; },
    bodyAt: bodyAt,
    advanceFor: advanceFor,
    isStale: isStale,
    stationLat: stationLat,
    bodyColor: bodyColor,
    moonLitPath: moonLitPath,
    compass: compass,
    fmtSigned: fmtSigned,
    fmtTime: fmtTime,
    fmtWhen: fmtWhen,
    fmtCountdown: fmtCountdown,
    fmtLinear: fmtLinear,
    useMiles: useMiles,
    distanceText: distanceText,
    tz: function(){ return tz; },
    stationName: function(){ return stationName; },
    base: BASE,
    nf0: nf0,
    nf1: nf1,
    nf2: nf2,
    num: num,
    cometsNow: cometsNow,
    satellitesNow: satellitesNow,
    passOf: passOf,
    trackAt: trackAt,
    soonestVisiblePass: soonestVisiblePass,
    onTick: function(fn){ tickHooks.push(fn); }
  };

  // ------------------------------------------------------------------
  // Main loop
  // ------------------------------------------------------------------
  function tick(){
    var now = serverNow();
    renderLive(now);
    renderChips(now);
    renderRoster(now);
    renderDial(now);
    tickHooks.forEach(function(fn){
      try { fn(now); } catch (e) { console.warn('celestial: panel update failed —', e); }
    });
  }
  function onFirstData(){ layoutDial(); }

  layoutDial();
  if (HAS.dial && window.ResizeObserver) {
    var lastW = 0;
    new ResizeObserver(function(){
      var w = document.getElementById('celDialWrap').clientWidth;
      if (Math.abs(w - lastW) > 2) { lastW = w; layoutDial(); renderDial(serverNow()); }
    }).observe(document.getElementById('celDialWrap'));
  }
  // Theme flips change the CSS colour tokens the marks read.
  new MutationObserver(function(){ renderDial(serverNow()); renderRoster(serverNow()); })
    .observe(document.body, { attributes: true, attributeFilter: ['class'] });
  window.addEventListener('storage', function(e){ if (e.key === 'dashboardUnitSystem') tick(); });
  document.addEventListener('click', function(){ if (focusKey) setFocus(null); });

  loadMeta();
  poll();
  setInterval(poll, POLL_MS);
  setInterval(tick, TICK_MS);
  setInterval(loadMeta, 30 * 60 * 1000);
})();
