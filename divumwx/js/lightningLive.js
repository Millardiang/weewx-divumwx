/*
##############################################################################################
# lightningLive.js version 1.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
  Live lightning map: strikes from the Blitzortung.org community network
  (their live websocket feed) on a D3 Equal Earth map, centred on the station.
  Used by the Boltek Lightning modal ("Live Network" tab) and the Lightning
  chart modal ("Live Map" button).

  Needs d3 (v7) and topojson (v3) loaded first. Usage:

      var live = DivumLiveLightning.mount(element, { radiusKm: 100 });
      live.show();   // connects to the feed and starts drawing
      live.hide();   // disconnects (strikes already received are kept)

  The station's name and position come from jsondata/archive.json (weewx.conf
  [Station]); distances follow the dashboard's unit system. Colours follow the
  Equal Earth map's palette and the dashboard theme (data-theme,
  data-bs-theme or body.dark), with panel colours from the DivumWX card tokens.

  Blitzortung data is for private, non-commercial use only; the credit line
  must stay visible.
*/
(function(){
  'use strict';
  if (window.DivumLiveLightning) return;

  var SERVERS = ['wss://ws1.blitzortung.org', 'wss://ws7.blitzortung.org',
                 'wss://ws8.blitzortung.org', 'wss://ws3.blitzortung.org'];
  var MAX_AGE_MIN = 60, MAX_AGE = MAX_AGE_MIN * 60000;
  var FLASH_MS = 1600, SIG_MS = 1200, TRACE_MS = 1300;
  var HOME_ZOOM = 5;                 // the opening view: the station's region
  var KM_PER_DEG = 111.195, EARTH_KM = 6371.0088;
  // [age limit (minutes), colour, legend]: the Boltek strike map's colours,
  // over this map's hour
  var AGES = [[5, '#FFFFFF', '< 5 min'], [10, '#FFF200', '5–10'], [20, '#FFB300', '10–20'],
              [30, '#FF6A00', '20–30'], [45, '#E0301E', '30–45'], [60, '#8E1730', '45–60 min']];

  // ---- Styles (injected once) ----
  var CSS = [
    // Map colours: css/equalEarthMap.css's --map-* values, light then dark
    '.ll-root{ --ll-ocean:#D5E2EA; --ll-land:#B7C4A3; --ll-coast:#FFFFFF; --ll-grid:#A9BCC8; --ll-rim:#1D2C4E;',
    '  --ll-night:10,15,34; --ll-night-alpha:.13; --ll-station:#B45309; --ll-dot-edge:rgba(29,44,78,.75);',
    '  --ll-sig:29,44,78; --ll-ring:rgba(29,44,78,.45); --ll-trail:#F59E0B; --ll-flash:255,255,255;',
    '  --ll-accent:var(--bw-accent,#B45309); --ll-panel:var(--bs-card-bg,#FFFFFF); --ll-text:var(--bs-body-color,#1D2C4E);',
    '  --ll-muted:var(--bs-secondary-color,#5C6672); --ll-border:var(--bs-border-color,#C9CFD8); --ll-btn:var(--bs-tertiary-bg,#F4F1E9);',
    '  --ll-good:var(--bw-good,#5BBB8A); --ll-warn:var(--bw-amber,#D97706); --ll-bad:var(--bw-danger,#E36B57);',
    '  font-family:\'Lexend\',\'Helvetica Neue\',Helvetica,Arial,sans-serif; color:var(--ll-text); }',
    '[data-theme="dark"] .ll-root, [data-bs-theme="dark"] .ll-root, body.dark .ll-root{',
    '  --ll-ocean:#16203F; --ll-land:#3E4F48; --ll-coast:#111834; --ll-grid:#2A3358; --ll-rim:#8B93B8;',
    '  --ll-night:0,0,0; --ll-night-alpha:.22; --ll-station:#D3A94C; --ll-dot-edge:rgba(0,0,0,.7);',
    '  --ll-sig:233,228,212; --ll-ring:rgba(233,228,212,.4); --ll-trail:#FFD54A; --ll-accent:var(--bw-accent,#D3A94C); }',
    '.ll-bar{ display:flex; align-items:center; justify-content:space-between; gap:8px 12px; flex-wrap:wrap; margin-bottom:8px; }',
    '.ll-btns{ display:flex; gap:6px; flex-wrap:wrap; }',
    '.ll-btn{ font:700 12px \'Lexend\',\'Helvetica Neue\',Helvetica,Arial,sans-serif; padding:6px 13px; border-radius:999px;',
    '  border:1px solid var(--ll-border); background:var(--ll-btn); color:var(--ll-text); cursor:pointer; min-width:34px; }',
    '.ll-btn:hover:not(.on){ border-color:var(--ll-accent); color:var(--ll-accent); }',
    '.ll-btn.on{ background:var(--ll-accent); border-color:var(--ll-accent); color:#fff; }',
    '[data-theme="dark"] .ll-btn.on, [data-bs-theme="dark"] .ll-btn.on, body.dark .ll-btn.on{ color:#0A0F22; }',
    '.ll-status{ display:flex; align-items:center; gap:6px; font-size:12px; color:var(--ll-muted); white-space:nowrap; }',
    '.ll-status i{ width:8px; height:8px; border-radius:50%; background:var(--ll-warn); display:inline-block; }',
    '.ll-status.live i{ background:var(--ll-good); box-shadow:0 0 6px var(--ll-good); }',
    '.ll-status.off i{ background:var(--ll-bad); }',
    '.ll-map{ position:relative; width:100%; height:480px; border-radius:10px; overflow:hidden; background:var(--ll-ocean); touch-action:none; }',
    '.ll-map canvas{ position:absolute; left:0; top:0; }',
    '.ll-map canvas.ll-fx{ cursor:grab; } .ll-map canvas.ll-fx:active{ cursor:grabbing; }',
    '.ll-info{ position:absolute; top:10px; left:10px; max-width:52%; pointer-events:none; font-size:12px; line-height:1.5;',
    '  background:color-mix(in srgb, var(--ll-panel) 86%, transparent); border:1px solid var(--ll-border); border-radius:10px; padding:8px 11px; }',
    '.ll-info b{ color:var(--ll-accent); font-weight:600; }',
    '.ll-msg{ position:absolute; left:50%; bottom:14px; transform:translateX(-50%); pointer-events:none; white-space:nowrap; font-size:12px;',
    '  color:var(--ll-muted); background:color-mix(in srgb, var(--ll-panel) 86%, transparent); border:1px solid var(--ll-border);',
    '  border-radius:999px; padding:6px 14px; }',
    '.ll-tip{ position:absolute; z-index:3; pointer-events:none; display:none; white-space:nowrap; font-size:11.5px; line-height:1.45;',
    '  background:var(--ll-btn); color:var(--ll-text); border:1px solid var(--ll-border); border-radius:8px; padding:7px 10px;',
    '  box-shadow:0 1px 2px rgba(0,0,0,.12), 0 8px 20px -8px rgba(0,0,0,.25); }',
    '.ll-tip b{ font-weight:600; }',
    '.ll-foot{ display:flex; align-items:center; justify-content:space-between; gap:8px 14px; flex-wrap:wrap; margin-top:8px; font-size:11.5px; color:var(--ll-muted); }',
    '.ll-age{ display:flex; gap:10px; flex-wrap:wrap; align-items:center; }',
    '.ll-age i{ display:inline-block; width:10px; height:10px; border-radius:50%; border:1px solid var(--ll-dot-edge); margin-right:4px; vertical-align:-1px; }',
    '.ll-credit{ font-style:italic; } .ll-credit a{ color:inherit; }',
    '@media (max-width:700px){ .ll-info{ max-width:none; right:10px; font-size:11.5px; } .ll-msg{ white-space:normal; width:88%; text-align:center; border-radius:10px; } }'
  ].join('\n');
  function injectCss(){
    if (document.getElementById('llStyles')) return;
    var s = document.createElement('style'); s.id = 'llStyles'; s.textContent = CSS;
    document.head.appendChild(s);
  }

  // ---- Helpers ----
  function t(s){ try { return (window.DivumWXI18N && DivumWXI18N.t) ? DivumWXI18N.t(s) : s; } catch (e) { return s; } }
  function distUnit(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key].dist;
    } catch (e) {}
    return 'km';
  }
  function kmTo(v, u){ return u === 'mi' ? v * 0.621371 : u === 'nm' ? v * 0.539957 : v; }
  function fmtDist(km){ var u = distUnit(), v = kmTo(km, u); return (v < 10 ? v.toFixed(1) : Math.round(v).toLocaleString()) + ' ' + uLabel(u); }
  function uLabel(u){ return u === 'nm' ? 'NM' : u; }
  function tz(){ try { return StationTime.getTZ(); } catch (e) { return undefined; } }
  function fmtClock(ms){
    try { return new Intl.DateTimeFormat('en-GB', { timeZone: tz(), hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23' }).format(new Date(ms)); }
    catch (e) { return new Date(ms).toLocaleTimeString(); }
  }
  function fmtAge(ms){ var s = Math.max(0, Math.round(ms / 1000)); return s < 60 ? s + ' s' : Math.floor(s / 60) + ' min ' + (s % 60) + ' s'; }
  function ageColor(ms){
    var m = ms / 60000;
    for (var i = 0; i < AGES.length; i++) if (m < AGES[i][0]) return i;
    return AGES.length - 1;
  }
  // Blitzortung messages are LZW-packed JSON
  function lzwDecode(b){
    var a, e = {}, d = b.split(''), c = d[0], f = c, g = [c], h = 256, o = h;
    for (var i = 1; i < d.length; i++) {
      a = d[i].charCodeAt(0);
      a = h > a ? d[i] : e[a] ? e[a] : f + c;
      g.push(a); c = a.charAt(0); e[o] = f + c; o++; f = a;
    }
    return g.join('');
  }
  // Night side, as the Equal Earth map draws it: washes round the antisolar
  // point (sun 0, 6, 12 and 18 degrees below the horizon).
  function antisolar(){
    var RAD = Math.PI / 180, n = Date.now() / 864e5 + 2440587.5 - 2451545.0;
    var L = (280.460 + 0.9856474 * n) % 360, g = ((357.528 + 0.9856003 * n) % 360) * RAD;
    var lam = (L + 1.915 * Math.sin(g) + 0.020 * Math.sin(2 * g)) * RAD, eps = (23.439 - 0.0000004 * n) * RAD;
    var ra = Math.atan2(Math.cos(eps) * Math.sin(lam), Math.cos(lam)) / RAD, dec = Math.asin(Math.sin(eps) * Math.sin(lam)) / RAD;
    var sunLon = ((ra - (280.46061837 + 360.98564736629 * n) % 360) % 360 + 540) % 360 - 180;
    return [sunLon + 180, -dec];
  }

  function mount(host, opts){
    opts = opts || {};
    injectCss();
    var radiusKm = opts.radiusKm || 100;
    var base = opts.base || './';

    host.classList.add('ll-root');
    host.innerHTML =
      '<div class="ll-bar">' +
        '<div class="ll-btns">' +
          '<button class="ll-btn" data-a="in" title="' + t('Zoom in') + '">+</button>' +
          '<button class="ll-btn" data-a="out" title="' + t('Zoom out') + '">&minus;</button>' +
          '<button class="ll-btn" data-a="home" title="' + t('Back to the station') + '">' + t('Home') + '</button>' +
          '<button class="ll-btn" data-a="world" title="' + t('Whole world') + '">' + t('World') + '</button>' +
          '<button class="ll-btn on" data-a="night" title="' + t('Day/night shading') + '">' + t('Night') + '</button>' +
          '<button class="ll-btn on" data-a="sig" title="' + t('Lines to the detecting stations') + '">' + t('Detectors') + '</button>' +
        '</div>' +
        '<span class="ll-status"><i></i><span>' + t('Not connected') + '</span></span>' +
      '</div>' +
      '<div class="ll-map">' +
        '<canvas class="ll-base"></canvas><canvas class="ll-dots"></canvas><canvas class="ll-fx" aria-label="' + t('Live lightning map') + '"></canvas>' +
        '<div class="ll-info">' + t('Connecting') + '&hellip;</div>' +
        '<div class="ll-msg" hidden></div>' +
        '<div class="ll-tip"></div>' +
      '</div>' +
      '<div class="ll-foot">' +
        '<div class="ll-age"></div>' +
        '<span class="ll-credit">' + t('Equal Earth projection') + ' &middot; ' + t('last hour') + ' &middot; ' + t('data') +
          ' &copy; <a href="https://www.blitzortung.org" target="_blank" rel="noopener">Blitzortung.org</a> ' + t('contributors') + '</span>' +
      '</div>';

    var q = function(sel){ return host.querySelector(sel); };
    var mapEl = q('.ll-map'), infoEl = q('.ll-info'), msgEl = q('.ll-msg'), tipEl = q('.ll-tip'), statusEl = q('.ll-status');
    var cB = q('.ll-base'), cD = q('.ll-dots'), cF = q('.ll-fx');
    var xB = cB.getContext('2d'), xD = cD.getContext('2d'), xF = cF.getContext('2d');
    q('.ll-age').innerHTML = t('Age') + ' ' + AGES.map(function(a){ return '<span><i style="background:' + a[1] + '"></i>' + a[2] + '</span>'; }).join('');

    var W = 1, H = 1, DPR = 1;
    var home = null, homeName = t('Station');
    var showNight = true, showSig = true;
    var active = false, built = false;
    var COL = {};
    function readColours(){
      var cs = getComputedStyle(host), v = function(n){ return cs.getPropertyValue(n).trim(); };
      COL = { ocean: v('--ll-ocean'), land: v('--ll-land'), coast: v('--ll-coast'), grid: v('--ll-grid'), rim: v('--ll-rim'),
              night: 'rgba(' + v('--ll-night') + ',' + v('--ll-night-alpha') + ')', station: v('--ll-station'),
              edge: v('--ll-dot-edge'), sig: v('--ll-sig'), ring: v('--ll-ring'), trail: v('--ll-trail'), flash: v('--ll-flash'),
              panel: v('--ll-panel') };
    }

    // ---- projection and zoom ----
    var proj = d3.geoEqualEarth().precision(0.3);
    var pathB = d3.geoPath(proj, xB), pathF = d3.geoPath(proj, xF);
    var graticule = d3.geoGraticule10();
    var s0 = 1, t0 = [0, 0], zt = d3.zoomIdentity;
    var land110, bord110, land50, bord50;
    var zoom = d3.zoom().scaleExtent([1, 80]).on('zoom', function(e){ zt = e.transform; scheduleView(); });
    var sel = d3.select(cF).call(zoom).on('dblclick.zoom', null);

    function measure(){
      var w = Math.floor(mapEl.clientWidth);
      if (!w) return false;                       // hidden
      var h = Math.round(w < 700 ? w * 0.9 : Math.min(600, w * 0.5));
      if (w === W && h === H) return false;
      W = w; H = h; DPR = window.devicePixelRatio || 1;
      mapEl.style.height = H + 'px';
      [[cB, xB], [cD, xD], [cF, xF]].forEach(function(p){
        p[0].width = W * DPR; p[0].height = H * DPR; p[0].style.width = W + 'px'; p[0].style.height = H + 'px';
        p[1].setTransform(DPR, 0, 0, DPR, 0, 0);
      });
      return true;
    }
    function fit(){
      proj.scale(1).translate([0, 0]).rotate([-(home ? home[0] : 0), 0]);
      proj.fitExtent([[6, 6], [W - 6, H - 6]], { type: 'Sphere' });
      s0 = proj.scale(); t0 = proj.translate();
      zoom.extent([[0, 0], [W, H]]).translateExtent([[0, 0], [W, H]]);
    }
    function applyView(){
      proj.scale(s0 * zt.k).translate([zt.x + t0[0] * zt.k, zt.y + t0[1] * zt.k]);
      drawBase(); drawDots();
    }
    var viewQueued = false;
    function scheduleView(){
      if (viewQueued) return; viewQueued = true;
      requestAnimationFrame(function(){ viewQueued = false; applyView(); });
    }
    function homeTransform(k){
      // the station in un-zoomed coordinates, put in the middle at zoom k
      proj.scale(s0).translate(t0);
      var p = proj(home || [0, 0]);
      proj.scale(s0 * zt.k).translate([zt.x + t0[0] * zt.k, zt.y + t0[1] * zt.k]);
      return d3.zoomIdentity.translate(W / 2, H / 2).scale(k).translate(-p[0], -p[1]);
    }
    function relayout(keepView){
      if (!active) return;
      var changed = measure();
      if (!changed && built) return;
      if (!W) return;
      fit();
      var k = built && keepView ? zt.k : HOME_ZOOM;
      sel.call(zoom.transform, homeTransform(k));   // also redraws
      built = true;
    }

    // ---- base map ----
    function drawBase(){
      if (!built && !W) return;
      var c = xB; c.clearRect(0, 0, W, H);
      var hi = zt.k >= 3 && land50, land = hi ? land50 : land110, bord = hi ? bord50 : bord110;
      c.beginPath(); pathB({ type: 'Sphere' }); c.fillStyle = COL.ocean; c.fill();
      c.beginPath(); pathB(graticule); c.strokeStyle = COL.grid; c.lineWidth = .5; c.stroke();
      if (land) {
        c.beginPath(); pathB(land); c.fillStyle = COL.land; c.fill();
        c.beginPath(); pathB(bord); c.strokeStyle = COL.coast; c.lineWidth = .7; c.lineJoin = 'round'; c.stroke();
      }
      if (showNight) {
        var anti = antisolar();
        c.fillStyle = COL.night;
        [90, 84, 78, 72].forEach(function(r){ c.beginPath(); pathB(d3.geoCircle().center(anti).radius(r)()); c.fill(); });
      }
      c.beginPath(); pathB({ type: 'Sphere' }); c.strokeStyle = COL.rim; c.globalAlpha = .5; c.lineWidth = 1; c.stroke(); c.globalAlpha = 1;
      if (!home) return;
      // the station and its radius ring
      c.beginPath(); pathB(d3.geoCircle().center(home).radius(radiusKm / KM_PER_DEG)());
      c.setLineDash([4, 4]); c.strokeStyle = COL.ring; c.lineWidth = 1; c.stroke(); c.setLineDash([]);
      var p = proj(home);
      if (p) {
        c.beginPath(); c.arc(p[0], p[1], 9, 0, 2 * Math.PI); c.strokeStyle = COL.station; c.globalAlpha = .35; c.lineWidth = 2; c.stroke(); c.globalAlpha = 1;
        c.beginPath(); c.arc(p[0], p[1], 5, 0, 2 * Math.PI); c.fillStyle = COL.station; c.fill();
        c.strokeStyle = COL.panel; c.lineWidth = 2; c.stroke();
        if (zt.k >= 2) {
          c.font = '600 12px Lexend, sans-serif'; c.lineJoin = 'round';
          c.strokeStyle = COL.panel; c.lineWidth = 3; c.strokeText(homeName, p[0] + 10, p[1] + 4);
          c.fillStyle = COL.station; c.fillText(homeName, p[0] + 10, p[1] + 4);
        }
      }
    }

    // ---- strikes ----
    var strikes = [], head = 0, fresh = [], qt = null, lastDots = 0;
    function addStrike(d){
      if (!d || typeof d.lat !== 'number' || typeof d.lon !== 'number') return;
      var now = Date.now(), tt = d.time ? d.time / 1e6 : now;   // feed time is in nanoseconds
      if (!isFinite(tt) || tt > now + 5000) tt = now;
      var sig = Array.isArray(d.sig) ? d.sig.filter(function(x){ return x && typeof x.lat === 'number' && typeof x.lon === 'number'; })
                                            .map(function(x){ return [x.lon, x.lat]; }) : null;
      var s = { lon: d.lon, lat: d.lat, t: tt, rx: performance.now(), n: Array.isArray(d.sig) ? d.sig.length : 0, sig: sig,
                km: home ? d3.geoDistance(home, [d.lon, d.lat]) * EARTH_KM : null };
      s.near = s.km != null && s.km <= radiusKm;
      strikes.push(s);
      if (active && !document.hidden && fresh.length < 400) fresh.push(s);
    }
    function prune(now){
      while (head < strikes.length && now - strikes[head].t > MAX_AGE) head++;
      if (head > 5000) { strikes.splice(0, head); head = 0; }
    }
    function drawDots(){
      var now = Date.now(); prune(now);
      var groups = AGES.map(function(){ return []; }), pts = [];
      for (var i = head; i < strikes.length; i++) {
        var s = strikes[i], p = proj([s.lon, s.lat]);
        if (!p || p[0] < -6 || p[1] < -6 || p[0] > W + 6 || p[1] > H + 6) continue;
        groups[ageColor(now - s.t)].push(p);
        pts.push([p[0], p[1], s]);
      }
      var c = xD; c.clearRect(0, 0, W, H);
      var r = Math.min(4.5, 1.8 + zt.k * .18);
      c.lineWidth = .8; c.strokeStyle = COL.edge;
      for (var b = AGES.length - 1; b >= 0; b--) {        // oldest first, newest on top
        if (!groups[b].length) continue;
        var rb = b === 0 ? r + 1 : r;
        c.beginPath();
        groups[b].forEach(function(p){ c.moveTo(p[0] + rb, p[1]); c.arc(p[0], p[1], rb, 0, 2 * Math.PI); });
        c.fillStyle = AGES[b][1]; c.fill(); if (rb > 2) c.stroke();
      }
      qt = d3.quadtree(pts);
      lastDots = performance.now();
    }
    function frame(tm){
      requestAnimationFrame(frame);
      if (!active || document.hidden) { fresh = []; return; }
      if (tm - lastDots > 1000) { drawDots(); renderInfo(); }
      var c = xF; c.clearRect(0, 0, W, H);
      var st = home ? proj(home) : null;
      fresh = fresh.filter(function(s){ return tm - s.rx < Math.max(FLASH_MS, s.near ? TRACE_MS + 900 : 0); });
      fresh.forEach(function(s){
        var p = proj([s.lon, s.lat]); if (!p) return;
        var age = tm - s.rx, f = Math.min(1, age / FLASH_MS);
        // lines to the detecting stations, as Blitzortung's own map shows
        if (showSig && s.sig && age < SIG_MS) {
          c.beginPath();
          s.sig.forEach(function(d){ pathF({ type: 'LineString', coordinates: [[s.lon, s.lat], d] }); });
          c.strokeStyle = 'rgba(' + COL.sig + ',' + (.35 * (1 - age / SIG_MS)) + ')'; c.lineWidth = .6; c.stroke();
        }
        // a strike inside the station's radius sends a tracer to the station
        if (s.near && st) {
          var g = Math.min(1, age / TRACE_MS), ease = g * g;
          if (!s.line) {
            var interp = d3.geoInterpolate([s.lon, s.lat], home);
            s.line = d3.range(0, 1.0001, 1 / 40).map(function(x){ return interp(x); });
          }
          var upto = Math.max(1, Math.round(ease * (s.line.length - 1)));
          var fade = age > TRACE_MS ? Math.max(0, 1 - (age - TRACE_MS) / 900) : 1;
          c.beginPath(); pathF({ type: 'LineString', coordinates: s.line.slice(0, upto + 1) });
          c.strokeStyle = COL.trail; c.globalAlpha = .9 * fade; c.lineWidth = 1.6; c.lineCap = 'round'; c.stroke();
          var hp = proj(s.line[upto]);
          if (hp && age < TRACE_MS) {
            c.beginPath(); c.arc(hp[0], hp[1], 3, 0, 2 * Math.PI); c.fillStyle = 'rgba(' + COL.flash + ',1)';
            c.shadowColor = COL.trail; c.shadowBlur = 10; c.fill(); c.shadowBlur = 0;
          }
          if (age >= TRACE_MS && age < TRACE_MS + 900) {          // arrival: the station pulses
            var a = (age - TRACE_MS) / 900;
            c.beginPath(); c.arc(st[0], st[1], 6 + 22 * a, 0, 2 * Math.PI);
            c.strokeStyle = COL.station; c.globalAlpha = .9 * (1 - a); c.lineWidth = 2; c.stroke();
          }
          c.globalAlpha = 1;
        }
        if (age < FLASH_MS) {
          c.beginPath(); c.arc(p[0], p[1], 3 + 24 * f, 0, 2 * Math.PI);
          c.strokeStyle = 'rgba(' + COL.flash + ',' + (1 - f) + ')'; c.lineWidth = 2; c.stroke();
          c.beginPath(); c.arc(p[0], p[1], 2.8, 0, 2 * Math.PI);
          c.fillStyle = 'rgba(' + COL.flash + ',' + (1 - f * .6) + ')'; c.shadowColor = 'rgba(' + COL.flash + ',1)'; c.shadowBlur = 8; c.fill(); c.shadowBlur = 0;
          c.strokeStyle = COL.edge; c.lineWidth = .8; c.stroke();
        }
      });
    }
    requestAnimationFrame(frame);

    function renderInfo(){
      var now = Date.now(); prune(now);
      var c1 = 0, c10 = 0, near = 0, best = null;
      for (var i = head; i < strikes.length; i++) {
        var s = strikes[i], age = now - s.t;
        if (age < 60000) c1++;
        if (age < 600000) c10++;
        if (s.km != null) {
          if (s.km <= radiusKm) near++;
          if (!best || s.km < best.km) best = s;
        }
      }
      var total = strikes.length - head, u = distUnit();
      infoEl.innerHTML = t('Strikes') + ' &nbsp;1 min <b>' + c1.toLocaleString() + '</b> &nbsp;&middot;&nbsp; 10 min <b>' + c10.toLocaleString() +
        '</b> &nbsp;&middot;&nbsp; ' + t('on map') + ' <b>' + total.toLocaleString() + '</b>' +
        (home ? '<br>' + t('Within') + ' ' + Math.round(kmTo(radiusKm, u)) + ' ' + uLabel(u) + ' (1 h) <b>' + near.toLocaleString() + '</b>' +
          (best ? ' &nbsp;&middot;&nbsp; ' + t('Nearest') + ' <b>' + fmtDist(best.km) + '</b> ' + fmtAge(now - best.t) + ' ' + t('ago') : '') : '');
      var msg = '';
      if (statusEl.classList.contains('off')) msg = t('The Blitzortung feed can’t be reached at the moment — retrying');
      else if (statusEl.classList.contains('live') && !total) msg = t('Listening — strikes appear here as the network detects them');
      msgEl.textContent = msg; msgEl.hidden = !msg;
    }

    // ---- the Blitzortung feed ----
    function setStatus(cls, txt){ statusEl.className = 'll-status ' + cls; statusEl.lastChild.textContent = txt; }
    var ws = null, srv = Math.floor(Math.random() * SERVERS.length), lastMsg = 0, fails = 0, retryT = null;
    function connect(){
      if (!active || ws) return;
      var url = SERVERS[srv % SERVERS.length];
      setStatus('', t('Connecting') + '…');
      try { ws = new WebSocket(url); } catch (e) { ws = null; return retry(); }
      ws.onopen = function(){ try { ws.send('{"a":111}'); } catch (e) {} lastMsg = Date.now(); };
      ws.onmessage = function(ev){
        lastMsg = Date.now();
        if (!statusEl.classList.contains('live')) { fails = 0; setStatus('live', t('Live') + ' · ' + url.replace('wss://', '').split('.')[0]); }
        var d; try { d = JSON.parse(lzwDecode(ev.data)); } catch (e) { return; }
        addStrike(d);
      };
      ws.onerror = function(){ try { ws.close(); } catch (e) {} };
      ws.onclose = function(){ ws = null; retry(); };
    }
    function retry(){
      if (ws) { ws.onclose = ws.onerror = ws.onmessage = null; try { ws.close(); } catch (e) {} ws = null; }
      if (!active) return;
      fails++; srv++;
      var bad = fails > SERVERS.length;
      setStatus(bad ? 'off' : '', bad ? t('Feed unavailable') : t('Reconnecting') + '…');
      clearTimeout(retryT); retryT = setTimeout(connect, Math.min(30000, 2000 * fails));
      renderInfo();
    }
    function disconnect(){
      clearTimeout(retryT);
      if (ws) { ws.onclose = ws.onerror = ws.onmessage = null; try { ws.close(); } catch (e) {} ws = null; }
      fails = 0; setStatus('', t('Paused'));
    }
    setInterval(function(){                           // watchdog: reconnect if the feed stalls
      if (active && ws && ws.readyState === 1 && Date.now() - lastMsg > 90000) { try { ws.close(); } catch (e) {} }
    }, 15000);

    // ---- tooltip ----
    cF.addEventListener('mousemove', function(ev){
      if (!qt) return;
      var r = cF.getBoundingClientRect(), x = ev.clientX - r.left, y = ev.clientY - r.top;
      var hit = qt.find(x, y, 7);
      if (!hit) { tipEl.style.display = 'none'; return; }
      var s = hit[2];
      tipEl.innerHTML = '<b>' + fmtClock(s.t) + '</b> <span style="opacity:.7">(' + fmtAge(Date.now() - s.t) + ' ' + t('ago') + ')</span><br>' +
        Math.abs(s.lat).toFixed(3) + '°' + (s.lat >= 0 ? 'N' : 'S') + ', ' + Math.abs(s.lon).toFixed(3) + '°' + (s.lon >= 0 ? 'E' : 'W') +
        '<br>' + s.n + ' ' + t('detecting stations') + (s.km != null ? '<br>' + fmtDist(s.km) + ' ' + t('from') + ' ' + homeName : '');
      tipEl.style.display = 'block';
      var tw = tipEl.offsetWidth, th = tipEl.offsetHeight;
      tipEl.style.left = Math.max(4, Math.min(W - tw - 4, x + 12)) + 'px';
      tipEl.style.top = (y + th + 16 > H ? y - th - 10 : y + 14) + 'px';
    });
    cF.addEventListener('mouseleave', function(){ tipEl.style.display = 'none'; });

    // ---- buttons ----
    q('.ll-btns').addEventListener('click', function(e){
      var b = e.target.closest('.ll-btn'); if (!b || !built) return;
      var a = b.getAttribute('data-a');
      if (a === 'in') sel.transition().duration(350).call(zoom.scaleBy, 2);
      else if (a === 'out') sel.transition().duration(350).call(zoom.scaleBy, .5);
      else if (a === 'world') sel.transition().duration(750).call(zoom.transform, d3.zoomIdentity);
      else if (a === 'home') sel.transition().duration(900).call(zoom.transform, homeTransform(HOME_ZOOM));
      else if (a === 'night') { showNight = !showNight; b.classList.toggle('on', showNight); drawBase(); }
      else if (a === 'sig') { showSig = !showSig; b.classList.toggle('on', showSig); }
    });

    // ---- theme: colours come from CSS, so redraw when the theme changes ----
    function onTheme(){ readColours(); if (built) { drawBase(); drawDots(); } }
    var mo = new MutationObserver(onTheme);
    mo.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme', 'data-bs-theme', 'data-season', 'class'] });
    mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('storage', function(e){ if (e.key === 'dashboardUnitSystem') renderInfo(); });
    new ResizeObserver(function(){ relayout(true); }).observe(mapEl.parentNode);
    setInterval(function(){ if (active) drawBase(); }, 60000);    // keep the terminator moving

    // ---- data: coastlines and the station ----
    d3.json(base + 'jsondata/countries-110m.json').then(function(w){
      land110 = topojson.feature(w, w.objects.land || w.objects.countries);
      bord110 = topojson.mesh(w, w.objects.countries, function(a, b){ return a !== b; });
      if (built) drawBase();
      return d3.json(base + 'jsondata/countries-50m.json');       // sharper coastlines when zoomed in
    }).then(function(w){
      land50 = topojson.feature(w, w.objects.land || w.objects.countries);
      bord50 = topojson.mesh(w, w.objects.countries, function(a, b){ return a !== b; });
      if (built && zt.k >= 3) drawBase();
    }).catch(function(e){ console.warn('lightningLive: map data failed to load —', e && e.message); });

    function setStation(lat, lon, name){
      if (name) homeName = name;
      if (!isFinite(lat) || !isFinite(lon)) return;
      home = [lon, lat];
      for (var i = head; i < strikes.length; i++) {
        strikes[i].km = d3.geoDistance(home, [strikes[i].lon, strikes[i].lat]) * EARTH_KM;
        strikes[i].near = strikes[i].km <= radiusKm;
      }
      if (built) { built = false; W = 0; relayout(false); }
    }
    if (opts.lat != null && opts.lon != null) setStation(+opts.lat, +opts.lon, opts.name);
    else {
      fetch(base + 'jsondata/archive.json?_=' + Date.now(), { cache: 'no-store' })
        .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(a){ var m = (a && a.meta) || {}; setStation(parseFloat(m.latitude), parseFloat(m.longitude), m.station_location); })
        .catch(function(){});
    }

    readColours();
    return {
      show: function(){
        if (active) return;
        active = true; readColours();
        W = 0; built = false;               // the box may have been hidden: measure again
        relayout(false);
        connect();
        renderInfo();
      },
      hide: function(){ if (!active) return; active = false; disconnect(); tipEl.style.display = 'none'; },
      setStation: setStation,
      _inject: function(d){ addStrike(d); }   // for testing
    };
  }

  window.DivumLiveLightning = { mount: mount };
})();
