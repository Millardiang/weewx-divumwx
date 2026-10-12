/*
##############################################################################################
# equalEarthMap.js version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// Options, set by the page before this script loads:
//   window.EE_MAP_OPTIONS = { flatOnly: true }   flat map only, no globe (modalDaylightMap.html)
(function () {
  const OPT = window.EE_MAP_OPTIONS || {};
  const FLAT_ONLY = !!OPT.flatOnly;
  const W = 960, H = FLAT_ONLY ? 480 : 540, CX = W / 2, CY = H / 2;
  const WORLD_URL = "./jsondata/countries-110m.json";
  let STATION = null;   // { lat, lon, name } from weewx.conf via archive.json
  const $ = id => document.getElementById(id);
  const cv = $("eeCanvas"), ctx = cv.getContext("2d");
  cv.style.aspectRatio = W + " / " + H;
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // ---- Data ----
  let land = null, borders = null;
  const graticule = d3.geoGraticule10();
  const sphere = { type: "Sphere" };

  // ---- Morphing projection: Equal Earth (t=0) to orthographic (t=1) ----
  // Blend of the Equal Earth and orthographic formulas. The far hemisphere folds
  // back behind the near one, like paper wrapped round a ball, so it is drawn
  // first (shaded) and the near hemisphere is drawn over it.
  const mutate = d3.geoProjectionMutator(t => (x, y) => {
    const a = d3.geoEqualEarthRaw(x, y), b = d3.geoOrthographicRaw(x, y);
    return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
  });
  const projection = mutate(1);
  const path = d3.geoPath(projection, ctx);
  const S_FLAT = (W - 24) / 2 / 2.7066, S_GLOBE = H / 2 - 22;

  // ---- Flat-map tools: Equal Earth or Mercator, Tissot circles, country areas ----
  const R_KM = 6371.0088;
  const eeFlat = d3.geoEqualEarth().scale(S_FLAT).translate([CX, CY]).precision(0.3);
  const MS = (H / 2 - 4) / Math.log(Math.tan(Math.PI / 4 + 78 * Math.PI / 360));   // Mercator to 78 deg N/S
  const mercFlat = d3.geoMercator().scale(MS).translate([CX, CY]).precision(0.3)
    .clipExtent([[CX - Math.PI * MS, 0], [CX + Math.PI * MS, H]]);
  let flatProjName = "ee", showTissot = false, countries = [], selected = null;
  const flatProj = () => (flatProjName === "merc" ? mercFlat : eeFlat).rotate([lambda, 0]);
  // Areas are measured without the map's edge clipping, so a country cut off at the
  // top of the Mercator map (Greenland, Antarctica) is still measured whole.
  const mercArea = d3.geoMercator().scale(MS).translate([CX, CY]).precision(0.3);
  const areaProj = () => (flatProjName === "merc" ? mercArea : eeFlat).rotate([lambda, 0]);
  const areaPath = d3.geoPath();
  const tissots = [];
  for (let la = -60; la <= 60; la += 30)
    for (let lo = -180; lo < 180; lo += 30)
      tissots.push(d3.geoCircle().center([lo + 15, la]).radius(500 / R_KM * 180 / Math.PI)());
  let proj = projection;   // the projection markers and tracks are drawn with

  let t = FLAT_ONLY ? 0 : 1, lambda = 0, phi = -30, night = true, offsetMin = 0, showSunTrack = true, showMoon = true;
  // The far side shrinks away over the first half of the wrap
  const backClip = () => (t > 0.001 ? 180 - 90 * Math.min(1, t * 2) : null);
  function applyProjection(clip) {
    mutate(t);
    projection
      .scale(S_FLAT + (S_GLOBE - S_FLAT) * t)
      .translate([CX, CY])
      .rotate([lambda, phi * t])
      .clipAngle(clip)
      .precision(0.3);
  }

  // ---- Sun and Moon ----
  // Low-precision formulae from the Astronomical Almanac: Sun to ~0.01 deg,
  // Moon to ~0.3 deg. Sub-points are geocentric (no parallax).
  const RAD = Math.PI / 180;
  function gmstDeg(n) { return (280.46061837 + 360.98564736629 * n) % 360; }
  function subPoint(raDeg, decDeg, n) { return [((raDeg - gmstDeg(n)) % 360 + 540) % 360 - 180, decDeg]; }
  function daysJ2000(date) { return date.valueOf() / 864e5 + 2440587.5 - 2451545.0; }
  function sunPos(date) {
    const n = daysJ2000(date);
    const L = (280.460 + 0.9856474 * n) % 360, g = ((357.528 + 0.9856003 * n) % 360) * RAD;
    const lam = (L + 1.915 * Math.sin(g) + 0.020 * Math.sin(2 * g)) * RAD;
    const eps = (23.439 - 0.0000004 * n) * RAD;
    const ra = Math.atan2(Math.cos(eps) * Math.sin(lam), Math.cos(lam)) / RAD;
    const dec = Math.asin(Math.sin(eps) * Math.sin(lam)) / RAD;
    return { sub: subPoint(ra, dec, n), eclLon: lam / RAD };
  }
  function moonPos(date) {
    const n = daysJ2000(date), T = n / 36525, s = d => Math.sin(d * RAD);
    const lam = 218.32 + 481267.881 * T + 6.29 * s(135.0 + 477198.87 * T) - 1.27 * s(259.3 - 413335.36 * T)
      + 0.66 * s(235.7 + 890534.22 * T) + 0.21 * s(269.9 + 954397.74 * T) - 0.19 * s(357.5 + 35999.05 * T)
      - 0.11 * s(186.5 + 966404.03 * T);
    const bet = 5.13 * s(93.3 + 483202.02 * T) + 0.28 * s(228.2 + 960400.89 * T)
      - 0.28 * s(318.3 + 6003.15 * T) - 0.17 * s(217.6 - 407332.21 * T);
    const eps = (23.439 - 0.0000004 * n) * RAD, l = lam * RAD, b = bet * RAD;
    const x = Math.cos(b) * Math.cos(l);
    const y = Math.cos(eps) * Math.cos(b) * Math.sin(l) - Math.sin(eps) * Math.sin(b);
    const z = Math.sin(eps) * Math.cos(b) * Math.sin(l) + Math.cos(eps) * Math.sin(b);
    return { sub: subPoint(Math.atan2(y, x) / RAD, Math.asin(z) / RAD, n), eclLon: lam };
  }
  // Ground track from `when` until the body has gone once round the Earth
  // (360 deg of longitude westward): 24 h for the Sun, about 24 h 50 min for the Moon.
  function track(fn, when) {
    const line = [], hours = [];
    let prev = null, swept = 0;
    for (let m = 0; m <= 1620; m += 10) {
      const p = fn(new Date(when.valueOf() + m * 60000)).sub;
      if (prev) {
        const step = ((prev[0] - p[0]) % 360 + 540) % 360 - 180;
        if (swept + step >= 360) {
          line.push(d3.geoInterpolate(prev, p)((360 - swept) / step));
          break;
        }
        swept += step;
      }
      line.push(p);
      if (m > 0 && m % 60 === 0) hours.push(p);
      prev = p;
    }
    return { line: { type: "LineString", coordinates: line }, hours };
  }
  const PHASES = ["New Moon", "Waxing crescent", "First quarter", "Waxing gibbous", "Full Moon", "Waning gibbous", "Last quarter", "Waning crescent"];

  let sun = [0, 0], moon = [0, 0], bands = [], sunTrack = null, moonTrack = null, illum = 0, waxing = true;
  function stationAlt(ll) { return STATION ? 90 - d3.geoDistance([STATION.lon, STATION.lat], ll) / RAD : null; }
  function signed(v) { return (v >= 0 ? "+" : "−") + Math.abs(v).toFixed(1) + "°"; }
  function updateSun() {
    const when = new Date(Date.now() + offsetMin * 60000);
    const S = sunPos(when), M = moonPos(when);
    sun = S.sub; moon = M.sub;
    const anti = [sun[0] + 180, -sun[1]];
    bands = [90, 96, 102, 108].map(z => d3.geoCircle().center(anti).radius(180 - z)());
    sunTrack = track(sunPos, when); moonTrack = track(moonPos, when);

    const elong = d3.geoDistance(moon, sun) / RAD;
    waxing = ((M.eclLon - S.eclLon) % 360 + 360) % 360 < 180;
    illum = (1 - Math.cos(elong * RAD)) / 2;
    const phaseAngle = waxing ? elong : 360 - elong;

    $("eeShown").textContent = when.toISOString().slice(0, 16).replace("T", " ");
    $("eeSubsolar").textContent = fmtLL(sun);
    $("eeSublunar").textContent = fmtLL(moon);
    if (!STATION) {
      $("eeSunAlt").textContent = "\u2014"; $("eeMoonAlt").textContent = "\u2014";
    } else {
    const alt = stationAlt(sun);
    const phase = alt > -0.833 ? ["Day", "day"] : alt > -6 ? ["Civil twilight", "civil"]
      : alt > -12 ? ["Nautical twilight", ""] : alt > -18 ? ["Astronomical twilight", ""] : ["Night", ""];
    $("eeSunAlt").innerHTML = signed(alt) + `<span class="ee-pill ${phase[1]}">${phase[0]}</span>`;
    const malt = stationAlt(moon);
    $("eeMoonAlt").innerHTML = signed(malt) + `<span class="ee-pill">${malt > -0.3 ? "Up" : "Set"}</span>`;
    }
    $("eePhase").textContent = PHASES[Math.round(phaseAngle / 45) % 8] + " · " + Math.round(illum * 100) + "% lit";
  }
  const fmt = (v, p, n) => Math.abs(v).toFixed(1) + "°" + (v >= 0 ? p : n);
  const wrap180 = v => ((v + 540) % 360) - 180;
  const fmtLL = ll => fmt(ll[1], "N", "S") + " " + fmt(wrap180(ll[0]), "E", "W");

  // ---- Theme colours for canvas (follow body.light / body.dark) ----
  let C = {};
  function readColours() {
    const cs = getComputedStyle(document.body), v = k => cs.getPropertyValue(k).trim();
    for (const k of ["ocean", "land", "coast", "grid", "rim", "sun", "station", "moon-lit", "moon-dark", "moon-track"]) C[k] = v("--map-" + k);
    C.card = v("--bs-card-bg") || "#FFFFFF";
    C.night = v("--map-night");
    C.nightAlpha = parseFloat(v("--map-night-alpha")) || .15;
    C.halo = v("--map-halo");
    C.select = v("--map-select");
    C.tissot = v("--map-tissot");
  }
  readColours();
  new MutationObserver(() => { readColours(); draw(); }).observe(document.body, { attributes: true, attributeFilter: ["class"] });

  // ---- Canvas sizing ----
  let dpr = 1;
  function resize() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    cv.width = W * dpr; cv.height = H * dpr;
    draw();
  }

  const isFlat = () => t <= 0.001;
  const frontClip = () => (t > 0.001 ? 90 : null);
  function visible(ll) {
    const c = frontClip();
    return c === null || d3.geoDistance(ll, [-lambda, -phi * t]) < Math.PI / 2 - 1e-6;
  }
  function drawTrack(tr, colour) {
    ctx.save();
    ctx.strokeStyle = colour; ctx.lineCap = "round";
    ctx.globalAlpha = 0.9; ctx.lineWidth = 2;
    ctx.beginPath(); path(tr.line); ctx.stroke();
    ctx.fillStyle = colour;
    for (const h of tr.hours) {
      if (!visible(h)) continue;
      const p = proj(h); if (!p || !isFinite(p[0])) continue;
      ctx.beginPath(); ctx.arc(p[0], p[1], 2, 0, 2 * Math.PI); ctx.fill();
    }
    ctx.restore();
  }
  function moonGlyph(ll, r) {
    if (!visible(ll)) return;
    const p = proj(ll); if (!p || !isFinite(p[0])) return;
    const [x, y] = p;
    ctx.save();
    ctx.beginPath(); ctx.arc(x, y, r, 0, 2 * Math.PI); ctx.fillStyle = C["moon-dark"]; ctx.fill();
    ctx.beginPath();
    if (waxing) ctx.arc(x, y, r, -Math.PI / 2, Math.PI / 2); else ctx.arc(x, y, r, Math.PI / 2, 3 * Math.PI / 2);
    ctx.closePath(); ctx.fillStyle = C["moon-lit"]; ctx.fill();
    ctx.beginPath(); ctx.ellipse(x, y, Math.max(0.01, r * Math.abs(1 - 2 * illum)), r, 0, 0, 2 * Math.PI);
    ctx.fillStyle = illum > 0.5 ? C["moon-lit"] : C["moon-dark"]; ctx.fill();
    ctx.beginPath(); ctx.arc(x, y, r, 0, 2 * Math.PI); ctx.lineWidth = 1.2; ctx.strokeStyle = C.rim; ctx.stroke();
    ctx.restore();
  }
  function dot(ll, r, fill, stroke) {
    if (!visible(ll)) return;
    const p = proj(ll);
    if (!p || !isFinite(p[0])) return;
    ctx.beginPath(); ctx.arc(p[0], p[1], r, 0, 2 * Math.PI);
    ctx.fillStyle = fill; ctx.fill();
    ctx.lineWidth = 1.5; ctx.strokeStyle = stroke; ctx.stroke();
  }

  // ---- Draw ----
  function layers(shade, ocean = true, shadeNight = true) {
    if (ocean) { ctx.beginPath(); path(sphere); ctx.fillStyle = C.ocean; ctx.fill(); }
    ctx.beginPath(); path(graticule); ctx.lineWidth = .5; ctx.strokeStyle = C.grid; ctx.stroke();
    if (land) {
      ctx.beginPath(); path(land); ctx.fillStyle = C.land; ctx.fill();
      ctx.beginPath(); path(borders); ctx.lineWidth = .5; ctx.strokeStyle = C.coast; ctx.stroke();
    }
    if (night && shadeNight) {
      ctx.fillStyle = `rgba(${C.night}, ${C.nightAlpha})`;
      for (const b of bands) { ctx.beginPath(); path(b); ctx.fill(); }
    }
    if (shade > 0) { ctx.beginPath(); path(sphere); ctx.fillStyle = `rgba(${C.night}, ${shade})`; ctx.fill(); }
  }

  function drawMarkers() {
    if (showSunTrack && sunTrack) drawTrack(sunTrack, C.sun);
    if (showMoon && moonTrack) drawTrack(moonTrack, C["moon-track"]);
    dot(sun, 6.5, C.sun, C.rim);
    if (showMoon) moonGlyph(moon, 7);
    if (STATION) dot([STATION.lon, STATION.lat], 4.5, C.station, C.card);
  }

  function drawFlat() {
    const fp = flatProj();
    path.projection(fp); proj = fp;
    layers(0);
    if (selected) {
      ctx.beginPath(); path(selected); ctx.fillStyle = C.select; ctx.fill();
      ctx.lineWidth = 1; ctx.strokeStyle = C.rim; ctx.stroke();
    }
    if (showTissot) {
      ctx.fillStyle = `rgba(${C.tissot}, .45)`; ctx.strokeStyle = `rgba(${C.tissot}, .9)`; ctx.lineWidth = .8;
      for (const c of tissots) { ctx.beginPath(); path(c); ctx.fill(); ctx.stroke(); }
    }
    ctx.beginPath(); path(sphere); ctx.lineWidth = 1.2; ctx.strokeStyle = C.rim; ctx.stroke();
    drawMarkers();
    path.projection(projection); proj = projection;
    $("eeCentre").textContent = fmtLL([-lambda, 0]);
    updateFlatReadout();
  }

  function draw() {
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);
    if (isFlat()) { drawFlat(); return; }
    applyProjection(90);
    const R = projection.scale();

    // atmosphere halo, fading in as the map becomes a globe
    if (t > 0.6) {
      const k = (t - 0.6) / 0.4;
      const g = ctx.createRadialGradient(CX, CY, R * 0.98, CX, CY, R * 1.12);
      g.addColorStop(0, `rgba(${C.halo}, ${0.35 * k})`);
      g.addColorStop(1, `rgba(${C.halo}, 0)`);
      ctx.fillStyle = g; ctx.beginPath(); ctx.arc(CX, CY, R * 1.12, 0, 2 * Math.PI); ctx.fill();
    }

    // far side: the part of the sheet curling round behind
    const clip = backClip();
    if (clip === null) {
      applyProjection(null);
      layers(0);
      ctx.beginPath(); path(sphere); ctx.lineWidth = 1.2; ctx.strokeStyle = C.rim; ctx.stroke();
    } else if (clip > 90) {
      applyProjection(clip);
      ctx.globalAlpha = Math.max(0, 1 - 2 * t) * 0.85;
      layers(0, false, false);
      ctx.globalAlpha = 1;
    }

    // near side
    if (t > 0.001) {
      applyProjection(90);
      layers(0);
      // gentle sphere shading, strongest when fully wrapped
      const g = ctx.createRadialGradient(CX - R * .35, CY - R * .35, R * .1, CX, CY, R);
      g.addColorStop(0, "rgba(255,255,255,0)");
      g.addColorStop(1, `rgba(${C.night}, ${0.18 * t * t})`);
      ctx.beginPath(); path(sphere); ctx.fillStyle = g; ctx.fill();
      ctx.beginPath(); path(sphere); ctx.lineWidth = 1.2;
      ctx.globalAlpha = Math.min(1, t * t * 1.5); ctx.strokeStyle = C.rim; ctx.stroke(); ctx.globalAlpha = 1;
    }

    applyProjection(frontClip());
    drawMarkers();
    $("eeCentre").textContent = fmtLL([-lambda, -phi * t]);
  }

  // ---- Wrap animation ----
  let anim = null;
  function syncFlatUI() {
    const flat = isFlat();
    $("eeFlatTools").hidden = !flat;
    $("eeFlatReadout").hidden = !flat;
    if (!flat) { tip.hidden = true; }
  }
  function leaveMercator() {
    if (flatProjName !== "merc") return;
    flatProjName = "ee";
    $("eeProjEE").setAttribute("aria-pressed", "true"); $("eeProjMerc").setAttribute("aria-pressed", "false");
  }
  function setWrap(target, animate) {
    if (anim) anim.stop();
    if (target > 0) leaveMercator();
    const from = t;
    const done = () => { $("eeGlobe").setAttribute("aria-pressed", String(t === 1)); $("eeFlat").setAttribute("aria-pressed", String(t === 0)); };
    if (!animate || reduceMotion || from === target) { t = target; syncWrapUI(); draw(); done(); return; }
    const dur = 1600 * Math.abs(target - from);
    anim = d3.timer(el => {
      const k = Math.min(1, el / dur);
      t = from + (target - from) * d3.easeCubicInOut(k);
      if (k === 1) { t = target; anim.stop(); anim = null; done(); }
      syncWrapUI(); draw();
    });
  }
  function syncWrapUI() {
    $("eeWrap").value = Math.round(t * 100);
    $("eeWrapOut").textContent = Math.round(t * 100) + "%";
    syncFlatUI();
  }
  $("eeFlat").onclick = () => setWrap(0, true);
  $("eeGlobe").onclick = () => setWrap(1, true);
  $("eeWrap").addEventListener("input", e => {
    if (anim) { anim.stop(); anim = null; }
    t = +e.target.value / 100;
    if (t > 0) leaveMercator();
    syncWrapUI(); draw();
    $("eeGlobe").setAttribute("aria-pressed", String(t === 1)); $("eeFlat").setAttribute("aria-pressed", String(t === 0));
  });

  // ---- Flat-map tools ----
  const tip = $("eeTip"), stage = $("eeStage");
  const nf = new Intl.NumberFormat();
  function setFlatProj(name) {
    flatProjName = name;
    $("eeProjEE").setAttribute("aria-pressed", String(name === "ee"));
    $("eeProjMerc").setAttribute("aria-pressed", String(name === "merc"));
    draw();
  }
  $("eeProjEE").onclick = () => setFlatProj("ee");
  $("eeProjMerc").onclick = () => setFlatProj("merc");
  $("eeTissotBtn").onclick = function () { showTissot = !showTissot; this.setAttribute("aria-pressed", String(showTissot)); draw(); };

  // Map size of a country relative to the same area at the equator on the
  // central meridian: 1.00 everywhere on an equal-area map.
  function updateFlatReadout() {
    if (!selected) { $("eeCountry").textContent = "\u2014"; $("eeArea").textContent = "\u2014"; $("eeDist").textContent = "\u2014"; return; }
    areaPath.projection(areaProj());
    const sr = d3.geoArea(selected);
    const ref = d3.geoCircle().center([-lambda, 0]).radius(1)();
    let ratio = areaPath.area(selected) / sr / (areaPath.area(ref) / d3.geoArea(ref));
    // Mercator stretches the poles to infinity, so a country that reaches a pole has no finite map size
    const reachesPole = flatProjName === "merc" && d3.geoBounds(selected).some(([, la]) => Math.abs(la) > 89.9);
    if (reachesPole || !isFinite(ratio)) ratio = Infinity;
    const ok = Math.abs(ratio - 1) < 0.05;
    $("eeCountry").textContent = selected.properties.name;
    $("eeArea").textContent = nf.format(Math.round(sr * R_KM * R_KM)) + " km\u00B2";
    $("eeDist").innerHTML = (isFinite(ratio) ? "\u00D7" + ratio.toFixed(2) : "\u221E") +
      `<span class="ee-pill ${ok ? "ok" : "bad"}">${ok ? "true size" : "inflated"}</span>`;
  }
  function canvasPoint(e) {
    const r = cv.getBoundingClientRect();
    return [(e.clientX - r.left) * W / r.width, (e.clientY - r.top) * H / r.height, e.clientX - r.left, e.clientY - r.top];
  }
  // Longitude/latitude under a canvas point on the flat map, or null if off the map
  function flatInvert(x, y) {
    const fp = flatProj(), ll = fp.invert([x, y]);
    if (!ll || !isFinite(ll[0]) || !isFinite(ll[1])) return null;
    if (flatProjName === "merc") return Math.abs(x - CX) <= Math.PI * MS && y >= 0 && y <= H ? ll : null;
    const back = fp(ll);
    return back && Math.hypot(back[0] - x, back[1] - y) < 0.5 ? ll : null;
  }
  function countryAt(ll) {
    if (!ll) return null;
    for (const f of countries) if (d3.geoContains(f, ll)) return f;
    return null;
  }
  let hoverFrame = 0, lastHover = null;
  cv.addEventListener("pointermove", e => {
    if (!isFlat() || drag) return;
    lastHover = e;
    if (hoverFrame) return;
    hoverFrame = requestAnimationFrame(() => {
      hoverFrame = 0;
      const [x, y, px, py] = canvasPoint(lastHover), ll = flatInvert(x, y);
      $("eeCursor").textContent = ll ? fmtLL(ll) : "\u2014";
      const c = countryAt(ll);
      cv.style.cursor = c ? "pointer" : "";
      if (c) { tip.textContent = c.properties.name; tip.style.left = px + "px"; tip.style.top = py + "px"; tip.hidden = false; }
      else tip.hidden = true;
    });
  });
  cv.addEventListener("pointerleave", () => { tip.hidden = true; $("eeCursor").textContent = "\u2014"; cv.style.cursor = ""; });
  cv.addEventListener("click", e => {
    if (!isFlat() || dragMoved) return;
    const [x, y] = canvasPoint(e), c = countryAt(flatInvert(x, y));
    selected = c && c !== selected ? c : null;
    draw();
  });

  // ---- Turn to a point ----
  let turn = null, autoView = true;
  function setAuto(on) { autoView = on; $("eeAuto").setAttribute("aria-pressed", String(on)); }
  // Centre that keeps the station, and the Sun and Moon when they are up there, on the near side
  function autoCentre() {
    if (!STATION) return [sun[0], Math.max(-60, Math.min(60, sun[1] + 25))];
    const st = [STATION.lon, STATION.lat], pts = [st];
    if (stationAlt(sun) > -0.833) pts.push(sun);
    if (showMoon && stationAlt(moon) > -0.3) pts.push(moon);
    let x = 0, y = 0, z = 0;
    for (const [lo, la] of pts) {
      const a = lo * RAD, b = la * RAD;
      x += Math.cos(b) * Math.cos(a); y += Math.cos(b) * Math.sin(a); z += Math.sin(b);
    }
    const c = [Math.atan2(y, x) / RAD, Math.atan2(z, Math.hypot(x, y)) / RAD];
    return pts.every(p => d3.geoDistance(p, c) / RAD < 80) ? c : st;
  }
  function jumpTo(ll) {
    lambda += ((-ll[0] - lambda) % 360 + 540) % 360 - 180;
    phi = -ll[1];
  }
  function faceTo(ll, keepAuto = false) {
    setAuto(keepAuto);
    stopSpin(); if (turn) turn.stop();
    const r0 = [lambda, phi], r1 = [-ll[0], -ll[1]];
    let dl = ((r1[0] - r0[0]) % 360 + 540) % 360 - 180;
    const from = [lambda, phi];
    if (reduceMotion) { lambda = from[0] + dl; phi = r1[1]; draw(); return; }
    turn = d3.timer(el => {
      const k = Math.min(1, el / 1100), e = d3.easeCubicInOut(k);
      lambda = from[0] + dl * e; phi = from[1] + (r1[1] - from[1]) * e;
      draw();
      if (k === 1) { turn.stop(); turn = null; }
    });
  }
  $("eeFaceStation").disabled = true;
  $("eeFaceStation").onclick = () => { if (STATION) faceTo([STATION.lon, STATION.lat]); };
  $("eeAuto").onclick = () => faceTo(autoCentre(), true);
  $("eeFaceSun").onclick = () => faceTo(sun);
  $("eeNightBtn").onclick = function () { night = !night; this.setAttribute("aria-pressed", String(night)); draw(); };
  $("eeSunTrackBtn").onclick = function () { showSunTrack = !showSunTrack; this.setAttribute("aria-pressed", String(showSunTrack)); draw(); };
  $("eeMoonBtn").onclick = function () { showMoon = !showMoon; this.setAttribute("aria-pressed", String(showMoon)); draw(); };
  $("eeFaceMoon").onclick = () => faceTo(moon);

  // ---- Spin ----
  let spin = null;
  function stopSpin() { if (spin) { spin.stop(); spin = null; $("eeSpin").setAttribute("aria-pressed", "false"); } }
  $("eeSpin").onclick = () => {
    if (spin) return stopSpin();
    if (turn) { turn.stop(); turn = null; }
    setAuto(false);
    $("eeSpin").setAttribute("aria-pressed", "true");
    let last = 0;
    spin = d3.timer(el => { lambda += (el - last) * (reduceMotion ? 0.003 : 0.012); last = el; draw(); });
  };

  // ---- Drag ----
  let drag = null, dragMoved = false;
  cv.addEventListener("pointerdown", e => {
    dragMoved = false;
    if (e.pointerType === "touch" && t < 1) return;
    drag = { x: e.clientX, y: e.clientY, l: lambda, p: phi, id: e.pointerId };
  });
  cv.addEventListener("pointermove", e => {
    if (!drag) return;
    if (!dragMoved) {
      if (Math.hypot(e.clientX - drag.x, e.clientY - drag.y) < 4) return;
      dragMoved = true; tip.hidden = true;
      stopSpin(); setAuto(false); if (turn) { turn.stop(); turn = null; }
      cv.setPointerCapture(drag.id); cv.classList.add("dragging");
    }
    const rect = cv.getBoundingClientRect(), k = W / rect.width;
    applyProjection(90);
    const R = projection.scale();
    const degPerPx = t > 0.5 ? 90 / R : 360 / (W - 24);
    lambda = drag.l + (e.clientX - drag.x) * k * degPerPx;
    if (t > 0.05) phi = Math.max(-90, Math.min(90, drag.p - (e.clientY - drag.y) * k * degPerPx));
    draw();
  });
  const endDrag = () => { drag = null; cv.classList.remove("dragging"); };
  cv.addEventListener("pointerup", endDrag);
  cv.addEventListener("pointercancel", endDrag);
  cv.addEventListener("keydown", e => {
    const step = 5, keys = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, step], ArrowDown: [0, -step] };
    if (!keys[e.key]) return;
    stopSpin(); setAuto(false); lambda += keys[e.key][0]; phi = Math.max(-90, Math.min(90, phi + keys[e.key][1])); draw(); e.preventDefault();
  });

  // ---- Time scrubber ----
  function fmtOffset(m) {
    if (m === 0) return "Now";
    const s = m < 0 ? "−" : "+", a = Math.abs(m);
    return s + Math.floor(a / 60) + "h" + String(a % 60).padStart(2, "0");
  }
  $("eeTime").addEventListener("input", e => { offsetMin = +e.target.value; $("eeTimeOut").textContent = fmtOffset(offsetMin); refresh(); });
  $("eeNow").onclick = () => { offsetMin = 0; $("eeTime").value = 0; $("eeTimeOut").textContent = "Now"; refresh(); };

  // ---- Live refresh ----
  function refresh() {
    updateSun();
    if (autoView && !turn && !drag && !spin) jumpTo(autoCentre());
    draw();
  }
  setInterval(refresh, 5000);
  document.addEventListener("visibilitychange", () => { if (!document.hidden) refresh(); });

  if (FLAT_ONLY) {
    for (const id of ["eeViewGroup", "eeWrapCtl", "eeSpin"]) if ($(id)) $(id).hidden = true;
    syncFlatUI();
  }
  updateSun();
  jumpTo(autoCentre());
  resize();
  window.addEventListener("resize", resize);

  fetch(WORLD_URL, { cache: "force-cache" })
    .then(r => { if (!r.ok) throw new Error("HTTP " + r.status); return r.json(); })
    .then(world => {
      land = topojson.feature(world, world.objects.countries);
      countries = land.features;
      selected = countries.find(f => f.properties.name === "Greenland") || null;
      borders = topojson.mesh(world, world.objects.countries, (a, b) => a !== b);
      draw();
    })
    .catch(e => console.warn("equalEarthMap: world map load failed \u2014", e.message));

  fetch("./jsondata/archive.json?_=" + Date.now(), { cache: "no-store" })
    .then(r => { if (!r.ok) throw new Error("HTTP " + r.status); return r.json(); })
    .then(data => {
      const m = data && data.meta;
      if (m && typeof m.latitude === "number" && typeof m.longitude === "number") {
        STATION = { lat: m.latitude, lon: m.longitude };
        try { STATION_LAT = m.latitude; STATION_LON = m.longitude; } catch (e) { /* page has no theme script */ }
        $("eeFaceStation").disabled = false;
        if (autoView) jumpTo(autoCentre());
        refresh();
        if (typeof applyDashboardTheme === "function") applyDashboardTheme();
      }
    })
    .catch(e => console.warn("equalEarthMap: station location fetch failed \u2014", e.message));

  function reportHeight() {
    if (window.parent === window || !document.body) return;
    window.parent.postMessage({ type: "chartModalContentHeight", height: document.body.scrollHeight, width: document.body.scrollWidth }, "*");
  }
  if (window.parent !== window) {
    window.addEventListener("load", () => {
      reportHeight();
      new ResizeObserver(reportHeight).observe(document.body);
    });
  }
})();
