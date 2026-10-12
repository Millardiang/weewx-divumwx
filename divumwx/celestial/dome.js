/*
##############################################################################################
# dome.js version 1.0.0
#  The Celestial page's two sky charts (D3 v7), both looking up from the
#  station -- zenith at the centre, horizon at the rim, north up, east left:
#
#   - The Sky Overhead: the sky as it stands now, with the Sun, Moon,
#     planets, comets and any satellite crossing it, and an "Up now" list.
#   - The Next Visible Pass: the sky at the highest point of the soonest
#     satellite pass you can see, with the pass's arc across it, the
#     satellite swept along the arc live while the pass is on, and a
#     table of every configured satellite's next passes.
#
#  Stars and constellation figures come from sky-data.json (built from
#  d3-celestial by tools/build_sky_data.py, J2000); they are precessed to
#  date and turned with the station's sidereal time in the browser. The
#  Sun, Moon, planets, comets and satellites come from almanac.json
#  through celestial.js (window.DivumwxCelestial).
#
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/
(function(){
  'use strict';

  var C = window.DivumwxCelestial;
  if (!C || !window.d3) return;

  // The catalogue sits beside this script, wherever the page is.
  var SKY_URL = 'sky-data.json?v=1.0.0';
  try { if (document.currentScript && document.currentScript.src) SKY_URL = new URL(SKY_URL, document.currentScript.src).href; } catch (e) {}
  var SIDEREAL_DEG_PER_S = 360.98564736629 / 86400;
  var RAD = Math.PI / 180;
  var REPROJECT_DEG = 0.02;            // re-place stars once the sky has turned this far (~5 s)
  var DOME_BODIES = ['sun', 'moon', 'mercury', 'venus', 'mars', 'jupiter', 'saturn', 'uranus', 'neptune'];
  var NAMES = { sun: 'Sun', moon: 'Moon', mercury: 'Mercury', venus: 'Venus', mars: 'Mars',
                jupiter: 'Jupiter', saturn: 'Saturn', uranus: 'Uranus', neptune: 'Neptune' };
  // How a planet can be seen once the sky is dark and it is clear of the horizon.
  var SEEN_WITH = { uranus: 'Binoculars', neptune: 'Telescope' };

  var sky = null;          // shared catalogue, precessed to date
  var conNames = {};       // 'Ori' -> 'Orion'
  var bounds = [];         // [[id, GeoJSON polygon]], J2000, winding fixed

  // ------------------------------------------------------------------
  // Astronomy (all angles in degrees)
  // ------------------------------------------------------------------
  function norm360(x){ return ((x % 360) + 360) % 360; }

  // J2000 -> mean equator and equinox of date (IAU 1976 precession).
  function precessor(T){
    var as = 1 / 3600;
    var zeta = (2306.2181 * T + 0.30188 * T * T + 0.017998 * T * T * T) * as * RAD;
    var z = (2306.2181 * T + 1.09468 * T * T + 0.018203 * T * T * T) * as * RAD;
    var th = (2004.3109 * T - 0.42665 * T * T - 0.041833 * T * T * T) * as * RAD;
    var ct = Math.cos(th), st = Math.sin(th);
    return function(ra, dec){
      var a = ra * RAD + zeta, d = dec * RAD;
      var A = Math.cos(d) * Math.sin(a);
      var B = ct * Math.cos(d) * Math.cos(a) - st * Math.sin(d);
      var Cc = st * Math.cos(d) * Math.cos(a) + ct * Math.sin(d);
      return [norm360((Math.atan2(A, B) + z) / RAD), Math.asin(Math.max(-1, Math.min(1, Cc))) / RAD];
    };
  }
  function centuriesSinceJ2000(unix){ return (unix / 86400 + 2440587.5 - 2451545.0) / 36525; }

  function altaz(ra, dec, lst, lat){
    var H = (lst - ra) * RAD, d = dec * RAD, p = lat * RAD;
    var sa = Math.sin(p) * Math.sin(d) + Math.cos(p) * Math.cos(d) * Math.cos(H);
    var alt = Math.asin(Math.max(-1, Math.min(1, sa))) / RAD;
    var az = Math.atan2(-Math.cos(d) * Math.sin(H), Math.sin(d) * Math.cos(p) - Math.cos(d) * Math.sin(p) * Math.cos(H)) / RAD;
    return [alt, norm360(az)];
  }
  // Horizontal -> equatorial of date.
  function radec(alt, az, lst, lat){
    var a = alt * RAD, A = az * RAD, p = lat * RAD;
    var sd = Math.sin(p) * Math.sin(a) + Math.cos(p) * Math.cos(a) * Math.cos(A);
    var dec = Math.asin(Math.max(-1, Math.min(1, sd)));
    var H = Math.atan2(-Math.sin(A) * Math.cos(a), Math.cos(p) * Math.sin(a) - Math.sin(p) * Math.cos(a) * Math.cos(A));
    return [norm360(lst - H / RAD), dec / RAD];
  }
  function vec(alt, az){
    var a = alt * RAD, A = az * RAD;
    return [Math.cos(a) * Math.sin(A), Math.cos(a) * Math.cos(A), Math.sin(a)];
  }

  function constellationAt(ra2000, dec2000){
    var lon = ra2000 > 180 ? ra2000 - 360 : ra2000;
    for (var i = 0; i < bounds.length; i++) {
      if (d3.geoContains(bounds[i][1], [lon, dec2000])) return bounds[i][0];
    }
    return null;
  }
  function constellationOfDate(ra, dec){
    if (!sky || !sky.unprecess) return null;
    var p = sky.unprecess(ra, dec);
    var id = constellationAt(p[0], p[1]);
    return id ? (conNames[id] || id) : null;
  }

  function stationLatitude(){
    var s = C.latest();
    return s ? C.num(s.d['celestial.station.latitude']) : null;
  }
  function currentLst(now){
    var s = C.latest();
    var lst = s && C.num(s.d['almanac.sidereal_time']);
    if (lst == null) return null;
    return norm360(lst + SIDEREAL_DEG_PER_S * C.advanceFor(now));
  }
  function esc(s){
    return String(s).replace(/[&<>"]/g, function(c){ return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
  }

  // ------------------------------------------------------------------
  // Catalogue (shared by both charts)
  // ------------------------------------------------------------------
  var skyReady = [];
  function loadSky(){
    fetch(SKY_URL, { cache: 'force-cache' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(raw){
        var ra = function(lon){ return lon < 0 ? lon + 360 : lon; };
        sky = {
          T: null,
          stars: raw.stars.map(function(s, i){
            return { ra0: ra(s[0]), dec0: s[1], mag: s[2], bv: s[3], name: raw.names[String(i)] || null };
          }),
          lines: raw.lines.map(function(l){
            return { id: l[0], segs: l[1].map(function(seg){ return seg.map(function(p){ return [ra(p[0]), p[1]]; }); }) };
          }),
          labels: raw.labels.map(function(l){ return { id: l[0], name: l[1], ra0: ra(l[2]), dec0: l[3], rank: l[4] }; })
        };
        sky.labels.forEach(function(l){ conNames[l.id] = l.name; });
        // d3-celestial's boundary rings don't all wind the same way; d3
        // reads a ring that encloses more than a hemisphere as "everything
        // outside it", so reverse those.
        bounds = raw.bounds.map(function(b){
          var g = { type: 'Polygon', coordinates: b[1] };
          if (d3.geoArea(g) > 2 * Math.PI) g = { type: 'Polygon', coordinates: b[1].map(function(r){ return r.slice().reverse(); }) };
          return [b[0], g];
        });
        skyReady.forEach(function(fn){ fn(); });
      })
      .catch(function(e){
        console.warn('dome: sky-data.json failed —', e.message);
        d3.selectAll('#celUpNow, [data-dome-note]').html('<p class="cel-note">The star catalogue (sky-data.json) could not be loaded.</p>');
      });
  }
  function precessTo(unix){
    var T = centuriesSinceJ2000(unix);
    if (sky.T != null && Math.abs(T - sky.T) < 0.002) return;     // re-precess every ~2 months
    var P = precessor(T);
    sky.stars.forEach(function(s){ var p = P(s.ra0, s.dec0); s.ra = p[0]; s.dec = p[1]; });
    sky.labels.forEach(function(l){ var p = P(l.ra0, l.dec0); l.ra = p[0]; l.dec = p[1]; });
    sky.lines.forEach(function(l){ l.segsDate = l.segs.map(function(seg){ return seg.map(function(q){ return P(q[0], q[1]); }); }); });
    sky.T = T;
    // Date -> J2000 is close enough to the inverse rotation for "which
    // constellation is this in"; a fraction of a degree only matters on a boundary.
    sky.unprecess = precessor(-T);
  }

  // ------------------------------------------------------------------
  // Colours that follow the Sun (and the theme)
  // ------------------------------------------------------------------
  var SUN_STOPS = [-18, -12, -6, 0, 6];
  var plateDark = d3.scaleLinear().domain(SUN_STOPS).range(['#04071A', '#0A1331', '#15234E', '#203767', '#284476']).clamp(true).interpolate(d3.interpolateRgb);
  var plateLight = d3.scaleLinear().domain(SUN_STOPS).range(['#EDE8DA', '#EDE9DD', '#EBEBE4', '#E5ECF2', '#DDE8F3']).clamp(true).interpolate(d3.interpolateRgb);
  var starOpDark = d3.scaleLinear().domain([-18, -6, 0, 6]).range([1, 0.95, 0.62, 0.4]).clamp(true);
  var starOpLight = d3.scaleLinear().domain([-18, 0, 6]).range([1, 0.9, 0.8]).clamp(true);
  function starColour(bv, dark){
    if (!dark) return null;          // light plate: ink, from CSS
    if (bv < 0.0) return '#B4CBFF';
    if (bv < 0.35) return '#E4ECFF';
    if (bv < 0.8) return '#FFF6E6';
    if (bv < 1.2) return '#FFE0B8';
    return '#FFC796';
  }
  function skyState(sunAlt){
    if (sunAlt > -0.833) return 'Daylight';
    if (sunAlt > -6) return 'Civil twilight';
    if (sunAlt > -12) return 'Nautical twilight';
    if (sunAlt > -18) return 'Astronomical twilight';
    return 'Dark sky';
  }
  function isDark(){ return document.body.classList.contains('dark'); }

  var measureCtx = document.createElement('canvas').getContext('2d');
  function textWidth(s, px, weight){
    measureCtx.font = (weight || 500) + ' ' + px + 'px Lexend, Helvetica, Arial, sans-serif';
    return measureCtx.measureText(s).width;
  }

  // ------------------------------------------------------------------
  // A sky chart: one SVG, one projection, its own label placement and hover.
  // ------------------------------------------------------------------
  var chartCount = 0;
  function SkyChart(svgId, wrapId, tipId){
    var self = this;
    this.wrap = document.getElementById(wrapId);
    this.svg = d3.select('#' + svgId);
    this.tip = document.getElementById(tipId);
    var clipId = 'celSkyClip' + (++chartCount);
    this.clipCircle = this.svg.append('defs').append('clipPath').attr('id', clipId).append('circle');
    this.gPlate = this.svg.append('g');
    var gClip = this.svg.append('g').attr('clip-path', 'url(#' + clipId + ')');
    this.gGrid = gClip.append('g').attr('class', 'grid');
    this.gLines = gClip.append('g').attr('class', 'figures');
    this.gEcl = gClip.append('g').attr('class', 'ecliptic');
    this.gStars = gClip.append('g').attr('class', 'stars');
    this.gArc = gClip.append('g').attr('class', 'pass-arc');
    this.gRim = this.svg.append('g').attr('class', 'rim');
    this.gLabels = this.svg.append('g').attr('class', 'labels');
    this.gMarks = this.svg.append('g').attr('class', 'dome-bodies');
    this.gFocus = this.svg.append('g').attr('class', 'focus-ring');
    this.geom = null;
    this.proj = {};
    this.starXY = [];        // per star: [x, y] or null, for this chart's projection
    this.starAlt = [];
    this.starAz = [];
    this.hover = { items: [], delaunay: null };
    this.focus = -1;
    this.lst = null; this.lat = null;
    this.svg.on('mousemove', function(ev){ self._hoverAt(ev, 16, false); })
      .on('mouseleave', function(){ self.hideTip(); })
      .on('click', function(ev){ ev.stopPropagation(); self._hoverAt(ev, 22, true); });
    document.addEventListener('click', function(){ if (self.focus >= 0) self.hideTip(); });
  }

  SkyChart.prototype.layout = function(){
    var W = Math.max(280, Math.min(720, this.wrap.clientWidth || 600));
    var small = W < 460;
    var pad = small ? 20 : 26;
    var g = this.geom = { W: W, cx: W / 2, cy: W / 2, R: W / 2 - pad, small: small };
    this.svg.attr('viewBox', '0 0 ' + W + ' ' + W).attr('width', W).attr('height', W).classed('small', small);
    this.clipCircle.attr('cx', g.cx).attr('cy', g.cy).attr('r', g.R);
    this.gPlate.selectAll('*').remove();
    this.gPlate.append('circle').attr('class', 'dome-plate').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.R);
    this.gGrid.selectAll('*').remove();
    var self = this;
    [30, 60].forEach(function(alt){
      self.gGrid.append('circle').attr('class', 'alt-ring').attr('cx', g.cx).attr('cy', g.cy)
        .attr('r', g.R * Math.tan((90 - alt) * RAD / 2));
    });
    this.gGrid.append('path').attr('class', 'zenith')
      .attr('d', 'M' + (g.cx - 5) + ',' + g.cy + 'h10M' + g.cx + ',' + (g.cy - 5) + 'v10');
    if (!small) {
      [30, 60].forEach(function(alt){
        var r = g.R * Math.tan((90 - alt) * RAD / 2);
        self.gGrid.append('text').attr('class', 'alt-label').attr('x', g.cx + 3).attr('y', g.cy - r - 3).text(alt + '°');
      });
    }
    this.gRim.selectAll('*').remove();
    this.gRim.append('circle').attr('class', 'horizon').attr('cx', g.cx).attr('cy', g.cy).attr('r', g.R);
    for (var t = 0; t < 360; t += 5) {
      var len = t % 45 === 0 ? 7 : (t % 15 === 0 ? 4 : 2), a = t * RAD;
      this.gRim.append('line').attr('class', 'rim-tick')
        .attr('x1', g.cx - g.R * Math.sin(a)).attr('y1', g.cy - g.R * Math.cos(a))
        .attr('x2', g.cx - (g.R - len) * Math.sin(a)).attr('y2', g.cy - (g.R - len) * Math.cos(a));
    }
    var pts = small ? [['N', 0], ['E', 90], ['S', 180], ['W', 270]]
      : [['N', 0], ['NE', 45], ['E', 90], ['SE', 135], ['S', 180], ['SW', 225], ['W', 270], ['NW', 315]];
    pts.forEach(function(c){
      var r = g.R + (small ? 11 : 14), a = c[1] * RAD;
      self.gRim.append('text').attr('class', 'compass' + (c[0] === 'N' ? ' compass-n' : '') + (c[0].length > 1 ? ' compass-minor' : ''))
        .attr('x', g.cx - r * Math.sin(a)).attr('y', g.cy - r * Math.cos(a))
        .attr('text-anchor', 'middle').attr('dominant-baseline', 'central').text(c[0]);
    });
    this.proj = {};
  };

  // Stereographic projection centred on the zenith; null well below the
  // horizon, where the projection runs away to infinity.
  SkyChart.prototype.project = function(alt, az){
    if (alt < -30) return null;
    var g = this.geom, r = g.R * Math.tan((90 - alt) * RAD / 2);
    return [g.cx - r * Math.sin(az * RAD), g.cy - r * Math.cos(az * RAD)];
  };

  SkyChart.prototype.starRadius = function(mag){
    return Math.max(0.55, (2.75 - 0.4 * mag) * (this.geom.small ? 0.82 : 1));
  };

  // Plate colour, star dimming, and (when the sky has turned enough) the
  // stars, figures and ecliptic.
  SkyChart.prototype.drawSky = function(lst, lat, sunAlt, unixForPrecession){
    var dark = isDark();
    this.gPlate.select('.dome-plate').attr('fill', (dark ? plateDark : plateLight)(sunAlt));
    this.gStars.attr('opacity', (dark ? starOpDark : starOpLight)(sunAlt));
    this.lst = lst; this.lat = lat;
    var p = this.proj;
    var turned = p.lst == null ? Infinity : Math.abs(((lst - p.lst + 540) % 360) - 180);
    if (!(turned >= REPROJECT_DEG || p.lat !== lat || p.W !== this.geom.W || p.dark !== dark)) return;
    precessTo(unixForPrecession);
    var self = this, bins = {};
    var magLimit = this.geom.small ? 4.8 : 5.5;     // a phone-sized chart gets crowded below this
    sky.stars.forEach(function(st, i){
      var h = altaz(st.ra, st.dec, lst, lat);
      self.starAlt[i] = h[0]; self.starAz[i] = h[1];
      var xy = (h[0] > -1 && st.mag <= magLimit) ? self.project(h[0], h[1]) : null;
      self.starXY[i] = xy;
      if (!xy) return;
      var r = Math.round(self.starRadius(st.mag) * 10) / 10;
      var col = starColour(st.bv, dark) || 'ink';
      var key = col + '|' + r;
      var bin = bins[key] || (bins[key] = { col: col, r: r, d: [] });
      bin.d.push('M' + (xy[0] - r).toFixed(1) + ',' + xy[1].toFixed(1)
        + 'a' + r + ',' + r + ' 0 1,0 ' + (2 * r) + ',0a' + r + ',' + r + ' 0 1,0 ' + (-2 * r) + ',0');
    });
    var data = Object.keys(bins).map(function(k){ return bins[k]; });
    var sel = this.gStars.selectAll('path').data(data, function(b){ return b.col + '|' + b.r; });
    sel.exit().remove();
    sel.enter().append('path').merge(sel)
      .attr('class', function(b){ return b.col === 'ink' ? 'star-ink' : null; })
      .attr('fill', function(b){ return b.col === 'ink' ? null : b.col; })
      .attr('d', function(b){ return b.d.join(''); });

    var dd = [];
    sky.lines.forEach(function(l){
      l.segsDate.forEach(function(seg){
        var started = false;
        seg.forEach(function(q){
          var h = altaz(q[0], q[1], lst, lat), xy = self.project(h[0], h[1]);
          if (!xy) { started = false; return; }
          dd.push((started ? 'L' : 'M') + xy[0].toFixed(1) + ',' + xy[1].toFixed(1));
          started = true;
        });
      });
    });
    var lp = this.gLines.selectAll('path').data([dd.join('')]);
    lp.enter().append('path').merge(lp).attr('d', function(x){ return x; });

    var s = C.latest();
    var eps = (s && C.num(s.d['almanac.ecliptic_obliquity'])) || 23.4393;
    var e = [], on = false;
    for (var lam = 0; lam <= 360; lam += 2) {
      var L = lam * RAD, E = eps * RAD;
      var ra = norm360(Math.atan2(Math.sin(L) * Math.cos(E), Math.cos(L)) / RAD);
      var dec = Math.asin(Math.sin(E) * Math.sin(L)) / RAD;
      var h = altaz(ra, dec, lst, lat), xy = this.project(h[0], h[1]);
      if (!xy) { on = false; continue; }
      e.push((on ? 'L' : 'M') + xy[0].toFixed(1) + ',' + xy[1].toFixed(1));
      on = true;
    }
    var ep = this.gEcl.selectAll('path').data([e.join('')]);
    ep.enter().append('path').merge(ep).attr('d', function(x){ return x; });

    this.labelXY = sky.labels.map(function(l){
      var h = altaz(l.ra, l.dec, lst, lat);
      return h[0] > 6 ? self.project(h[0], h[1]) : null;
    });
    this.proj = { lst: lst, lat: lat, W: this.geom.W, dark: dark };
  };

  // Marks, then labels (marks first, then bright-star names, then
  // constellation names -- later ones that would collide are dropped),
  // then hover targets. Each mark: { key, kind, alt, az, name, colour,
  // r, lit, faint, moonK, moonRot, tip, label (default name), labelCls }.
  SkyChart.prototype.drawMarks = function(marks){
    var self = this, g = this.geom, small = g.small;
    marks.forEach(function(m){
      var xy = self.project(m.alt, m.az);
      m.x = xy ? xy[0] : null; m.y = xy ? xy[1] : null;
    });
    marks = marks.filter(function(m){ return m.x != null; });
    var boxes = [];
    function clear(b){
      for (var i = 0; i < boxes.length; i++) {
        var p = boxes[i];
        if (b[0] < p[2] && b[2] > p[0] && b[1] < p[3] && b[3] > p[1]) return false;
      }
      var cx = (b[0] + b[2]) / 2 - g.cx, cy = (b[1] + b[3]) / 2 - g.cy;
      return Math.sqrt(cx * cx + cy * cy) < g.R - 6;
    }
    function tryAt(x, y, w, h){ var b = [x, y, x + w, y + h]; if (clear(b)) { boxes.push(b); return true; } return false; }
    marks.forEach(function(m){ var r = (m.r || 4) + 2; boxes.push([m.x - r, m.y - r, m.x + r, m.y + r]); });

    var sel = this.gMarks.selectAll('g.mark-g').data(marks, function(m){ return m.key; });
    sel.exit().remove();
    var ent = sel.enter().append('g').attr('class', function(m){ return 'mark-g mark-' + m.kind; });
    ent.each(function(m){
      var gg = d3.select(this);
      if (m.kind === 'sun') { gg.append('circle').attr('class', 'sun-glow'); gg.append('circle').attr('class', 'mark'); }
      else if (m.kind === 'moon') { gg.append('circle').attr('class', 'moon-dark'); gg.append('path').attr('class', 'moon-lit'); gg.append('circle').attr('class', 'moon-edge'); }
      else if (m.kind === 'comet') gg.append('path').attr('class', 'mark comet-mark');
      else if (m.kind === 'tick') gg.append('circle').attr('class', 'tick-mark');
      else gg.append('circle').attr('class', 'mark');
    });
    ent.merge(sel).each(function(m){
      var gg = d3.select(this);
      gg.classed('in-shadow', m.kind === 'sat' && !m.lit).classed('faint', !!m.faint).classed('dim', !!m.dim);
      gg.style('--mark-col', m.colour);
      gg.selectAll('circle').attr('cx', m.x).attr('cy', m.y);
      gg.select('.mark').attr('r', m.r).attr('fill', m.colour);
      gg.select('.sun-glow').attr('r', m.r * 2).attr('fill', m.colour);
      gg.select('.moon-dark').attr('r', m.r);
      gg.select('.moon-edge').attr('r', m.r);
      gg.select('.tick-mark').attr('r', m.r);
      gg.select('.moon-lit').attr('d', C.moonLitPath(m.r, m.moonK || 0))
        .attr('transform', 'translate(' + m.x + ',' + m.y + ') rotate(' + (m.moonRot || 0).toFixed(1) + ')');
      var q = m.r + 0.5;
      gg.select('.comet-mark').attr('d', 'M' + m.x + ',' + (m.y - q) + 'l' + q + ',' + q + 'l' + (-q) + ',' + q + 'l' + (-q) + ',' + (-q) + 'z')
        .attr('fill', m.colour).attr('stroke', m.colour);
    });

    var labels = [];
    var px = small ? 10 : 11;
    marks.forEach(function(m){
      var text = m.label != null ? m.label : m.name;
      if (!text) return;
      var w = textWidth(text, px, 600), h = px + 2, gap = (m.r || 4) + 4;
      var tries = [[m.x + gap, m.y - h / 2], [m.x - gap - w, m.y - h / 2], [m.x - w / 2, m.y - gap - h], [m.x - w / 2, m.y + gap]];
      for (var i = 0; i < tries.length; i++) {
        if (tryAt(tries[i][0], tries[i][1], w, h)) { labels.push({ cls: m.labelCls || 'lbl-body', text: text, x: tries[i][0], y: tries[i][1] + h - 3 }); break; }
      }
    });
    var magCut = small ? 1.0 : 1.6;
    sky.stars.forEach(function(st, i){
      var xy = self.starXY[i];
      if (!st.name || st.mag > magCut || !xy || self.starAlt[i] < 4) return;
      var spx = px - 1, w = textWidth(st.name, spx, 400), h = spx + 2, gap = self.starRadius(st.mag) + 3;
      var tries = [[xy[0] + gap, xy[1] - h / 2], [xy[0] - gap - w, xy[1] - h / 2]];
      for (var k = 0; k < tries.length; k++) {
        if (tryAt(tries[k][0], tries[k][1], w, h)) { labels.push({ cls: 'lbl-star', text: st.name, x: tries[k][0], y: tries[k][1] + h - 3 }); break; }
      }
    });
    var rankCut = small ? 1 : 2;
    sky.labels.map(function(l, i){ return { l: l, xy: self.labelXY[i] }; })
      .sort(function(a, b){ return a.l.rank - b.l.rank; })
      .forEach(function(o){
        if (!o.xy || o.l.rank > rankCut) return;
        var cpx = small ? 8.5 : 9.5, txt = o.l.name.toUpperCase();
        var w = textWidth(txt, cpx, 500) * 1.12, h = cpx + 2;
        var x = o.xy[0] - w / 2, y = o.xy[1] - h / 2;
        if (tryAt(x, y, w, h)) labels.push({ cls: 'lbl-con', text: txt, x: o.xy[0], y: y + h - 2.5, mid: true });
      });
    var ls = this.gLabels.selectAll('text').data(labels);
    ls.exit().remove();
    ls.enter().append('text').merge(ls)
      .attr('class', function(l){ return l.cls; })
      .attr('x', function(l){ return l.x; }).attr('y', function(l){ return l.y; })
      .attr('text-anchor', function(l){ return l.mid ? 'middle' : 'start'; })
      .text(function(l){ return l.text; });

    // Hover targets: marks first, then named or bright stars.
    var items = marks.filter(function(m){ return m.tip; }).map(function(m){ return { x: m.x, y: m.y, r: m.r, tip: m.tip }; });
    sky.stars.forEach(function(st, i){
      var xy = self.starXY[i];
      if (!xy || self.starAlt[i] <= 0 || !(st.name || st.mag <= 3.5)) return;
      items.push({ x: xy[0], y: xy[1], r: 4, star: st, i: i });
    });
    this.hover.items = items;
    this.hover.delaunay = items.length ? d3.Delaunay.from(items, function(i){ return i.x; }, function(i){ return i.y; }) : null;
    if (this.focus >= 0) this.showTip(this.focus);
  };

  SkyChart.prototype._hoverAt = function(ev, radius, toggle){
    if (!this.hover.delaunay) return;
    var rect = this.svg.node().getBoundingClientRect();
    var k = this.geom.W / (rect.width || this.geom.W);
    var x = (ev.clientX - rect.left) * k, y = (ev.clientY - rect.top) * k;
    var i = this.hover.delaunay.find(x, y), it = this.hover.items[i];
    var dx = it.x - x, dy = it.y - y;
    if (dx * dx + dy * dy > radius * radius || (toggle && i === this.focus)) { this.hideTip(); return; }
    if (i !== this.focus) { this.focus = i; this.showTip(i); }
  };
  SkyChart.prototype.showTip = function(i){
    var it = this.hover.items[i];
    if (!it) { this.hideTip(); return; }
    var html;
    if (it.star) {
      var st = it.star, alt = this.starAlt[it.i], az = this.starAz[it.i];
      var con = constellationOfDate(st.ra, st.dec);
      html = '<b>' + esc(st.name || 'Star') + '</b>' + (con ? ' · ' + esc(con) : '')
        + '<br><span class="k">Magnitude</span> ' + C.nf1.format(st.mag)
        + '<br><span class="k">Bearing</span> ' + C.nf1.format(az) + '° ' + C.compass(az)
        + '<br><span class="k">Altitude</span> ' + C.fmtSigned(alt, C.nf1) + '°';
    } else {
      html = typeof it.tip === 'function' ? it.tip() : it.tip;
    }
    this.tip.innerHTML = html;
    var k = (this.svg.node().getBoundingClientRect().width || this.geom.W) / this.geom.W;
    this.tip.style.left = (it.x * k) + 'px';
    this.tip.style.top = (it.y * k) + 'px';
    this.tip.hidden = false;
    this.gFocus.selectAll('*').remove();
    this.gFocus.append('circle').attr('cx', it.x).attr('cy', it.y).attr('r', (it.r || 4) + 4);
  };
  SkyChart.prototype.hideTip = function(){
    this.focus = -1; this.tip.hidden = true; this.gFocus.selectAll('*').remove();
  };
  SkyChart.prototype.whereTip = function(name, alt, az, extra){
    var rd = radec(alt, az, this.lst, this.lat);
    var con = constellationOfDate(rd[0], rd[1]);
    return '<b>' + esc(name) + '</b>' + (con ? ' · in ' + esc(con) : '') + (extra || '')
      + '<br><span class="k">Bearing</span> ' + C.nf1.format(az) + '° ' + C.compass(az)
      + '<br><span class="k">Altitude</span> ' + C.fmtSigned(alt, C.nf1) + '°';
  };

  // A pass's arc: visible stretch bold, the rest (low, daylit or in Earth's
  // shadow) faint; plus an arrowhead showing the direction of travel.
  SkyChart.prototype.drawArc = function(pass){
    this.gArc.selectAll('*').remove();
    if (!pass || !pass.track) return;
    var self = this, segs = [], cur = null;
    pass.track.forEach(function(q){
      var t = pass.rise + q[0];
      var seen = pass.visible && q[3] && t >= pass.visible_start - 0.5 && t <= pass.visible_end + 0.5;
      var xy = self.project(q[1], q[2]);
      if (!xy) { cur = null; return; }
      if (!cur || cur.seen !== seen) {
        var prev = cur && cur.pts[cur.pts.length - 1];
        cur = { seen: seen, pts: prev ? [prev] : [] };
        segs.push(cur);
      }
      cur.pts.push(xy);
    });
    var line = d3.line();
    segs.forEach(function(s){
      self.gArc.append('path').attr('class', s.seen ? 'arc-seen' : 'arc-unseen').attr('d', line(s.pts));
    });
    var vis = segs.filter(function(s){ return s.seen; });
    var last = vis.length ? vis[vis.length - 1].pts : null;
    if (last && last.length > 1) {
      var a = last[last.length - 2], b = last[last.length - 1];
      var ang = Math.atan2(b[1] - a[1], b[0] - a[0]) * 180 / Math.PI;
      this.gArc.append('path').attr('class', 'arc-arrow')
        .attr('d', 'M0,0L-9,-4.5L-9,4.5Z').attr('transform', 'translate(' + b[0] + ',' + b[1] + ') rotate(' + ang + ')');
    }
  };

  // ------------------------------------------------------------------
  // The Sky Overhead
  // ------------------------------------------------------------------
  var dome = document.getElementById('celDome') ? new SkyChart('celDome', 'celDomeWrap', 'celDomeTip') : null;

  function satMark(s, pos, sunAlt){
    return {
      key: 'sat-' + s.tag, kind: 'sat', name: s.label, alt: pos.alt, az: pos.az,
      colour: C.bodyColor(s.tag), r: dome.geom.small ? 3.5 : 4.2, lit: pos.lit,
      dim: sunAlt > -6, labelCls: 'lbl-sat',
      tip: function(){
        return dome.whereTip(s.label, pos.alt, pos.az,
          ' · ' + (pos.lit ? 'sunlit' : 'in Earth’s shadow'));
      }
    };
  }
  // A satellite's position now: along its pass track while a pass is on
  // (the feed's own az/alt is only every few seconds -- far too coarse for
  // something crossing the sky in minutes).
  function satNow(s, now){
    var np = C.passOf(s, 'next_pass');
    var vp = C.passOf(s, 'next_visible_pass');
    return C.trackAt(np, now) || C.trackAt(vp, now);
  }

  function renderDome(now){
    if (!dome) return;
    if (!dome.geom) dome.layout();
    var s = C.latest();
    if (!s || !sky) return;
    var lat = stationLatitude(), lst = currentLst(now);
    if (lat == null || lst == null) {
      d3.selectAll('#celUpNow, [data-dome-note]').html('<p class="cel-note">The sky charts need SkyfieldLoopData 0.2.0 or later in divumwx.py (it reports the station latitude).</p>');
      return;
    }
    var d = s.d;
    var sun = C.bodyAt('sun', now), sunAlt = sun ? sun.alt : -90;
    dome.drawSky(lst, lat, sunAlt, now);

    var marks = [];
    DOME_BODIES.forEach(function(key){
      var p = C.bodyAt(key, now);
      if (!p || p.alt < -0.5) return;
      var small = dome.geom.small;
      marks.push({ key: key, kind: key === 'sun' || key === 'moon' ? key : 'planet', name: NAMES[key],
                   alt: p.alt, az: p.az, colour: C.bodyColor(key),
                   r: key === 'sun' || key === 'moon' ? (small ? 6.5 : 8) : (key === 'venus' || key === 'jupiter' ? (small ? 4 : 5) : (small ? 3.2 : 4)),
                   tip: (function(name, alt, az){ return function(){ return dome.whereTip(name, alt, az); }; })(NAMES[key], p.alt, p.az) });
    });
    // Moon: lit limb turned towards the Sun along the sky.
    var moon = marks.filter(function(m){ return m.key === 'moon'; })[0];
    if (moon) {
      moon.moonK = (C.num(d['almanac.moon.phase']) || 0) / 100;
      var mxy = dome.project(moon.alt, moon.az);
      if (sun && mxy) {
        var m = vec(moon.alt, moon.az), sv = vec(sun.alt, sun.az);
        var dot = m[0] * sv[0] + m[1] * sv[1] + m[2] * sv[2];
        var t = [sv[0] - dot * m[0], sv[1] - dot * m[1], sv[2] - dot * m[2]];
        var tl = Math.sqrt(t[0] * t[0] + t[1] * t[1] + t[2] * t[2]) || 1;
        var q = [m[0] + 0.01 * t[0] / tl, m[1] + 0.01 * t[1] / tl, m[2] + 0.01 * t[2] / tl];
        var ql = Math.sqrt(q[0] * q[0] + q[1] * q[1] + q[2] * q[2]);
        var qxy = dome.project(Math.asin(q[2] / ql) / RAD, norm360(Math.atan2(q[0], q[1]) / RAD));
        if (qxy) moon.moonRot = Math.atan2(qxy[1] - mxy[1], qxy[0] - mxy[0]) / RAD;
      }
    }
    C.cometsNow().forEach(function(c){
      if (c.missing) return;
      var p = C.bodyAt(c.key, now);
      if (!p || p.alt < -0.5) return;
      var mag = C.num(c.info.mag);
      marks.push({ key: 'comet-' + c.key, kind: 'comet', name: c.name, alt: p.alt, az: p.az,
                   colour: C.bodyColor(c.key), r: dome.geom.small ? 3.5 : 4.2, faint: !(mag != null && mag <= 6),
                   tip: (function(alt, az){ return function(){
                     return dome.whereTip(c.name, alt, az) + '<br><span class="k">Magnitude</span> ' + (mag != null ? C.nf1.format(mag) : '—'); }; })(p.alt, p.az) });
    });
    var satsUp = [];
    C.satellitesNow().forEach(function(st){
      var pos = satNow(st, now);
      if (!pos || pos.alt < 0) return;
      satsUp.push({ s: st, pos: pos });
      marks.push(satMark(st, pos, sunAlt));
    });
    // The rest of any pass in progress, faintly, so you can see where it's heading.
    dome.gArc.selectAll('*').remove();
    satsUp.forEach(function(o){
      var pass = C.trackAt(C.passOf(o.s, 'next_pass'), now) ? C.passOf(o.s, 'next_pass') : C.passOf(o.s, 'next_visible_pass');
      var pts = pass.track.filter(function(q){ return pass.rise + q[0] >= now; })
        .map(function(q){ return dome.project(q[1], q[2]); }).filter(Boolean);
      var here = dome.project(o.pos.alt, o.pos.az);
      if (here) pts.unshift(here);
      if (pts.length > 1) dome.gArc.append('path').attr('class', 'arc-ahead').attr('stroke', C.bodyColor(o.s.tag)).attr('d', d3.line()(pts));
    });
    dome.drawMarks(marks);
    renderUpNow(now, lst, lat, sunAlt, satsUp);
  }

  function renderUpNow(now, lst, lat, sunAlt, satsUp){
    if (!document.getElementById('celUpNow')) return;
    var zen = sky.unprecess ? sky.unprecess(lst, lat) : [lst, lat];
    var zenId = constellationAt(zen[0], zen[1]);
    var state = skyState(sunAlt);
    var html = '<div class="up-state" data-state="' + state.split(' ')[0].toLowerCase() + '">'
      + '<span class="up-state-name">' + state + '</span>'
      + '<span class="up-state-sub">Sun at ' + C.fmtSigned(sunAlt, C.nf1) + '°'
      + (zenId ? ' · ' + esc(conNames[zenId] || zenId) + ' overhead' : '') + '</span></div>';

    var darkEnough = sunAlt < -6;
    var rows = [];
    function row(colour, name, sub, az, alt, seen, cls){
      return '<li' + (cls ? ' class="' + cls + '"' : '') + '><span class="sw" style="--sw:' + colour + '"></span>'
        + '<span class="up-name">' + esc(name) + (sub ? '<span class="up-sub">' + esc(sub) + '</span>' : '') + '</span>'
        + '<span class="up-where">' + C.compass(az) + ' · ' + Math.round(alt) + '°</span>'
        + '<span class="up-seen" data-seen="' + seen.toLowerCase().replace(/ /g, '-') + '">' + seen + '</span></li>';
    }
    satsUp.forEach(function(o){
      var seen = !o.pos.lit ? 'In shadow' : darkEnough && o.pos.alt >= 10 ? 'Naked eye' : 'Daylight';
      rows.push(row(C.bodyColor(o.s.tag), o.s.label, 'Satellite crossing now', o.pos.az, o.pos.alt, seen, 'up-sat'));
    });
    ['moon', 'mercury', 'venus', 'mars', 'jupiter', 'saturn', 'uranus', 'neptune'].forEach(function(key){
      var p = C.bodyAt(key, now);
      if (!p || p.alt <= 0) return;
      var rd = radec(p.alt, p.az, lst, lat);
      var con = constellationOfDate(rd[0], rd[1]);
      var seen;
      if (p.alt < 8) seen = 'Low';
      else if (!darkEnough) seen = (key === 'moon' || key === 'venus') ? 'Naked eye' : 'Daylight';
      else seen = SEEN_WITH[key] || 'Naked eye';
      rows.push(row(C.bodyColor(key), NAMES[key], con ? 'in ' + con : '', p.az, p.alt, seen));
    });
    C.cometsNow().forEach(function(c){
      var p = !c.missing && C.bodyAt(c.key, now);
      if (!p || p.alt <= 0) return;
      var mag = C.num(c.info.mag);
      var seen = mag == null ? '' : mag <= 6 && darkEnough ? 'Naked eye' : mag <= 9 ? 'Binoculars' : mag <= 13 ? 'Telescope' : 'Too faint';
      var rd = radec(p.alt, p.az, lst, lat);
      var con = constellationOfDate(rd[0], rd[1]);
      rows.push(row(C.bodyColor(c.key), c.name, 'Comet' + (con ? ' in ' + con : '') + (mag != null ? ', mag ' + C.nf1.format(mag) : ''), p.az, p.alt, seen));
    });
    html += '<h3 class="up-h">Moon, planets and comets up</h3>'
      + (rows.length ? '<ul class="up-list">' + rows.join('') + '</ul>' : '<p class="up-none">None above the horizon right now.</p>');

    var stars = [];
    sky.stars.forEach(function(st, i){ if (st.name && dome.starAlt[i] > 10 && stars.length < 6) stars.push(i); });
    html += '<h3 class="up-h">Brightest stars up</h3>';
    if (stars.length) {
      html += '<ul class="up-list">' + stars.map(function(i){
        var st = sky.stars[i], con = constellationOfDate(st.ra, st.dec);
        return '<li><span class="sw sw-star"></span><span class="up-name">' + esc(st.name)
          + (con ? '<span class="up-sub">' + esc(con) + '</span>' : '') + '</span>'
          + '<span class="up-where">' + C.compass(dome.starAz[i]) + ' · ' + Math.round(dome.starAlt[i]) + '°</span>'
          + '<span class="up-seen">mag ' + C.nf1.format(st.mag) + '</span></li>';
      }).join('') + '</ul>';
    } else {
      html += '<p class="up-none">None clear of the horizon.</p>';
    }
    var el = document.getElementById('celUpNow');
    if (el && el.__html !== html) { el.innerHTML = html; el.__html = html; }
  }

  // ------------------------------------------------------------------
  // The Next Visible Pass
  // ------------------------------------------------------------------
  var passChart = document.getElementById('celPass') ? new SkyChart('celPass', 'celPassWrap', 'celPassTip') : null;
  var chosenTag = null;            // the satellite whose pass is charted; null = soonest
  var chartedKey = null;

  function chosenPass(now){
    if (chosenTag) {
      var s = C.satellitesNow().filter(function(x){ return x.tag === chosenTag; })[0];
      var vp = s && C.passOf(s, 'next_visible_pass');
      if (vp && vp.visible_end > now) return { sat: s, pass: vp };
      chosenTag = null;
    }
    return C.soonestVisiblePass(now);
  }
  // Why a satellite has no elements, from the station's download status.
  // short: for a table cell ("Download failed: refused (HTTP 403)").
  function satStatusText(sat, now, short){
    var st = sat.status;
    if (!st || st.state === 'pending') return 'Downloading from CelesTrak…';
    if (st.state === 'error') {
      var err = st.error || 'unknown error';
      if (short) err = err.split(' — ')[0];
      return (short ? 'Download failed: ' : 'CelesTrak download failed: ') + err
        + (C.num(st.retry) ? (short ? '; retry ' + C.fmtTime(st.retry) : '. Next try ' + C.fmtWhen(st.retry, now)) : '');
    }
    return 'Loading…';
  }
  function fmtDuration(sec){
    sec = Math.max(0, Math.round(sec));
    var m = Math.floor(sec / 60), s = sec % 60;
    return m ? m + ' min' + (s ? ' ' + s + ' s' : '') : s + ' s';
  }

  function renderPass(now){
    if (!passChart) return;
    if (!passChart.geom) passChart.layout();
    var s = C.latest();
    if (!s || !sky) return;
    var lat = stationLatitude(), lstNow = currentLst(now);
    var info = document.getElementById('celPassInfo');
    var sats = C.satellitesNow();
    renderSatTable(now, sats);
    if (lat == null || lstNow == null) return;
    if (!sats.length) {
      setHtml(info, '<p class="cel-note">No satellites are configured. Add them under [SkyfieldLoopData] [[Satellites]] in weewx.conf (DivumWX 1.6 or later).</p>');
      passChart.svg.attr('opacity', 0.35);
      return;
    }
    var vis = chosenPass(now);
    if (!vis) {
      var why;
      if (sats.every(function(x){ return x.missing; })) {
        if (s.d['celestial.orbits.running'] === false) {
          why = 'The satellite updater on the station isn’t running. Check the WeeWX log for SkyfieldLoopData errors.';
        } else {
          // One reason, once, when every satellite shares it.
          var reasons = sats.map(function(x){ return satStatusText(x, now); });
          var same = reasons.every(function(r){ return r === reasons[0]; });
          why = same ? 'No satellite orbits yet. ' + esc(reasons[0]) + '.'
            : 'No satellite orbits yet:<ul class="sat-why">' + sats.map(function(x, i){
                return '<li><b>' + esc(x.label) + '</b> — ' + esc(reasons[i]) + '</li>';
              }).join('') + '</ul>';
        }
      } else {
        why = 'No visible pass found for any satellite in the next few days.';
      }
      setHtml(info, '<div class="cel-note">' + why + '</div>');
      passChart.svg.attr('opacity', 0.35);
      passChart.gArc.selectAll('*').remove();
      passChart.gMarks.selectAll('*').remove();
      return;
    }
    passChart.svg.attr('opacity', null);
    var sat = vis.sat, vp = vis.pass;
    // The sky at culmination: sidereal time runs at a fixed rate, so it can
    // be carried forward from now.
    var lstC = norm360(lstNow + SIDEREAL_DEG_PER_S * (vp.culmination - now));
    var key = sat.tag + '|' + vp.rise + '|' + passChart.geom.W + '|' + isDark();
    if (key !== chartedKey) { passChart.proj = {}; chartedKey = key; }
    passChart.drawSky(passChart.proj.lst == null ? lstC : passChart.proj.lst, lat, vp.sun_altitude, vp.culmination);
    passChart.drawArc(vp);

    var colour = C.bodyColor(sat.tag);
    passChart.svg.style('--pass-col', colour);
    var marks = [];
    var a = C.trackAt(vp, vp.visible_start), b = C.trackAt(vp, vp.visible_end);
    var culm = C.trackAt(vp, vp.culmination);
    var r = passChart.geom.small ? 3 : 3.5;
    if (a) marks.push({ key: 'p-start', kind: 'tick', alt: a.alt, az: a.az, r: r, colour: colour, label: C.fmtTime(vp.visible_start), labelCls: 'lbl-tick' });
    if (culm && vp.culmination > vp.visible_start + 20 && vp.culmination < vp.visible_end - 20)
      marks.push({ key: 'p-culm', kind: 'tick', alt: culm.alt, az: culm.az, r: r, colour: colour, label: C.fmtTime(vp.culmination) + ' · ' + Math.round(vp.max_altitude) + '°', labelCls: 'lbl-tick' });
    if (b) marks.push({ key: 'p-end', kind: 'tick', alt: b.alt, az: b.az, r: r, colour: colour, label: C.fmtTime(vp.visible_end), labelCls: 'lbl-tick' });
    var live = C.trackAt(vp, now);
    if (live && live.alt > 0) {
      marks.unshift({ key: 'p-live', kind: 'sat', name: sat.label, alt: live.alt, az: live.az, colour: colour,
                      r: passChart.geom.small ? 4 : 5, lit: live.lit, labelCls: 'lbl-sat',
                      tip: passChart.whereTip(sat.label, live.alt, live.az, ' · ' + (live.lit ? 'sunlit' : 'in Earth’s shadow')) });
    }
    passChart.drawMarks(marks);

    // The words beside the chart.
    var onNow = vp.visible_start <= now && now <= vp.visible_end;
    var endsInShadow = vp.track.length && (function(){
      var after = C.trackAt(vp, vp.visible_end + 15);
      return after && !after.lit && after.alt > 0;
    })();
    var startsFromShadow = (function(){
      var before = C.trackAt(vp, vp.visible_start - 15);
      return before && !before.lit && before.alt > 0;
    })();
    var html = '<div class="pass-head"><span class="sw" style="--sw:' + colour + '"></span>'
      + '<span class="pass-name">' + esc(sat.label) + '</span>'
      + '<span class="pass-date">' + esc(C.fmtWhen(vp.visible_start, now)) + '</span></div>'
      + '<div class="pass-count' + (onNow ? ' is-now' : '') + '">'
      + (onNow ? 'Visible now · ' + C.fmtCountdown(vp.visible_end - now) + ' left' : 'In ' + C.fmtCountdown(vp.visible_start - now)) + '</div>'
      + '<ol class="pass-steps">'
      + (a ? '<li><b>' + C.fmtTime(vp.visible_start) + '</b> ' + (startsFromShadow ? 'emerges from Earth’s shadow' : 'appears') + ' in the ' + C.compass(a.az) + ', ' + Math.round(a.alt) + '° up</li>' : '')
      + (culm ? '<li><b>' + C.fmtTime(vp.culmination) + '</b> highest, ' + Math.round(vp.max_altitude) + '° in the ' + C.compass(vp.culmination_azimuth) + '</li>' : '')
      + (b ? '<li><b>' + C.fmtTime(vp.visible_end) + '</b> ' + (endsInShadow ? 'fades into Earth’s shadow' : 'drops out of sight') + ' in the ' + C.compass(b.az) + ', ' + Math.round(b.alt) + '° up</li>' : '')
      + '</ol>'
      + '<p class="pass-meta">Visible for ' + fmtDuration(vp.visible_end - vp.visible_start)
      + ' · above the horizon ' + C.fmtTime(vp.rise) + '–' + C.fmtTime(vp.set) + '</p>';
    setHtml(info, html);
  }

  function renderSatTable(now, sats){
    var el = document.getElementById('celSatTable');
    if (!el) return;
    if (!sats.length) { setHtml(el, ''); return; }
    var current = chosenPass(now);
    var rows = sats.map(function(s){
      var np = C.passOf(s, 'next_pass'), vp = C.passOf(s, 'next_visible_pass');
      var cell = function(p, visibleOnly){
        if (!p) {
          if (s.missing) return visibleOnly ? '' : '<span class="dim">' + esc(satStatusText(s, now, true)) + '</span>';
          return '<span class="dim">' + (visibleOnly ? 'None in the next few days' : '—') + '</span>';
        }
        var t = visibleOnly ? p.visible_start : p.rise;
        var on = visibleOnly ? (p.visible_start <= now && now <= p.visible_end) : (p.rise <= now && now <= p.set);
        return '<span class="when' + (on ? ' on' : '') + '">' + (on ? 'Now' : esc(C.fmtWhen(t, now))) + '</span>'
          + '<span class="sub">max ' + Math.round(p.max_altitude) + '°'
          + (visibleOnly ? ' · ' + fmtDuration(p.visible_end - p.visible_start) : (p.visible ? ' · visible' : ' · not visible')) + '</span>';
      };
      var sel = current && current.sat.tag === s.tag;
      return '<tr data-tag="' + esc(s.tag) + '"' + (sel ? ' class="sel"' : '') + (vp ? ' tabindex="0"' : '') + '>'
        + '<td><span class="who"><span class="swatch" style="--sw:' + C.bodyColor(s.tag) + '"></span>' + esc(s.label) + '</span>'
        + '<span class="state">NORAD ' + s.norad + (s.status && s.status.source ? ' · ' + esc(s.status.source) : '') + '</span></td>'
        + (s.missing ? '<td colspan="2">' + cell(null, false) + '</td></tr>'
                     : '<td>' + cell(np, false) + '</td><td>' + cell(vp, true) + '</td></tr>');
    });
    setHtml(el, '<table class="cel-roster cel-sats"><caption class="cel-visually-hidden">Next passes of each satellite</caption>'
      + '<thead><tr><th scope="col">Satellite</th><th scope="col">Next pass</th><th scope="col">Next visible pass</th></tr></thead>'
      + '<tbody>' + rows.join('') + '</tbody></table>'
      + '<p class="cel-note">Pick a satellite to chart its next visible pass. A pass is visible when the satellite is sunlit, at least 10° up, and the sky is darker than civil twilight.</p>');
  }
  function setHtml(el, html){ if (el && el.__html !== html) { el.innerHTML = html; el.__html = html; } }

  var satTable = document.getElementById('celSatTable');
  if (satTable) {
    var pick = function(ev){
      var tr = ev.target.closest('tr[data-tag]');
      if (!tr || !tr.hasAttribute('tabindex')) return;
      chosenTag = tr.getAttribute('data-tag');
      chartedKey = null;
      renderPass(C.now());
    };
    satTable.addEventListener('click', pick);
    satTable.addEventListener('keydown', function(ev){ if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); pick(ev); } });
  }

  // ------------------------------------------------------------------
  // Wiring
  // ------------------------------------------------------------------
  function renderAll(now){ renderDome(now); renderPass(now); }
  C.onTick(renderAll);
  skyReady.push(function(){ renderAll(C.now()); });
  [[dome, 'celDomeWrap'], [passChart, 'celPassWrap']].forEach(function(pair){
    var chart = pair[0];
    if (!chart) return;
    if (window.ResizeObserver) {
      var lastW = 0;
      new ResizeObserver(function(){
        var w = chart.wrap.clientWidth;
        if (Math.abs(w - lastW) > 2) { lastW = w; chart.layout(); chartedKey = null; if (sky) renderAll(C.now()); }
      }).observe(chart.wrap);
    } else {
      chart.layout();
    }
  });
  new MutationObserver(function(){ if (dome) dome.proj = {}; chartedKey = null; if (sky) renderAll(C.now()); })
    .observe(document.body, { attributes: true, attributeFilter: ['class'] });
  loadSky();
})();
