/*
 * EcowittConsoleEmulator — dashboard logic for the WeeWX EcowittConsoleEmulator skin.
 * Layout follows the Ecowitt WH2560 / HP2560 console.
 *
 * Copyright (c) Ian Millard 2026
 *
 * Two data sources:
 *   ecowitt.json  written by WeeWX each archive interval (highs/lows, rain totals,
 *                 trends, almanac); also embedded in index.html as ECCE_INITIAL
 *   live.json     written every few seconds by the EcowittConsoleEmulatorLive service
 *                 (latest loop packet, incl. battery/signal fields). Optional.
 * Live values override archive values while live.json is fresh.
 */
(function () {
  'use strict';

  var S = {
    arch: null, alm: null, cfg: null, opts: null,
    live: null, liveFails: 0,
    windBuf: [], windAngle: null, windAvgAngle: null,
    chIdx: 0, groundIdx: 0, pmIdx: 0, rainSrc: null,
    baroMode: null, wakeLock: null
  };
  var NS = 'http://www.w3.org/2000/svg';
  function $(id) { return document.getElementById(id); }

  // ---------------------------------------------------------------- storage
  function lsGet(k, d) { try { var v = localStorage.getItem('ecce.' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } }
  function lsSet(k, v) { try { localStorage.setItem('ecce.' + k, JSON.stringify(v)); } catch (e) { /* ignore */ } }

  // ---------------------------------------------------------------- language (as weewx-divumwx)
  // The same languages, codes and browser key ('dashboardLanguage') as weewx-divumwx, so the
  // two sites share one choice when served from the same web server. English is built in;
  // every other language is lang/<code>.json, keyed by the English text.
  var LANG_KEY = 'dashboardLanguage';
  //   code: [name in that language, locale for dates]
  var LANGS = {
    ar: ['العربية', 'ar-u-ca-gregory-nu-latn'], br: ['Brezhoneg', 'br'], ca: ['Català', 'ca'],
    cn: ['中文 (简体)', 'zh-CN'], cy: ['Cymraeg', 'cy'], cz: ['Čeština', 'cs'], da: ['Dansk', 'da'],
    de: ['Deutsch', 'de'], en: ['English', 'en-GB'], en_US: ['English (US)', 'en-US'], es: ['Español', 'es'],
    eu: ['Euskara', 'eu'], fi: ['Suomi', 'fi'], fr: ['Français', 'fr'], gr: ['Ελληνικά', 'el'],
    hi: ['हिन्दी', 'hi-u-nu-latn'], hu: ['Magyar', 'hu'], is: ['Íslenska', 'is'], it: ['Italiano', 'it'],
    ja: ['日本語', 'ja'], nl: ['Nederlands', 'nl'], no: ['Norsk', 'nb'], pl: ['Polski', 'pl'],
    pt: ['Português', 'pt-PT'], sv: ['Svenska', 'sv'], ta: ['தமிழ்', 'ta-u-nu-latn'],
    th: ['ไทย', 'th-u-ca-gregory-nu-latn'], tr: ['Türkçe', 'tr'], uk: ['Українська', 'uk'],
    ur: ['اردو', 'ur-u-nu-latn']
  };
  var TR = {};                                   // translations of the current language
  // _t('Gust'), _t('{0} min ago', 5). A key may carry a context ('battery|Low'), which
  // English doesn't show. Missing translations fall back to English.
  function _t(k) {
    var s = TR[k];
    if (s == null) { s = String(k); var i = s.indexOf('|'); if (i >= 0) s = s.slice(i + 1); }
    for (var a = 1; a < arguments.length; a++) s = s.split('{' + (a - 1) + '}').join(arguments[a]);
    return s;
  }
  // Sensor and channel names: a whole known name ('Indoor'), or the word after the model
  // number ('WS90 array', 'WH55 leak CH2'). Names set in skin.conf are shown as they are.
  function tl(label) {
    label = String(label == null ? '' : label);
    if (TR[label]) return TR[label];
    var m = label.match(/^((?:[A-Z]{2}\d{2}[A-Z]?\/?)+)\s+(.+?)(\s+CH\d+)?$/);
    return m && TR[m[2]] ? m[1] + ' ' + TR[m[2]] + (m[3] || '') : label;
  }
  function loadLangKey() {
    var k = null;
    try { k = localStorage.getItem(LANG_KEY); } catch (e) { /* private mode */ }
    if (!LANGS[k]) k = (S.cfg && LANGS[S.cfg.language]) ? S.cfg.language : 'en';
    return k;
  }
  function loadLang(code) {
    if (code === 'en') { TR = {}; return Promise.resolve(); }
    var v = (S.arch && S.arch.version) || '';
    return fetch('lang/' + code + '.json?v=' + encodeURIComponent(v)).then(function (r) {
      if (!r.ok) throw new Error(r.status);
      return r.json();
    }).then(function (j) { TR = j || {}; }, function () { TR = {}; });
  }
  function locale() { return (LANGS[S.lang] || LANGS.en)[1]; }
  // Tooltips written in index.html (the English text is kept in data-tk)
  function translateStatic() {
    document.documentElement.lang = (S.lang || 'en').replace('_', '-');
    // Arabic and Urdu: each piece of text reads in its own direction; the layout stays as on the console
    document.body.classList.toggle('rtl-text', S.lang === 'ar' || S.lang === 'ur');
    Array.prototype.forEach.call(document.querySelectorAll('#hdr-leak, #soil, #g-ch, #toolbar button, #ov-close'), function (el) {
      if (!el.hasAttribute('data-tk')) el.setAttribute('data-tk', el.getAttribute('title') || '');
      var k = el.getAttribute('data-tk');
      if (k) el.title = _t(k);
    });
  }
  function setLanguage(code) {
    if (!LANGS[code]) return;
    S.lang = code;
    try { localStorage.setItem(LANG_KEY, code); } catch (e) { /* private mode */ }
    applyLanguage();
  }
  function applyLanguage() {
    return loadLang(S.lang).then(function () {
      fmtCache = {};
      translateStatic();
      renderAll(); tickClock();
      if (S.panel === 'Settings') showSettings();
      else if (S.panel === 'Sensors') showSensors();
      else if (S.panel === 'Charts') showCharts();
      else if (S.panel) $('ov-title').textContent = _t(S.panel);
    });
  }

  // ---------------------------------------------------------------- utils
  function isNum(v) { return typeof v === 'number' && isFinite(v); }
  function fmt(v, nd) { return isNum(v) ? v.toFixed(nd) : '--'; }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function maxOf() { var r = null; for (var i = 0; i < arguments.length; i++) { var v = arguments[i]; if (isNum(v) && (r === null || v > r)) r = v; } return r; }
  function minOf() { var r = null; for (var i = 0; i < arguments.length; i++) { var v = arguments[i]; if (isNum(v) && (r === null || v < r)) r = v; } return r; }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }
  function stageScale() { var r = $('stage') && $('stage').getBoundingClientRect(); return r && r.width ? r.width / $('stage').offsetWidth : 1; }
  // width of an element's text in stage pixels
  function textWidth(el) { var r = document.createRange(); r.selectNodeContents(el); return r.getBoundingClientRect().width / stageScale(); }
  // Labels in fixed-width slots (longer in some languages): first made a little smaller,
  // then, if still too wide, put on two lines
  function fitText(el) {
    if (!el) return;
    el.style.fontSize = ''; el.style.whiteSpace = ''; el.style.lineHeight = '';
    var base = parseFloat(getComputedStyle(el).fontSize), fs = base;
    var wide = function () { return textWidth(el) > el.getBoundingClientRect().width / stageScale() - 0.5; };
    while (wide() && fs > base * 0.78) { fs -= 0.5; el.style.fontSize = fs + 'px'; }
    if (!wide()) return;
    el.style.whiteSpace = 'normal'; el.style.lineHeight = '1.08';
    fs = base * 0.9; el.style.fontSize = fs + 'px';
    while ((wide() || el.offsetHeight > fs * 1.08 * 2 + 1) && fs > 10) { fs -= 0.5; el.style.fontSize = fs + 'px'; }
  }
  // The same for SVG text that must stay left of x = right
  function fitSvgText(el, right, min) {
    if (!el || !el.getComputedTextLength) return;
    var x = parseFloat(el.getAttribute('x')) || 0, avail = right - x, len = el.getComputedTextLength();
    if (!(len > avail) || avail <= 0) return;
    var fs = parseFloat(el.getAttribute('font-size')) || 16, nfs = Math.max(min, fs * avail / len);
    el.setAttribute('font-size', nfs.toFixed(1));
    if (el.getComputedTextLength() > avail) { el.setAttribute('textLength', avail.toFixed(1)); el.setAttribute('lengthAdjust', 'spacingAndGlyphs'); }
  }
  var DIRS = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
  // (the invisible left-to-right mark keeps "ش 357°" in order next to a number in Arabic/Urdu)
  function compass(d) { return isNum(d) ? _t(DIRS[Math.round(((d % 360) + 360) % 360 / 22.5) % 16]) + '\u200e' : '--'; }
  function now() { return Date.now() / 1000; }
  function ago(ts) {
    if (!isNum(ts) || ts <= 0) return '--';
    var s = now() - ts;
    if (s < 90) return _t('just now');
    if (s < 3600) return _t('{0} min ago', Math.round(s / 60));
    if (s < 86400 * 2) return _t('{0} h ago', Math.round(s / 3600));
    return _t('{0} d ago', Math.round(s / 86400));
  }

  // ---------------------------------------------------------------- units (as weewx-divumwx)
  // Feeds are METRICWX: °C, m/s, mm, mm/h, hPa, km. Conversion happens here, for
  // display only, using the same presets and storage key as weewx-divumwx.
  var UNIT_KEY = 'dashboardUnitSystem';
  var SYSTEMS = {
    uk:       { temp: 'C', wind: 'mph', pressure: 'hpa',  rain: 'mm', dist: 'km' },
    us:       { temp: 'F', wind: 'mph', pressure: 'inhg', rain: 'in', dist: 'mi' },
    metric:   { temp: 'C', wind: 'kmh', pressure: 'hpa',  rain: 'mm', dist: 'km' },
    scandi:   { temp: 'C', wind: 'ms',  pressure: 'hpa',  rain: 'mm', dist: 'km' },
    canada:   { temp: 'C', wind: 'kmh', pressure: 'kpa',  rain: 'mm', dist: 'km' },
    icao:     { temp: 'C', wind: 'kt',  pressure: 'hpa',  rain: 'mm', dist: 'nm' }
  };
  var SYSTEM_LABELS = {
    uk: ['UK', '°C, mph, hPa'], us: ['US', '°F, mph, inHg'], metric: ['Metric', '°C, km/h, hPa'],
    scandi: ['Scandinavian', '°C, m/s, hPa'], canada: ['Canada', '°C, km/h, kPa'],
    icao: ['ICAO', '°C, kt, hPa, NM']
  };
  function loadUnitKey() {
    var k = null;
    try { k = localStorage.getItem(UNIT_KEY); if (k === 'aviation') k = 'icao'; } catch (e) { /* private mode */ }
    // Beaufort is shown as its own reading here; a DivumWX 'beaufort' choice uses metric units
    // (the stored choice is left alone so DivumWX keeps it)
    if (k === 'beaufort') k = 'metric';
    if (!SYSTEMS[k]) k = (S.cfg && SYSTEMS[S.cfg.unit_system]) ? S.cfg.unit_system : 'uk';
    return k;
  }
  function US() { return SYSTEMS[S.unitKey] || SYSTEMS.uk; }
  var BFT_NAMES = ['Calm', 'Light air', 'Light breeze', 'Gentle breeze', 'Moderate breeze', 'Fresh breeze',
    'Strong breeze', 'Near gale', 'Gale', 'Strong gale', 'Storm', 'Violent storm', 'Hurricane'];
  function bft(ms) {
    if (!isNum(ms)) return null;
    var lim = [0.5, 1.5, 3.3, 5.5, 7.9, 10.7, 13.8, 17.1, 20.7, 24.4, 28.4, 32.6];
    for (var i = 0; i < lim.length; i++) if (ms < lim[i]) return i;
    return 12;
  }
  var cv = {
    temp: function (c) { return !isNum(c) ? null : US().temp === 'F' ? c * 9 / 5 + 32 : c; },
    wind: function (ms) {
      if (!isNum(ms)) return null;
      switch (US().wind) { case 'mph': return ms * 2.23694; case 'kmh': return ms * 3.6; case 'kt': return ms * 1.94384; default: return ms; }
    },
    press: function (h) { return !isNum(h) ? null : US().pressure === 'inhg' ? h * 0.0295301 : US().pressure === 'kpa' ? h / 10 : h; },
    rain: function (mm) { return !isNum(mm) ? null : US().rain === 'in' ? mm * 0.0393701 : mm; },
    dist: function (km) { return !isNum(km) ? null : US().dist === 'mi' ? km * 0.621371 : US().dist === 'nm' ? km * 0.539957 : km; }
  };
  var lbl = {
    temp: function () { return US().temp === 'F' ? '°F' : '°C'; },
    wind: function () { return { mph: 'mph', kmh: 'km/h', ms: 'm/s', kt: 'kt' }[US().wind]; },
    press: function () { return { hpa: 'hPa', inhg: 'inHg', kpa: 'kPa' }[US().pressure]; },
    rain: function () { return US().rain === 'in' ? 'in' : 'mm'; },
    rainRate: function () { return US().rain === 'in' ? 'in/h' : 'mm/h'; },
    dist: function () { return { km: 'km', mi: 'mi', nm: 'NM' }[US().dist]; }
  };
  var dec = {
    wind: function () { return 1; },
    press: function () { return US().pressure === 'hpa' ? 1 : 2; },
    rain: function () { return US().rain === 'in' ? 2 : 1; }
  };

  // ---------------------------------------------------------------- data access
  function liveFresh() {
    if (!S.live || !S.live.data) return false;
    return (now() - (S.live.written || 0)) < (S.cfg.live_stale || 120);
  }
  function lv(field) {
    if (!field || !liveFresh()) return undefined;
    var v = S.live.data[field];
    return v === null ? undefined : v;
  }
  function pick(field, archVal) { var v = lv(field); return isNum(v) ? v : (isNum(archVal) ? archVal : null); }
  function F(k) { return S.cfg.fields[k] || k; }
  function availList(list) { return (list || []).filter(function (e) { return isNum(pick(e.field, e.value)); }); }

  // ---------------------------------------------------------------- SVG helpers
  function svg(w, h, inner) { return '<svg xmlns="' + NS + '" viewBox="0 0 ' + w + ' ' + h + '" width="' + w + '" height="' + h + '">' + inner + '</svg>'; }
  function numSpans(v, nd, bigSize, smallSize) {
    if (!isNum(v)) return '<tspan font-size="' + bigSize + '">--</tspan>';
    var s = v.toFixed(nd), i = s.indexOf('.');
    var a = i < 0 ? s : s.slice(0, i), b = i < 0 ? '' : s.slice(i);
    return '<tspan font-size="' + bigSize + '">' + a + '</tspan>' + (b ? '<tspan font-size="' + smallSize + '">' + b + '</tspan>' : '');
  }
  // Big temperature inside a ring: "23.4°" with a small decimal and raised degree sign.
  // Shrinks for wide values (−18.0, 100.4) so they stay inside the ring.
  function ringNum(v, size) {
    if (isNum(v) && v.toFixed(1).length >= 5) size = Math.round(size * 0.8);
    var small = Math.round(size * 0.56), deg = Math.round(size * 0.36);
    return numSpans(v, 1, size, small) + (isNum(v) ? '<tspan font-size="' + deg + '" dy="' + (-Math.round(size * 0.48)) + '">' + lbl.temp() + '</tspan>' : '');
  }
  function bigHtml(v, nd, small) {
    if (!isNum(v)) return '<span class="na">--</span>';
    var s = v.toFixed(nd), i = s.indexOf('.');
    if (i < 0) return s;
    return s.slice(0, i) + '<span style="font-size:' + (small || '.62em') + '">' + s.slice(i) + '</span>';
  }
  // Temperature colour scale (°C). Each stop: [°C, hue, lightness of deep end, lightness of light end].
  // The cold end is lifted in lightness so blue/violet stay readable on the navy background
  // (every stop is at least 3.8:1 against it). The light theme uses deeper shades instead.
  var TEMP_STOPS = [[-20, 262, .70, .80], [-5, 215, .62, .72], [5, 192, .52, .62], [12, 150, .48, .60],
                    [18, 52, .52, .62], [24, 32, .52, .62], [30, 15, .55, .65], [38, 0, .58, .68]];
  function tempScale(c) {
    var st = TEMP_STOPS, A = st[0], B = st[0], f = 0;
    if (!isNum(c)) return { h: 30, ld: .52, ll: .62 };
    if (c >= st[st.length - 1][0]) A = B = st[st.length - 1];
    else if (c > st[0][0]) {
      for (var i = 0; i < st.length - 1; i++) {
        if (c <= st[i + 1][0]) { A = st[i]; B = st[i + 1]; f = (c - A[0]) / (B[0] - A[0]); break; }
      }
    }
    return { h: A[1] + f * (B[1] - A[1]), ld: A[2] + f * (B[2] - A[2]), ll: A[3] + f * (B[3] - A[3]) };
  }
  // Console-style ring: a gradient from a lighter to a deeper shade of the temperature colour
  function ringGrad(id, c) {
    var t = tempScale(c), light = S.opts && S.opts.theme === 'light';
    var ld = light ? .44 : t.ld, ll = light ? .54 : t.ll;
    var hd = t.h > 40 ? t.h - 22 : t.h - 10;   // cold hues shift less so blue doesn't turn violet early
    var pct = function (v) { return (v * 100).toFixed(0) + '%'; };
    return '<linearGradient id="' + id + '" x1="0" y1="0" x2="1" y2="1">' +
      '<stop offset="0" stop-color="hsl(' + (t.h + 18).toFixed(0) + ',100%,' + pct(ll) + ')"/>' +
      '<stop offset="1" stop-color="hsl(' + hd.toFixed(0) + ',100%,' + pct(ld) + ')"/></linearGradient>';
  }

  // ---------------------------------------------------------------- icons
  var I = {
    battery: function (level, low, w) {
      w = w || 12; var h = w * 2;
      var col = low ? 'var(--bad)' : 'var(--good)';
      var fill = clamp(level, 0.08, 1) * (h - 6);
      var x = low ? '<path d="M3 ' + (h * .35) + 'L' + (w - 3) + ' ' + (h * .75) + 'M' + (w - 3) + ' ' + (h * .35) + 'L3 ' + (h * .75) + '" stroke="var(--bad)" stroke-width="2.2"/>' : '';
      return svg(w, h + 2, '<rect x="' + (w * .3) + '" y="0" width="' + (w * .4) + '" height="3" rx="1" fill="' + col + '"/>' +
        '<rect x="1" y="3" width="' + (w - 2) + '" height="' + (h - 2) + '" rx="2.5" fill="none" stroke="' + col + '" stroke-width="1.6"/>' +
        '<rect x="3" y="' + (h + 1 - 2 - fill) + '" width="' + (w - 6) + '" height="' + fill + '" rx="1" fill="' + col + '"/>' + x);
    },
    signal: function (n, sz) {
      sz = sz || 22; if (!isNum(n)) return '';
      var r = '';
      for (var i = 0; i < 4; i++) {
        var bh = 5 + i * 5, on = i < n;
        r += '<rect x="' + (2 + i * 6) + '" y="' + (22 - bh) + '" width="4" height="' + bh + '" rx="1" fill="' + (on ? 'currentColor' : 'var(--fg-faint)') + '" opacity="' + (on ? 1 : .45) + '"/>';
      }
      return '<span class="sig">' + svg(sz, sz * 22 / 24, '<g transform="scale(' + (sz / 24) + ')">' + r + '</g>') + '</span>';
    },
    wifi: function (color) {
      return svg(34, 26, '<g transform="scale(.77)"><g fill="none" stroke="' + color + '" stroke-width="4" stroke-linecap="round">' +
        '<path d="M4 12 Q22 -3 40 12"/><path d="M10 19 Q22 9 34 19"/><path d="M16 26 Q22 21 28 26"/></g>' +
        '<circle cx="22" cy="31" r="3" fill="' + color + '"/></g>');
    },
    leakDrop: function (n, state) {
      var col = state === 1 ? '#ff4b4b' : '#2fbf55';
      return svg(24, 30, '<path d="M12 1 C8 9 2 13 2 19 a10 10 0 0 0 20 0 C22 13 16 9 12 1Z" fill="' + col + '"/>' +
        '<path d="M8 13 q-2 4 0 7" stroke="#fff" stroke-opacity=".6" stroke-width="2" fill="none" stroke-linecap="round"/>' +
        (n ? '<text x="12" y="24" text-anchor="middle" font-size="11" font-weight="700" fill="#fff">' + esc(n) + '</text>' : ''));
    },
    radar: function () {
      return svg(20, 20, '<g fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10" cy="10" r="8.5"/><circle cx="10" cy="10" r="5"/></g>' +
        '<path d="M10 10 L10 1.5 A8.5 8.5 0 0 1 17.4 5.8 Z" fill="currentColor" opacity=".55"/><circle cx="10" cy="10" r="1.6" fill="currentColor"/>' +
        '<circle cx="14" cy="6.5" r="1.4" fill="#ffd23a"/>');
    },
    bolt: function () {
      return svg(22, 30, '<path d="M14 1 L3 17 H11 L7 29 L20 11 H12 L16 1Z" fill="#ffc21a" stroke="#ff8a00" stroke-width="1.2" stroke-linejoin="round"/>');
    },
    rainIcon: function () {
      // big drop with three small drops above, as on the console
      var small = function (x, y) { return '<path d="M' + x + ' ' + y + ' c-3 5 -5 7 -5 10 a5 5 0 0 0 10 0 c0 -3 -2 -5 -5 -10z" fill="#5fb7ff"/>'; };
      return svg(80, 96, small(22, 2) + small(40, 0) + small(58, 2) +
        '<path d="M40 24 C32 42 16 54 16 70 a24 24 0 0 0 48 0 C64 54 48 42 40 24Z" fill="none" stroke="var(--fg-dim)" stroke-width="3"/>');
    },
    sun: function (r) {
      r = r || 20; var rays = '';
      for (var i = 0; i < 12; i++) { var a = i * Math.PI / 6; rays += '<line x1="' + (Math.cos(a) * r * 1.2).toFixed(1) + '" y1="' + (Math.sin(a) * r * 1.2).toFixed(1) + '" x2="' + (Math.cos(a) * r * 1.6).toFixed(1) + '" y2="' + (Math.sin(a) * r * 1.6).toFixed(1) + '"/>'; }
      return '<g><g stroke="#ffd23a" stroke-width="' + (r / 7).toFixed(1) + '" stroke-linecap="round">' + rays + '</g><circle r="' + r + '" fill="url(#sunG)"/></g>';
    },
    sunDefs: '<radialGradient id="sunG" cx=".4" cy=".4" r=".7"><stop offset="0" stop-color="#fff6b0"/><stop offset=".6" stop-color="#ffd23a"/><stop offset="1" stop-color="#ff9a1a"/></radialGradient>',
    moon: function (fullness, waxing, r) {
      var k = clamp((fullness || 0) / 100, 0, 1);
      var rx = Math.abs(2 * k - 1) * r, sweep = k < 0.5 ? 0 : 1;
      var lit = k <= 0.01 ? '' : k >= 0.99 ? '<circle r="' + r + '" fill="url(#moonLit)"/>' :
        '<path d="M0 ' + (-r) + ' A' + r + ' ' + r + ' 0 0 1 0 ' + r + ' A' + rx.toFixed(2) + ' ' + r + ' 0 0 ' + sweep + ' 0 ' + (-r) + 'Z" fill="url(#moonLit)"' + (waxing ? '' : ' transform="scale(-1,1)"') + '/>';
      return '<defs><radialGradient id="moonLit" cx=".4" cy=".35" r=".8"><stop offset="0" stop-color="#fbf8ea"/><stop offset="1" stop-color="#c8c2a6"/></radialGradient></defs>' +
        '<circle r="' + r + '" fill="#2b2d3c"/>' + lit;
    },
    // Clear-night crescent with two small stars
    moonNight: function (r) {
      return '<g><mask id="crMask"><circle r="' + r + '" fill="#fff"/><circle cx="' + (r * .5) + '" cy="' + (-r * .3) + '" r="' + (r * .82) + '" fill="#000"/></mask>' +
        '<circle r="' + r + '" fill="url(#nightMoon)" mask="url(#crMask)"/></g>';
    },
    forecast: function (kind, w, night) {
      w = w || 80;
      var cloud = function (dx, dy, s, c) { return '<g transform="translate(' + dx + ',' + dy + ') scale(' + s + ')"><path d="M14 40 a12 12 0 0 1 2 -24 a17 17 0 0 1 32 -4 a13 13 0 0 1 6 28 Z" fill="' + (c || 'url(#cloudG)') + '"/></g>'; };
      var defs = '<defs><linearGradient id="cloudG" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#9fd0ff"/></linearGradient>' +
        '<linearGradient id="dcloudG" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#a3aed8"/><stop offset="1" stop-color="#5a6491"/></linearGradient>' + I.sunDefs +
        '<radialGradient id="nightMoon" cx=".35" cy=".6" r=".8"><stop offset="0" stop-color="#fff8d8"/><stop offset="1" stop-color="#e3d48a"/></radialGradient></defs>';
      var star = function (x, y, r) { return '<path d="M' + x + ' ' + (y - r) + ' L' + (x + r * .3) + ' ' + (y - r * .3) + ' L' + (x + r) + ' ' + y + ' L' + (x + r * .3) + ' ' + (y + r * .3) + ' L' + x + ' ' + (y + r) + ' L' + (x - r * .3) + ' ' + (y + r * .3) + ' L' + (x - r) + ' ' + y + ' L' + (x - r * .3) + ' ' + (y - r * .3) + 'Z" fill="#fff6c8"/>'; };
      var g = '';
      if (kind === 'sunny') g = night ? '<g transform="translate(40,36) rotate(-20)">' + I.moonNight(22) + '</g>' + star(64, 14, 5) + star(70, 40, 3.5) + star(18, 16, 3)
        : '<g transform="translate(42,34)">' + I.sun(18) + '</g>';
      else if (kind === 'partly') g = (night ? '<g transform="translate(54,22) rotate(-20)">' + I.moonNight(15) + '</g>' + star(24, 10, 3.5)
        : '<g transform="translate(50,24)">' + I.sun(14) + '</g>') + cloud(4, 18, 1.15);
      else if (kind === 'cloudy') g = cloud(20, 2, 0.9, 'url(#dcloudG)') + cloud(2, 16, 1.1);
      else if (kind === 'rain') g = cloud(6, 0, 1.15, 'url(#dcloudG)') + '<g stroke="#5fb7ff" stroke-width="3" stroke-linecap="round"><line x1="26" y1="54" x2="22" y2="64"/><line x1="40" y1="54" x2="36" y2="64"/><line x1="54" y1="54" x2="50" y2="64"/></g>';
      else if (kind === 'storm') g = cloud(6, 0, 1.15, 'url(#dcloudG)') + '<path d="M42 44 L32 58 H40 L35 68 L52 52 H43 L48 44Z" fill="#ffb21a" stroke="#ff7a00"/>';
      else return '';
      return svg(w, w * 70 / 84, '<g transform="scale(' + (w / 84) + ')">' + defs + g + '</g>');
    },
    trendArrow: function (deg) {
      return svg(56, 56, '<circle cx="28" cy="28" r="25" fill="none" stroke="var(--fg)" stroke-width="2.5"/>' +
        '<g style="transform-origin:28px 28px;transform:rotate(' + deg + 'deg);transition:transform 1s"><path d="M14 28 H40 M32 19 L41 28 L32 37" fill="none" stroke="var(--fg)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/></g>');
    },
    tool: {
      graphs: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 20h16v2H4zM5 10h3v8H5zM10.5 6h3v12h-3zM16 13h3v5h-3z"/></svg>',
      theme: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 3a9 9 0 0 0 0 18z" fill="currentColor"/></svg>',
      channel: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="2"/><text x="12" y="15.6" text-anchor="middle" font-size="9" font-weight="700" fill="currentColor" font-family="sans-serif">CH</text></svg>',
      sensors: '<svg viewBox="0 0 24 24"><rect x="9" y="1.8" width="6" height="2.6" rx="1" fill="currentColor"/><rect x="6" y="4" width="12" height="18.2" rx="2.4" fill="none" stroke="currentColor" stroke-width="2"/><rect x="8.6" y="12" width="6.8" height="7.6" rx="1" fill="currentColor"/><rect x="8.6" y="7.6" width="6.8" height="3.2" rx="1" fill="currentColor" opacity=".45"/></svg>',
      refresh: '<svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" d="M20 12a8 8 0 1 1-2.6-5.9"/><path fill="currentColor" d="M21 3v6h-6z"/></svg>',
      settings: '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M19.4 13a7.5 7.5 0 0 0 0-2l2.1-1.6-2-3.5-2.5 1a7.4 7.4 0 0 0-1.7-1L15 3h-4l-.4 2.7a7.4 7.4 0 0 0-1.7 1l-2.5-1-2 3.5L6.6 11a7.5 7.5 0 0 0 0 2l-2.1 1.6 2 3.5 2.5-1a7.4 7.4 0 0 0 1.7 1L11 21h4l.4-2.7a7.4 7.4 0 0 0 1.7-1l2.5 1 2-3.5zM13 15.5A3.5 3.5 0 1 1 13 8.5a3.5 3.5 0 0 1 0 7z"/></svg>'
    }
  };

  // ---------------------------------------------------------------- batteries / signal
  function battState(kind, v) {
    if (!isNum(v)) return null;
    kind = String(kind || 'binary');
    if (kind.indexOf('volt') === 0) {
      var low = parseFloat(kind.split(':')[1]) || 1.2;
      var lo = low * 0.92, hi = low * 1.33;
      return { level: clamp((v - lo) / (hi - lo), 0, 1), low: v < low, txt: v.toFixed(2) + ' V' };
    }
    if (kind === 'level') {
      if (v >= 6) return { level: 1, low: false, txt: 'DC' };
      return { level: clamp(v / 5, 0, 1), low: v <= 1, txt: v + '/5' };
    }
    return { level: v ? 0.15 : 1, low: !!v, txt: v ? _t('battery|Low') : _t('battery|OK') };
  }
  function sensorById(id) {
    var list = S.cfg.sensors || [];
    for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
    return null;
  }
  // The console only shows signal bars next to a reading; the battery appears only when low.
  // id is a sensor id from weewx-EcowittGateway naming, e.g. 'ws90', 'wh51_ch5', 'wn31_ch2'.
  function sigFor(id) {
    var s = id && sensorById(id);
    if (!s) return '';
    var b = battState(s.kind, lv(s.batt)), sig = lv(s.sig);
    return (b && b.low ? I.battery(b.level, true, 11) : '') + (isNum(sig) ? I.signal(sig, 20) : '');
  }
  function lowSensors() {
    return (S.cfg.sensors || []).filter(function (s) { var b = battState(s.kind, lv(s.batt)); return b && b.low; });
  }

  // ---------------------------------------------------------------- header / clock
  // ---- station time: every time on the page is shown in the station's time zone
  // (sent by the report), whatever the time zone of the device viewing the page.
  // W(t) gives a Date whose UTC fields are the wall-clock time; read it with getUTC*().
  var zoneOK = {}, tzFmt = null, tzFmtZone = null;
  function zoneValid(z) {
    if (!(z in zoneOK)) { try { new Intl.DateTimeFormat('en-US', { timeZone: z }); zoneOK[z] = true; } catch (e) { zoneOK[z] = false; } }
    return zoneOK[z];
  }
  function stationZone() { var z = S.arch && S.arch.tz; return z && zoneValid(z) ? z : null; }
  function tzOffset(t) {
    var z = stationZone();
    if (z) {
      if (tzFmtZone !== z) {
        tzFmt = new Intl.DateTimeFormat('en-US', { timeZone: z, hourCycle: 'h23', year: 'numeric', month: 'numeric',
          day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' });
        tzFmtZone = z;
      }
      var p = {};
      tzFmt.formatToParts(new Date(Math.floor(t) * 1000)).forEach(function (x) { p[x.type] = x.value; });
      return Date.UTC(+p.year, +p.month - 1, +p.day, (+p.hour) % 24, +p.minute, +p.second) / 1000 - Math.floor(t);
    }
    // zone name not known to this browser: the station's UTC offset at report time
    // (the device's own offset is used only until the first report has loaded)
    return (S.arch && isNum(S.arch.tzOffset)) ? S.arch.tzOffset : -new Date(t * 1000).getTimezoneOffset() * 60;
  }
  function W(t) { return new Date((Math.floor(t) + tzOffset(t)) * 1000); }
  function dayIndex(t) { return Math.floor((t + tzOffset(t)) / 86400); }

  var fmtCache = {};
  function dtf(opts) {             // formats a W() date, so always read in UTC
    var o = {}; for (var k0 in opts) o[k0] = opts[k0]; o.timeZone = 'UTC';
    var k = JSON.stringify(o);
    if (!fmtCache[k]) { try { fmtCache[k] = new Intl.DateTimeFormat(locale(), o); } catch (e) { fmtCache[k] = new Intl.DateTimeFormat(undefined, o); } }
    return fmtCache[k];
  }
  function wallHM(d, secs) {
    var h = d.getUTCHours(), m = ('0' + d.getUTCMinutes()).slice(-2), s = ('0' + d.getUTCSeconds()).slice(-2);
    if (S.opts.clock24) return ('0' + h).slice(-2) + ':' + m + (secs ? ':' + s : '');
    return (h < 12 ? _t('am') : _t('pm')) + ' ' + (h % 12 || 12) + ':' + m + (secs ? ':' + s : '');
  }
  function hhmm(ts, secs) { return isNum(ts) ? wallHM(W(ts), secs) : '--:--'; }
  function tickClock() {
    var t = now(), d = W(t);
    $('date').textContent = dtf({ weekday: 'short' }).format(d) + ', ' + dtf({ day: 'numeric' }).format(d) + ' ' +
      dtf({ month: 'short' }).format(d) + ' ' + dtf({ year: 'numeric' }).format(d);
    $('time').textContent = wallHM(d, S.opts.seconds);
    if (Math.floor(t) % 10 === 0) safe(renderHeader, 'header');
  }
  function renderHeader() {
    var col, tip, a = S.arch;
    var archAge = a ? now() - a.archiveTime : Infinity, maxArch = ((a && a.interval) || 300) * 2 + 180;
    if (liveFresh()) { col = 'var(--good)'; tip = _t('Live data, {0} s old', Math.round(now() - S.live.written)); }
    else if (archAge < maxArch) { col = 'var(--warn)'; tip = _t('Archive data only ({0} min old)', Math.round(archAge / 60)); }
    else { col = 'var(--bad)'; tip = _t('Data is stale'); }
    var h = '<span title="' + esc(tip) + '">' + I.wifi(col) + '</span>';
    ['ws90', 'ws85', 'ws80', 'wh69', 'wh65'].some(function (b) { var x = sigFor(b); if (x) { h += x; return true; } return false; });
    var low = lowSensors();
    if (low.length) h += '<span class="tap" id="low-batt" title="' + esc(_t('Low battery: {0}', low.map(function (s) { return tl(s.label); }).join(', '))) + '">' + I.battery(0.1, true, 12) + '</span>';
    if (S.cfg.windy_radar && isNum(S.arch.lat) && isNum(S.arch.lon)) {
      var url = windyUrl(S.cfg.windy_url);
      h += S.cfg.windy_mode === 'link'
        ? '<a class="chip radar" href="' + esc(url) + '" target="_blank" rel="noopener" title="' + esc(_t('Windy radar for this station')) + '">' + I.radar() + esc(_t('Radar')) + '</a>'
        : '<button class="chip radar" id="radar-btn" title="' + esc(_t('Windy radar for this station')) + '">' + I.radar() + esc(_t('Radar')) + '</button>';
    }
    var links = S.cfg.links || {};
    Object.keys(links).forEach(function (k) { h += '<a class="chip" href="' + esc(links[k]) + '" target="_blank" rel="noopener">' + esc(k) + '</a>'; });
    $('hdr-left').innerHTML = h;
    // title: skin.conf [Extras] title, or the station name ([Station] location in weewx.conf)
    var title = S.cfg.title || a.station || '';
    if ($('hdr-title').textContent !== title) $('hdr-title').textContent = title;
    var lb = $('low-batt'); if (lb) lb.onclick = showSensors;
    var rb = $('radar-btn'); if (rb) rb.onclick = showRadar;

    var leaks = availList(a.leak);
    $('hdr-leak').innerHTML = leaks.map(function (e) { return '<span title="' + esc(_t('Leak {0}', e.label)) + '">' + I.leakDrop(e.label, pick(e.field, e.value) ? 1 : 0) + '</span>'; }).join('');
  }

  // ---------------------------------------------------------------- outdoor temperature
  function renderOut() {
    var a = S.arch, fm = S.cfg.fields;
    var t = pick(fm.outTemp, a.out.temp), hi = maxOf(a.out.hi, t), lo = minOf(a.out.lo, t);
    var cx = 170, cy = 170, r = 146;
    var raw = t; t = cv.temp(t); hi = cv.temp(hi); lo = cv.temp(lo);
    var g = '<defs>' + ringGrad('gOut', raw) + '</defs>' +
      '<circle cx="' + cx + '" cy="' + cy + '" r="' + r + '" fill="none" stroke="url(#gOut)" stroke-width="24"/>' +
      '<text x="' + cx + '" y="' + (cy - 66) + '" text-anchor="middle" font-size="34" font-weight="500" fill="var(--fg)">↑ ' + fmt(hi, 1) + lbl.temp() + '</text>' +
      '<text x="' + cx + '" y="' + (cy + 38) + '" text-anchor="middle" font-weight="700" fill="var(--fg)">' + ringNum(t, 112) + '</text>' +
      '<text x="' + cx + '" y="' + (cy + 100) + '" text-anchor="middle" font-size="34" font-weight="500" fill="var(--fg)">↓ ' + fmt(lo, 1) + lbl.temp() + '</text>';
    $('g-out').innerHTML = svg(340, 340, g);
  }

  // ---------------------------------------------------------------- wind
  function buildWind() {
    var cx = 180, cy = 170, r = 150, ticks = '';
    for (var d = 0; d < 360; d += 6) {
      var major = d % 30 === 0, a = (d - 90) * Math.PI / 180, r1 = r - (major ? 22 : 13), r2 = r - 6;
      ticks += '<line x1="' + (cx + Math.cos(a) * r1).toFixed(1) + '" y1="' + (cy + Math.sin(a) * r1).toFixed(1) + '" x2="' + (cx + Math.cos(a) * r2).toFixed(1) + '" y2="' + (cy + Math.sin(a) * r2).toFixed(1) + '" stroke-width="' + (major ? 2.6 : 1.4) + '"/>';
    }
    var g = '<circle cx="' + cx + '" cy="' + cy + '" r="' + r + '" fill="var(--dial)" stroke="var(--wind)" stroke-width="4"/>' +
      '<g stroke="var(--fg-dim)" opacity=".85">' + ticks + '</g>' +
      // 10-minute average direction: a smaller grey arrow at the rim, behind the main pointer
      '<g id="w-avg" style="transform-origin:' + cx + 'px ' + cy + 'px;transition:transform 1.2s">' +
      '<path d="M' + cx + ' ' + (cy - r + 17) + ' L' + (cx - 10) + ' ' + (cy - r - 7) + ' L' + cx + ' ' + (cy - r - 1) + ' L' + (cx + 10) + ' ' + (cy - r - 7) + ' Z" fill="var(--avg-arrow)"/></g>' +
      '<g id="w-ptr" style="transform-origin:' + cx + 'px ' + cy + 'px;transition:transform 1.2s cubic-bezier(.3,.7,.3,1)">' +
      '<path d="M' + cx + ' ' + (cy - r + 30) + ' L' + (cx - 17) + ' ' + (cy - r - 12) + ' L' + cx + ' ' + (cy - r - 2) + ' L' + (cx + 17) + ' ' + (cy - r - 12) + ' Z" fill="var(--wind)" stroke="#e4f3ff" stroke-width="1.5"/></g>' +
      '<text id="w-dir" x="' + cx + '" y="' + (cy - 62) + '" text-anchor="middle" font-size="32" font-weight="500" fill="var(--fg)"></text>' +
      '<text id="w-spd" x="' + cx + '" y="' + (cy + 34) + '" text-anchor="middle" font-weight="700" fill="var(--fg)"></text>' +
      '<text id="w-gust" x="' + cx + '" y="' + (cy + 78) + '" text-anchor="middle" font-size="30" font-weight="500" fill="var(--fg)"></text>' +
      '<text id="w-unit" x="' + cx + '" y="' + (cy + 112) + '" text-anchor="middle" font-size="26" font-weight="500" fill="var(--fg-dim)"></text>';
    $('g-wind').innerHTML = svg(360, 340, g);
  }
  function rotateTo(el, key, target) {
    if (!isNum(target)) return;
    var cur = S[key];
    cur = isNum(cur) ? cur + ((((target - cur) % 360) + 540) % 360 - 180) : target;
    S[key] = cur;
    el.style.transform = 'rotate(' + cur + 'deg)';
  }
  function windAvg10() {
    var buf = S.windBuf;
    if (buf.length >= 3 && now() - buf[0].t >= 240) {
      var n = 0, sum = 0, x = 0, y = 0;
      buf.forEach(function (s) {
        if (!isNum(s.v)) return; n++; sum += s.v;
        if (isNum(s.d)) { x += s.v * Math.sin(s.d * Math.PI / 180); y += s.v * Math.cos(s.d * Math.PI / 180); }
      });
      var dl = lv(S.cfg.fields.windDir10);
      if (n) return { v: sum / n, d: isNum(dl) ? dl : (x || y) ? (Math.atan2(x, y) * 180 / Math.PI + 360) % 360 : null };
    }
    var d10 = lv(S.cfg.fields.windDir10);
    return { v: S.arch.wind.avg10, d: isNum(d10) ? d10 : S.arch.wind.avg10dir };
  }
  function renderWind() {
    var a = S.arch, fm = S.cfg.fields;
    var spd = pick(fm.windSpeed, a.wind.speed), gust = pick(fm.windGust, a.wind.gust);
    var dir = lv(fm.windDir);
    if (!isNum(dir)) dir = (liveFresh() && fm.windDir in S.live.data) ? null : a.wind.dir;
    var avg = windAvg10();
    $('w-dir').textContent = isNum(dir) ? compass(dir) + '  ' + Math.round(dir) + '°' : _t('Calm');
    $('w-spd').innerHTML = numSpans(cv.wind(spd), dec.wind(), 100, 100);
    $('w-gust').textContent = _t('Gust') + ' ' + fmt(cv.wind(gust), dec.wind());
    $('w-unit').textContent = lbl.wind();
    rotateTo($('w-ptr'), 'windAngle', dir);
    $('w-ptr').style.opacity = isNum(dir) ? 1 : 0.25;
    if (isNum(avg.d)) rotateTo($('w-avg'), 'windAvgAngle', avg.d);
  }

  // ---------------------------------------------------------------- soil / leaf + lightning line
  function groundList() {
    var out = [];
    availList(S.arch.soil).forEach(function (e) { out.push({ e: e, kind: _t('Soil Moisture') }); });
    availList(S.arch.leaf).forEach(function (e) { out.push({ e: e, kind: _t('Leaf Wetness') }); });
    return out;
  }
  function renderTopRight() {
    var a = S.arch, fm = S.cfg.fields, gl = groundList(), el = $('soil');
    if (gl.length) {
      var g = gl[S.groundIdx % gl.length];
      var label = String(g.e.label).replace(/^(WH51|WH52|WN35)\s+/, '');
      el.innerHTML = sigFor(g.e.sensor) +
        '<span>' + esc(tl(label)) + ' ' + esc(g.kind) + ': ' + fmt(pick(g.e.field, g.e.value), 0) + ' %</span>';
      el.onclick = function () { S.groundIdx = (S.groundIdx + 1) % gl.length; renderTopRight(); };
    } else el.innerHTML = '';

    var last = pick(fm.lightning_time, a.lightning.last), dist = pick(fm.lightning_dist, a.lightning.dist),
      cnt = pick(fm.lightningcount, a.lightning.countDay);
    $('light').innerHTML = (isNum(last) || isNum(dist) || isNum(cnt)) ?
      sigFor('wh57') + I.bolt() + '<div class="lines"><span>' + ago(last) + '</span><span>' + esc(_t('Dis')) + ': ' + fmt(cv.dist(dist), 0) + ' ' + esc(lbl.dist()) +
      '&nbsp;&nbsp;' + esc(_t('Cnt')) + ': ' + fmt(cnt, 0) + '</span></div>' : '';
  }

  // ---------------------------------------------------------------- channel T&H
  function channels() {
    return (S.arch.channels || []).filter(function (c) { return isNum(pick(c.temp, c.value)) || isNum(pick(c.hum, c.humValue)); });
  }
  function renderChannel() {
    var list = channels(), el = $('g-ch');
    if (!list.length) {
      el.innerHTML = '';
      var b0 = document.querySelector('#toolbar button[data-act=channel]'); if (b0) b0.classList.add('off');
      return;
    }
    var c = list[S.chIdx % list.length];
    var t = pick(c.temp, c.value), hi = maxOf(c.hi, t), lo = minOf(c.lo, t);
    var h = pick(c.hum, c.humValue);
    var r = 86, C = 2 * Math.PI * r, frac = isNum(h) ? clamp(h / 100, 0, 1) : 0;
    var raw = t; t = cv.temp(t); hi = cv.temp(hi); lo = cv.temp(lo);
    var g = '<defs>' + ringGrad('gCh', raw) +
      '<linearGradient id="gHum" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="var(--hum1)"/><stop offset="1" stop-color="var(--hum2)"/></linearGradient></defs>';
    if (isNum(t)) {
      g += '<circle cx="110" cy="112" r="' + r + '" fill="none" stroke="url(#gCh)" stroke-width="16"/>' +
        '<text x="110" y="70" text-anchor="middle" font-size="22" font-weight="500" fill="var(--fg-dim)">↑ ' + fmt(hi, 1) + lbl.temp() + '</text>' +
        '<text x="110" y="134" text-anchor="middle" font-weight="700" fill="var(--fg)">' + ringNum(t, 64) + '</text>' +
        '<text x="110" y="172" text-anchor="middle" font-size="22" font-weight="500" fill="var(--fg-dim)">↓ ' + fmt(lo, 1) + lbl.temp() + '</text>' +
        '<text x="110" y="232" text-anchor="middle" font-size="22" font-weight="700" fill="var(--fg-dim)">' + esc(_t('Temperature')) + '</text>';
    }
    if (isNum(h)) {
      g += '<circle cx="340" cy="112" r="' + r + '" fill="none" stroke="var(--ring-track)" stroke-width="16"/>' +
        '<circle cx="340" cy="112" r="' + r + '" fill="none" stroke="url(#gHum)" stroke-width="16" stroke-linecap="round" transform="rotate(-90 340 112)" stroke-dasharray="' + (C * frac).toFixed(1) + ' ' + C.toFixed(1) + '"/>' +
        '<text x="340" y="134" text-anchor="middle" font-weight="700" fill="var(--fg)"><tspan font-size="64">' + fmt(h, 0) + '</tspan><tspan font-size="34"> %</tspan></text>' +
        '<text x="340" y="232" text-anchor="middle" font-size="22" font-weight="700" fill="var(--fg-dim)">' + esc(_t('Humidity')) + '</text>';
    }
    // channel name and a console-style "CH" button
    g += '<text x="225" y="266" text-anchor="middle" font-size="22" font-weight="700" fill="var(--fg)">' + esc(tl(c.label)) + '</text>' +
      (list.length > 1 ? '<g transform="translate(452,214)"><circle r="16" fill="none" stroke="var(--fg)" stroke-width="2"/><text y="6" text-anchor="middle" font-size="15" font-weight="700" fill="var(--fg)">CH</text></g>' : '');
    var sig = sigFor(c.sensor);
    el.innerHTML = svg(474, 272, g) + (sig ? '<div style="position:absolute;left:14px;top:244px">' + sig + '</div>' : '');
    var chb = document.querySelector('#toolbar button[data-act=channel]');
    if (chb) { chb.classList.toggle('off', list.length < 2); chb.title = list.length < 2 ? _t('Only one channel reporting') : _t('Next channel ({0})', list.length); }
  }
  // CH (footer button, the CH badge or a tap on the rings): next temperature/humidity channel
  function nextChannel() {
    var list = channels();
    if (list.length < 2) {
      toast(list.length ? _t('Only one temperature/humidity channel is reporting ({0}). WN31 sensors on CH1–CH8 appear here automatically.', tl(list[0].label))
        : _t('No temperature/humidity channels are reporting.'));
      return;
    }
    S.chIdx = (S.chIdx + 1) % list.length; lsSet('chIdx', S.chIdx); renderChannel();
    var el = $('g-ch'); el.classList.remove('flash'); void el.offsetWidth; el.classList.add('flash');
    toast(tl(list[S.chIdx].label) + '  (' + _t('{0} of {1}', S.chIdx + 1, list.length) + ')', 1500);
  }
  function toast(msg, ms) {
    var t = $('toast'); if (!t) return;
    t.textContent = msg; t.classList.add('show');
    clearTimeout(S.toastT); S.toastT = setTimeout(function () { t.classList.remove('show'); }, ms || 3500);
  }

  // ---------------------------------------------------------------- stats + AQ
  function feelsLike(t, h, v) {
    var c = t; if (!isNum(c)) return null;
    var k = isNum(v) ? v * 3.6 : null, out = c;
    if (c >= 26.7 && isNum(h)) {
      var T = c * 9 / 5 + 32, R = h;
      var hi = -42.379 + 2.04901523 * T + 10.14333127 * R - .22475541 * T * R - .00683783 * T * T - .05481717 * R * R + .00122874 * T * T * R + .00085282 * T * R * R - .00000199 * T * T * R * R;
      out = (hi - 32) * 5 / 9;
    } else if (c <= 10 && isNum(k) && k > 4.8) {
      out = 13.12 + 0.6215 * c - 11.37 * Math.pow(k, 0.16) + 0.3965 * c * Math.pow(k, 0.16);
    }
    return out;
  }
  // Vapour pressure deficit in kPa from °C and %RH (always kPa, whatever the unit preset)
  function vpdKpa(tC, rh) {
    if (!isNum(tC) || !isNum(rh)) return null;
    return 0.6108 * Math.exp(17.27 * tC / (tC + 237.3)) * (1 - rh / 100);
  }
  function renderStats() {
    var a = S.arch, fm = S.cfg.fields;
    var t = pick(fm.outTemp, a.out.temp), h = pick(fm.outHumidity, a.out.hum), spd = pick(fm.windSpeed, a.wind.speed);
    var feels = isNum(lv(fm.feelslike)) ? lv(fm.feelslike) : (liveFresh() && isNum(lv(fm.outTemp))) ? feelsLike(t, h, spd) : a.out.feels;
    var avg = windAvg10();
    var items = [
      [_t('Feels Like'), fmt(cv.temp(feels), 1) + '<span class="unit">' + lbl.temp() + '</span>'],
      [_t('Dewpoint'), fmt(cv.temp(pick(fm.dewpoint, a.out.dew)), 1) + '<span class="unit">' + lbl.temp() + '</span>'], [_t('Humidity'), fmt(h, 0) + '%'],
      [_t('VPD'), fmt(vpdKpa(t, h), 2) + '<span class="unit">kPa</span>'],
      [_t('10Min.Avg'), (isNum(avg.d) ? compass(avg.d) + ' ' : '') + fmt(cv.wind(avg.v), dec.wind())],
      [_t('Max Daily Gust'), fmt(cv.wind(maxOf(a.wind.maxGust, lv(fm.maxdailygust), lv(fm.windGust))), dec.wind())],
      // Beaufort force of the current wind (always Bft, whatever the unit preset)
      [_t('Beaufort'), isNum(bft(spd)) ? bft(spd) + '<span class="unit">Bft</span>' : '--', isNum(bft(spd)) ? _t(BFT_NAMES[bft(spd)]) : '']
    ];
    $('stats').innerHTML = items.map(function (it) {
      return '<div' + (it[2] ? ' title="' + esc(it[2]) + '"' : '') + '><div class="k">' + esc(it[0]) + '</div><div class="v">' + it[1] + '</div></div>';
    }).join('');
    // Labels as on the console; only when a row's labels can't sit side by side (longer
    // words in some languages) do the columns become equal and the labels fitted to them
    var st = $('stats'), ks = Array.prototype.slice.call(st.querySelectorAll('.k'));
    st.classList.remove('tight');
    ks.forEach(function (k) { k.style.fontSize = ''; k.style.whiteSpace = ''; k.style.lineHeight = ''; });
    var sc = stageScale(), rects = ks.map(function (k) { var r = document.createRange(); r.selectNodeContents(k); return r.getBoundingClientRect(); });
    var tight = rects.some(function (r, i) {
      var n = rects[i + 1];       // the next label in the same row
      return n && Math.abs(n.top - r.top) < 4 && (n.left - r.right) / sc < 6;
    });
    if (tight) { st.classList.add('tight'); ks.forEach(fitText); }
  }
  function pmColor(v) {
    if (!isNum(v)) return 'var(--fg-faint)';
    return v <= 12 ? '#2fbf55' : v <= 35.4 ? '#f2d21c' : v <= 55.4 ? '#ff8a1a' : v <= 150.4 ? '#ff3b3b' : v <= 250.4 ? '#a34ad6' : '#8a1c2b';
  }
  function renderAQ() {
    var a = S.arch, fm = S.cfg.fields, h = '';
    var pms = availList(a.aq.pm);
    if (pms.length) {
      var e = pms[S.pmIdx % pms.length], v = pick(e.field, e.value);
      h += '<span class="grp tap" id="aq-pm" title="' + esc(e.label) + '"><i class="aqi-dot" style="background:' + pmColor(v) + '"></i>PM2.5: ' + fmt(v, 0) +
        ' <span class="unit">' + 'µg/m³' + '</span></span>';
    }
    var co2 = pick(fm.co2, a.aq.co2);
    if (isNum(co2)) h += '<span class="grp">CO₂: ' + fmt(co2, 0) + ' <span class="unit">ppm</span></span>';
    $('aq').innerHTML = h;
    var el = $('aq-pm');
    if (el) el.onclick = function () { S.pmIdx++; renderAQ(); };
  }

  // ---------------------------------------------------------------- sun arc + moon
  // Moon altitude (degrees) at time ms for the station, from low-precision lunar
  // formulas: rise/set within ~4 min of PyEphem. No server-side ephem needed.
  var RAD = Math.PI / 180;
  function moonAltitude(ms, lat, lon) {
    var d = ms / 86400000 - 10957.5;                 // days since J2000.0
    var e = 23.4397 * RAD;
    var L = 218.316 + 13.176396 * d, M = RAD * (134.963 + 13.064993 * d), F = RAD * (93.272 + 13.229350 * d);
    var D = RAD * (297.850 + 12.190749 * d), Ms = RAD * (357.529 + 0.98560028 * d), sin = Math.sin;
    // ecliptic longitude/latitude with the main periodic terms (evection, variation, annual equation...)
    var l = RAD * (L + 6.289 * sin(M) - 1.274 * sin(M - 2 * D) + 0.658 * sin(2 * D) - 0.186 * sin(Ms)
      - 0.059 * sin(2 * M - 2 * D) - 0.057 * sin(M - 2 * D + Ms) + 0.053 * sin(M + 2 * D) + 0.046 * sin(2 * D - Ms)
      + 0.041 * sin(M - Ms) - 0.035 * sin(D) - 0.031 * sin(M + Ms));
    var b = RAD * (5.128 * sin(F) + 0.281 * sin(M + F) + 0.278 * sin(M - F) + 0.173 * sin(2 * D - F));
    var ra = Math.atan2(sin(l) * Math.cos(e) - Math.tan(b) * sin(e), Math.cos(l));
    var dec = Math.asin(sin(b) * Math.cos(e) + Math.cos(b) * sin(e) * sin(l));
    var H = RAD * (280.16 + 360.9856235 * d) + lon * RAD - ra, phi = lat * RAD;
    return Math.asin(sin(phi) * sin(dec) + Math.cos(phi) * Math.cos(dec) * Math.cos(H)) / RAD;
  }
  // {up, rise, set} in epoch seconds: while the moon is up, the rise just gone and the
  // coming set; while it is down, the next rise and the set after it.
  function moonTrack() {
    var a = S.arch;
    if (!isNum(a.lat) || !isNum(a.lon)) return null;
    var t0 = Date.now();
    if (S.moonCache && Math.abs(t0 - S.moonCache.at) < 300000) return S.moonCache.v;
    var H0 = 0.125, step = 600000, ev = [];       // rise/set altitude incl. parallax, refraction, semi-diameter
    var prevT = t0 - 26 * 3600000, prev = moonAltitude(prevT, a.lat, a.lon) - H0;
    for (var t = prevT + step; t <= t0 + 50 * 3600000; t += step) {
      var cur = moonAltitude(t, a.lat, a.lon) - H0;
      if ((prev <= 0) !== (cur <= 0)) ev.push({ t: (prevT + step * (prev / (prev - cur))) / 1000, up: cur > 0 });
      prev = cur; prevT = t;
    }
    var nowS = t0 / 1000, up = moonAltitude(t0, a.lat, a.lon) - H0 > 0, rise = null, set = null, i;
    if (up) {
      for (i = ev.length - 1; i >= 0; i--) if (ev[i].up && ev[i].t <= nowS) { rise = ev[i].t; break; }
      for (i = 0; i < ev.length; i++) if (!ev[i].up && ev[i].t > nowS) { set = ev[i].t; break; }
    } else {
      for (i = 0; i < ev.length; i++) if (ev[i].up && ev[i].t > nowS) { rise = ev[i].t; break; }
      for (i = 0; i < ev.length; i++) if (!ev[i].up && isNum(rise) && ev[i].t > rise) { set = ev[i].t; break; }
    }
    var v = { up: up, rise: rise, set: set };
    S.moonCache = { at: t0, v: v };
    return v;
  }
  // Moon phase name from WeeWX's phase index (0 = new … 4 = full … 7), in the current language
  var MOON_NAMES = ['New moon', 'Waxing crescent', 'First quarter', 'Waxing gibbous', 'Full moon',
    'Waning gibbous', 'Last quarter', 'Waning crescent'];
  function moonPhaseName(al) {
    return isNum(al.moonIndex) && MOON_NAMES[al.moonIndex] ? _t(MOON_NAMES[al.moonIndex]) : (al.moonPhase || '');
  }
  function arcPoint(cx, cy, rx, ry, f) {
    var ang = Math.PI * (1 - clamp(f, 0, 1));
    return [(cx + rx * Math.cos(ang)).toFixed(1), (cy - ry * Math.sin(ang)).toFixed(1)];
  }
  function renderSky() {
    var a = S.arch, al = S.alm || {}, fm = S.cfg.fields;
    var rad = pick(fm.radiation, a.solar.rad), uv = pick(fm.UV, a.solar.uv);
    // geometry: the phone layout gets a narrower drawing with larger text
    var phone = S.layout === 'phone';
    var SW = phone ? 540 : 776;
    var cx = phone ? 270 : 330, cy = 186, rx = phone ? 252 : 292, ry = 150;   // sun
    var mrx = phone ? 160 : 182, mry = phone ? 94 : 96;                      // moon, inside the sun's arc
    var fs = phone ? { val: 32, unit: 19, sun: 26, moon: 20, phase: 22 } : { val: 30, unit: 18, sun: 24, moon: 18, phase: 20 };
    var waxing = isNum(al.moonIndex) ? al.moonIndex < 4 : true;
    var g = '<defs>' + I.sunDefs + '</defs>' +
      '<path d="M' + (cx - rx) + ' ' + cy + ' A' + rx + ' ' + ry + ' 0 0 1 ' + (cx + rx) + ' ' + cy + '" fill="none" stroke="var(--fg-dim)" stroke-width="2.4" stroke-dasharray="7 9"/>' +
      '<path d="M' + (cx - mrx) + ' ' + cy + ' A' + mrx + ' ' + mry + ' 0 0 1 ' + (cx + mrx) + ' ' + cy + '" fill="none" stroke="var(--moon-track)" stroke-width="2.6" stroke-linecap="round" stroke-dasharray="0.1 8"/>';
    // moon on its track
    var mt = moonTrack();
    if (mt && mt.up && isNum(mt.rise) && isNum(mt.set)) {
      var mp = arcPoint(cx, cy, mrx, mry, (now() - mt.rise) / (mt.set - mt.rise));
      g += '<g transform="translate(' + mp[0] + ',' + mp[1] + ')">' + I.moon(al.moonFullness, waxing, 11) + '</g>';
    }
    // sun on its track
    var sun = todaySun() || {}, t = now(), sr = sun.rise, ss = sun.set;
    if (isNum(sr) && isNum(ss) && t > sr && t < ss) {
      var sp = arcPoint(cx, cy, rx, ry, (t - sr) / (ss - sr));
      g += '<g transform="translate(' + sp[0] + ',' + sp[1] + ')">' + I.sun(14) + '</g>';
    }
    // radiation and UV in the middle, under both arcs
    g += '<text class="sky-mid" x="' + cx + '" y="' + (cy - 50) + '" text-anchor="middle" font-weight="700" fill="var(--fg)"><tspan font-size="' + fs.val + '">' + fmt(rad, rad >= 100 ? 0 : 1) + '</tspan><tspan font-size="' + fs.unit + '" fill="var(--fg-dim)"> W/m²</tspan></text>' +
      '<text class="sky-mid" x="' + cx + '" y="' + (cy - 12) + '" text-anchor="middle" font-weight="700" fill="var(--fg)"><tspan font-size="' + fs.val + '">' + fmt(uv, 0) + '</tspan><tspan font-size="' + fs.unit + '" fill="var(--fg-dim)"> ' + esc(_t('UV Index')) + '</tspan></text>' +
      // sunrise / sunset at the ends of the sun's arc
      // (on the phone they sit just below the arc's ends, clear of the moon's times)
      (phone
        ? '<text x="' + (cx - rx - 6) + '" y="' + (cy + 30) + '" font-size="' + fs.sun + '" font-weight="500" fill="var(--fg)">' + hhmm(sr) + '</text>' +
          '<text x="' + (cx + rx + 6) + '" y="' + (cy + 30) + '" text-anchor="end" font-size="' + fs.sun + '" font-weight="500" fill="var(--fg)">' + hhmm(ss) + '</text>'
        : '<text x="' + (cx - rx + 50) + '" y="' + (cy - 4) + '" text-anchor="middle" font-size="' + fs.sun + '" font-weight="500" fill="var(--fg)">' + hhmm(sr) + '</text>' +
          '<text x="' + (cx + rx - 50) + '" y="' + (cy - 4) + '" text-anchor="middle" font-size="' + fs.sun + '" font-weight="500" fill="var(--fg)">' + hhmm(ss) + '</text>');
    // moonrise / moonset at the ends of the moon's track
    if (mt) {
      g += '<text x="' + (cx - mrx + 8) + '" y="' + (cy - 4) + '" font-size="' + fs.moon + '" font-weight="700" fill="var(--moon-track)">↑ ' + hhmm(mt.rise) + '</text>' +
        '<text x="' + (cx + mrx - 8) + '" y="' + (cy - 4) + '" text-anchor="end" font-size="' + fs.moon + '" font-weight="700" fill="var(--moon-track)">↓ ' + hhmm(mt.set) + '</text>';
    }
    // moon phase
    var px = phone ? 330 : 600;
    g += '<g id="moon-g" transform="translate(' + px + ',-14)">' + I.moon(al.moonFullness, waxing, 18) + '</g>' +
      '<text id="moon-ph" x="' + (px + 28) + '" y="-7" font-size="' + fs.phase + '" font-weight="700" fill="var(--fg-dim)">' + esc(moonPhaseName(al)) + '</text>';
    $('sky').innerHTML = svg(SW, phone ? 226 : 200, g);
    // radiation and UV stay between the moonrise and moonset times
    Array.prototype.forEach.call($('sky').querySelectorAll('.sky-mid'), function (el) {
      var maxW = 2 * mrx - 170, len = el.getComputedTextLength ? el.getComputedTextLength() : 0;
      if (len > maxW) Array.prototype.forEach.call(el.querySelectorAll('tspan'), function (ts) {
        ts.setAttribute('font-size', (parseFloat(ts.getAttribute('font-size')) * Math.max(0.55, maxW / len)).toFixed(1));
      });
    });
    // a long phase name (some languages) moves left, moon included, before it is made smaller
    var ph = $('moon-ph'), right = SW - 4;
    if (ph && ph.getComputedTextLength) {
      var tx = Math.max(phone ? 120 : 300, Math.min(px + 28, right - ph.getComputedTextLength()));
      ph.setAttribute('x', tx);
      $('moon-g').setAttribute('transform', 'translate(' + (tx - 28) + ',-14)');
      fitSvgText(ph, right, 12);
    }
  }

  // ---------------------------------------------------------------- rain (one gauge, like the console)
  function rainBlocks() {
    // {trad: {...}, piezo: {...}}; each block has .any = true when it reports data
    var a = S.arch, out = {};
    var RF = S.cfg.rainFields;
    // With the driver's rain_source = piezo, the tipping-gauge totals aren't the station's gauge
    var noTrad = S.arch.driverRainSource === 'piezo';
    [['trad', a.rain[0]], ['piezo', a.rain[1]]].forEach(function (d) {
      var b = d[1] || {}, f = RF[d[0]], r = {};
      ['rate', 'event', 'hour', 'day', 'week', 'month', 'year'].forEach(function (k) {
        r[k] = (d[0] === 'trad' && noTrad) ? null : pick(f[k], b[k]);
      });
      r.any = ['rate', 'day', 'week', 'month', 'year', 'event'].some(function (k) { return isNum(r[k]); });
      out[d[0]] = r;
    });
    return out;
  }
  function rainSig(key) { return key === 'piezo' ? (sigFor('ws90') || sigFor('ws85')) : (sigFor('wh40') || sigFor('wn20') || sigFor('wh69')); }
  function renderRain() {
    var blocks = rainBlocks(), mode = S.cfg.rain_sensors || 'auto';
    if (mode === 'both') return renderRainBoth(blocks.trad, blocks.piezo);
    var key, toggle = false;
    if (mode === 'tipping') key = 'trad';
    else if (mode === 'piezo') key = 'piezo';
    else {
      var have = ['trad', 'piezo'].filter(function (k) { return blocks[k].any; });
      if (!have.length) { $('rain').innerHTML = ''; return; }
      key = have.indexOf(S.rainSrc) >= 0 ? S.rainSrc : have[0];
      toggle = have.length > 1;
    }
    renderRainSingle(blocks[key], key, toggle);
  }
  function renderRainSingle(r, key, toggle) {
    var nd = dec.rain(), u = lbl.rain(), name = key === 'piezo' ? _t('Piezo') : _t('Tipping');
    var srcTag = toggle ? '<div class="src tap" id="rain-src">' + esc(name) + ' ⇄</div>' : (key === 'piezo' ? '<div class="src">' + esc(name) + '</div>' : '');
    var sig = rainSig(key);
    $('rain').innerHTML = '<div class="icon">' + I.rainIcon() + srcTag + (sig ? '<div style="margin-top:4px">' + sig + '</div>' : '') + '</div>' +
      '<div class="rate">' + esc(_t('Rate')) + '<b>' + fmt(cv.rain(r.rate), nd) + ' ' + esc(lbl.rainRate()) + '</b></div>' +
      '<div class="day">' + bigHtml(cv.rain(r.day), nd, '.6em') + '<span class="unit">' + esc(u) + '</span></div>' +
      '<div class="lbl">' + esc(_t('Daily Rain')) + '</div>' +
      '<div class="list">' + [[_t('Event'), r.event], [_t('Hourly'), r.hour], [_t('Weekly'), r.week], [_t('Monthly'), r.month], [_t('Yearly'), r.year]].map(function (x) {
        return '<div class="row"><span>' + esc(x[0]) + '</span><span>' + fmt(cv.rain(x[1]), nd) + ' ' + esc(u) + '</span></div>';
      }).join('') + '</div>';
    var t = $('rain-src');
    if (t) t.onclick = function () { S.rainSrc = key === 'piezo' ? 'trad' : 'piezo'; lsSet('rainSrc', S.rainSrc); renderRain(); };
  }
  // Both sensors: labels | Tipping | Piezo
  function renderRainBoth(tp, p) {
    var nd = dec.rain(), u = lbl.rain();
    var cell = function (v, cls) { return '<span class="' + (cls || 'v') + '">' + fmt(cv.rain(v), nd) + '</span>'; };
    var row = function (label, a, b, cls) { return '<span class="k">' + esc(label) + '</span>' + cell(a, cls) + cell(b, cls); };
    var h = '<span class="k unit-h">' + esc(u) + '</span>' +
      '<span class="h">' + esc(_t('Tipping')) + ' ' + rainSig('trad') + '</span><span class="h">' + esc(_t('Piezo')) + ' ' + rainSig('piezo') + '</span>' +
      row(_t('Daily'), tp.day, p.day, 'big') +
      '<span class="k">' + esc(_t('Rate')) + '</span><span class="v">' + fmt(cv.rain(tp.rate), nd) + '<i>/h</i></span><span class="v">' + fmt(cv.rain(p.rate), nd) + '<i>/h</i></span>' +
      row(_t('Event'), tp.event, p.event) + row(_t('Hourly'), tp.hour, p.hour) + row(_t('Weekly'), tp.week, p.week) +
      row(_t('Monthly'), tp.month, p.month) + row(_t('Yearly'), tp.year, p.year);
    $('rain').innerHTML = '<div class="icon both">' + I.rainIcon() + '</div><div class="r3">' + h + '</div>';
  }

  // Today's sunrise/sunset from the WeeWX almanac. If the almanac is from an earlier
  // day (no report yet since midnight), move it onto today's date.
  function todaySun() {
    var al = S.alm || {}, sr = al.sunrise, ss = al.sunset;
    if (!isNum(sr) || !isNum(ss)) return null;
    var k = dayIndex(now()) - dayIndex(sr);       // today in station time
    return { rise: sr + k * 86400, set: ss + k * 86400 };
  }
  // Night = after today's sunset or before today's sunrise
  function isNight() {
    var sun = todaySun(), t = now();
    return !!sun && (t < sun.rise || t > sun.set);
  }

  // ---------------------------------------------------------------- barometer
  function renderBaro() {
    var a = S.arch, fm = S.cfg.fields, b = a.baro;
    var rel = pick(fm.barometer, b.rel), abs = pick(fm.pressure, b.abs);
    var mode = (S.baroMode === 'ABS' && isNum(abs)) ? 'ABS' : 'REL';
    var val = mode === 'ABS' ? abs : rel;
    var trend = b.trend3h;
    if (isNum(trend) && isNum(rel) && isNum(b.rel)) trend = trend + (rel - b.rel);
    var deg = isNum(trend) ? -clamp(trend / 3 * 60, -75, 75) : 0;   // feed is hPa
    var dd = dec.press(), tr = cv.press(trend);
    $('baro').innerHTML = '<div class="k">' + esc(_t('Barometer Reading')) + '</div>' +
      '<div class="reading tap" id="b-val"><span class="mode">' + mode + '</span><span class="val">' + bigHtml(cv.press(val), dd, '.7em') + '</span><span class="u">' + esc(lbl.press()) + '</span></div>' +
      '<div class="trend" title="' + esc(_t('3-hour change')) + '">' + '<div style="display:inline-block">' + I.trendArrow(deg) + '</div>' +
      '<div class="delta">' + (isNum(tr) ? (tr > 0 ? '+' : '') + tr.toFixed(dd) + ' ' + esc(lbl.press()) : '--') + '</div></div>' +
      '<div class="fc" title="' + esc(_t('Pressure-trend forecast')) + '">' + I.forecast(b.forecast, 84, isNight()) + '</div>';
    $('b-val').onclick = function () { if (isNum(abs)) { S.baroMode = mode === 'REL' ? 'ABS' : 'REL'; lsSet('baroMode', S.baroMode); renderBaro(); } };
  }

  // ---------------------------------------------------------------- overlays
  // Panels fill the whole screen (they sit outside the scaled dashboard).
  // flush: no padding/scrolling (radar map); otherwise content is centred in .wrap
  function openOverlay(id, html, flush) {
    $('ov-title').textContent = _t(id);
    $('ov-tabs').hidden = true;
    $('ov-body').className = flush ? 'flush' : '';
    $('ov-body').innerHTML = flush ? html : '<div class="wrap">' + html + '</div>';
    $('ov-body').scrollTop = 0;
    $('overlay').hidden = false;
    S.panel = id;
  }
  function closeOverlay() {
    $('overlay').hidden = true;
    $('ov-body').innerHTML = '';        // unloads the radar map
    $('ov-tabs').hidden = true;
    S.panel = null;
  }
  function windyUrl(tpl) {
    var u = US(), a = S.arch;
    var wind = { mph: 'mph', kmh: 'km/h', ms: 'm/s', kt: 'kt', bf: 'bft' }[u.wind] || 'default';
    return String(tpl).replace(/\{lat\}/g, a.lat.toFixed(3)).replace(/\{lon\}/g, a.lon.toFixed(3))
      .replace(/\{zoom\}/g, S.cfg.windy_zoom || 8)
      .replace(/\{wind\}/g, encodeURIComponent(wind)).replace(/\{temp\}/g, encodeURIComponent(lbl.temp()));
  }
  // Windy's radar map in a panel over the dashboard, so a full-screen tablet never leaves the page
  function showRadar() {
    if (!isNum(S.arch.lat) || !isNum(S.arch.lon)) return;
    var src = windyUrl(S.cfg.windy_embed_url);
    openOverlay('Windy radar', '<iframe class="radar-frame" src="' + esc(src) + '" title="' + esc(_t('Windy radar')) + '" referrerpolicy="no-referrer-when-downgrade"></iframe>', true);
  }
  function showSensors() {
    var rows = (S.cfg.sensors || []).map(function (s) {
      var b = battState(s.kind, lv(s.batt)), sig = lv(s.sig);
      if (!b && !isNum(sig)) return '';
      return '<tr><td>' + esc(tl(s.label)) + '</td><td>' + (b ? I.battery(b.level, b.low, 12) : '') + '</td><td>' + (b ? esc(b.txt) : '--') +
        '</td><td title="' + (isNum(sig) ? sig + '/4' : '') + '">' + (isNum(sig) ? I.signal(sig, 26) : '--') + '</td></tr>';
    }).join('');
    var html = rows ? '<table class="sensors"><tr><th>' + esc(_t('Sensor')) + '</th><th></th><th>' + esc(_t('Battery')) + '</th><th>' + esc(_t('Signal')) + '</th></tr>' + rows + '</table>' :
      '<p>' + esc(_t('No battery or signal data yet.')) + '</p><p class="meta">' +
      esc(_t('Battery and signal levels come from the gateway driver\'s loop packets, so they need the live data service and a fresh live.json.')) + ' ' +
      esc(liveFresh() ? _t('Live data is arriving but has no battery fields: check the driver\'s field map.') : _t('Live data is not arriving at the moment.')) + '</p>';
    openOverlay('Sensors', html);
  }
  function seg(name, opts, cur) {
    return '<span class="seg" data-opt="' + name + '">' + opts.map(function (o) { return '<button data-v="' + esc(o[0]) + '"' + (String(cur) === String(o[0]) ? ' class="on"' : '') + '>' + esc(_t(o[1])) + '</button>'; }).join('') + '</span>';
  }
  function showSettings() {
    var a = S.arch;
    var units = '<select id="unit-sel">' + Object.keys(SYSTEMS).map(function (k) {
      return '<option value="' + k + '"' + (k === S.unitKey ? ' selected' : '') + '>' + esc(_t(SYSTEM_LABELS[k][0]) + ' (' + SYSTEM_LABELS[k][1] + ')') + '</option>';
    }).join('') + '</select>';
    var langs = '<select id="lang-sel">' + Object.keys(LANGS).sort(function (x, y) { return LANGS[x][0].localeCompare(LANGS[y][0]); }).map(function (k) {
      return '<option value="' + k + '"' + (k === S.lang ? ' selected' : '') + '>' + esc(LANGS[k][0]) + '</option>';
    }).join('') + '</select>';
    var row = function (k, ctl) { return '<div class="opt"><span>' + esc(_t(k)) + '</span>' + ctl + '</div>'; };
    var html = row('Language', langs) + row('Units', units) +
      row('Layout', seg('layout', [['auto', 'Auto'], ['landscape', 'Landscape'], ['portrait', 'Portrait'], ['phone', 'Phone']], S.opts.layout || 'auto')) +
      row('Theme', seg('theme', [['navy', 'Navy'], ['black', 'Black'], ['light', 'Light']], S.opts.theme)) +
      row('Clock', seg('clock24', [['true', '24 h'], ['false', '12 h']], S.opts.clock24)) +
      row('Show seconds', seg('seconds', [['true', 'On'], ['false', 'Off']], S.opts.seconds)) +
      row('Keep screen awake', seg('wake', [['true', 'On'], ['false', 'Off']], S.opts.wake)) +
      row('Full screen on touch', seg('fullscreen', [['true', 'On'], ['false', 'Off']], S.opts.fullscreen !== false)) +
      '<div class="meta">' + esc(S.cfg.title || a.station) + (a.hardware ? ' · ' + esc(a.hardware) : '') + '<br>' +
      esc(_t('Archive record')) + ': ' + dtf({ day: 'numeric', month: 'short', year: 'numeric' }).format(W(a.archiveTime)) + ' ' + hhmm(a.archiveTime) +
      ' (' + esc(_t('every {0} min', Math.round(a.interval / 60))) + ')<br>' +
      esc(_t('Times shown in station time')) + (stationZone() ? ' (' + esc(stationZone()) + ')' : '') + '<br>' +
      esc(_t('Live data')) + ': ' + esc(liveFresh() ? _t('OK, {0} s old', Math.round(now() - S.live.written)) : (S.live ? _t('stale') : _t('not available'))) + ' (' + esc(S.cfg.live_url) + ')<br>' +
      'EcowittConsoleEmulator ' + esc(a.version) + ' · WeeWX ' + esc(a.weewx) + '</div>';
    openOverlay('Settings', html);
    $('unit-sel').onchange = function () { setUnitSystem(this.value); };
    $('lang-sel').onchange = function () { setLanguage(this.value); };
    Array.prototype.forEach.call($('ov-body').querySelectorAll('.seg'), function (sg) {
      sg.onclick = function (ev) {
        var btn = ev.target.closest('button'); if (!btn) return;
        var k = sg.getAttribute('data-opt'), v = btn.getAttribute('data-v');
        if (v === 'true' || v === 'false') v = v === 'true';
        S.opts[k] = v; lsSet('opts', S.opts); applyOpts(); showSettings(); renderAll(); tickClock();
      };
    });
  }
  // ---------------------------------------------------------------- charts
  // Drawn here from chart_<period>.json (METRICWX), so they follow the theme and the
  // display units. Tap or drag on a chart to read values.
  var PERIODS = [['day', 'Day'], ['week', 'Week'], ['month', 'Month'], ['year', 'Year']];
  var chartCache = {};
  function groupConv(g) {
    return { temp: cv.temp, speed: cv.wind, press: cv.press, rain: cv.rain }[g] || function (v) { return v; };
  }
  function groupUnit(g) {
    switch (g) {
      case 'temp': return lbl.temp(); case 'speed': return lbl.wind(); case 'press': return lbl.press();
      case 'rain': return lbl.rain(); case 'hum': return '%'; case 'rad': return 'W/m²'; case 'vpd': return 'kPa';
      default: return '';
    }
  }
  function niceTicks(lo, hi, n) {
    if (lo === hi) { lo -= 1; hi += 1; }
    var raw = (hi - lo) / n, mag = Math.pow(10, Math.floor(Math.log10(raw))), f = raw / mag;
    var step = (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * mag;
    var a = Math.floor(lo / step) * step, b = Math.ceil(hi / step) * step, out = [];
    for (var v = a; v <= b + step / 2; v += step) out.push(+v.toFixed(6));
    return { ticks: out, step: step };
  }
  function timeTicks(t0, t1, period) {
    // ticks on round station wall-clock times, converted back to real time stamps
    var out = [], off = tzOffset(t0), w1 = t1 + tzOffset(t1), d = new Date((t0 + off) * 1000);
    var back = function (dd) { var w = dd.getTime() / 1000; return w - tzOffset(w - off); };
    if (period === 'day') {
      var hs = CW < 500 ? 6 : 3;
      d.setUTCMinutes(0, 0, 0); d.setUTCHours(Math.ceil(d.getUTCHours() / hs) * hs);
      for (; d.getTime() / 1000 <= w1; d.setUTCHours(d.getUTCHours() + hs)) out.push([back(d), wallHM(d)]);
    } else if (period === 'week') {
      d.setUTCHours(24, 0, 0, 0);
      for (; d.getTime() / 1000 <= w1; d.setUTCDate(d.getUTCDate() + 1)) out.push([back(d), dtf({ weekday: 'short' }).format(d)]);
    } else if (period === 'month') {
      d.setUTCHours(24, 0, 0, 0);
      for (; d.getTime() / 1000 <= w1; d.setUTCDate(d.getUTCDate() + 1))
        if ((d.getUTCDate() - 1) % (CW < 500 ? 10 : 5) === 0) out.push([back(d), dtf({ day: 'numeric', month: 'short' }).format(d)]);
    } else {
      var on1st = d.getUTCDate() === 1 && d.getUTCHours() === 0 && d.getUTCMinutes() === 0;
      d.setUTCDate(1); d.setUTCHours(0, 0, 0, 0); if (!on1st) d.setUTCMonth(d.getUTCMonth() + 1);
      for (; d.getTime() / 1000 <= w1; d.setUTCMonth(d.getUTCMonth() + 1))
        if (CW >= 500 || d.getUTCMonth() % 2 === 0) out.push([back(d), dtf({ month: 'short' }).format(d), Math.min(back(new Date(d.getTime() + 15 * 86400000)), (back(d) + t1) / 2)]);
    }
    return out;
  }
  var CW = 600, CH = 250, ML = 50, MR = 12, MT = 10, MB = 26;
  function chartSize() {
    // drawn close to the size it is shown at, so axis text stays readable on phones
    if (window.innerWidth < 640) { CW = 380; CH = 210; ML = 44; } else { CW = 600; CH = 250; ML = 50; }
  }
  function drawChart(def, data) {
    var conv = groupConv(def.group), kind = def.kind;
    // rain: tipping gauge blue, piezo sensor violet (also when only one of them reports)
    var colors = def.group === 'rain' ? def.series.map(function (sd) { return sd.key === 'rain_p' ? 'var(--c3)' : 'var(--c2)'; })
      : def.group === 'vpd' ? ['var(--c4)'] : ['var(--c1)', 'var(--c2)'];
    var t0 = data.start, t1 = data.stop;
    var ser = def.series.map(function (sd) {
      var s = data.series[sd.key];
      return { label: _t(sd.label), t: kind === 'bar' ? s.t0 : s.t, te: s.t, v: s.v.map(function (x) { return isNum(x) ? conv(x) : null; }) };
    });
    var vals = [];
    ser.forEach(function (s) { s.v.forEach(function (v) { if (isNum(v)) vals.push(v); }); });
    if (!vals.length) return null;
    var lo = Math.min.apply(null, vals), hi = Math.max.apply(null, vals);
    if (def.group === 'hum') { lo = 0; hi = 100; }
    if (def.group === 'dir') { lo = 0; hi = 360; }
    if (kind === 'bar' || def.group === 'rad' || def.group === 'uv' || def.group === 'vpd') lo = Math.min(0, lo);
    if (kind === 'bar' && hi <= 0) hi = 1;
    if (kind === 'bar') hi *= 1.12;           // room for the values over the columns
    var yt = def.group === 'dir' ? { ticks: [0, 90, 180, 270, 360], step: 90 } : niceTicks(lo, hi, 4);
    lo = yt.ticks[0]; hi = yt.ticks[yt.ticks.length - 1];
    var pw = CW - ML - MR, ph = CH - MT - MB;
    var X = function (t) { return ML + (t - t0) / (t1 - t0) * pw; };
    var Y = function (v) { return MT + (1 - (v - lo) / (hi - lo)) * ph; };
    var nser = ser.length, typ = 0;
    if (kind === 'bar') {                     // usual interval length (median)
      var durs = ser[0].te.map(function (e, i) { return e - ser[0].t[i]; }).sort(function (a, b) { return a - b; });
      typ = Math.max(durs.length ? durs[Math.floor(durs.length / 2)] : 0, { day: 3600, week: 86400, month: 86400, year: 30.44 * 86400 }[data.period] || 0);
    }
    var barX = function (s, i, si) {
      // one separate column per series in each interval, with a clear gap between them
      // a part interval (this month so far) gets the full width, kept inside the plot
      var xa = X(s.t[i]), xb = X(s.te[i]), full = typ * pw / (t1 - t0);
      if (xb - xa < full * 0.9) { if (i === s.t.length - 1) xb = Math.min(ML + pw, xa + full); else xa = Math.max(ML, xb - full); }
      if (xb - xa < full * 0.9) { if (i === s.t.length - 1) xa = xb - full; else xb = xa + full; }
      // the pair takes 76% of the interval, so the pairs stand apart from each other
      var slot = (xb - xa) * (nser > 1 ? 0.76 : 0.8);
      var gap = nser > 1 ? Math.min(4, Math.max(1, slot * 0.06)) : 0, w = Math.max(1, (slot - gap * (nser - 1)) / nser);
      var x = xa + (xb - xa - slot) / 2 + si * (w + gap);
      return { x: x, w: w, c: x + w / 2 };
    };
    var g = '';
    var ydec = Math.abs(yt.step - Math.round(yt.step)) < 1e-9 ? 0 : Math.abs(yt.step * 10 - Math.round(yt.step * 10)) < 1e-9 ? 1 : 2;
    yt.ticks.forEach(function (v) {
      var y = Y(v).toFixed(1), lab = def.group === 'dir' ? esc(_t(['N', 'E', 'S', 'W', 'N'][v / 90])) : v.toFixed(ydec);
      g += '<line x1="' + ML + '" x2="' + (CW - MR) + '" y1="' + y + '" y2="' + y + '" stroke="var(--grid)"/>' +
        '<text x="' + (ML - 6) + '" y="' + (+y + 4) + '" text-anchor="end" font-size="12" fill="var(--fg-dim)">' + lab + '</text>';
    });
    timeTicks(t0, t1, data.period).forEach(function (tk) {
      var x = X(tk[0]);
      if (x < ML || x > CW - MR) return;
      g += '<line x1="' + x.toFixed(1) + '" x2="' + x.toFixed(1) + '" y1="' + MT + '" y2="' + (CH - MB) + '" stroke="var(--grid)"/>' +
        '<text x="' + (tk[2] ? X(tk[2]) : x).toFixed(1) + '" y="' + (CH - 8) + '" text-anchor="middle" font-size="12" fill="var(--fg-dim)">' + esc(tk[1]) + '</text>';
    });
    ser.forEach(function (s, si) {
      var col = colors[si % colors.length];
      if (kind === 'bar') {
        // several series (tipping and piezo) side by side in each interval
        for (var i = 0; i < s.v.length; i++) {
          if (!isNum(s.v[i]) || s.v[i] <= 0) continue;
          var bx = barX(s, i, si);
          g += '<rect x="' + bx.x.toFixed(1) + '" y="' + Y(s.v[i]).toFixed(1) + '" width="' + bx.w.toFixed(1) +
            '" height="' + (Y(0) - Y(s.v[i])).toFixed(1) + '" fill="' + col + '" rx="1.5"/>';
          // value on top of each column when there is room for it
          if (bx.w >= 18) g += '<text x="' + bx.c.toFixed(1) + '" y="' + (Y(s.v[i]) - 4).toFixed(1) + '" text-anchor="middle" font-size="' +
            (bx.w >= 30 ? 12 : 10) + '" fill="' + col + '">' + s.v[i].toFixed(dec.rain()) + '</text>';
        }
      } else if (kind === 'dots') {
        for (var j = 0; j < s.v.length; j++) if (isNum(s.v[j])) g += '<circle cx="' + X(s.t[j]).toFixed(1) + '" cy="' + Y(s.v[j]).toFixed(1) + '" r="1.7" fill="' + col + '"/>';
      } else {
        var d = '', pen = false, gap = s.t.length > 1 ? 3 * (s.t[1] - s.t[0]) : Infinity;
        for (var k = 0; k < s.v.length; k++) {
          if (!isNum(s.v[k]) || (k && s.t[k] - s.t[k - 1] > gap)) { pen = false; if (!isNum(s.v[k])) continue; }
          d += (pen ? 'L' : 'M') + X(s.t[k]).toFixed(1) + ' ' + Y(s.v[k]).toFixed(1);
          pen = true;
        }
        g += '<path d="' + d + '" fill="none" stroke="' + col + '" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>';
      }
    });
    g += '<line class="xh" x1="0" x2="0" y1="' + MT + '" y2="' + (CH - MB) + '" stroke="var(--fg)" stroke-width="1" opacity="0" />' +
      ser.map(function (s, i) { return '<circle class="mk" r="4.5" cx="0" cy="0" fill="' + colors[i % colors.length] + '" stroke="var(--panel-bg)" stroke-width="2" opacity="0"/>'; }).join('') +
      '<rect class="hit" x="' + ML + '" y="' + MT + '" width="' + pw + '" height="' + ph + '" fill="transparent"/>';
    var u = groupUnit(def.group);
    // rain legend: each gauge's total for today / this week / this month / this year,
    // the same calendar periods (and figures) as the rain table, from the archive
    // (the table's own figure — the gateway's counter when live — else the archive sum)
    var tot = def.totals, blocks = kind === 'bar' ? rainBlocks() : null, totalOf = function (s, i) {
      var key = def.series[i].key, v = blocks ? blocks[key === 'rain_p' ? 'piezo' : 'trad'][data.period] : null;
      if (!isNum(v) && tot && tot.v) v = tot.v[key];
      return isNum(v) ? conv(v) : null;
    };
    var legend = (ser.length > 1 || kind === 'bar') ? '<span class="chart-legend">' +
      (kind === 'bar' && tot ? '<span class="tot-l">' + esc(_t(tot.label)) + '</span>' : '') + ser.map(function (s, i) {
      var tv = kind === 'bar' ? totalOf(s, i) : null;
      return '<span><i style="background:' + colors[i] + '"></i>' + esc(s.label) +
        (isNum(tv) ? ' <b>' + tv.toFixed(dec.rain()) + '</b>' : '') + '</span>'; }).join('') + '</span>' : '';
    return { html: '<div class="chart" data-id="' + def.id + '"><div class="chart-head"><span class="chart-title">' + esc(_t(def.title)) +
      (u ? '<span class="u">' + esc(u) + '</span>' : '') + '</span>' + legend + '</div><div class="chart-tip"></div>' +
      '<svg viewBox="0 0 ' + CW + ' ' + CH + '" preserveAspectRatio="xMidYMid meet">' + g + '</svg></div>',
      ser: ser, X: X, Y: Y, barX: barX, t0: t0, t1: t1, pw: pw, unit: u, def: def, period: data.period, colors: colors };
  }
  // Tooltip: hover (mouse) or tap/drag (touch) shows the nearest point of each series
  function hideTips(except) {
    Array.prototype.forEach.call(document.querySelectorAll('.chart'), function (el) {
      if (el === except) return;
      var tip = el.querySelector('.chart-tip'); if (tip) tip.style.display = 'none';
      var xh = el.querySelector('.xh'); if (xh) xh.setAttribute('opacity', 0);
      Array.prototype.forEach.call(el.querySelectorAll('.mk'), function (m) { m.setAttribute('opacity', 0); });
    });
  }
  function attachReadout(el, c) {
    var svgEl = el.querySelector('svg'), xh = el.querySelector('.xh'), tip = el.querySelector('.chart-tip');
    var mks = el.querySelectorAll('.mk');
    var nd = { temp: 1, speed: 1, press: dec.press(), rain: dec.rain(), hum: 0, rad: 0, uv: 1, dir: 0, vpd: 2 }[c.def.group];
    if (!isNum(nd)) nd = 1;
    var fmtV = function (v) {
      if (!isNum(v)) return '--';
      if (c.def.group === 'dir') return compass(v) + ' ' + Math.round(v) + '°';
      return v.toFixed(nd) + (c.unit ? ' ' + c.unit : '');
    };
    function show(ev) {
      hideTips(el);
      var r = svgEl.getBoundingClientRect(), x = (ev.clientX - r.left) / r.width * CW;
      var t = c.t0 + (x - ML) / c.pw * (c.t1 - c.t0);
      var s0 = c.ser[0], best = -1, bd = Infinity;
      for (var i = 0; i < s0.t.length; i++) {
        // bars: the interval the pointer is in; lines/dots: the nearest point
        var mid = c.def.kind === 'bar' ? (s0.t[i] + s0.te[i]) / 2 : s0.t[i];
        var dd = Math.abs(mid - t);
        if (dd < bd && c.ser.some(function (s) { return isNum(s.v[i]); })) { bd = dd; best = i; }
      }
      if (best < 0) return;
      var tt = s0.t[best], xx = c.def.kind === 'bar' ? c.X((s0.t[best] + s0.te[best]) / 2) : c.X(tt);
      xh.setAttribute('x1', xx); xh.setAttribute('x2', xx); xh.setAttribute('opacity', .5);
      var ytop = Infinity;
      c.ser.forEach(function (s, si) {
        var m = mks[si], v = s.v[best]; if (!m) return;
        if (isNum(v)) {
          var yy = c.Y(v); ytop = Math.min(ytop, yy);
          m.setAttribute('cx', c.def.kind === 'bar' ? c.barX(s, best, si).c : xx); m.setAttribute('cy', yy); m.setAttribute('opacity', 1);
        } else m.setAttribute('opacity', 0);
      });
      var when;
      if (c.def.kind === 'bar') {
        when = c.period === 'day' ? hhmm(s0.t[best]) + '–' + hhmm(s0.te[best])
          : c.period === 'year' ? dtf({ month: 'long', year: 'numeric' }).format(W(tt))
          : dtf({ weekday: 'short', day: 'numeric', month: 'short' }).format(W(tt));
      } else {
        when = c.period === 'day' ? hhmm(tt) : c.period === 'year'
          ? dtf({ weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }).format(W(tt))
          : dtf({ weekday: 'short', day: 'numeric', month: 'short' }).format(W(tt)) + ' ' + hhmm(tt);
      }
      tip.innerHTML = '<div class="tt-when">' + esc(when) + '</div>' + c.ser.map(function (s, si) {
        return '<div class="tt-row"><i style="background:' + c.colors[si % c.colors.length] + '"></i>' +
          '<span>' + esc(s.label) + '</span><b>' + esc(fmtV(s.v[best])) + '</b></div>';
      }).join('') + (c.def.group === 'rain' && c.ser.length === 2 && isNum(c.ser[0].v[best]) && isNum(c.ser[1].v[best])
        ? '<div class="tt-row tt-diff"><span>' + esc(_t('Difference')) + '</span><b>' + esc((function (d) { var f = fmtV(Math.abs(d) < 1e-9 ? 0 : d); return d > 1e-9 && +f.split(' ')[0] > 0 ? '+' + f : f; })(c.ser[1].v[best] - c.ser[0].v[best])) + '</b></div>' : '');
      tip.style.display = 'block';
      // place beside the crosshair, flipping to the other side near the right edge
      var er = el.getBoundingClientRect(), sc = r.width / CW;
      var px = r.left - er.left + xx * sc, py = r.top - er.top + (isFinite(ytop) ? ytop : MT) * sc;
      var tw = tip.offsetWidth, th = tip.offsetHeight, left = px + 14;
      if (left + tw > er.width - 6) left = px - 14 - tw;
      if (left < 6) left = 6;
      var top = Math.max(r.top - er.top + 4, Math.min(py - th / 2, r.bottom - er.top - th - 4));
      tip.style.left = left + 'px'; tip.style.top = top + 'px';
    }
    svgEl.addEventListener('pointerdown', show);
    svgEl.addEventListener('pointermove', function (ev) { if (ev.pointerType === 'mouse' || ev.buttons) show(ev); });
    // a mouse hides it on leaving; on touch it stays until the next tap elsewhere
    svgEl.addEventListener('pointerleave', function (ev) { if (ev.pointerType === 'mouse') hideTips(); });
  }
  document.addEventListener('pointerdown', function (ev) { if (!ev.target.closest || !ev.target.closest('.chart svg')) hideTips(); });
  function renderCharts(data) {
    if (S.panel !== 'Charts') return;
    chartSize();
    var built = data.charts.map(function (def) { return drawChart(def, data); }).filter(Boolean);
    $('ov-body').innerHTML = built.length ? '<div class="chart-hint">' + esc(matchMedia('(hover: hover)').matches ? _t('Hover over a chart to see its values') : _t('Tap or drag on a chart to see its values')) +
      '</div><div class="charts">' + built.map(function (c) { return c.html; }).join('') + '</div>'
      : '<div class="chart-empty">' + esc(_t('No data for this period.')) + '</div>';
    built.forEach(function (c) { attachReadout($('ov-body').querySelector('.chart[data-id="' + c.def.id + '"]'), c); });
  }
  function loadCharts(period) {
    S.chartPeriod = period; lsSet('chartPeriod', period);
    Array.prototype.forEach.call($('ov-tabs').querySelectorAll('button'), function (b) { b.className = b.getAttribute('data-p') === period ? 'on' : ''; });
    var c = chartCache[period];
    if (c && Date.now() - c.at < 300000) return renderCharts(c.data);
    $('ov-body').innerHTML = '<div class="chart-empty">' + esc(_t('Loading…')) + '</div>';
    getJSON('chart_' + period + '.json').then(function (d) {
      chartCache[period] = { at: Date.now(), data: d };
      if (S.chartPeriod === period) renderCharts(d);
    }).catch(function () { $('ov-body').innerHTML = '<div class="chart-empty">' + esc(_t('Chart data is not available yet.')) + '</div>'; });
  }
  function showCharts() {
    openOverlay('Charts', '');
    $('ov-body').className = '';
    var tabs = $('ov-tabs');
    tabs.innerHTML = PERIODS.map(function (p) { return '<button data-p="' + p[0] + '">' + esc(_t(p[1])) + '</button>'; }).join('');
    tabs.hidden = false;
    tabs.onclick = function (ev) { var b = ev.target.closest('button'); if (b) loadCharts(b.getAttribute('data-p')); };
    loadCharts(S.chartPeriod || lsGet('chartPeriod', 'day') || 'day');
  }

  // Same behaviour as weewx-divumwx: store the choice under 'dashboardUnitSystem'
  // and announce it with a 'unitsystemchange' event.
  function setUnitSystem(key) {
    if (!SYSTEMS[key]) return;
    S.unitKey = key;
    try { localStorage.setItem(UNIT_KEY, key); } catch (e) { /* private mode */ }
    try { window.dispatchEvent(new CustomEvent('unitsystemchange', { detail: { system: key, config: SYSTEMS[key] } })); } catch (e) { /* old browser */ }
    renderAll();
  }
  function goFullscreen() {
    if (S.opts && S.opts.fullscreen === false) return;
    var d = document, el = d.documentElement;
    if (d.fullscreenElement || d.webkitFullscreenElement) return;
    var req = el.requestFullscreen || el.webkitRequestFullscreen;
    if (!req) return;                        // e.g. iPhone Safari: use "Add to Home Screen" instead
    try { var p = req.call(el, { navigationUI: 'hide' }); if (p && p.catch) p.catch(function () { }); } catch (e) { /* not allowed */ }
  }
  function applyOpts() {
    document.body.setAttribute('data-theme', S.opts.theme);
    if ($('sizer')) fit();
    var meta = document.querySelector('meta[name=theme-color]');
    if (meta) meta.content = S.opts.theme === 'light' ? '#eef1fb' : S.opts.theme === 'black' ? '#000000' : '#11185a';
    wake(S.opts.wake);
  }
  function wake(on) {
    if (!('wakeLock' in navigator)) return;
    if (on && !S.wakeLock && document.visibilityState === 'visible') {
      navigator.wakeLock.request('screen').then(function (l) { S.wakeLock = l; l.addEventListener('release', function () { S.wakeLock = null; }); }).catch(function () { });
    } else if (!on && S.wakeLock) { S.wakeLock.release(); S.wakeLock = null; }
  }

  // ---------------------------------------------------------------- toolbar
  function buildToolbar() {
    Array.prototype.forEach.call(document.querySelectorAll('#toolbar button'), function (b) {
      var act = b.getAttribute('data-act');
      b.innerHTML = I.tool[act] || '';
      b.onclick = function () {
        if (act === 'graphs') showCharts();
        else if (act === 'theme') { var th = ['navy', 'black', 'light']; S.opts.theme = th[(th.indexOf(S.opts.theme) + 1) % th.length]; lsSet('opts', S.opts); applyOpts(); renderAll(); }
        else if (act === 'channel') nextChannel();
        else if (act === 'sensors') showSensors();
        else if (act === 'refresh') { fetchArchive(); fetchLive(); }
        else if (act === 'settings') showSettings();
      };
    });
    $('ov-close').onclick = closeOverlay;
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !$('overlay').hidden) closeOverlay(); });
    // Browsers only allow full screen after a tap: the first touch anywhere enters it
    // (and re-enters it if the browser left it). Settings → "Full screen on touch".
    document.addEventListener('pointerdown', goFullscreen, true);
    $('overlay').onclick = function (e) { if (e.target === $('overlay')) closeOverlay(); };
    $('g-ch').onclick = nextChannel;
  }

  // ---------------------------------------------------------------- render
  function safe(fn, name) { try { fn(); } catch (e) { if (window.console) console.error('EcowittConsoleEmulator: ' + name, e); } }
  function renderAll() {
    if (!S.arch) return;
    safe(renderHeader, 'header'); safe(renderOut, 'outdoor'); safe(renderWind, 'wind');
    safe(renderTopRight, 'soil/lightning'); safe(renderChannel, 'channel');
    safe(renderStats, 'stats'); safe(renderAQ, 'aq'); safe(renderSky, 'sky');
    safe(renderRain, 'rain'); safe(renderBaro, 'baro');
  }

  // ---------------------------------------------------------------- fetching
  function getJSON(url) {
    return fetch(url + (url.indexOf('?') < 0 ? '?' : '&') + '_=' + Date.now(), { cache: 'no-store' }).then(function (r) {
      if (!r.ok) throw new Error(r.status + ' ' + url);
      return r.json();
    });
  }
  function setArchive(j) {
    if (!j || !j.data) return;
    S.arch = j.data; S.alm = j.almanac || {}; S.cfg = j.data.config;
  }
  function fetchArchive() {
    return getJSON('ecowitt.json').then(function (j) {
      var changed = !S.arch || j.data.archiveTime !== S.arch.archiveTime;
      setArchive(j);
      if (changed) renderAll(); else { safe(renderSky, 'sky'); safe(renderHeader, 'header'); }
    }).catch(function () { safe(renderHeader, 'header'); });
  }
  function fetchLive() {
    if (!S.cfg || !S.cfg.live_url) return Promise.resolve();
    return getJSON(S.cfg.live_url).then(function (raw) {
      S.liveFails = 0;
      var j = normaliseLive(raw);
      if (!j || (S.live && j.written === S.live.written)) return;
      S.live = j;
      var fm = S.cfg.fields, d = j.data || {};
      if (isNum(d[fm.windSpeed])) {
        S.windBuf.push({ t: j.written, v: d[fm.windSpeed], d: isNum(d[fm.windDir]) ? d[fm.windDir] : null });
        while (S.windBuf.length && j.written - S.windBuf[0].t > 600) S.windBuf.shift();
      }
      renderAll();
    }).catch(function () { S.liveFails++; safe(renderHeader, 'header'); });
  }
  // Accept the driver's ecwLoop.json (a flat loop packet in any unit system) or the
  // skin's own live.json ({written, data} in METRICWX). Returns {written, data} in METRICWX.
  var US_UNITS = 1, METRIC = 16, METRICWX = 17;
  function toMetricWX(v, group, us) {
    if (!isNum(v) || us === METRICWX) return v;
    switch (group) {
      case 'group_temperature': return us === US_UNITS ? (v - 32) * 5 / 9 : v;
      case 'group_speed': case 'group_speed2': return us === US_UNITS ? v * 0.44704 : v / 3.6;
      case 'group_rain': return us === US_UNITS ? v * 25.4 : v * 10;
      case 'group_rainrate': return us === US_UNITS ? v * 25.4 : v * 10;
      case 'group_pressure': return us === US_UNITS ? v * 33.8639 : v;
      case 'group_distance': return us === US_UNITS ? v * 1.609344 : v;
      default: return v;
    }
  }
  function normaliseLive(raw) {
    if (!raw || typeof raw !== 'object') return null;
    if (raw.data && raw.written) return raw;                       // skin's live.json
    var us = raw.usUnits, groups = S.cfg.groups || {}, data = {};
    Object.keys(raw).forEach(function (k) { data[k] = toMetricWX(raw[k], groups[k], us); });
    return { written: raw.dateTime || now(), data: data };
  }
  function scheduleLive() {
    var base = (S.cfg && S.cfg.live_poll) || 5;
    var delay = S.liveFails > 3 ? Math.max(60, base) : base;  // back off when there is no live file
    setTimeout(function () { fetchLive().then(scheduleLive, scheduleLive); }, delay * 1000);
  }

  // ---------------------------------------------------------------- init
  // ---------------------------------------------------------------- layout
  // landscape: the console layout (1280 x 800), fitted to the screen
  // portrait:  tablet held upright (800 x 1518), fitted to the screen
  // phone:     narrow screens, one column (540 wide), fitted to the width and scrolling
  var STAGES = { landscape: [1280, 800], portrait: [800, 1518], phone: [540, 2062] };
  function autoLayout(w, h) {
    if (w >= h) return 'landscape';
    return w < 600 ? 'phone' : 'portrait';
  }
  function fit() {
    var w = window.innerWidth, h = window.innerHeight;
    var pref = (S.opts && S.opts.layout) || 'auto';
    var layout = STAGES[pref] ? pref : autoLayout(w, h);
    if (document.body.getAttribute('data-layout') !== layout) {
      document.body.setAttribute('data-layout', layout);
      S.layout = layout;
      if (S.arch) safe(renderSky, 'sky');
    }
    var W = STAGES[layout][0], H = STAGES[layout][1];
    // phones scroll vertically; the other layouts fit the whole screen
    var s = layout === 'phone' ? w / W : Math.min(w / W, h / H);
    var sizer = $('sizer');
    sizer.style.width = Math.floor(W * s) + 'px';
    sizer.style.height = Math.floor(H * s) + 'px';
    $('stage').style.transform = 'scale(' + s + ')';
    $('viewport').style.alignItems = H * s > h + 1 ? 'flex-start' : 'center';
  }
  function init() {
    window.addEventListener('resize', fit);
    window.addEventListener('orientationchange', function () { setTimeout(fit, 250); });
    var rz; window.addEventListener('resize', function () {
      clearTimeout(rz); rz = setTimeout(function () { var c = chartCache[S.chartPeriod]; if (S.panel === 'Charts' && c) renderCharts(c.data); }, 300);
    });
    setArchive(window.ECCE_INITIAL);
    if (!S.cfg) { document.body.innerHTML = '<p style="padding:2em">' + esc(_t('No data from WeeWX yet.')) + '</p>'; return; }
    S.opts = lsGet('opts', null) || { theme: S.cfg.theme || 'navy', clock24: S.cfg.clock_24h !== false, seconds: true, wake: false };
    S.chIdx = lsGet('chIdx', 0) || 0;
    S.unitKey = loadUnitKey();
    S.lang = loadLangKey();
    // A change made in another DivumWX/EcowittConsoleEmulator tab on the same site
    window.addEventListener('storage', function (e) {
      if (e.key === UNIT_KEY) { S.unitKey = loadUnitKey(); renderAll(); }
      else if (e.key === LANG_KEY) { S.lang = loadLangKey(); applyLanguage(); }
    });
    S.rainSrc = lsGet('rainSrc', null);
    S.baroMode = lsGet('baroMode', S.cfg.baro_mode || 'REL');
    applyOpts();
    fit();
    buildToolbar();
    buildWind();
    // The page is drawn once the language has loaded, so it doesn't flash English first
    loadLang(S.lang).then(start, start);
  }
  function start() {
    translateStatic();
    renderAll();
    tickClock();
    // labels are fitted to their places by measuring them: again once the web font is in
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { renderAll(); });
    setInterval(tickClock, 1000);
    setInterval(fetchArchive, Math.max(15, S.cfg.archive_poll || 60) * 1000);
    setInterval(function () { safe(renderSky, 'sky'); safe(renderTopRight, 'soil/lightning'); safe(renderBaro, 'baro'); }, 60000);
    fetchLive().then(scheduleLive, scheduleLive);
    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'visible') { if (S.opts.wake) wake(true); fetchArchive(); fetchLive(); }
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
