/*
##############################################################################################
# skyfield.js version 1.0.1
#  The Skyfield page (D3 v7): the Moon and the coming-up chips, the bodies
#  today beside the sky dome, rise & set ribbons, the Sun's path, the orrery,
#  the solar year, the equation of time, the analemma, the lunar month and
#  the almanac table.
#
#  Reads ../jsondata/sky.json (SkyfieldLoopData's _SkyAtlas, refreshed every
#  15 minutes) for everything about today and the year, and the live feed
#  through ../celestial/celestial.js (window.DivumwxCelestial) for where each
#  body is right now. The sky dome itself is ../celestial/dome.js.
#
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/
(function(){
  'use strict';

  var C = window.DivumwxCelestial;
  if (!C || !window.d3) return;

  var SKY_URL = C.base + '../jsondata/sky.json';
  var POLL_MS = 2 * 60 * 1000;
  var LIVE_MS = 10 * 1000;
  var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  var STATE_NAMES = ['Night', 'Astronomical twilight', 'Nautical twilight', 'Civil twilight', 'Daylight'];
  var PHASE_NAMES = ['New', 'First quarter', 'Full', 'Last quarter'];

  var sky = null;            // the last sky.json
  var skyStamp = null;
  var missing = false;

  // ------------------------------------------------------------------
  // Helpers
  // ------------------------------------------------------------------
  var num = C.num;
  function esc(s){ return String(s).replace(/[&<>"]/g, function(c){ return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function tzOpts(o){ var z = C.tz(); if (z) o.timeZone = z; return o; }
  function fmt(ts, o){
    try { return new Date(ts * 1000).toLocaleString(undefined, tzOpts(o)); }
    catch (e) { delete o.timeZone; return new Date(ts * 1000).toLocaleString(undefined, o); }
  }
  function hm(ts){ return ts == null ? '—' : C.fmtTime(ts); }
  function dayMonth(ts){ return fmt(ts, { day: 'numeric', month: 'short' }); }
  function dayMonthHm(ts){ return fmt(ts, { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit', hour12: false }); }
  function minToHm(m){
    if (m == null) return '—';
    m = ((Math.round(m) % 1440) + 1440) % 1440;
    return (m < 600 ? '0' : '') + Math.floor(m / 60) + ':' + (m % 60 < 10 ? '0' : '') + (m % 60);
  }
  function dur(sec){
    if (sec == null) return '—';
    var m = Math.round(sec / 60);
    return Math.floor(m / 60) + ' h ' + (m % 60) + ' m';
  }
  function minsSigned(m){
    var s = Math.round(Math.abs(m) * 60);
    return (m < 0 ? '−' : '+') + Math.floor(s / 60) + ' m ' + (s % 60) + ' s';
  }
  function daysUntil(ts, now){
    var d = Math.round((dayIndexOf(ts) - dayIndexOf(now)));
    if (d <= 0) return 'today';
    if (d === 1) return 'tomorrow';
    return 'in ' + d + ' days';
  }
  // Calendar-day number in the station's time zone.
  function dayIndexOf(ts){
    var p = {};
    try {
      new Intl.DateTimeFormat('en-GB', tzOpts({ year: 'numeric', month: 'numeric', day: 'numeric' }))
        .formatToParts(new Date(ts * 1000)).forEach(function(x){ p[x.type] = +x.value; });
      return Date.UTC(p.year, p.month - 1, p.day) / 86400000;
    } catch (e) { return Math.floor(ts / 86400); }
  }
  function bodyColor(key){ return C.bodyColor(key); }
  function bodyByKey(key){ return sky && sky.bodies.filter(function(b){ return b.key === key; })[0]; }

  // A responsive chart: an <svg> filling its container, redrawn whenever the
  // container's width changes, plus one tooltip.
  function Chart(id, draw){
    var self = this;
    this.el = document.getElementById(id);
    if (!this.el) { this.dead = true; return; }
    this.svg = d3.select(this.el).append('svg');
    this.tip = d3.select(this.el).append('div').attr('class', 'cel-tip').attr('hidden', true).node();
    this.draw = draw;
    this.W = 0;
    if (window.ResizeObserver) {
      new ResizeObserver(function(){
        var w = self.el.clientWidth;
        if (Math.abs(w - self.W) > 2) self.render();
      }).observe(this.el);
    }
  }
  Chart.prototype.render = function(){
    if (this.dead || !sky) return;
    this.W = Math.max(280, this.el.clientWidth || 600);
    this.svg.selectAll('*').remove();
    this.live = null;
    var H = this.draw(this, this.W, this.W < 560);
    this.svg.attr('viewBox', '0 0 ' + this.W + ' ' + H).attr('width', this.W).attr('height', H);
    this.hideTip();
    if (this.live) this.live(C.now());
  };
  Chart.prototype.showTip = function(html, x, y){
    this.tip.innerHTML = html;
    var k = (this.svg.node().getBoundingClientRect().width || this.W) / this.W;
    this.tip.style.left = Math.max(70, Math.min(this.W - 70, x) * k) + 'px';
    this.tip.style.top = (y * k) + 'px';
    this.tip.hidden = false;
  };
  Chart.prototype.hideTip = function(){ this.tip.hidden = true; };

  // ------------------------------------------------------------------
  // Header line, the Moon, the coming-up chips
  // ------------------------------------------------------------------
  function renderSub(now){
    var el = document.getElementById('skySub');
    if (!el) return;
    var st = sky && sky.station;
    var pos = st ? Math.abs(st.lat).toFixed(2) + '° ' + (st.lat >= 0 ? 'N' : 'S') + ' · '
      + Math.abs(st.lon).toFixed(2) + '° ' + (st.lon >= 0 ? 'E' : 'W') + ' · ' : '';
    el.textContent = pos + fmt(now, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) + ', ' + C.fmtTime(now);
  }

  var lastMoonDraw = 0;
  function renderMoon(now){
    var s = C.latest();
    var box = document.getElementById('skyMoonText');
    if (!s || !box) return;
    var d = s.d;
    var illum = num(d['almanac.moon.phase']);
    var age = num(d['almanac.moon.age_days']);
    var phase = d['moonPhase'] || d['almanac.moon.phase_name'] || '';
    var m = bodyByKey('moon');
    var html = '<div class="phase">' + esc(phase) + '</div>'
      + '<div class="line">' + (illum != null ? Math.round(illum) + '% illuminated' : '') + (age != null ? ' · ' + C.nf1.format(age) + ' days old' : '') + '</div>';
    if (m) {
      var bits = [];
      if (m.rise) bits.push('Rises ' + hm(m.rise));
      if (m.set) bits.push('sets ' + hm(m.set));
      if (m.state === 'always_up') bits.push('Up all day');
      if (m.state === 'never_up') bits.push('Not up today');
      html += '<div class="line">' + bits.join(' · ') + '</div>';
    }
    var mb = C.bodyAt('moon', now);
    if (mb) html += '<div class="line">' + (mb.up ? 'Up now, ' + Math.round(mb.alt) + '° in the ' + C.compass(mb.az) : 'Below the horizon') + '</div>';
    if (box.__html !== html) { box.innerHTML = html; box.__html = html; }
    if (now - lastMoonDraw > 300 && window.DivumWXMoonDisc) {
      var phx = num(d['almanac.moon.ecliptic_angle']), tilt = num(d['almanac.moon.parallactic_angle']);
      if (phx != null) {
        DivumWXMoonDisc.render(document.getElementById('skyMoonDisc'), { phasex: phx, tiltDeg: tilt || 0, size: 96 });
        lastMoonDraw = now;
      }
    }
  }

  function renderChips(now){
    var s = C.latest();
    var el = document.getElementById('skyChips');
    if (!s || !el) return;
    var d = s.d, chips = [];
    function add(kind, label, at, extra){
      at = num(at);
      if (at == null || at < now - 3600) return;
      chips.push({ kind: kind, label: label, at: at, extra: extra || '' });
    }
    add('moon', 'New moon', d['almanac.next_new_moon.unix_epoch.raw']);
    add('moon', 'Full moon', d['almanac.next_full_moon.unix_epoch.raw'],
        d['celestial.supermoon.distance_km'] && Math.abs(num(d['almanac.next_supermoon.unix_epoch.raw']) - num(d['almanac.next_full_moon.unix_epoch.raw'])) < 3600 ? 'supermoon' : '');
    var eq = num(d['almanac.next_equinox.unix_epoch.raw']), so = num(d['almanac.next_solstice.unix_epoch.raw']);
    if (eq != null) add('season', 'Equinox', eq);
    if (so != null) add('season', 'Solstice', so);
    if (d['almanac.next_eclipse_kind']) {
      // "Penumbral Lunar Eclipse" -> "Lunar eclipse", with "penumbral" on the chip's last line.
      var ek = String(d['almanac.next_eclipse_kind']), em = /^(\w+)\s+(lunar|solar)\s+eclipse$/i.exec(ek);
      if (em) add('eclipse', em[2].charAt(0).toUpperCase() + em[2].slice(1).toLowerCase() + ' eclipse', d['almanac.next_eclipse.unix_epoch.raw'], em[1].toLowerCase());
      else add('eclipse', ek, d['almanac.next_eclipse.unix_epoch.raw']);
    }
    var showers = d['celestial.meteor_showers'];
    if (Array.isArray(showers)) {
      var sh = showers.filter(function(x){ return num(x.peak) > now; })[0];
      if (sh) add('meteor', sh.name, sh.peak, num(sh.moon_illumination) != null ? 'Moon ' + Math.round(sh.moon_illumination) + '%' : '');
    }
    C.cometsNow().forEach(function(c){
      var p = num(d['almanac.' + c.key + '.perihelion.unix_epoch.raw']);
      if (p != null && p - now < 365 * 86400) add('comet', c.name + ' perihelion', p);
    });
    chips.sort(function(a, b){ return a.at - b.at; });
    var html = chips.map(function(c){
      var when = daysUntil(c.at, now);
      return '<div class="sky-chip' + (when === 'today' || when === 'tomorrow' ? ' soon' : '') + '" data-kind="' + c.kind + '">'
        + '<div class="k" title="' + esc(c.label) + '">' + esc(c.label) + '</div>'
        + '<div class="v">' + esc(dayMonth(c.at)) + '</div>'
        + '<div class="s">' + when + (c.extra ? ' · ' + esc(c.extra) : '') + '</div></div>';
    }).join('');
    if (el.__html !== html) { el.innerHTML = html; el.__html = html; }
  }

  // ------------------------------------------------------------------
  // Bodies today, beside the dome
  // ------------------------------------------------------------------
  function renderToday(now){
    var el = document.getElementById('skyToday');
    if (!el || !sky) return;
    var sun = bodyByKey('sun');
    var tw = twilightTimes();
    var rows = [];
    if (sun) {
      var main = sun.state === 'always_up' ? 'The Sun is up all day'
        : sun.state === 'never_up' ? 'The Sun stays below the horizon'
        : dur(sun.up_seconds) + ' · ' + hm(sun.rise) + ' → ' + hm(sun.set);
      var sub = [];
      if (tw.civil_dusk) sub.push('civil dusk ' + hm(tw.civil_dusk));
      if (tw.astro_dusk) sub.push('astronomical dark ' + hm(tw.astro_dusk));
      else if (sun.state !== 'never_up') sub.push('no astronomical darkness tonight');
      rows.push('<li class="daylight"><span class="sw" style="--sw:' + bodyColor('sun') + '"></span>'
        + '<span><span class="nm">Daylight</span><span class="main">' + esc(main) + '</span></span>'
        + '<span class="sub">' + esc(sub.join(' · ')) + '</span></li>');
    }
    sky.bodies.forEach(function(b){
      if (b.key === 'sun') return;
      var p = C.bodyAt(b.key, now);
      var main, up = false;
      if (p && p.up) {
        up = true;
        main = 'Up now · ' + Math.round(p.alt) + '° in the ' + C.compass(p.az)
          + (b.set && b.set > now ? ' · sets ' + hm(b.set) : '');
      } else if (b.rise && b.rise > now) {
        main = 'Rises ' + hm(b.rise);
      } else if (b.state === 'never_up') {
        main = 'Not up today';
      } else {
        main = 'Below the horizon' + (b.set && b.set <= now ? ' · set ' + hm(b.set) : '');
      }
      var sub = [];
      if (b.constellation) sub.push('in ' + b.constellation);
      if (num(b.mag) != null) sub.push('mag ' + C.fmtSigned(b.mag, C.nf1));
      if (b.key === 'moon') sub.push(C.distanceText('moon', b.dist_au).main);
      else sub.push(C.nf2.format(b.dist_au) + ' AU');
      if (num(b.elong) != null) sub.push(Math.round(b.elong) + '° ' + (b.elong_dir || '') + ' of the Sun');
      if (num(b.cml1) != null) sub.push('CML I ' + Math.round(b.cml1) + '° · II ' + Math.round(b.cml2) + '°');
      if (num(b.ring_tilt) != null) sub.push('rings tilted ' + C.fmtSigned(b.ring_tilt, C.nf1) + '°');
      rows.push('<li><span class="sw' + (b.comet ? ' comet' : '') + '" style="--sw:' + bodyColor(b.key) + '"></span>'
        + '<span><span class="nm">' + esc(b.name) + '</span><span class="main' + (up ? ' up' : '') + '">' + esc(main) + '</span></span>'
        + '<span class="sub">' + esc(sub.join(' · ')) + '</span></li>');
    });
    var sats = C.satellitesNow();
    var html = '<ul>' + rows.join('') + '</ul>'
      + (sats.length ? '<p class="sat-link">Satellite passes and the Next Visible Pass chart are on the <a href="../celestial/index.html">Celestial page</a>.</p>' : '');
    if (el.__html !== html) { el.innerHTML = html; el.__html = html; }
  }

  // Today's twilight instants, from the sky-state changes.
  function twilightTimes(){
    var out = {};
    var st = sky.sky_states || [];
    for (var i = 1; i < st.length; i++) {
      var a = st[i - 1][1], b = st[i][1], t = st[i][0];
      if (a === 4 && b === 3) out.sunset = t;
      if (a === 3 && b === 2) out.civil_dusk = t;
      if (a === 2 && b === 1) out.nautical_dusk = t;
      if (a === 1 && b === 0) out.astro_dusk = t;
      if (a === 0 && b === 1) out.astro_dawn = t;
      if (a === 2 && b === 3) out.civil_dawn = t;
    }
    return out;
  }

  // ------------------------------------------------------------------
  // Rise & set ribbons
  // ------------------------------------------------------------------
  var ribbons = new Chart('skyRibbons', function(ch, W, small){
    var day = sky.day, bodies = sky.bodies;
    var L = small ? 74 : 92, R = small ? 10 : 150, TOP = 24, ROW = small ? 22 : 24, BOT = 26;
    var H = TOP + ROW * bodies.length + BOT;
    var x = d3.scaleLinear().domain([day.start, day.end]).range([L, W - R]);
    var g = ch.svg.append('g');
    // Twilight shades behind the plot.
    var st = sky.sky_states || [];
    st.forEach(function(s, i){
      var a = Math.max(day.start, s[0]), b = i + 1 < st.length ? st[i + 1][0] : day.end;
      if (b <= a) return;
      g.append('rect').attr('class', 'tw-' + s[1]).attr('x', x(a)).attr('y', TOP - 6).attr('width', x(b) - x(a)).attr('height', ROW * bodies.length + 12);
    });
    // Hours.
    var step = small ? 6 : 3;
    var grid = g.append('g').attr('class', 'grid'), axis = g.append('g').attr('class', 'axis');
    for (var h = 0; h <= 24; h += step) {
      var t = day.start + (day.end - day.start) * h / 24;
      grid.append('line').attr('x1', x(t)).attr('x2', x(t)).attr('y1', TOP - 6).attr('y2', TOP + ROW * bodies.length + 6).attr('opacity', .55);
      axis.append('text').attr('x', x(t)).attr('y', H - 8).attr('text-anchor', 'middle').text((h < 10 ? '0' : '') + h);
    }
    // One row per body.
    bodies.forEach(function(b, i){
      var y = TOP + ROW * i + ROW / 2;
      var row = g.append('g').attr('class', 'rib-row');
      var col = bodyColor(b.key);
      if (b.comet) row.append('path').attr('class', 'rib-sw').attr('fill', col).attr('d', 'M' + 8 + ',' + (y - 4.5) + 'l4.5,4.5l-4.5,4.5l-4.5,-4.5z');
      else row.append('circle').attr('class', 'rib-sw').attr('fill', col).attr('cx', 8).attr('cy', y).attr('r', 4.5);
      row.append('text').attr('class', 'rib-name').attr('x', 18).attr('y', y + 4).text(b.name);
      (b.up || []).forEach(function(span){
        var x0 = x(span[0]), x1 = x(span[1]);
        row.append('rect').attr('class', 'rib-bar').attr('fill', col)
          .attr('x', x0).attr('y', y - 4).attr('width', Math.max(2, x1 - x0)).attr('height', 8).attr('rx', 4);
      });
      if (b.transit && (b.up || []).some(function(sp){ return b.transit >= sp[0] && b.transit <= sp[1]; })) {
        row.append('line').attr('class', 'rib-transit').attr('x1', x(b.transit)).attr('x2', x(b.transit)).attr('y1', y - 6).attr('y2', y + 6);
      }
      if (!small) row.append('text').attr('class', 'rib-times').attr('x', W - R + 10).attr('y', y + 4).text(riseSetText(b));
      row.append('rect').attr('class', 'hit').attr('x', 0).attr('y', y - ROW / 2).attr('width', W).attr('height', ROW)
        .on('mouseenter click', function(ev){
          ev.stopPropagation();
          ch.showTip('<b>' + esc(b.name) + '</b><br><span class="k">Rises</span> ' + hm(b.rise)
            + '<br><span class="k">Highest</span> ' + hm(b.transit) + '<br><span class="k">Sets</span> ' + hm(b.set)
            + '<br><span class="k">Up for</span> ' + dur(b.up_seconds), Math.min(W - R, Math.max(L, x((b.up && b.up[0]) ? (b.up[0][0] + b.up[0][1]) / 2 : day.start + 43200))), y - 6);
        })
        .on('mouseleave', function(){ ch.hideTip(); });
    });
    var nowG = ch.svg.append('g');
    ch.live = function(now){
      nowG.selectAll('*').remove();
      if (now < day.start || now > day.end) return;
      nowG.append('line').attr('class', 'now-line').attr('x1', x(now)).attr('x2', x(now)).attr('y1', TOP - 10).attr('y2', TOP + ROW * bodies.length + 6);
      var lbl = 'now ' + C.fmtTime(now);
      nowG.append('text').attr('class', 'now-lbl').attr('x', x(now)).attr('y', TOP - 13)
        .attr('text-anchor', x(now) > W - R - 40 ? 'end' : x(now) < L + 40 ? 'start' : 'middle').text(lbl);
    };
    return H;
  });
  function riseSetText(b){
    if (b.state === 'always_up') return 'up all day';
    if (b.state === 'never_up') return 'not up today';
    var parts = [];
    if (b.rise) parts.push('↑ ' + hm(b.rise));
    if (b.set) parts.push('↓ ' + hm(b.set));
    return parts.join('   ');
  }

  // ------------------------------------------------------------------
  // The Sun's path
  // ------------------------------------------------------------------
  var sunPath = new Chart('skySunPath', function(ch, W, small){
    var P = sky.paths, sunPts = P.sun, moonPts = P.moon;
    var L = 38, R = 12, T = 12, B = 30;
    var H = Math.round(Math.min(W * 0.78, 460));
    var maxAlt = d3.max(sunPts.concat(moonPts), function(p){ return p[1]; });
    var top = Math.max(30, Math.ceil((maxAlt + 4) / 10) * 10);
    var x = d3.scaleLinear().domain([0, 360]).range([L, W - R]);
    var y = d3.scaleLinear().domain([-24, top]).range([H - B, T]);
    var clipId = 'skySunClip';
    ch.svg.append('clipPath').attr('id', clipId).append('rect').attr('x', L).attr('y', T).attr('width', W - R - L).attr('height', H - B - T);
    var g = ch.svg.append('g').attr('clip-path', 'url(#' + clipId + ')');
    // Day above the horizon, then the twilight depths below it.
    [[top, 0, 4], [0, -6, 3], [-6, -12, 2], [-12, -18, 1], [-18, -24, 0]].forEach(function(b){
      g.append('rect').attr('class', 'tw-' + b[2]).attr('x', L).attr('width', W - R - L).attr('y', y(b[0])).attr('height', y(b[1]) - y(b[0]));
    });
    var grid = g.append('g').attr('class', 'grid');
    for (var a = 30; a < top; a += 30) grid.append('line').attr('x1', L).attr('x2', W - R).attr('y1', y(a)).attr('y2', y(a)).attr('opacity', .6);
    [45, 90, 135, 180, 225, 270, 315].forEach(function(az){
      grid.append('line').attr('x1', x(az)).attr('x2', x(az)).attr('y1', T).attr('y2', H - B).attr('opacity', az % 90 ? .3 : .6);
    });
    g.append('line').attr('class', 'horizon-line').attr('x1', L).attr('x2', W - R).attr('y1', y(0)).attr('y2', y(0));
    // Paths, broken where the azimuth wraps through north.
    function pathOf(pts){
      var segs = [[]];
      pts.forEach(function(p, i){
        if (i && Math.abs(p[2] - pts[i - 1][2]) > 180) segs.push([]);
        segs[segs.length - 1].push([x(p[2]), y(p[1])]);
      });
      return segs.filter(function(s){ return s.length > 1; }).map(function(s){ return d3.line().curve(d3.curveMonotoneX)(s); }).join('');
    }
    g.append('path').attr('class', 'moon-curve').attr('d', pathOf(moonPts));
    g.append('path').attr('class', 'sun-curve').attr('d', pathOf(sunPts));
    // A dot every hour, numbered every three (six on a phone).
    var day = sky.day, every = small ? 6 : 3;
    sunPts.forEach(function(p){
      var hr = Math.round((p[0] - day.start) / 3600);
      if (Math.abs(p[0] - (day.start + hr * 3600)) > 60 || hr >= 24) return;
      g.append('circle').attr('class', 'hour-dot').attr('cx', x(p[2])).attr('cy', y(p[1])).attr('r', 2.6);
      if (hr % every === 0 && p[1] > -22) {
        g.append('text').attr('class', 'hour-lbl').attr('x', x(p[2]) + 5).attr('y', y(p[1]) - 5).text((hr < 10 ? '0' : '') + hr);
      }
    });
    // Axes.
    var axis = ch.svg.append('g').attr('class', 'axis');
    [0, 30, 60, 90].forEach(function(a){
      if (a > top) return;
      axis.append('text').attr('x', L - 6).attr('y', y(a) + 4).attr('text-anchor', 'end').text(a + '°');
    });
    [['N', 0], ['E', 90], ['S', 180], ['W', 270], ['N', 360]].forEach(function(c){
      axis.append('text').attr('x', x(c[1])).attr('y', H - 10).attr('text-anchor', 'middle').text(c[0]);
    });
    if (!small) {
      axis.append('text').attr('class', 'band-lbl').attr('x', W - R - 6).attr('y', y(-3) + 4).attr('text-anchor', 'end').text('civil');
      axis.append('text').attr('class', 'band-lbl').attr('x', W - R - 6).attr('y', y(-9) + 4).attr('text-anchor', 'end').text('nautical');
      axis.append('text').attr('class', 'band-lbl').attr('x', W - R - 6).attr('y', y(-15) + 4).attr('text-anchor', 'end').text('astronomical');
    }
    // Hover: the nearest point on the Sun's or Moon's path.
    var pts = sunPts.map(function(p){ return { who: 'Sun', p: p }; }).concat(moonPts.map(function(p){ return { who: 'Moon', p: p }; }));
    var del = d3.Delaunay.from(pts, function(o){ return x(o.p[2]); }, function(o){ return y(o.p[1]); });
    var hov = ch.svg.append('circle').attr('class', 'hover-dot').attr('r', 4).attr('visibility', 'hidden');
    ch.svg.append('rect').attr('class', 'hit').attr('x', L).attr('y', T).attr('width', W - R - L).attr('height', H - B - T)
      .on('mousemove click', function(ev){
        ev.stopPropagation();
        var m = d3.pointer(ev), o = pts[del.find(m[0], m[1])];
        var px = x(o.p[2]), py = y(o.p[1]);
        if (Math.hypot(px - m[0], py - m[1]) > 24) { hov.attr('visibility', 'hidden'); ch.hideTip(); return; }
        hov.attr('cx', px).attr('cy', py).attr('visibility', 'visible');
        ch.showTip('<b>' + o.who + '</b> at ' + C.fmtTime(o.p[0]) + '<br><span class="k">Altitude</span> ' + C.fmtSigned(o.p[1], C.nf1)
          + '°<br><span class="k">Bearing</span> ' + Math.round(o.p[2]) + '° ' + C.compass(o.p[2]), px, py);
      })
      .on('mouseleave', function(){ hov.attr('visibility', 'hidden'); ch.hideTip(); });
    var nowG = ch.svg.append('g').attr('clip-path', 'url(#' + clipId + ')').style('pointer-events', 'none');
    ch.live = function(now){
      nowG.selectAll('*').remove();
      ['moon', 'sun'].forEach(function(key){
        var p = C.bodyAt(key, now);
        if (!p) return;
        nowG.append('circle').attr('class', 'now-ring').attr('cx', x(p.az)).attr('cy', y(p.alt)).attr('r', key === 'sun' ? 9 : 8);
        nowG.append('circle').attr('class', 'now-mark').attr('fill', bodyColor(key)).attr('cx', x(p.az)).attr('cy', y(p.alt)).attr('r', key === 'sun' ? 5.5 : 4.5);
      });
    };
    return H;
  });

  // ------------------------------------------------------------------
  // The orrery
  // ------------------------------------------------------------------
  var orrery = new Chart('skyOrrery', function(ch, W, small){
    var H = Math.round(Math.min(W, 460));
    var cx = W / 2, cy = H / 2, Rmax = Math.min(W, H) / 2 - (small ? 22 : 30);
    var items = sky.orrery.filter(function(o){ return num(o.r_au) != null; });
    var rmaxAU = Math.max(32, d3.max(items, function(o){ return o.r_au; }));
    var r = d3.scaleLog().domain([0.3, rmaxAU]).range([Rmax * 0.12, Rmax]).clamp(true);
    var g = ch.svg.append('g');
    g.append('line').attr('class', 'equinox-ray').attr('x1', cx).attr('y1', cy).attr('x2', cx + Rmax + 8).attr('y2', cy);
    items.forEach(function(o){
      g.append('circle').attr('class', 'orbit' + (o.comet ? ' comet' : '')).attr('cx', cx).attr('cy', cy).attr('r', r(o.r_au));
    });
    g.append('circle').attr('class', 'sun-mark').attr('cx', cx).attr('cy', cy).attr('r', small ? 6 : 8);
    var NAMES = { earth: 'Earth', mercury: 'Mercury', venus: 'Venus', mars: 'Mars', jupiter: 'Jupiter', saturn: 'Saturn', uranus: 'Uranus', neptune: 'Neptune' };
    var placed = [[cx - 10, cy - 10, cx + 10, cy + 10]];
    var marks = items.map(function(o){
      var a = o.hlon * Math.PI / 180, rr = r(o.r_au);
      return { o: o, x: cx + rr * Math.cos(a), y: cy - rr * Math.sin(a), name: o.name || NAMES[o.key] || o.key };
    });
    marks.forEach(function(m){ placed.push([m.x - 6, m.y - 6, m.x + 6, m.y + 6]); });
    // The equinox ray's "0°", above the ray or, if a body is there, below it.
    [[cy - 17, cy - 6], [cy + 4, cy + 15]].some(function(yy){
      var b = [cx + Rmax - 18, yy[0], cx + Rmax + 6, yy[1] + 2];
      if (placed.some(function(p){ return b[0] < p[2] && b[2] > p[0] && b[1] < p[3] && b[3] > p[1]; })) return false;
      placed.push(b);
      g.append('text').attr('class', 'lbl').attr('x', cx + Rmax + 4).attr('y', yy[1]).attr('text-anchor', 'end').text('0°');
      return true;
    });
    var measure = document.createElement('canvas').getContext('2d');
    measure.font = '500 11px Lexend, Helvetica, Arial, sans-serif';
    marks.forEach(function(m){
      var col = m.o.key === 'earth' ? '#3B7CC4' : bodyColor(m.o.key);
      if (m.o.comet) {
        var q = 5;
        g.append('path').attr('class', 'body-dot').attr('fill', col).attr('d', 'M' + m.x + ',' + (m.y - q) + 'l' + q + ',' + q + 'l' + (-q) + ',' + q + 'l' + (-q) + ',' + (-q) + 'z');
      } else {
        g.append('circle').attr('class', 'body-dot').attr('fill', col).attr('cx', m.x).attr('cy', m.y).attr('r', m.o.key === 'jupiter' || m.o.key === 'saturn' ? 5.5 : 4.5);
      }
      var w = measure.measureText(m.name).width, h = 12;
      var tries = [[m.x + 8, m.y - h / 2], [m.x - 8 - w, m.y - h / 2], [m.x - w / 2, m.y - 8 - h], [m.x - w / 2, m.y + 8]];
      for (var i = 0; i < tries.length; i++) {
        var b = [tries[i][0], tries[i][1], tries[i][0] + w, tries[i][1] + h];
        if (b[0] < 2 || b[2] > W - 2 || b[1] < 2 || b[3] > H - 2) continue;
        if (placed.some(function(p){ return b[0] < p[2] && b[2] > p[0] && b[1] < p[3] && b[3] > p[1]; })) continue;
        placed.push(b);
        g.append('text').attr('class', 'planet-lbl').attr('x', b[0]).attr('y', b[1] + h - 2).text(m.name);
        break;
      }
      g.append('circle').attr('class', 'hit').attr('cx', m.x).attr('cy', m.y).attr('r', 12)
        .on('mouseenter click', function(ev){
          ev.stopPropagation();
          ch.showTip('<b>' + esc(m.name) + '</b><br><span class="k">Longitude</span> ' + C.nf1.format(m.o.hlon)
            + '°<br><span class="k">From the Sun</span> ' + C.nf2.format(m.o.r_au) + ' AU', m.x, m.y - 8);
        })
        .on('mouseleave', function(){ ch.hideTip(); });
    });
    return H;
  });

  // ------------------------------------------------------------------
  // Year charts: shared x scale over the days of the year
  // ------------------------------------------------------------------
  function yearDates(){
    var Y = sky.year, out = [];
    for (var i = 0; i < Y.days.length; i++) out.push(Y.start + i * 86400 + 43200);   // a noon in each day
    return out;
  }
  function todayIndex(now){
    var Y = sky.year;
    var i = dayIndexOf(now) - dayIndexOf(Y.start + 43200);
    return i >= 0 && i < Y.days.length ? i : null;
  }
  function monthAxis(ch, x, H, B, small, n){
    var axis = ch.svg.append('g').attr('class', 'axis');
    var grid = ch.svg.append('g').attr('class', 'grid');
    var year = sky.year.year, start = dayIndexOf(sky.year.start + 43200);
    for (var m = 0; m < 12; m++) {
      var idx = Date.UTC(year, m, 1) / 86400000 - start;
      grid.append('line').attr('x1', x(idx)).attr('x2', x(idx)).attr('y1', 10).attr('y2', H - B).attr('opacity', .5);
      if (small && m % 2) continue;
      var mid = (Date.UTC(year, m, 15) / 86400000) - start;
      axis.append('text').attr('x', x(mid)).attr('y', H - B + 18).attr('text-anchor', 'middle').text(MONTHS[m]);
    }
  }

  // ------------------------------------------------------------------
  // The solar year
  // ------------------------------------------------------------------
  var solarYear = new Chart('skyYear', function(ch, W, small){
    var Y = sky.year, rows = Y.days, n = rows.length;
    var L = small ? 30 : 40, R = 12, T = 22, B = 30;
    var H = Math.round(Math.min(Math.max(W * 0.36, 260), 400));
    var x = d3.scaleLinear().domain([0, n]).range([L, W - R]);
    var y = d3.scaleLinear().domain([0, 1440]).range([H - B, T]);
    var g = ch.svg.append('g');
    g.append('rect').attr('class', 'tw-0').attr('x', L).attr('y', T).attr('width', W - R - L).attr('height', H - B - T);
    // Each level's band: the part of each day at least that bright.
    var COLS = { 4: [0, 1], 3: [3, 4], 2: [5, 6], 1: [7, 8] };
    [1, 2, 3, 4].forEach(function(lv){
      var d = '';
      rows.forEach(function(r, i){
        var hi = r[9], lo = r[10], spans = [];
        if (lo != null && lo >= lv) spans = [[0, 1440]];
        else if (hi == null || hi < lv) spans = [];
        else {
          var a = r[COLS[lv][0]], b = r[COLS[lv][1]];
          if (a != null && b != null) spans = b > a ? [[a, b]] : [[0, b], [a, 1440]];
          else if (a != null) spans = [[a, 1440]];
          else if (b != null) spans = [[0, b]];
          else spans = [[0, 1440]];   // a dip too brief to time: draw the day as never reaching it
        }
        spans.forEach(function(s){
          d += 'M' + x(i).toFixed(1) + ',' + y(s[1]).toFixed(1) + 'H' + x(i + 1).toFixed(1) + 'V' + y(s[0]).toFixed(1) + 'H' + x(i).toFixed(1) + 'Z';
        });
      });
      g.append('path').attr('class', 'tw-' + lv).attr('d', d).attr('shape-rendering', 'crispEdges');
    });
    var grid = g.append('g').attr('class', 'grid'), axis = ch.svg.append('g').attr('class', 'axis');
    var hs = small ? 6 : 3;
    for (var h = 0; h <= 24; h += hs) {
      grid.append('line').attr('x1', L).attr('x2', W - R).attr('y1', y(h * 60)).attr('y2', y(h * 60)).attr('opacity', .45);
      axis.append('text').attr('x', L - 6).attr('y', y(h * 60) + 4).attr('text-anchor', 'end').text((h < 10 ? '0' : '') + h);
    }
    monthAxis(ch, x, H, B, small, n);
    function curve(col, cls){
      var line = d3.line().defined(function(r){ return r[col] != null; })
        .x(function(r, i){ return x(i + 0.5); }).y(function(r){ return y(r[col]); });
      // Break at the DST steps rather than drawing a slanted riser.
      ch.svg.append('path').attr('class', 'curve' + (cls ? ' ' + cls : '')).attr('d', line(rows));
    }
    curve(0); curve(1); curve(2, 'dashed');
    // Today, and the hover crosshair.
    var liveG = ch.svg.append('g');
    var hoverG = ch.svg.append('g');
    ch.svg.append('rect').attr('class', 'hit').attr('x', L).attr('y', T).attr('width', W - R - L).attr('height', H - B - T)
      .on('mousemove click', function(ev){
        ev.stopPropagation();
        var i = Math.max(0, Math.min(n - 1, Math.floor(x.invert(d3.pointer(ev)[0]))));
        var r = rows[i], xi = x(i + 0.5);
        hoverG.selectAll('*').remove();
        hoverG.append('line').attr('class', 'hover-line').attr('x1', xi).attr('x2', xi).attr('y1', T).attr('y2', H - B);
        [0, 1].forEach(function(c){ if (r[c] != null) hoverG.append('circle').attr('class', 'hover-dot').attr('cx', xi).attr('cy', y(r[c])).attr('r', 3.5); });
        var date = fmt(Y.start + i * 86400 + 43200, { weekday: 'short', day: 'numeric', month: 'short' });
        var len = r[11] != null ? Math.floor(r[11] / 60) + ' h ' + (r[11] % 60) + ' m' : '—';
        ch.showTip('<b>' + esc(date) + '</b><br><span class="k">Sunrise</span> ' + minToHm(r[0]) + ' · <span class="k">sunset</span> ' + minToHm(r[1])
          + '<br><span class="k">Daylight</span> ' + len + '<br><span class="k">Solar noon</span> ' + minToHm(r[2])
          + (r[7] != null || r[8] != null ? '<br><span class="k">Astronomical dark</span> ' + minToHm(r[8]) + '–' + minToHm(r[7]) : ''), xi, T + 30);
      })
      .on('mouseleave', function(){ hoverG.selectAll('*').remove(); ch.hideTip(); });
    ch.live = function(now){
      liveG.selectAll('*').remove();
      var i = todayIndex(now);
      if (i == null) return;
      var xi = x(i + 0.5);
      liveG.append('line').attr('class', 'today-line').attr('x1', xi).attr('x2', xi).attr('y1', T - 4).attr('y2', H - B);
      liveG.append('text').attr('class', 'now-lbl').attr('x', xi).attr('y', T - 8)
        .attr('text-anchor', xi > W - 50 ? 'end' : xi < L + 30 ? 'start' : 'middle').text('today');
    };
    return H;
  });

  // ------------------------------------------------------------------
  // The equation of time
  // ------------------------------------------------------------------
  var eot = new Chart('skyEot', function(ch, W, small){
    var Y = sky.year, vals = Y.eot, n = vals.length;
    var L = small ? 44 : 50, R = 12, T = 14, B = 30;
    // As tall as the analemma beside it, so the pair lines up.
    var H = small ? Math.round(Math.max(W * 0.62, 240)) : Math.round(Math.min(W * 0.95, 460));
    var x = d3.scaleLinear().domain([0, n]).range([L, W - R]);
    var y = d3.scaleLinear().domain([-18, 18]).range([H - B, T]);
    var grid = ch.svg.append('g').attr('class', 'grid'), axis = ch.svg.append('g').attr('class', 'axis');
    for (var m = -15; m <= 15; m += 5) {
      grid.append('line').attr('x1', L).attr('x2', W - R).attr('y1', y(m)).attr('y2', y(m)).attr('opacity', .5);
      if (!small || m % 10 === 0 || m === 15 || m === -15) axis.append('text').attr('x', L - 6).attr('y', y(m) + 4).attr('text-anchor', 'end').text(m ? (m > 0 ? '+' : '−') + Math.abs(m) + ' m' : '0');
    }
    monthAxis(ch, x, H, B, small, n);
    ch.svg.append('line').attr('class', 'zero').attr('x1', L).attr('x2', W - R).attr('y1', y(0)).attr('y2', y(0));
    ch.svg.append('path').attr('class', 'curve').attr('d', d3.line().x(function(v, i){ return x(i + 0.5); }).y(function(v){ return y(v); })(vals));
    var liveG = ch.svg.append('g'), hoverG = ch.svg.append('g');
    function label(v){ return minsSigned(v) + (v >= 0 ? ', sundial ahead' : ', sundial behind'); }
    ch.svg.append('rect').attr('class', 'hit').attr('x', L).attr('y', T).attr('width', W - R - L).attr('height', H - B - T)
      .on('mousemove click', function(ev){
        ev.stopPropagation();
        var i = Math.max(0, Math.min(n - 1, Math.floor(x.invert(d3.pointer(ev)[0]))));
        hoverG.selectAll('*').remove();
        hoverG.append('circle').attr('class', 'hover-dot').attr('cx', x(i + 0.5)).attr('cy', y(vals[i])).attr('r', 4);
        ch.showTip('<b>' + esc(fmt(Y.start + i * 86400 + 43200, { day: 'numeric', month: 'short' })) + '</b><br>' + label(vals[i]), x(i + 0.5), y(vals[i]) - 6);
      })
      .on('mouseleave', function(){ hoverG.selectAll('*').remove(); ch.hideTip(); });
    ch.live = function(now){
      liveG.selectAll('*').remove();
      var i = todayIndex(now);
      if (i == null) return;
      var cx = x(i + 0.5), cy = y(vals[i]);
      liveG.append('circle').attr('class', 'today-dot').attr('cx', cx).attr('cy', cy).attr('r', 6);
      var right = cx < W - 150;
      liveG.append('text').attr('class', 'val-lbl').attr('x', cx + (right ? 11 : -11)).attr('y', cy + (vals[i] > 10 ? 18 : -10))
        .attr('text-anchor', right ? 'start' : 'end').text(minsSigned(vals[i]));
    };
    return H;
  });

  // ------------------------------------------------------------------
  // The analemma
  // ------------------------------------------------------------------
  var analemma = new Chart('skyAnalemma', function(ch, W, small){
    var pts = sky.year.analemma, today = sky.analemma_today;
    var L = 44, R = 14, T = 14, B = 34;
    var H = Math.round(Math.min(W * 0.95, 460));
    var all = pts.concat(today ? [today] : []);
    var azs = all.map(function(p){ return p[2]; }), alts = all.map(function(p){ return p[1]; });
    var az0 = d3.min(azs) - 3, az1 = d3.max(azs) + 3;
    var al0 = Math.floor((d3.min(alts) - 3) / 10) * 10, al1 = Math.ceil((d3.max(alts) + 3) / 10) * 10;
    var x = d3.scaleLinear().domain([az0, az1]).range([L, W - R]);
    var y = d3.scaleLinear().domain([al0, al1]).range([H - B, T]);
    var grid = ch.svg.append('g').attr('class', 'grid'), axis = ch.svg.append('g').attr('class', 'axis');
    for (var a = al0; a <= al1; a += 10) {
      grid.append('line').attr('x1', L).attr('x2', W - R).attr('y1', y(a)).attr('y2', y(a)).attr('opacity', .5);
      axis.append('text').attr('x', L - 6).attr('y', y(a) + 4).attr('text-anchor', 'end').text(a + '°');
    }
    var span = az1 - az0, step = span > 40 ? 10 : span > 16 ? 5 : 2;
    for (var z = Math.ceil(az0 / step) * step; z <= az1; z += step) {
      grid.append('line').attr('x1', x(z)).attr('x2', x(z)).attr('y1', T).attr('y2', H - B).attr('opacity', .4);
      axis.append('text').attr('x', x(z)).attr('y', H - B + 16).attr('text-anchor', 'middle').text(z + '°');
    }
    axis.append('text').attr('class', 'axis-title').attr('x', (L + W - R) / 2).attr('y', H - 4).attr('text-anchor', 'middle').text('bearing at noon');
    ch.svg.append('path').attr('class', 'curve').attr('d', d3.line().curve(d3.curveCatmullRomClosed).x(function(p){ return x(p[2]); }).y(function(p){ return y(p[1]); })(pts.slice(0, 52)));
    pts.forEach(function(p){ ch.svg.append('circle').attr('class', 'hover-dot').attr('cx', x(p[2])).attr('cy', y(p[1])).attr('r', 2.2); });
    // Month names beside their first week.
    var months = small ? [0, 5] : [0, 2, 5, 8, 10];
    var cxm = d3.mean(pts, function(p){ return x(p[2]); }), cym = d3.mean(pts, function(p){ return y(p[1]); });
    months.forEach(function(m){
      var w = Math.round(m * 52 / 12 + 1), p = pts[Math.min(w, pts.length - 1)];
      var px = x(p[2]), py = y(p[1]);
      var dx = px - cxm, dy = (py - cym) * 0.35, len = Math.hypot(dx, dy) || 1;
      var lx = px + dx / len * 12, ly = py + dy / len * 12 + 4;
      ch.svg.append('text').attr('class', 'lbl').attr('x', lx).attr('y', ly)
        .attr('text-anchor', Math.abs(dx) < 6 ? 'middle' : dx > 0 ? 'start' : 'end').text(MONTHS[m]);
    });
    if (today) {
      ch.svg.append('circle').attr('class', 'today-dot').attr('cx', x(today[2])).attr('cy', y(today[1])).attr('r', 6);
      var tr = x(today[2]) < cxm;
      ch.svg.append('text').attr('class', 'val-lbl').attr('x', x(today[2]) + (tr ? -11 : 11)).attr('y', y(today[1]) - 8)
        .attr('text-anchor', tr ? 'end' : 'start').text('today');
    }
    var del = d3.Delaunay.from(all, function(p){ return x(p[2]); }, function(p){ return y(p[1]); });
    ch.svg.append('rect').attr('class', 'hit').attr('x', L).attr('y', T).attr('width', W - R - L).attr('height', H - B - T)
      .on('mousemove click', function(ev){
        ev.stopPropagation();
        var m = d3.pointer(ev), p = all[del.find(m[0], m[1])];
        if (Math.hypot(x(p[2]) - m[0], y(p[1]) - m[1]) > 20) { ch.hideTip(); return; }
        ch.showTip('<b>' + esc(fmt(p[0], { day: 'numeric', month: 'short' })) + '</b>, standard-time noon<br><span class="k">Altitude</span> '
          + C.nf1.format(p[1]) + '°<br><span class="k">Bearing</span> ' + C.nf1.format(p[2]) + '°', x(p[2]), y(p[1]) - 6);
      })
      .on('mouseleave', function(){ ch.hideTip(); });
    return H;
  });

  // ------------------------------------------------------------------
  // The lunar month
  // ------------------------------------------------------------------
  var lunation = new Chart('skyLunation', function(ch, W, small){
    var lu = sky.lunation;
    if (!lu || !lu.discs) return 20;
    var discs = small ? lu.discs.filter(function(d, i){ return i % 2 === 1; }) : lu.discs;
    var n = discs.length, pad = 18;
    var x = d3.scaleLinear().domain([lu.prev_new, lu.next_new]).range([pad, W - pad]);
    var slot = (W - 2 * pad) / n;
    var r = Math.max(5, Math.min(small ? 11 : 13, slot / 2 - 2));
    var yD = 34 + r, H = yD + r + (small ? 62 : 52);
    var south = C.stationLat(C.latest() && C.latest().d) < 0;
    var g = ch.svg.append('g');
    var liveG = ch.svg.append('g');
    discs.forEach(function(d, i){
      var cx = x(d[0]);
      var gg = g.append('g');
      gg.append('circle').attr('class', 'lun-dark').attr('cx', cx).attr('cy', yD).attr('r', r);
      // Lit on the right while waxing (left in the southern hemisphere).
      var mirror = (d[2] ? 1 : -1) * (south ? -1 : 1);
      gg.append('path').attr('class', 'lun-lit').attr('d', C.moonLitPath(r, d[1]))
        .attr('transform', 'translate(' + cx + ',' + yD + ') scale(' + mirror + ',1)');
      gg.append('circle').attr('class', 'lun-edge').attr('cx', cx).attr('cy', yD).attr('r', r);
      gg.append('circle').attr('class', 'hit').attr('cx', cx).attr('cy', yD).attr('r', Math.max(r + 2, slot / 2))
        .on('mouseenter click', function(ev){
          ev.stopPropagation();
          ch.showTip('<b>' + esc(dayMonthHm(d[0])) + '</b><br>' + Math.round(d[1] * 100) + '% illuminated, ' + (d[2] ? 'waxing' : 'waning'), cx, yD - r - 4);
        })
        .on('mouseleave', function(){ ch.hideTip(); });
    });
    // The principal phases, dated.
    var phases = lu.phases || [];
    phases.forEach(function(p, i){
      var px = x(p[0]);
      g.append('line').attr('class', 'lun-tick').attr('x1', px).attr('x2', px).attr('y1', yD + r + 4).attr('y2', yD + r + 10);
      var lvl = small && (i % 2) ? 26 : 0;
      var anchor = i === 0 ? 'start' : i === phases.length - 1 ? 'end' : 'middle';
      var tx = i === 0 ? Math.max(2, px - 8) : i === phases.length - 1 ? Math.min(W - 2, px + 8) : px;
      g.append('text').attr('class', 'lun-phase').attr('x', tx).attr('y', yD + r + 24 + lvl).attr('text-anchor', anchor).text(PHASE_NAMES[p[1]]);
      g.append('text').attr('class', 'lun-date').attr('x', tx).attr('y', yD + r + 38 + lvl).attr('text-anchor', anchor).text(dayMonth(p[0]));
    });
    ch.live = function(now){
      liveG.selectAll('*').remove();
      if (now < lu.prev_new || now > lu.next_new) return;
      var best = d3.minIndex(discs, function(d){ return Math.abs(d[0] - now); });
      var cx = x(discs[best][0]);
      liveG.append('circle').attr('class', 'lun-today').attr('cx', cx).attr('cy', yD).attr('r', r + 4);
      liveG.append('text').attr('class', 'now-lbl').attr('x', cx).attr('y', yD - r - 9).attr('text-anchor', 'middle').text('today');
    };
    return H;
  });
  function renderLunationNote(){
    var el = document.getElementById('skyLunationNote');
    var lu = sky && sky.lunation;
    if (!el || !lu) return;
    var bits = [];
    if (lu.perigee) bits.push([lu.perigee, 'Perigee ' + dayMonthHm(lu.perigee) + ' · ' + C.fmtLinear(lu.perigee_km)]);
    if (lu.apogee) bits.push([lu.apogee, 'Apogee ' + dayMonthHm(lu.apogee) + ' · ' + C.fmtLinear(lu.apogee_km)]);
    bits.sort(function(a, b){ return a[0] - b[0]; });
    var html = esc(bits.map(function(b){ return b[1]; }).join('   —   '));
    if (lu.supermoon && lu.next_full) {
      html += '<br><span class="super">The full moon on ' + esc(dayMonth(lu.next_full)) + ' is a supermoon, '
        + esc(C.fmtLinear(lu.next_full_km)) + ' away.</span>';
    }
    if (el.__html !== html) { el.innerHTML = html; el.__html = html; }
  }

  // ------------------------------------------------------------------
  // The almanac table
  // ------------------------------------------------------------------
  function renderTable(now){
    var tb = document.querySelector('#skyTable tbody');
    if (!tb || !sky) return;
    var rows = sky.bodies.map(function(b){ return { b: b, p: C.bodyAt(b.key, now) }; });
    var sel = d3.select(tb).selectAll('tr').data(rows, function(r){ return r.b.key; });
    sel.exit().remove();
    var ent = sel.enter().append('tr');
    var who = ent.append('td').append('span').attr('class', 'who');
    who.append('span').attr('class', 'swatch');
    who.append('span').attr('class', 'nm');
    ['rise', 'transit', 'set', 'upfor', 'alt', 'az', 'mag', 'dist'].forEach(function(c){ ent.append('td').attr('class', 'num ' + c); });
    var all = ent.merge(sel).order();
    all.classed('below', function(r){ return !(r.p && r.p.up); });
    all.select('.swatch').style('--sw', function(r){ return bodyColor(r.b.key); }).style('border-radius', function(r){ return r.b.comet ? '1px' : null; });
    all.select('.nm').text(function(r){ return r.b.name; });
    all.select('.rise').text(function(r){ return r.b.rise ? hm(r.b.rise) : '—'; });
    all.select('.transit').text(function(r){ return r.b.transit ? hm(r.b.transit) : '—'; });
    all.select('.set').text(function(r){ return r.b.set ? hm(r.b.set) : '—'; });
    all.select('.upfor').text(function(r){ return r.b.state === 'never_up' ? 'not up' : r.b.state === 'always_up' ? 'all day' : dur(r.b.up_seconds); });
    all.select('.alt').text(function(r){ return r.p ? C.fmtSigned(r.p.alt, C.nf1) + '°' : '—'; });
    all.select('.az').text(function(r){ return r.p ? C.nf1.format(r.p.az) + '°' : '—'; });
    all.select('.mag').text(function(r){ return num(r.b.mag) != null ? C.fmtSigned(r.b.mag, C.nf1) : '—'; });
    all.select('.dist').text(function(r){ return r.b.key === 'moon' ? C.distanceText('moon', r.b.dist_au).main : C.nf2.format(r.b.dist_au) + ' AU'; });
    var upd = document.getElementById('skyUpdated');
    if (upd && sky.generated) upd.textContent = 'Rise, set and the charts computed ' + C.fmtTime(sky.generated) + '; altitude and azimuth are live.';
  }

  // ------------------------------------------------------------------
  // Loading and the update loop
  // ------------------------------------------------------------------
  var charts = [ribbons, sunPath, orrery, solarYear, eot, analemma, lunation];

  function renderAll(){
    if (!sky) return;
    charts.forEach(function(c){ try { c.render(); } catch (e) { console.warn('skyfield: chart failed —', e); } });
    renderLunationNote();
    live();
  }
  function live(){
    var now = C.now();
    renderSub(now);
    renderChips(now);
    renderMoon(now);
    if (!sky) return;
    renderToday(now);
    renderTable(now);
    charts.forEach(function(c){ if (c.live) { try { c.live(now); } catch (e) {} } });
  }

  function load(){
    fetch(SKY_URL + '?_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(d){
        missing = false;
        document.getElementById('skyWait').hidden = true;
        if (d.generated === skyStamp) return;
        sky = d; skyStamp = d.generated;
        renderAll();
      })
      .catch(function(e){
        console.warn('skyfield: sky.json —', e.message);
        if (!sky) {
          missing = true;
          var w = document.getElementById('skyWait');
          w.textContent = 'Waiting for the station’s sky data (jsondata/sky.json). It needs SkyfieldLoopData 0.4.0 or later in divumwx.py, and is first written a few seconds after WeeWX starts.';
          w.hidden = false;
        }
      });
  }

  // Redraw on theme flips (the marks read CSS colour tokens) and unit changes.
  new MutationObserver(function(){ renderAll(); }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
  window.addEventListener('storage', function(e){ if (e.key === 'dashboardUnitSystem') renderAll(); });
  document.addEventListener('click', function(){ charts.forEach(function(c){ if (!c.dead) c.hideTip(); }); });

  // The first frames need the live feed's time zone and latest record.
  var firstLive = true;
  C.onTick(function(){
    if (firstLive && C.latest()) { firstLive = false; live(); if (sky) renderAll(); }
  });
  load();
  setInterval(load, POLL_MS);
  setInterval(live, LIVE_MS);
})();
