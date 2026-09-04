/* ===== cardI18n.js ===== */
try {
/*
##############################################################################################
# cardI18n.js version 0.0.3
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardI18n.js =====================
//
// Loads jsondata/strings.json ONCE for the whole page -- generated
// server-side by strings.json.tmpl, which now contains EVERY language's
// full phrase set in one payload, nested by code:
//
//   {"_default": "da", "da": {"Temperature": "Temperatur", ...},
//    "fr": {...}, ...}
//
// "_default" is this report's own configured WeeWX `lang` setting -- the
// language a fresh visitor sees before ever touching the language
// dropdown. Everything else is a real language code mapped to that
// language's dictionary.
//
// window.DivumWXI18N.t(key)
//   Synchronous lookup against the CURRENTLY ACTIVE language (see
//   setLanguage() below). Returns the translated string if the payload
//   has loaded and the active language's dictionary has an entry for
//   `key`, otherwise returns `key` itself unchanged -- correct, not just
//   a fallback of convenience, because every key IS the English phrase
//   (same convention as the server-side [Texts] files). A card can call
//   t() before the fetch resolves and will simply get English back for
//   that first paint.
//
// window.DivumWXI18N.applyLabel(el, key)
//   For text that gets set ONCE at card-boot time and never touched
//   again afterwards (a card's own addChipRow(label) helper is the main
//   case). Plain t(key) is wrong for that case for two separate reasons,
//   both handled here: (1) card boot always runs before the initial
//   fetch can possibly resolve (JS is single-threaded), so a bare t()
//   call at boot always returns the English fallback, permanently, even
//   after the payload loads; (2) even after it loads, switching language
//   later needs this same text updated again, and nothing else would
//   ever revisit it. applyLabel sets el.textContent = t(key) right away
//   (same correct English-first-paint behaviour as t()), and PERMANENTLY
//   registers the {el, key} pair -- re-applied not just once when the
//   payload first loads, but every time setLanguage() is called
//   afterwards too. Cards using addChipRow(DivumWXI18N.t('X')) need to
//   change to addChipRow('X') and have addChipRow itself call
//   DivumWXI18N.applyLabel(labelEl, label) instead of a bare
//   labelEl.textContent = label -- see cardTemperature.js's addChipRow
//   for a card that never needed this fix, because it rebuilds every
//   label fresh inside renderCard() instead of once at boot (and so
//   picks up a live language switch correctly too, via the 'i18nready'
//   re-render below).
//
// window.DivumWXI18N.applyAttr(el, attr, key)
//   The same idea as applyLabel, for setAttribute-based text (tooltips'
//   data-title, mainly). Same permanent registration, same re-apply on
//   every setLanguage() call.
//
// window.DivumWXI18N.getLanguage()
//   The currently active language code.
//
// window.DivumWXI18N.getLanguageName(code)
//   That language's own self-name, read directly from its [Texts]
//   section's "Language" key (e.g. "Dansk", "Français", "العربية") --
//   NOT translated into the currently active language, always that
//   language's own name for itself, the way a language picker should
//   read. Falls back to the raw code if that language isn't in the
//   loaded payload. Lets a language-picker dropdown build its own
//   option labels straight from the same payload everything else reads
//   from, rather than needing a second hardcoded code-to-name list kept
//   in sync separately (which is exactly the trap DIVUMWX_LANG_CHOICES
//   in install.py already had to be careful about on the server side).
//
// window.DivumWXI18N.getLanguageFlagEmoji(code) /
// window.DivumWXI18N.getLanguageFlagUrl(code)
//   A representative country flag for that language -- a judgment call
//   for languages with no country of their own (Breton, Catalan, Welsh,
//   Basque) or spoken across several (Arabic, Hindi, Tamil, Urdu), see
//   LANGUAGE_FLAG_COUNTRY's own comment for the specific choices made.
//   getLanguageFlagEmoji returns a Unicode flag emoji (works directly as
//   plain text, including inside a native <option> -- real image files
//   can't be embedded in <option> elements in any browser); returns ''
//   if the code isn't recognized. getLanguageFlagUrl returns a path to
//   the matching SVG under img/flags/ (for use in an actual <img>
//   element next to the closed selector, where images work fine -- it's
//   only inside the open <option> list itself that's restricted to
//   plain text). Both driven by the same country-code table, so the
//   emoji and the SVG can never show two different countries for the
//   same language.
//
// window.DivumWXI18N.getAvailableLanguages()
//   Array of every language code present in the loaded payload (empty
//   array before the payload has loaded) -- e.g. for a language-picker
//   dropdown to populate its own options from, rather than hardcoding
//   the list separately somewhere else. Order matches the order
//   strings.json.tmpl's Python side produced them in (alphabetical by
//   code), not necessarily the order a UI wants to display them in.
//
// window.DivumWXI18N.setLanguage(code)
//   Switches the active language, in the browser, with no server round
//   trip -- every language's text already arrived in the one payload
//   fetch. Persists the choice to localStorage (key 'dashboardLanguage',
//   read back on the next page load ahead of "_default") so it survives
//   a refresh, same convention as the existing unit-system dropdown's
//   'dashboardUnitSystem' key. No-ops (returns false) if `code` isn't a
//   language actually present in the loaded payload, rather than
//   silently switching to an all-English-fallback state.  Re-applies
//   every applyLabel/applyAttr-registered element immediately, then
//   fires 'i18nready' again so every card's own re-render listener (the
//   same one used for the very first load) picks up the switch too --
//   this is deliberately the SAME event as the initial-load signal, not
//   a separate 'languagechange' event, so no card needs new listener
//   code to support live switching; whatever already made a card
//   correctly show translations on page load makes it correctly react
//   to a live switch too. Returns true on a successful switch.
//
// window.DivumWXI18N.ready
//   A Promise that resolves once the initial payload load has settled
//   (loaded or failed -- a network hiccup here should degrade to "page
//   stays in English", never break the page).
//
// 'i18nready' event on window
//   Fired once after the initial payload loads, AND again every time
//   setLanguage() successfully switches languages. Cards do this via the
//   same "cache lastData, re-render on an event" pattern already used
//   for 'unitsystemchange' and 'resize' (see cardTemperature.js) -- one
//   more event in that same family, not a new pattern. This event alone
//   does NOT fix applyLabel/applyAttr-created labels -- those are
//   handled internally, automatically, without the card needing to do
//   anything on this event.
(function(){
  var STRINGS_JSON_URL = './jsondata/strings.json';
  var LANG_STORAGE_KEY = 'dashboardLanguage';
  var payload = null;      // the full {"_default": "...", "da": {...}, ...} object once loaded
  var activeLang = null;   // resolved once the payload loads: localStorage override, or "_default"
  var loaded = false;
  var registeredLabels = []; // {el, key} pairs -- permanent, re-applied on every language switch
  var registeredAttrs = [];  // {el, attr, key} pairs -- same

  function t(key){
    if (loaded && payload[activeLang] && Object.prototype.hasOwnProperty.call(payload[activeLang], key)) {
      return payload[activeLang][key];
    }
    return key;
  }

  function applyLabel(el, key){
    el.textContent = t(key);
    registeredLabels.push({ el: el, key: key });
  }

  function applyAttr(el, attr, key){
    el.setAttribute(attr, t(key));
    registeredAttrs.push({ el: el, attr: attr, key: key });
  }

  function getLanguage(){
    return activeLang;
  }

  function getLanguageName(code){
    return (payload && payload[code] && payload[code]['Language']) || code;
  }

  // Flags are COUNTRY (or, for the four marked below, REGION) symbols,
  // not language symbols, so this is a deliberate representative choice
  // for every code, not a lookup that could be derived automatically --
  // most are a direct match (fr->fr, de->de) but several of DivumWX's
  // languages are regional/minority languages spoken across multiple
  // countries (Arabic, Hindi, Tamil, Urdu), where the "obvious" flag is
  // a judgment call, not a fact. Two of the country ones are NOT the
  // same 2 letters as the language code, on purpose -- 'da' (Danish)
  // needs Denmark's flag ('dk'), not a (nonexistent) country called
  // "da"; 'uk' (Ukrainian) needs Ukraine's flag ('ua'), NOT the United
  // Kingdom's ('gb') -- a genuinely easy mix-up since "UK" reads as
  // "United Kingdom" to a human but is this project's language code for
  // Ukrainian, inherited from ISO 639-1. Likewise 'sv' (Swedish) needs
  // Sweden's flag ('se'), NOT El Salvador's ('sv' is El Salvador's ISO
  // 3166-1 country code, an entirely unrelated coincidence).
  //
  // cy/ca/eu/br use actual REGIONAL flags (Wales, Catalonia, Basque
  // Country, Brittany), not a nearby country's flag -- sourced from
  // HatScripts/circle-flags (MIT licensed), the values below are that
  // project's own subdivision codes (gb-wls, es-ct, es-pv, fr-bre), not
  // ISO 3166-1 country codes, since none of these four regions has one
  // of their own. These four are also circular artwork, not the
  // rectangular style every other flag in img/flags/ uses -- a real,
  // visible style inconsistency, traded deliberately for actual
  // correctness (a Welsh person's own flag, not the Union Jack) rather
  // than left as the earlier country-flag approximation.
  var LANGUAGE_FLAG_COUNTRY = {
    ar: 'sa',      // Arabic -> Saudi Arabia (representative choice; Arabic has no single country)
    br: 'fr-bre',  // Breton -> Brittany (regional flag, not France's)
    ca: 'es-ct',   // Catalan -> Catalonia (regional flag, not Spain's)
    cn: 'cn',      // Chinese -> China
    cy: 'gb-wls',  // Welsh -> Wales (regional flag, not the UK's)
    cz: 'cz',      // Czech -> Czech Republic
    da: 'dk',      // Danish -> Denmark (NOT "da" -- no such country code)
    de: 'de',      // German -> Germany
    en: 'gb',      // English -> United Kingdom (this project's own default/reference)
    en_US: 'us',   // English (US) -> United States
    es: 'es',      // Spanish -> Spain
    eu: 'es-pv',   // Basque -> Basque Country (regional flag; also spoken in France, but this is the larger Spanish side)
    fr: 'fr',      // French -> France
    gr: 'gr',      // Greek -> Greece
    hi: 'in',      // Hindi -> India
    it: 'it',      // Italian -> Italy
    nl: 'nl',      // Dutch -> Netherlands
    no: 'no',      // Norwegian -> Norway
    pl: 'pl',      // Polish -> Poland
    pt: 'pt',      // Portuguese -> Portugal
    sv: 'se',      // Swedish -> Sweden (NOT "sv" -- that's El Salvador's country code)
    fi: 'fi',      // Finnish -> Finland
    hu: 'hu',      // Hungarian -> Hungary
    is: 'is',      // Icelandic -> Iceland
    ta: 'in',      // Tamil -> India (representative choice; also widely spoken in Sri Lanka)
    th: 'th',      // Thai -> Thailand
    tr: 'tr',      // Turkish -> Turkey
    uk: 'ua',      // Ukrainian -> Ukraine (NOT "uk"/United Kingdom -- see note above)
    ur: 'pk'       // Urdu -> Pakistan
  };
  // Same country-code table drives both the emoji (built from Unicode
  // "regional indicator symbol" letters -- every flag emoji is just two
  // of these back to back) and the real SVG file path, so the two can
  // never drift out of sync with each other.
  function countryCodeToEmoji(cc){
    if (!cc || cc.length !== 2) return '';
    var A = 0x1F1E6, base = 'a'.charCodeAt(0);
    return String.fromCodePoint(A + (cc.charCodeAt(0) - base)) +
           String.fromCodePoint(A + (cc.charCodeAt(1) - base));
  }
  function getLanguageFlagEmoji(code){
    var cc = LANGUAGE_FLAG_COUNTRY[code];
    return cc ? countryCodeToEmoji(cc) : '';
  }
  function getLanguageFlagUrl(code){
    var cc = LANGUAGE_FLAG_COUNTRY[code];
    return cc ? ('./img/flags/' + cc + '.svg') : '';
  }

  function getAvailableLanguages(){
    if (!loaded) return [];
    return Object.keys(payload).filter(function(k){ return k.indexOf('_') !== 0; });
  }

  function reapplyAll(){
    registeredLabels.forEach(function(p){ p.el.textContent = t(p.key); });
    registeredAttrs.forEach(function(p){ p.el.setAttribute(p.attr, t(p.key)); });
  }

  function setLanguage(code){
    if (!loaded || !payload[code]) return false;
    activeLang = code;
    try { localStorage.setItem(LANG_STORAGE_KEY, code); } catch (e) {}
    reapplyAll();
    window.dispatchEvent(new CustomEvent('i18nready'));
    return true;
  }

  var resolveReady;
  var ready = new Promise(function(resolve){ resolveReady = resolve; });

  fetch(STRINGS_JSON_URL, {cache: 'no-store'})
    .then(function(r){
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(json){
      payload = json || {};
      var saved = null;
      try { saved = localStorage.getItem(LANG_STORAGE_KEY); } catch (e) {}
      // A saved choice only wins if that language actually exists in
      // THIS payload -- a station that's since dropped a language (or a
      // stale value from a much older install) falls back to the
      // server's own configured default instead of silently landing on
      // an all-English page.
      activeLang = (saved && payload[saved]) ? saved : (payload['_default'] || 'en');
      loaded = true;
      // Fix up every label/attr that was registered before this point --
      // this is the only thing that makes applyLabel/applyAttr different
      // from a bare t()/setAttribute call at boot time.
      reapplyAll();
      resolveReady();
    })
    .catch(function(e){
      console.warn('cardI18n: strings.json fetch failed \u2014 staying in English:', e.message);
      payload = {};
      activeLang = 'en';
      loaded = true; // so t() falls through to the (already-correct) English key cleanly
      resolveReady();
    })
    .then(function(){
      // Deliberately OUTSIDE the fetch/parse .then()-.catch() pair above,
      // in its own link of the chain: dispatchEvent is a native browser
      // method that should never throw in practice, but if it somehow
      // did, we don't want that exception being mistaken for a fetch
      // failure and reverting payload/activeLang back to the all-English
      // fallback state right after a genuinely successful load.
      window.dispatchEvent(new CustomEvent('i18nready'));
    });

  window.DivumWXI18N = {
    t: t,
    applyLabel: applyLabel,
    applyAttr: applyAttr,
    getLanguage: getLanguage,
    getLanguageName: getLanguageName,
    getLanguageFlagEmoji: getLanguageFlagEmoji,
    getLanguageFlagUrl: getLanguageFlagUrl,
    getAvailableLanguages: getAvailableLanguages,
    setLanguage: setLanguage,
    ready: ready
  };
})();

} catch (e) {
  console.error("cardsBundle: cardI18n.js failed:", e);
}
/* ===== cardClockOutlook.js ===== */
try {
/*
##############################################################################################
# cardClockOutlook.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardClockOutlook.js =====================
(function(){

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit', weekday: 'short'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function toStationFakeUtc(realDate){
    var p = stationParts(realDate);
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function parseWallClockIso(str){
    var m = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(str);
    if (!m) return new Date(NaN);
    return new Date(Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]));
  }
  function utcLabelFor(date){
    var p = stationParts(date);
    var asUTC = Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second);
    var mins = Math.round((asUTC - date.getTime()) / 60000);
    var sign = mins >= 0 ? '+' : '-';
    var abs = Math.abs(mins);
    var hh = Math.floor(abs / 60), mm = abs % 60;
    return 'UTC' + sign + hh + (mm ? ':' + (mm < 10 ? '0' : '') + mm : '');
  }
  function ordinal(n){
    var s = ['th', 'st', 'nd', 'rd'], v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
  }
  var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  function dateStrFor(date){
    var p = stationParts(date);
    return p.weekday + ' ' + ordinal(+p.day) + ' ' + MONTHS[+p.month - 1] + ' ' + p.year;
  }
  function pad(n){ return n < 10 ? '0' + n : String(n); }
  function formatUptime(totalSeconds){
    if (typeof totalSeconds !== 'number' || isNaN(totalSeconds)) return null;
    totalSeconds = Math.max(0, Math.floor(totalSeconds));
    var d = Math.floor(totalSeconds / 86400);
    var h = Math.floor((totalSeconds % 86400) / 3600);
    var m = Math.floor((totalSeconds % 3600) / 60);
    var s = totalSeconds % 60;
    return d + 'd ' + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
  }
  function archField(obj, key){
    if (obj && typeof obj[key] === 'number') return obj[key];
    if (obj && obj.meta && typeof obj.meta[key] === 'number') return obj.meta[key];
    return null;
  }

  var mount = document.getElementById('stationTimeClock');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (link removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var ringColor   = '#6b9b6f';
  var handColor   = 'var(--bs-body-color)';
  var dateColor   = 'var(--bs-body-color)';
  var digitalBg   = ringColor;
  var digitalCol  = '#fff';
  var centreColor = 'var(--bw-accent)';
  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '10px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'StationTime | Outlook');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad(t.getUTCHours()) + ':' + pad(t.getUTCMinutes()) + ':' + pad(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: clock + time/date/uptime, right: outlook) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.flexDirection = 'column';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  leftPane.style.padding = '3px 10px';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.alignItems = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // -- Analogue clock face. R=42 (was 50) — the full time/date/uptime
  // stack below didn't fit the 175px pane at R=50 without clipping the
  // top of the clock and hiding the server uptime line off the bottom;
  // this, plus the tightened gaps below, is what fixes that overflow.
  var W = 180, cx = 90, cy = 50, R = 42;
  var clockWrap = document.createElement('div');
  clockWrap.style.flex = '0 0 auto';
  clockWrap.style.width = '100%';
  leftPane.appendChild(clockWrap);

  var svg = d3.select(clockWrap).append('svg').attr('viewBox', '0 0 ' + W + ' 100').attr('width', '100%').attr('height', '100');

  svg.append('circle')
    .attr('cx', cx).attr('cy', cy).attr('r', R)
    .style('stroke', ringColor).style('stroke-width', 2.5).style('fill', 'none');

  var tickG = svg.append('g');
  for (var t12 = 0; t12 < 12; t12++){
    var ang = t12 * 30 * Math.PI / 180;
    var outerR = R, innerR = R - (t12 % 3 === 0 ? 8 : 5);
    tickG.append('line')
      .attr('x1', cx + outerR * Math.sin(ang)).attr('y1', cy - outerR * Math.cos(ang))
      .attr('x2', cx + innerR * Math.sin(ang)).attr('y2', cy - innerR * Math.cos(ang))
      .style('stroke', ringColor).style('stroke-width', t12 % 3 === 0 ? 1.5 : 1);
  }

  var analogContent = svg.append('g')
    .attr('class', 'analog-content')
    .attr('transform', 'translate(' + cx + ',' + cy + ')');

  var hourScale   = d3.scaleLinear().domain([0, 12]).range([0, 360]);
  var minuteScale = d3.scaleLinear().domain([0, 60]).range([0, 360]);
  var secondScale = d3.scaleLinear().domain([0, 60]).range([0, 360]);
  var handData = [
    { label: 'hours',   scale: hourScale,   length: -R * 0.5,  color: handColor },
    { label: 'minutes', scale: minuteScale, length: -R * 0.7,  color: handColor },
    { label: 'seconds', scale: secondScale, length: -R * 0.8,  color: '#e74c3c' }
  ];
  analogContent.selectAll('.hands')
    .data(handData).enter()
    .append('g')
    .attr('class', function(d){ return 'hands analog-' + d.label; })
    .append('line')
    .attr('x1', 0).attr('y1', 0).attr('x2', 0)
    .attr('y2', function(d){ return d.length; })
    .style('stroke', function(d){ return d.color; }).style('stroke-width', 1.5);

  svg.append('circle')
    .attr('cx', cx).attr('cy', cy).attr('r', 3.5)
    .style('fill', centreColor);

  // -- Date, digital time (pill) and uptime, stacked below the clock face --
  var dateTextDiv = document.createElement('div');
  dateTextDiv.style.flex = '0 0 auto';
  dateTextDiv.style.marginTop = '2px';
  dateTextDiv.style.fontSize = '11px';
  dateTextDiv.style.color = dateColor;
  dateTextDiv.style.whiteSpace = 'nowrap';
  leftPane.appendChild(dateTextDiv);

  var digitalTimeDiv = document.createElement('div');
  digitalTimeDiv.style.flex = '0 0 auto';
  digitalTimeDiv.style.marginTop = '3px';
  digitalTimeDiv.style.display = 'inline-block';
  digitalTimeDiv.style.padding = '2px 12px';
  digitalTimeDiv.style.borderRadius = '10px';
  digitalTimeDiv.style.background = digitalBg;
  digitalTimeDiv.style.color = digitalCol;
  digitalTimeDiv.style.fontSize = '11.5px';
  digitalTimeDiv.style.fontFamily = "'IBM Plex Mono', ui-monospace, monospace";
  digitalTimeDiv.style.whiteSpace = 'nowrap';
  leftPane.appendChild(digitalTimeDiv);

  function tick(){
    var t = stationNow();
    handData[0].value = (t.getUTCHours() % 12) + t.getUTCMinutes() / 60;
    handData[1].value = t.getUTCMinutes();
    handData[2].value = t.getUTCSeconds();
    svg.selectAll('.hands').data(handData)
      .attr('transform', function(d){ return 'rotate(' + d.scale(d.value) + ')'; });

    var now = new Date();
    digitalTimeDiv.textContent = pad(t.getUTCHours()) + ':' + pad(t.getUTCMinutes()) + ':' + pad(t.getUTCSeconds()) + ' ' + utcLabelFor(now);
    dateTextDiv.textContent = dateStrFor(now);
    updateUptimeDisplay();
  }

  var uptimeDiv = document.createElement('div');
  uptimeDiv.style.flex = '0 0 auto';
  uptimeDiv.style.width = '100%';
  uptimeDiv.style.boxSizing = 'border-box';
  uptimeDiv.style.marginTop = '3px';
  uptimeDiv.style.fontSize = '8.5px';
  uptimeDiv.style.lineHeight = '1.2';
  uptimeDiv.style.textAlign = 'center';
  uptimeDiv.style.color = overlayTextColor;
  uptimeDiv.style.opacity = '0.85';
  leftPane.appendChild(uptimeDiv);

  var stationUptimeBaseSec = null, serverUptimeBaseSec = null, uptimeBaseMs = null;
  function updateUptimeDisplay(){
    if (stationUptimeBaseSec == null && serverUptimeBaseSec == null){
      uptimeDiv.textContent = '';
      return;
    }
    var elapsed = uptimeBaseMs != null ? Math.floor((Date.now() - uptimeBaseMs) / 1000) : 0;
    var stationStr = stationUptimeBaseSec != null ? formatUptime(stationUptimeBaseSec + elapsed) : '\u2014';
    var serverStr = serverUptimeBaseSec != null ? formatUptime(serverUptimeBaseSec + elapsed) : '\u2014';
    var lineStyle = 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
    uptimeDiv.innerHTML =
      '<div style="' + lineStyle + '">Station uptime: <b>' + stationStr + '</b></div>' +
      '<div style="' + lineStyle + '">Server uptime: <b>' + serverStr + '</b></div>';
  }
  function refreshUptime(){
    var ARCHIVE_JSON_URL = './jsondata/archive.json';
    fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(data){
        stationUptimeBaseSec = archField(data, 'uptime_seconds');
        serverUptimeBaseSec = archField(data, 'os_uptime');
        uptimeBaseMs = Date.now();
        updateUptimeDisplay();
      })
      .catch(function(e){
        console.warn('cardClockOutlook: archive.json (uptime) fetch failed \u2014', e.message);
      });
  }
  refreshUptime();
  setInterval(refreshUptime, 5 * 60 * 1000);

  tick();
  setInterval(tick, 1000);

  // -- Outlook phrase, right pane --------------------------------------
  var outlookDiv = document.createElement('div');
  outlookDiv.style.width = '100%';
  outlookDiv.style.boxSizing = 'border-box';
  outlookDiv.style.maxHeight = '100%';
  outlookDiv.style.overflow = 'hidden';
  outlookDiv.style.fontSize = '10.5px';
  outlookDiv.style.lineHeight = '1.4';
  outlookDiv.style.color = overlayTextColor;
  rightPane.appendChild(outlookDiv);

  // Whole card is a click-through to the climatological summary report —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of titleBar/contentWrap and actually receives the
  // click. top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above) — same technique used on the
  // Forecast card and to fix the card outline extending into the card
  // below.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'climate.html?embed=1';
  cardLink.setAttribute('data-modal', 'Climate Summary');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Climatological Summary');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-url', 'climate.html?embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastForecastJson) renderOutlook(lastForecastJson);
    }
  });

  function pickKey(Hh, candidates){
    for (var i = 0; i < candidates.length; i++){ if (Hh[candidates[i]] !== undefined) return candidates[i]; }
    return null;
  }
  var COMPASS_16 = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
  function compassOf(deg){
    var idx = Math.round((((deg % 360) + 360) % 360) / 22.5) % 16;
    return DivumWXI18N.t(COMPASS_16[idx]);
  }

  function computeOutlookHtml(data, unitsCfg){
    if (!data || !data.hourly) throw new Error('Invalid forecast JSON — missing hourly');
    var Hh = data.hourly;
    var HU = data.hourly_units || {};
    var timeUnit = String(HU.time || 'iso8601').toLowerCase();

    var kTemp   = pickKey(Hh, ['temperature_2m']);
    var kPrecip = pickKey(Hh, ['precipitation', 'rain']);
    var kWcode  = pickKey(Hh, ['weathercode', 'weather_code']);
    var kWspd   = pickKey(Hh, ['windspeed_10m', 'wind_speed_10m']);
    var kWgust  = pickKey(Hh, ['windgusts_10m', 'wind_gusts_10m']);
    var kWdir   = pickKey(Hh, ['winddirection_10m', 'wind_direction_10m']);

    var wantTempF = unitsCfg.temp === 'F';
    var tempSuffix = wantTempF ? ' \u00B0F' : ' \u00B0C';

    var wantWind, speedSuffix;
    switch (unitsCfg.wind) {
      case 'mph': wantWind = 'mph'; speedSuffix = ' mph';  break;
      case 'kmh': wantWind = 'kmh'; speedSuffix = ' km/h'; break;
      case 'kt':  wantWind = 'kn';  speedSuffix = ' kt';   break;
      case 'ms':  wantWind = 'ms';  speedSuffix = ' m/s';  break;
      default:    wantWind = 'kmh'; speedSuffix = ' km/h'; break;
    }
    var wantRainIn = unitsCfg.rain === 'in';

    var uTemp = HU.temperature_2m || '\u00B0C';
    var uPrecip = HU[kPrecip] || 'mm';
    var uWind = HU[kWspd] || HU[kWgust] || 'm/s';

    function toC(v){ return (uTemp === '\u00B0F' || uTemp.toLowerCase() === 'fahrenheit') ? (v - 32) * 5 / 9 : v; }
    function fromC(vC){ return wantTempF ? (vC * 9 / 5 + 32) : vC; }
    function precipToMM(v){ return uPrecip.toLowerCase().indexOf('in') !== -1 ? v * 25.4 : v; }
    function windToMS(v){
      var n = uWind.toLowerCase().replace(/\s/g, '');
      if (n.indexOf('mph') !== -1) return v * 0.44704;
      if (n.indexOf('kmh') !== -1 || n.indexOf('kph') !== -1 || n.indexOf('km') !== -1) return v / 3.6;
      if (n.indexOf('kn') !== -1 || n.indexOf('kt') !== -1) return v * 0.514444;
      return v;
    }
    function windFromMS(v){
      if (wantWind === 'mph') return v / 0.44704;
      if (wantWind === 'kmh') return v * 3.6;
      if (wantWind === 'kn')  return v / 0.514444;
      return v;
    }

    var times = Hh.time;
    var hoursAhead = 3;
    var now = stationNow();

    var startIdx = 0;
    for (var i = 0; i < times.length; i++){
      var raw = times[i];
      var dt = (timeUnit === 'unixtime' || typeof raw === 'number')
        ? toStationFakeUtc(new Date(Number(raw) * 1000))
        : parseWallClockIso(String(raw));
      if (dt >= now) { startIdx = i; break; }
    }
    var endIdx = Math.min(times.length - 1, startIdx + hoursAhead - 1);

    var lastRaw = times[endIdx];
    var lastDt = (timeUnit === 'unixtime' || typeof lastRaw === 'number')
      ? toStationFakeUtc(new Date(Number(lastRaw) * 1000))
      : parseWallClockIso(String(lastRaw));
    var hour = lastDt.getUTCHours();
    var isNight = (hour >= 18 || hour < 6);

    var tempValsC = [], spdVals = [], gustVals = [], dirVals = [];
    var precipTotalMM = 0, precipCode = null;
    for (var j = startIdx; j <= endIdx; j++){
      if (kTemp && Hh[kTemp][j] != null) tempValsC.push(toC(Hh[kTemp][j]));
      if (kPrecip && Hh[kPrecip][j] != null) precipTotalMM += precipToMM(Hh[kPrecip][j]);
      if (kWcode && Hh[kWcode][j] != null) precipCode = Hh[kWcode][j];
      if (kWspd && Hh[kWspd][j] != null) spdVals.push(windToMS(Hh[kWspd][j]));
      if (kWgust && Hh[kWgust][j] != null) gustVals.push(windToMS(Hh[kWgust][j]));
      if (kWdir && Hh[kWdir][j] != null) dirVals.push(Hh[kWdir][j]);
    }

    var selTempOut = null;
    if (tempValsC.length){
      var selTempC = isNight ? Math.min.apply(null, tempValsC) : Math.max.apply(null, tempValsC);
      selTempOut = Math.round(fromC(selTempC));
    }

    var isSnow = precipCode != null && [71,73,75,77,85,86].indexOf(precipCode) !== -1;
    var precipOut, precipUnit;
    if (isSnow){
      if (wantRainIn){ precipOut = Math.round(precipTotalMM / 25.4 * 100) / 100; precipUnit = ' in snow'; }
      else { precipOut = Math.round(precipTotalMM / 10 * 10) / 10; precipUnit = ' cm snow'; }
    } else {
      precipOut = wantRainIn ? Math.round(precipTotalMM / 25.4 * 100) / 100 : Math.round(precipTotalMM * 10) / 10;
      precipUnit = wantRainIn ? ' in rain' : ' mm rain';
    }

    var spdMeanMS = spdVals.length ? spdVals.reduce(function(a, b){ return a + b; }, 0) / spdVals.length : null;
    var gustMaxMS = gustVals.length ? Math.max.apply(null, gustVals) : null;
    var dirMean = dirVals.length ? dirVals.reduce(function(a, b){ return a + b; }, 0) / dirVals.length : null;

    var spdOut  = spdMeanMS != null ? Math.round(windFromMS(spdMeanMS)) : null;
    var gustOut = gustMaxMS != null ? Math.round(windFromMS(gustMaxMS)) : null;
    var dirOut  = dirMean != null ? compassOf(dirMean) : null;

    var until = pad(lastDt.getUTCHours()) + ':' + pad(lastDt.getUTCMinutes());
    var out = [];

    if (selTempOut !== null){
      var tempPhrase = 'Temperature ' + (isNight ? 'low' : 'high') + ' around ' + selTempOut + tempSuffix;
      var windPhrase = '';
      if (dirOut !== null || spdOut !== null || gustOut !== null){
        windPhrase = ', winds ' + (dirOut || '');
        if (spdOut !== null) windPhrase += ' ' + spdOut + speedSuffix;
        if (gustOut !== null && (spdOut === null || gustOut > spdOut)) windPhrase += ' gusting to ' + gustOut + speedSuffix;
      }
      out.push(tempPhrase + windPhrase + '.');
    }

    if (precipTotalMM > 0.05){
      var typeWord = isSnow ? 'Snow' : 'Light rain';
      if (precipTotalMM > 2.0 && !isSnow) typeWord = 'Rain';
      if (precipTotalMM > 5.0 && !isSnow) typeWord = 'Heavy rain';
      out.push(typeWord + ', total ' + precipOut + precipUnit + ' through to ' + until + '.');
    } else {
      out.push('Remaining dry through to ' + until + '.');
    }

    return '<span style="color:' + overlayTextColor + ';font-weight:600;">Outlook For Next Three Hours</span><br><span style="color:var(--bw-accent);">' + out.join(' ') + '</span>';
  }

  var lastForecastJson = null;
  function renderOutlook(json){
    try {
      outlookDiv.innerHTML = computeOutlookHtml(json, currentUnits);
    } catch (e) {
      console.warn('cardClockOutlook: outlook computation failed —', e.message);
      outlookDiv.innerHTML = '';
    }
  }
  function refreshOutlook(){
    var FORECAST_JSON_URL = './jsondata/forecastcard.txt';
    fetch(FORECAST_JSON_URL + ((FORECAST_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(json){
        lastForecastJson = json;
        renderOutlook(json);
        setStatus(true);
      })
      .catch(function(e){
        console.warn('cardClockOutlook: forecast fetch failed —', e.message);
        setStatus(false);
      });
  }
  refreshOutlook();
  setInterval(refreshOutlook, 5 * 60 * 1000);
})();
} catch (e) {
  console.error("cardsBundle: cardClockOutlook.js failed:", e);
}

/* ===== alertBar.js ===== */
try {
/*
##############################################################################################
# alertBar.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== alertBar.js =====================

(function(){
  var ADVISORY_ZONE = 'unitedkingdom';
  var POLL_MS = 5 * 60 * 1000;

  var mount = document.getElementById('alertBarMount');
  if (!mount) return;

  function fetchJson(url){
    return fetch(url + (url.indexOf('?') > -1 ? '&' : '?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
  }
  function fetchText(url){
    return fetch(url + (url.indexOf('?') > -1 ? '&' : '?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); });
  }
  // almanac.json now gives raw unix_epoch timestamps rather than
  // pre-formatted strings.
  function fmtEpochDate(ts){
    if (ts == null) return null;
    var opts = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
    try { if (window.StationTime) opts.timeZone = window.StationTime.getTZ(); } catch (e) {}
    return new Date(ts * 1000).toLocaleString(undefined, opts);
  }

  // -- Aurora, same parsing approach as modalGeomagneticChart.html --
  function parseAuroraXml(xmlText){
    var xml = new DOMParser().parseFromString(xmlText, 'text/xml');
    if (xml.querySelector('parsererror')) throw new Error('aurora.txt did not parse as XML');
    var activities = Array.prototype.map.call(xml.querySelectorAll('activity'), function(el){
      return { status: el.getAttribute('status_id') };
    });
    return activities;
  }
  var AURORA_LEVELS = {
    yellow: ['#FFFF09', 'Minor geomagnetic activity', 'Aurora may be visible by eye from Scotland and may be visible by camera from Scotland, northern England and Northern Ireland.'],
    amber:  ['rgb(253,135,6)', 'Amber alert: possible aurora', 'Aurora is likely to be visible by eye from Scotland, northern England and Northern Ireland; possibly visible from elsewhere in the UK. Photographs of aurora are likely from anywhere in the UK.'],
    red:    ['#F30006', 'Red alert: aurora likely', 'It is likely that aurora will be visible by eye and camera from anywhere in the UK.']
  };
  function getAuroraData(xmlText){
    try {
      var activities = parseAuroraXml(xmlText);
      var statusId = (activities.length > 23 ? activities[23] : activities[activities.length - 1] || {}).status || 'green';
      return AURORA_LEVELS[statusId] || null;
    } catch (e) {
      console.warn('alertBar: aurora.txt parse failed —', e.message);
      return null;
    }
  }

  var HEALTH_ALERT_CONFIG = {
    c1: { bg: '#F9F0C7', border: '#E9C143', color: 'yellow', label: 'Yellow Cold Health Alert' },
    c2: { bg: 'rgb(247,223,209)', border: '', color: 'amber', label: 'Amber Cold Health Alert' },
    c3: { bg: 'rgb(169,34,23)', border: '', color: 'red', label: 'Red Cold Health Alert' },
    h1: { bg: '#F9F0C7', border: '#E9C143', color: 'yellow', label: 'Yellow Heat Health Alert' },
    h2: { bg: 'rgb(247,223,209)', border: '', color: 'amber', label: 'Amber Heat Health Alert' },
    h3: { bg: 'rgb(169,34,23)', border: '', color: 'red', label: 'Red Heat Health Alert' }
  };

  function getAlertLevelConfig(level, type){
    if (type === 'flood'){
      switch (Number(level)){
        case 4: return { bg: 'white', border: '#b1b4b6', text: 'black' };
        case 3: return { bg: 'rgb(252,243,231)', border: 'rgb(227,140,80)', text: 'black' };
        case 2:
        case 1: return { bg: 'rgb(251,237,238)', border: 'rgb(208,45,36)', text: 'black' };
        default: return { bg: 'white', border: '#b1b4b6', text: 'black' };
      }
    }
    switch (level){
      case 'MN': return { bg: 'white', text: 'black' };
      case 'MD': return { bg: 'yellow', text: 'black' };
      case 'SV': return { bg: 'orange', text: 'black' };
      case 'EX': return { bg: 'red', text: 'white' };
      default:   return { bg: 'white', text: 'black' };
    }
  }
  function levelFromEventText(event){
    var e = String(event || '').toLowerCase();
    // UK warnings (Met Office, via OpenWeatherMap or the RSS fallback) are
    // literally colour-coded in their own text — trust that colour word
    // directly, checked with word boundaries so an unrelated word that
    // merely CONTAINS "red" (e.g. "prepared", "covered", "required") or
    // "extreme" (e.g. "extremely") can't be mistaken for the colour red
    // the way a plain substring search would. An amber warning whose
    // prose happens to say "extremely difficult conditions" was
    // previously being silently promoted to a red warning this way.
    if (/\bred\b/.test(e)) return 'EX';
    if (/\bamber\b/.test(e)) return 'SV';
    if (/\byellow\b/.test(e)) return 'MD';
    // No explicit colour word found — fall back to generic severity language.
    if (/\bextreme\b/.test(e)) return 'EX';
    if (/\bsevere\b/.test(e)) return 'SV';
    if (/\bminor\b/.test(e)) return 'MN';
    return 'MD';
  }
  function dedupeAlerts(alerts){
    // Keyed on the exact text shown to the person (event + start + end),
    // not on any source/sender field the API response may or may not
    // include -- two alert objects with identical wording and window are
    // a duplicate regardless of what fed them into the array, and two
    // objects with the SAME wording but a genuinely different window are
    // kept as separate, legitimate alerts.
    //
    // Keeps the LATEST occurrence of each key, not the first: OpenWeatherMap's
    // own alerts array can contain both an original alert AND a subsequent
    // re-issue of that SAME alert (same event/start/end, updated
    // description -- e.g. "Information on update: Warning area
    // extended..."), with the update appearing LATER in the array. Keeping
    // the first-seen copy would silently show the stale pre-update text.
    // Display order still follows first-seen position, only the VALUE
    // shown for each slot is the latest one.
    var order = [];
    var latest = {};
    for (var i = 0; i < alerts.length; i++){
      var a = alerts[i] || {};
      var key = String(a.event || '') + '|' + String(a.start || '') + '|' + String(a.end || '');
      if (!(key in latest)) order.push(key);
      latest[key] = a;
    }
    return order.map(function(key){ return latest[key]; });
  }
  var TRIANGLE_ICON = {
    MN: './img/yellow_triangle.svg',
    MD: './img/yellow_triangle.svg',
    SV: './img/orange_triangle.svg',
    EX: './img/red_triangle.svg'
  };

  function fmtDateTime(unixSeconds){
    if (!unixSeconds) return '';
    var d = new Date(unixSeconds * 1000);
    if (isNaN(d.getTime())) return '';
    var WD = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    var pad2 = function(n){ return n < 10 ? '0' + n : String(n); };
    return WD[d.getDay()] + ' ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
  }
  function fmtDateLong(isoOrParsable){
    if (!isoOrParsable) return '';
    var d = new Date(isoOrParsable);
    if (isNaN(d.getTime())) return '';
    var WD = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var h = d.getHours(), suffix = h >= 12 ? 'pm' : 'am', h12 = h % 12 || 12;
    var pad2 = function(n){ return n < 10 ? '0' + n : String(n); };
    return WD[d.getDay()] + ', ' + d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear() + ' at ' + h12 + ':' + pad2(d.getMinutes()) + suffix;
  }
  var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var MONTHS3 = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

  // -- Met Office RSS fallback, ported from parseMetOfficeRSSFromFile() --
  function parseMetOfficeRss(xmlText){
    var xml = new DOMParser().parseFromString(xmlText, 'text/xml');
    if (xml.querySelector('parsererror')) throw new Error('metofficerss.txt did not parse as XML');

    var pubDateEl = xml.querySelector('channel > pubDate');
    var pubDate = pubDateEl ? new Date(pubDateEl.textContent) : null;
    var baseYear = (pubDate && !isNaN(pubDate.getTime())) ? pubDate.getFullYear() : new Date().getFullYear();

    var warnings = [];
    xml.querySelectorAll('channel > item').forEach(function(item){
      var title = (item.querySelector('title') || {}).textContent || '';
      var description = (item.querySelector('description') || {}).textContent || '';

      var color = 'yellow';
      var t = title.toLowerCase();
      if (t.indexOf('red warning') !== -1) color = 'red';
      else if (t.indexOf('amber warning') !== -1) color = 'amber';
      else if (t.indexOf('yellow warning') !== -1) color = 'yellow';

      var level = color === 'red' ? 'EX' : color === 'amber' ? 'SV' : 'MD';

      var m = /valid from (\d{1,4})\s+(\w{3})\s+(\d{1,2})\s+(\w{3})\s+to\s+(\d{1,4})\s+(\w{3})\s+(\d{1,2})\s+(\w{3})/i.exec(description);
      var startDate = null, endDate = null;
      if (m){
        var startTimeRaw = m[1], endTimeRaw = m[5];
        var startTime = startTimeRaw.length === 4 ? startTimeRaw.slice(0,2) + ':' + startTimeRaw.slice(2) : startTimeRaw;
        var endTime = endTimeRaw.length === 4 ? endTimeRaw.slice(0,2) + ':' + endTimeRaw.slice(2) : endTimeRaw;
        var startMonth = m[4], endMonth = m[8];
        var startMonthNum = MONTHS3.indexOf(startMonth.toLowerCase());
        var endMonthNum = MONTHS3.indexOf(endMonth.toLowerCase());

        var startYear = baseYear;
        if (pubDate && pubDate.getMonth() === 11 && startMonth.toLowerCase() === 'jan') startYear += 1;
        var endYear = startYear;
        if (startMonthNum !== -1 && endMonthNum !== -1 && endMonthNum < startMonthNum) endYear += 1;

        startDate = tryParseDate(m[3], startMonthNum, startYear, startTime);
        endDate = tryParseDate(m[7], endMonthNum, endYear, endTime);
      }

      var warningType = title;
      var idx = title.indexOf(' affecting ');
      if (idx !== -1) warningType = title.slice(0, idx);

      warnings.push({ title: title, warningType: warningType, description: description, color: color, level: level, startDate: startDate, endDate: endDate });
    });
    return warnings;
  }
  function tryParseDate(day, monthIdx, year, hhmm){
    if (monthIdx === -1) return null;
    var parts = hhmm.split(':');
    var d = new Date(year, monthIdx, Number(day), Number(parts[0]) || 0, Number(parts[1]) || 0);
    return isNaN(d.getTime()) ? null : d;
  }
  function fmtDayMonHM(d){
    if (!d) return '';
    var WD = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    var pad2 = function(n){ return n < 10 ? '0' + n : String(n); };
    return WD[d.getDay()] + ' ' + d.getDate() + ' ' + MONTHS[d.getMonth()].slice(0,3) + ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
  }

  // -- Section builder, ported from displayAlertSection() --
  var sectionCount = 0;
  function buildSection(opts){
    sectionCount++;
    var id = 'alertSection' + sectionCount;
    var bg = opts.bg || 'white';
    var text = opts.text || 'black';
    var border = opts.border;
    var icon = opts.icon;
    var headlineHtml = opts.headlineHtml || '';
    var descriptionHtml = opts.descriptionHtml || '';

    var section = document.createElement('div');
    section.className = 'alertbar';
    section.id = id;
    section.style.backgroundColor = bg;
    section.style.color = text;
    if (border) section.style.borderLeft = '4px solid ' + border;

    var outer = document.createElement('div');
    outer.className = 'alert-outer';
    // Column stack, set inline (not via the stylesheet) so this is
    // guaranteed regardless of whatever .alert-inner's own row/flex
    // rules do — the description block below is a sibling of the
    // icon+headline row, not a third item inside it.
    outer.style.display = 'flex';
    outer.style.flexDirection = 'column';

    var inner = document.createElement('div');
    inner.className = 'alert-inner';
    if (icon) {
      var img = document.createElement('img');
      img.className = 'alert-icon';
      img.src = icon;
      img.alt = '';
      inner.appendChild(img);
    }
    var headline = document.createElement('div');
    headline.className = 'alert-headline';
    headline.innerHTML = headlineHtml;
    inner.appendChild(headline);
    outer.appendChild(inner);

    if (descriptionHtml) {
      var more = document.createElement('div');
      more.className = 'alert-more';
      more.style.display = 'none';
      // Full width so it reads as a continuation paragraph under the
      // headline, not a second column next to it.
      more.style.width = '100%';
      more.style.boxSizing = 'border-box';
      more.innerHTML = '<p>' + descriptionHtml + '</p>';

      var moreBtn = document.createElement('button');
      moreBtn.type = 'button';
      moreBtn.className = 'alert-read alert-more-btn';
      moreBtn.textContent = DivumWXI18N.t('More');

      var lessBtn = document.createElement('button');
      lessBtn.type = 'button';
      lessBtn.className = 'alert-read alert-less-btn';
      lessBtn.textContent = DivumWXI18N.t('Less');
      more.appendChild(lessBtn);

      function setExpanded(expanded){
        more.style.display = expanded ? 'block' : 'none';
        moreBtn.style.display = expanded ? 'none' : '';
      }
      moreBtn.addEventListener('click', function(){ setExpanded(true); });
      lessBtn.addEventListener('click', function(){ setExpanded(false); });

      headline.appendChild(moreBtn);
      outer.appendChild(more);
    }

    section.appendChild(outer);
    return section;
  }

  function escapeHtml(s){
    var d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
  }

  function render(sections){
    mount.innerHTML = '';
    sections.forEach(function(s){ mount.appendChild(s); });
    mount.classList.toggle('has-content', sections.length > 0);
  }

  function refresh(){
    Promise.allSettled([
      fetchText('./jsondata/aurora.txt'),
      fetchJson('./jsondata/cold.txt'),
      fetchJson('./jsondata/heat.txt'),
      fetchJson('./jsondata/openweathermap.txt'),
      fetchJson('./jsondata/flood.txt'),
      fetchText('./jsondata/metofficerss.txt'),
      fetchJson('./jsondata/archive.json'),
      fetchJson('./jsondata/almanac.json')
    ]).then(function(results){
      var auroraText = results[0].status === 'fulfilled' ? results[0].value : null;
      var cold = results[1].status === 'fulfilled' ? results[1].value : null;
      var heat = results[2].status === 'fulfilled' ? results[2].value : null;
      var owm = results[3].status === 'fulfilled' ? results[3].value : {};
      var flood = results[4].status === 'fulfilled' ? results[4].value : {};
      var rssText = results[5].status === 'fulfilled' ? results[5].value : null;
      var archive = results[6].status === 'fulfilled' ? results[6].value : {};
      var almanac = results[7].status === 'fulfilled' ? results[7].value : {};
      var stationLocation = (archive.meta && archive.meta.station_location) || 'this location';

      if (ADVISORY_ZONE !== 'unitedkingdom') return;

      var sections = [];
      var now = Date.now();

      // -- Aurora (amber/red only — yellow is "Minor geomagnetic activity", skip it) --
      if (auroraText) {
        var aurora = getAuroraData(auroraText);
        if (aurora && aurora !== AURORA_LEVELS.yellow) {
          sections.push(buildSection({
            bg: aurora[0], text: 'black',
            icon: 'img/auroraWatchBlackText.svg',
            headlineHtml: escapeHtml(aurora[1]),
            descriptionHtml: escapeHtml(aurora[2]) +
              ' <p style="text-align:center"><iframe scrolling="no" frameborder="0" allowtransparency="true" width="600" height="530" src="https://aurorawatch.lancs.ac.uk/external/rolling_status_text"></iframe></p>'
          }));
        }
      }

      // -- Eclipse announcement (almanac.json's next_eclipse*, written by
      // SkyfieldLoopData — lunar eclipses only; see that service's own
      // comments for why solar eclipses aren't covered). Not a danger-level
      // warning like the sections above, so it gets its own night-sky
      // colour rather than the yellow/amber/red palette, and only appears
      // once the eclipse is close enough to be worth mentioning. --
      var ECLIPSE_ANNOUNCE_WINDOW_MS = 14 * 24 * 60 * 60 * 1000; // 14 days
      var eclipseTs = almanac && almanac['almanac.next_eclipse.unix_epoch.raw'];
      if (eclipseTs) {
        var eclipseMs = eclipseTs * 1000;
        if (eclipseMs > now && (eclipseMs - now) <= ECLIPSE_ANNOUNCE_WINDOW_MS) {
          sections.push(buildSection({
            bg: '#22315C', text: 'white',
            icon: 'img/eclipse.svg',
            headlineHtml: escapeHtml(almanac['almanac.next_eclipse_kind'] || 'Lunar Eclipse') +
              ' on ' + escapeHtml(fmtEpochDate(eclipseTs) || ''),
            descriptionHtml: 'The Moon passes through the Earth\u2019s shadow. Visibility from ' +
              escapeHtml(stationLocation) + ' depends on whether the Moon is above the horizon at the time — check a dedicated eclipse calculator for local circumstances.'
          }));
        }
      }

      // -- Health alert: cold first, then heat --
      var healthData = null;
      [['cold', cold], ['heat', heat]].some(function(pair){
        var type = pair[0], json = pair[1];
        if (!json || !json.status || json.status === 'No alert' || json.status === 'Green') return false;
        var isActive = true;
        if (json.period_end) {
          var endTime = new Date(json.period_end).getTime();
          if (!isNaN(endTime) && now > endTime) isActive = false;
        }
        if (!isActive) return false;
        var idMap = type === 'cold' ? { Yellow: 'c1', Amber: 'c2', Red: 'c3' } : { Yellow: 'h1', Amber: 'h2', Red: 'h3' };
        healthData = {
          type: type,
          alertId: idMap[json.status] || (type === 'cold' ? 'c1' : 'h1'),
          from: json.period_start, to: json.period_end, updated: json.refresh_date,
          region: json.geography_name || 'South East England',
          riskScore: json.risk_score || 0, text: json.text || ''
        };
        return true;
      });
      if (healthData && HEALTH_ALERT_CONFIG[healthData.alertId]) {
        var hc = HEALTH_ALERT_CONFIG[healthData.alertId];
        var riskText = healthData.riskScore ? (' Risk score: ' + healthData.riskScore + '/10.') : '';
        var headline = hc.label + ' for ' + healthData.region + '. Effective from ' + fmtDateLong(healthData.from) +
          ' to ' + fmtDateLong(healthData.to) + '.' + riskText + ' Updated ' + fmtDateLong(healthData.updated) + '.';
        var iconPrefix = healthData.type === 'cold' ? 'coldHealth' : 'heatHealth';
        var regionSlug = String(healthData.region).toLowerCase().replace(/\s+/g, '-');
        var link = 'https://ukhsa-dashboard.data.gov.uk/weather-health-alerts/' + healthData.type + '/' + regionSlug;
        sections.push(buildSection({
          bg: hc.bg, text: '#000000', border: hc.border,
          icon: 'img/' + iconPrefix + hc.color + '.svg',
          headlineHtml: escapeHtml(headline),
          descriptionHtml: (healthData.text || '') + '<br><br><a href="' + link + '">' + link + '</a>'
        }));
      }

      // -- OpenWeatherMap alerts, else Met Office RSS fallback --
      var owmAlerts = (owm && owm.alerts) || [];
      owmAlerts = dedupeAlerts(owmAlerts);
      var rssFallbackUsed = false;
      if (owmAlerts.length > 0) {
        owmAlerts.forEach(function(alert){
          var event = alert.event || '';
          var level = levelFromEventText(event);
          var cfg = getAlertLevelConfig(level);
          var begins = fmtDateTime(alert.start), expires = fmtDateTime(alert.end);
          var headline = escapeHtml(event) + ' ALERT. From ' + begins + ' to ' + expires + '.  ';
          var metofficeUrl = 'https://www.metoffice.gov.uk/weather/warnings-and-advice/uk-warnings';
          sections.push(buildSection({
            bg: cfg.bg, text: cfg.text, icon: TRIANGLE_ICON[level],
            headlineHtml: headline,
            descriptionHtml: escapeHtml(alert.description || '') + ' Tags: ' + escapeHtml((alert.tags || []).join(', ')) +
              ' <a href="' + metofficeUrl + '">' + metofficeUrl + '</a>'
          }));
        });
      } else if (rssText) {
        try {
          var rssWarnings = parseMetOfficeRss(rssText);
          if (rssWarnings.length > 0) rssFallbackUsed = true;
          rssWarnings.forEach(function(w){
            var cfg = getAlertLevelConfig(w.level);
            var dateInfo = (w.startDate && w.endDate) ? (' From ' + fmtDayMonHM(w.startDate) + ' to ' + fmtDayMonHM(w.endDate) + '. ') : '';
            var link = 'https://www.metoffice.gov.uk/weather/warnings-and-advice/uk-warnings';
            sections.push(buildSection({
              bg: cfg.bg, text: cfg.text, icon: TRIANGLE_ICON[w.level],
              headlineHtml: escapeHtml(w.warningType) + '.' + dateInfo,
              descriptionHtml: escapeHtml(w.description) + ' <a href="' + link + '">View full details on Met Office</a>'
            }));
          });
        } catch (e) {
          console.warn('alertBar: metofficerss.txt parse failed —', e.message);
        }
      }

      // -- Flood alerts (unfiltered — see header comment) --
      var floodItems = (flood && flood.items) || [];
      floodItems.forEach(function(item){
        var cfg = getAlertLevelConfig(item.severityLevel != null ? item.severityLevel : 4, 'flood');
        var updated = item.timeMessageChanged ? fmtDayMonHM(new Date(item.timeMessageChanged)) : '';
        sections.push(buildSection({
          bg: cfg.bg, text: cfg.text, border: cfg.border,
          icon: 'img/flood' + (item.severityLevel != null ? item.severityLevel : 4) + '.svg',
          headlineHtml: escapeHtml(item.severity || 'Flood') + ' for ' + escapeHtml(item.description || '') + '. Updated ' + updated + '.',
          descriptionHtml: escapeHtml(item.message || '') + '.'
        }));
      });

      render(sections);
    }).catch(function(e){
      console.warn('alertBar: refresh failed —', e.message);
    });
  }

  refresh();
  setInterval(refresh, POLL_MS);
  // No i18nready listener existed here at all previously -- without one,
  // the compass/More-Less/section text was only ever guaranteed correct
  // on the NEXT scheduled poll (up to POLL_MS away), not immediately
  // once translations load, unlike every other card.
  window.addEventListener('i18nready', refresh);
})();
} catch (e) {
  console.error("cardsBundle: alertBar.js failed:", e);
}

/* ===== cardCurrent.js ===== */
try {
/*
##############################################################################################
# cardCurrent.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardCurrent.js =====================

(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var ASTRO_JSON_URL   = './jsondata/almanac.json';
  var CLOUD_JSON_URL   = './jsondata/cloud_coverage.json';
  var ME_JSON_URL      = './jsondata/me.txt';
  var POLL_MS = 30 * 1000;
  var ICON_BASE = './meteocons/fill/svg/';

  function pickIcon(d){
    var night = !d.isDay;



    if (d.rainRate > 0 && d.windSpeedAvg > 15) return night ? 'extreme-night-rain.svg' : 'extreme-day-rain.svg';
    if (d.rainRate > 10) return night ? 'extreme-night-rain.svg' : 'extreme-day-rain.svg';
    if (d.rainRate > 0) return night ? 'overcast-night-rain.svg' : 'overcast-day-rain.svg';
    if (d.tdDiff < 0.5 && night && d.outTemp > 5) return 'fog-night.svg';
    if (d.tdDiff < 0.5 && d.outTemp > 5) return 'fog-day.svg';

    if (d.tdDiff < 0.8 && night && d.outTemp > 5) return 'fog-night.svg';
    if (d.tdDiff < 0.8 && d.outTemp > 5) return 'fog-day.svg';

    if (d.snow > 0.1) return night ? 'overcast-night-snow.svg' : 'overcast-day-snow.svg';
    if (d.cloudCover < 7 && d.cloudCover > 0) return night ? 'clear-night.svg' : 'clear-day.svg';
    if (d.cloudCover < 32) return night ? 'mostly-clear-night.svg' : 'mostly-clear-day.svg';
    if (d.cloudCover < 70) return night ? 'partly-cloudy-night.svg' : 'partly-cloudy-day.svg';
    return night ? 'overcast-night.svg' : 'overcast-day.svg';
  }

  function pickSummary(d){
    var night = !d.isDay;
    if (d.rainRate > 0 && d.windSpeedAvg > 7.5) return DivumWXI18N.t('Rain Showers Windy Conditions');
    if (d.rainRate >= 20) return DivumWXI18N.t('Flooding Possible');
    if (d.rainRate >= 10) return DivumWXI18N.t('Heavy Rain');
    if (d.rainRate >= 5) return DivumWXI18N.t('Moderate Rain');
    if (d.rainRate >= 1) return DivumWXI18N.t('Steady Rain');
    if (d.rainRate > 0) return DivumWXI18N.t('Light Rain');
    if (d.snow >= 1.5) return DivumWXI18N.t('Heavy Snow');
    if (d.snow >= 0.75) return DivumWXI18N.t('Moderate Snow');
    if (d.snow > 0.1) return DivumWXI18N.t('Light Snow');
    if (d.tdDiff < 0.5 && d.outTemp > 5) return DivumWXI18N.t('Misty Conditions');
    if (d.tdDiff < 0.8 && d.outTemp > 5) return DivumWXI18N.t('Misty Hazy Conditions');
    if (d.windSpeedAvg >= 40) return DivumWXI18N.t('Strong Wind Conditions');
    if (d.windSpeedAvg >= 30) return DivumWXI18N.t('Very Windy Conditions');
    if (d.windSpeedAvg >= 22) return DivumWXI18N.t('Moderate Wind Conditions');
    if (d.windSpeedAvg >= 7.5) return DivumWXI18N.t('Breezy Conditions');
    if (d.cloudCover < 7 && d.cloudCover > 0) return night ? DivumWXI18N.t('Clear Sky') : DivumWXI18N.t('Sunny');
    if (d.cloudCover < 32) return night ? DivumWXI18N.t('Mostly Clear Conditions') : DivumWXI18N.t('Mostly Sunny Conditions');
    if (d.cloudCover < 70) return DivumWXI18N.t('Partly Cloudy Conditions');
    if (d.cloudCover < 95) return DivumWXI18N.t('Mostly Cloudy Conditions');
    return DivumWXI18N.t('Overcast Conditions');
  }

  function cloudOktas(pct){
    var thresholds = [[5,'0 oktas'],[12.5,'1 okta'],[25,'2 oktas'],[37.5,'3 oktas'],
                       [50,'4 oktas'],[62.5,'5 oktas'],[75,'6 oktas'],[87.5,'7 oktas'],[100,'8 oktas']];
    for (var i = 0; i < thresholds.length; i++){
      if (pct <= thresholds[i][0]) return thresholds[i][1];
    }
    return '8 oktas';
  }

  // Parses METAR's visib notation ("6+", "10+", "1/2", "1 1/2", "3") into
  // a numeric statute-miles value plus whether a "+" (>=) was present.
  // Returns null if the string doesn't parse as any recognized METAR
  // visibility format, rather than guessing.
  function parseMetarVisibilityMiles(visib){
    if (visib === null || visib === undefined || visib === '') return null;
    var str = String(visib).trim();
    var plus = false;
    if (str.charAt(str.length - 1) === '+') {
      plus = true;
      str = str.slice(0, -1).trim();
    }
    var parts = str.split(' ');
    var total = 0, valid = false;
    for (var i = 0; i < parts.length; i++){
      var part = parts[i];
      if (part.indexOf('/') > -1){
        var frac = part.split('/');
        if (frac.length === 2){
          var num = parseFloat(frac[0]), den = parseFloat(frac[1]);
          if (!isNaN(num) && !isNaN(den) && den !== 0){ total += num / den; valid = true; }
        }
      } else {
        var n = parseFloat(part);
        if (!isNaN(n)){ total += n; valid = true; }
      }
    }
    return valid ? { miles: total, plus: plus } : null;
  }

  // Imperial (currentUnits.wind === 'mph', the same proxy cloudBaseLabel
  // above already uses for lack of a dedicated distance-unit toggle):
  // shows METAR's own string completely unchanged -- "6+" means "6
  // statute miles or greater" (AWC's API caps visibility reporting at
  // this value even when the raw observation is effectively unlimited,
  // per the raw METAR line's own "9999"/CAVOK-equivalent), and this is
  // the native, correctly-understood notation for that unit system.
  // Metric: parses and converts to km, preserving the "+"/fraction
  // semantics rather than dropping them -- "6+" mi becomes "9.7+" km,
  // not a bare "9.7" that quietly loses the >= meaning. Falls back to
  // the raw string (still with a unit, just not converted) if the
  // format doesn't parse, rather than showing nothing.
  function metarVisibilityLabel(visib){
    if (visib === null || visib === undefined || visib === '') return '\u2014';
    if (currentUnits.wind === 'mph') return visib + ' mi';
    var parsed = parseMetarVisibilityMiles(visib);
    if (parsed === null) return visib + ' mi';
    var km = parsed.miles * 1.60934;
    return d3.format('.1f')(km) + (parsed.plus ? '+' : '') + ' km';
  }

  function toOrdinal(deg){
    var points = [
      [11.25,DivumWXI18N.t('North')], [33.75,DivumWXI18N.t('NNE')],  [56.25,DivumWXI18N.t('NE')],   [78.75,DivumWXI18N.t('ENE')],
      [101.25,DivumWXI18N.t('East')], [123.75,DivumWXI18N.t('ESE')], [146.25,DivumWXI18N.t('SE')],  [168.75,DivumWXI18N.t('SSE')],
      [191.25,DivumWXI18N.t('South')],[213.75,DivumWXI18N.t('SSW')], [236.25,DivumWXI18N.t('SW')],  [261.25,DivumWXI18N.t('WSW')],
      [281.25,DivumWXI18N.t('West')], [303.75,DivumWXI18N.t('WNW')], [326.25,DivumWXI18N.t('NW')],  [348.75,DivumWXI18N.t('NNW')],
    ];
    for (var i = 0; i < points.length; i++){
      if (deg <= points[i][0]) return points[i][1];
    }
    return DivumWXI18N.t('North');
  }

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      refresh();
    }
  });
  window.addEventListener('i18nready', refresh);

  function mphToMs(v){ return v * 0.44704; }
  function windLabel(mphValue){
    var ms = mphToMs(mphValue);
    switch (currentUnits.wind) {
      case 'mph': return d3.format('.1f')(ms2mph(ms)) + ' mph';
      case 'kmh': return d3.format('.1f')(ms2kmh(ms)) + ' km/h';
      case 'kt':  return d3.format('.1f')(ms2kt(ms)) + ' kt';
      case 'ms':  return d3.format('.1f')(ms) + ' m/s';
      case 'bf':  var b = beaufort(ms); return b.force + ' Bft (' + b.label + ')';
      default:    return d3.format('.1f')(ms2kmh(ms)) + ' km/h';
    }
  }
  function tempLabel(celsius){
    return currentUnits.temp === 'F'
      ? d3.format('.1f')(C2F(celsius)) + '\u00B0F'
      : d3.format('.1f')(celsius) + '\u00B0C';
  }
  function rainLabel(mmValue){
    return currentUnits.rain === 'in'
      ? d3.format('.2f')(mm2in(mmValue)) + ' in'
      : d3.format('.1f')(mmValue) + ' mm';
  }
  function cloudBaseLabel(metres){
    return currentUnits.wind === 'mph'
      ? Math.round(metres * 3.281) + ' ft'
      : Math.round(metres) + ' m';
  }

  var mount = document.getElementById('currentModule');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var textColor    = 'var(--bs-body-color)';
  var labelColor   = 'var(--bs-body-color)';
  var valueColor   = 'var(--bw-accent)';
  var bearingColor = 'var(--bw-sky)';
  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Current Conditions');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function stationTimeParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var p = stationTimeParts(new Date());
    statusTime.textContent = p.hour + ':' + p.minute + ':' + p.second;
  }

  // ---- 60:40 content split (left: icon + description, right: readouts) ----
  // Height is 175px now (was 157px) — reclaims the 18px that used to be the
  // border-bottom band, now that the toolbar/links below are gone.
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  // Vertical divider is a child of `mount` (not contentWrap) so its extent
  // isn't tied to the content pane's own box — stops 6px short of the top
  // border line and 6px short of the card's true bottom edge (there's no
  // border-bottom band to run through on this card any more).
  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.display = 'flex';
  leftPane.style.flexDirection = 'column';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  leftPane.style.gap = '12px';
  leftPane.style.padding = '6px 12px';
  contentWrap.appendChild(leftPane);

  var iconImg = document.createElement('img');
  iconImg.alt = '';
  iconImg.style.width = '108px';
  iconImg.style.height = '108px';
  iconImg.style.display = 'block';
  leftPane.appendChild(iconImg);

  // Description text is a reading (today's condition summary), not a
  // static label, so it follows the same value-colour rule as every
  // number on the dashboard rather than the plain theme text colour.
  var summaryText = document.createElement('div');
  summaryText.style.fontSize = '13px';
  summaryText.style.lineHeight = '1.3';
  summaryText.style.textAlign = 'center';
  summaryText.style.color = valueColor;
  leftPane.appendChild(summaryText);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Fixed row height (rather than sizing purely to font metrics) so 8 rows
  // always land within the 175px content pane regardless of font
  // rendering quirks — 8 * 20px = 160px, comfortably inside 175px.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '20px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.lineHeight = '1';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = labelColor;
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.lineHeight = '1.2';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = valueColor;
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var cloudBaseText  = addChipRow('Cloud Base');
  var visibilityText = addChipRow('Visibility');
  visibilityText.textContent = '\u2014';
  var cloudCoverText = addChipRow('Cloud Cover');
  var tempAvgText    = addChipRow('60min Temp Avg');
  var gustText       = addChipRow('10min Gust Max');
  var speedText      = addChipRow('10min Speed Avg');
  var rainText       = addChipRow('Rainfall (last hr)');

  var dirValueEl = addChipRow('10min Wind Dir');
  var dirText = {
    base:   document.createElement('span'),
    suffix: document.createElement('span')
  };
  dirText.base.style.color = bearingColor;
  dirText.suffix.style.color = valueColor;
  dirValueEl.appendChild(dirText.base);
  dirValueEl.appendChild(document.createTextNode(' '));
  dirValueEl.appendChild(dirText.suffix);
  dirValueEl.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the nearby METAR report — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of titleBar/contentWrap and actually receives the
  // click. top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above) — same technique used on the
  // Forecast and StationTime|Outlook cards.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'modalMetar.html';
  cardLink.setAttribute('data-modal', 'METAR');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Nearby METAR');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '900px');
  cardLink.setAttribute('data-modal-height', '600px');
  cardLink.setAttribute('data-url', 'modalMetar.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(CLOUD_JSON_URL + ((CLOUD_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ME_JSON_URL + ((ME_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1], astroResult = results[2], cloudResult = results[3], metarResult = results[4];
      if(loopResult.status === 'rejected') console.warn('cardCurrent: loop.json fetch failed —', loopResult.reason.message);
      if(archResult.status === 'rejected') console.warn('cardCurrent: archive.json fetch failed —', archResult.reason.message);
      if(astroResult.status === 'rejected') console.warn('cardCurrent: almanac.json fetch failed —', astroResult.reason.message);
      // cloud_coverage.json is optional -- absent-and-rejected is a
      // normal state on installs without it, so this logs at info (not
      // warn) rather than looking like an error. Still logged, not
      // silent, so "is it actually being used?" is answerable from the
      // console instead of guessing: covers both fetch failure (wrong
      // path, 404, network) and fetch-succeeded-but-malformed
      // (cloudPercent missing/not a number) -- two different failure
      // modes that would otherwise look identical from the outside.
      if(cloudResult.status === 'rejected'){
        console.info('cardCurrent: cloud_coverage.json fetch failed (falling back to loop.json/archive.json) —', cloudResult.reason.message);
      } else if(typeof cloudResult.value.cloudPercent !== 'number' || isNaN(cloudResult.value.cloudPercent)){
        console.info('cardCurrent: cloud_coverage.json fetched but cloudPercent is missing/invalid (falling back) —', JSON.stringify(cloudResult.value));
      }
      // me.txt (METAR) is likewise optional -- only configured if the
      // person supplied an ICAO code during install (install.py's own
      // apply_weatherapi_metar_merge() leaves url/data_path unset
      // otherwise). Same info-not-warn logging rationale as above.
      if(metarResult.status === 'rejected'){
        console.info('cardCurrent: me.txt fetch failed (Visibility will show —) —', metarResult.reason.message);
      }

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};
      var cloudCoverage = cloudResult.status === 'fulfilled' ? cloudResult.value : null;
      // me.txt is a JSON ARRAY (one METAR report per configured ICAO
      // station, and this install only configures one) -- confirmed via
      // a real capture: [{"icaoId":"EGTK",...,"visib":"6+",...}]. Take
      // the first (only) element.
      var metarReport = (metarResult.status === 'fulfilled' && Array.isArray(metarResult.value) && metarResult.value.length > 0)
        ? metarResult.value[0] : null;
      var sky = arch.sky || {};
      var wind = arch.wind || {};
      var rain = arch.rain || {};
      var temp = arch.temp || {};

      // Primary source: almanac.json's actual sun altitude -- "day" is
      // precisely "between sunrise and sunset" by definition (sun above
      // the horizon), astronomically exact regardless of what a
      // hardware-derived isDay flag happens to mean. Falls back to
      // loop.json's own observations.isDay only if almanac.json's fetch
      // failed.
      var sunAltRaw = alm['almanac.sun.alt'];
      var sunAlt = (typeof sunAltRaw === 'number' && !isNaN(sunAltRaw)) ? sunAltRaw : null;
      var isDay       = (sunAlt !== null) ? (sunAlt > 0) : (o.isDay === 1);
      var outTemp     = (typeof o.outTemp === 'number') ? o.outTemp : 0;
      var tdDiff       = outTemp - ((typeof o.dewpoint === 'number') ? o.dewpoint : 0);
      var windSpeedAvg = (typeof wind.speed_avg === 'number') ? wind.speed_avg : 0;
      var rainRate     = (typeof rain.rate === 'number') ? rain.rate : 0;
      // Was: prefer archive.json's sky.cloud_cover, fall back to
      // loop.json's o.cloudcover only if the former isn't a number.
      // Bug: archive.json's sky.cloud_cover has been observed stuck at
      // 0 in every sample captured this whole conversation -- 0 is
      // still a valid number, so that fallback never actually
      // triggered, and the live, correct loop.json reading (confirmed
      // 94.0 in a real capture while the card showed 0%) was never
      // used. loop.json is the live per-loop-packet value and is the
      // right primary source for something this fast-changing anyway;
      // archive.json is now only a fallback for the rare case
      // loop.json's own field is genuinely absent.
      // Between sunrise and sunset, cloud_coverage.json (a sky-camera-
      // derived reading, when available) takes priority over
      // loop.json/archive.json -- but only during the day, since it's
      // presumably not meaningful after dark. "Available" means the
      // fetch succeeded AND cloudPercent is actually a valid number,
      // not just that the file exists. Falls back to the existing
      // loop.json-primary/archive.json-fallback logic at night, or any
      // time cloud_coverage.json's fetch failed or its data was invalid.
      var cloudPercentFromCamera = (cloudCoverage && typeof cloudCoverage.cloudPercent === 'number' && !isNaN(cloudCoverage.cloudPercent))
        ? cloudCoverage.cloudPercent : null;
      var cloudCover = (isDay && cloudPercentFromCamera !== null)
        ? cloudPercentFromCamera
        : ((typeof o.cloudcover === 'number') ? o.cloudcover : (sky.cloud_cover || 0));
      console.info('cardCurrent: cloud cover source —', {
        isDay: isDay, sunAlt: sunAlt, cameraAvailable: cloudPercentFromCamera !== null,
        cameraValue: cloudPercentFromCamera, usedValue: cloudCover,
        source: (isDay && cloudPercentFromCamera !== null) ? 'cloud_coverage.json' : 'loop.json/archive.json'
      });

      var inputs = { rainRate: rainRate, windSpeedAvg: windSpeedAvg, tdDiff: tdDiff, outTemp: outTemp, isDay: isDay, snow: 0, cloudCover: cloudCover };

      summaryText.textContent = pickSummary(inputs);
      iconImg.src = ICON_BASE + pickIcon(inputs);
      cloudBaseText.textContent = cloudBaseLabel(o.cloudBase || 0);
      cloudCoverText.textContent = Math.round(cloudCover) + '% \u2013 ' + cloudOktas(cloudCover);
      visibilityText.textContent = metarVisibilityLabel(metarReport && metarReport.visib);
      tempAvgText.textContent = tempLabel(temp.day_avg_last_hour || 0);
      gustText.textContent = windLabel(wind.gust_10m_max || 0);
      speedText.textContent = windLabel(wind.speed_10m_avg || 0);
      rainText.textContent = rainLabel(rain.hour || 0);

      var dirDeg = wind.direction_10m_avg || 0;
      dirText.base.textContent = toOrdinal(dirDeg);
      dirText.suffix.textContent = ' ' + dirDeg + '\u00B0';

      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardCurrent: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardCurrent.js failed:", e);
}

/* ===== cardTemperature.js ===== */
try {
/*
##############################################################################################
# cardTemperature.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardTemperature.js =====================

(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function getTrend(current, past){
    var diff = parseFloat(current) - parseFloat(past);
    if (diff >= 0.6) return 1;
    if (diff <= -0.6) return -1;
    return 0;
  }

  // ---- Media mode (2 or 1 dashboard columns) ----
  // At narrow widths the card itself shrinks along with the grid, so the
  // 9-row text zone gets cramped. Checked against the dashboard's own
  // grid rather than window width directly, since that's what actually
  // determines this card's rendered size.
  function currentColumnCount(){
    var grid = document.querySelector('.wrapper');
    if (!grid) return null;
    var cols = window.getComputedStyle(grid).getPropertyValue('grid-template-columns');
    if (!cols) return null;
    var tracks = cols.trim().split(/\s+/).filter(Boolean);
    return tracks.length || null;
  }
  function isMediaMode(){
    var n = currentColumnCount();
    return n !== null && n <= 2;
  }

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) render(lastData);
    }
  });
  window.addEventListener('resize', function(){
    if (lastData) render(lastData);
  });
  // Card typically renders once (in English, since strings.json is still
  // fetching) before DivumWXI18N's own fetch resolves. Re-render once it
  // has, same pattern as unitsystemchange/resize above -- swaps the
  // already-rendered English labels for translated ones in place.
  window.addEventListener('i18nready', function(){
    if (lastData) render(lastData);
  });

  var mount = document.getElementById('thermometerCard3');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Temperature');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function stationTimeParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var p = stationTimeParts(new Date());
    statusTime.textContent = p.hour + ':' + p.minute + ':' + p.second;
  }

  // ---- 60:40 content split (left: thermometer gauge, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  // Vertical divider is a child of `mount` (not contentWrap) — same pattern
  // as the Current Conditions / Forecast cards — stopping 6px short of the
  // top border line and 6px short of the card's true bottom edge.
  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.height = '175px';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions / Barometer — was a 3x3 grid
  // when this pane was 70% wide, but that grid doesn't fit in 40% without
  // clipping the values, so it's now the same single-column list every
  // other 60:40 gauge card already uses. render()'s population loop below
  // is unchanged — it only ever addressed rows by index, not grid position.
  function addChipRow(){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '19px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    labelEl.style.fontSize = '7px';
    labelEl.style.lineHeight = '1';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    labelEl.style.whiteSpace = 'nowrap';
    labelEl.style.overflow = 'hidden';
    labelEl.style.textOverflow = 'ellipsis';
    row.appendChild(labelEl);

    var valueRow = document.createElement('span');
    valueRow.style.display = 'flex';
    valueRow.style.alignItems = 'baseline';
    valueRow.style.gap = '4px';
    row.appendChild(valueRow);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.lineHeight = '1.2';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueRow.appendChild(valueEl);

    var trendEl = document.createElement('span');
    trendEl.style.fontSize = '10px';
    valueRow.appendChild(trendEl);

    rightPane.appendChild(row);
    return { row: row, label: labelEl, value: valueEl, trend: trendEl };
  }

  var readoutRows = [];
  for (var ri = 0; ri < 9; ri++) readoutRows.push(addChipRow());
  readoutRows[readoutRows.length - 1].row.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the temperature chart/records page —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above) — same technique used on the
  // other cards' whole-card links.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=temperature&embed=1';
  cardLink.setAttribute('data-modal', 'Temperature');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Temperature Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=temperature&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  function render(v){
    // Gauge geometry is untouched from the 30%-pane version — bulb_cx,
    // tubeWidth etc. still describe the same ~90-unit-wide subsystem. Only
    // the viewBox changed, to a wider window that recenters that same
    // artwork within the new 60%-wide pane, rather than rescaling any of
    // the drawing math itself.
    var height = 175;
    var bulbRadius = 25.5, tubeWidth = 16.5, tubeBorderWidth = 1,
        innerBulbColor = 'rgb(230, 200, 200)', tubeBorderColor = '#999999';
    var bottomY = height - 5, bulb_cy = bottomY - bulbRadius, bulb_cx = 45, top_cy = 5 + tubeWidth / 2;

    var mountSel = d3.select(leftPane);
    var svg = mountSel.select('svg');
    if (svg.empty()){
      svg = mountSel.append('svg').attr('viewBox', '-42 0 175 175').attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    // Read units live from the module-scoped currentUnits (updated
    // immediately by the unitsystemchange listener above) rather than
    // from v.unitsTemp -- v.unitsTemp was baked into lastData once,
    // inside refresh(), at whatever units were active at the time of
    // the last loop.json/archive.json fetch. Since unitsystemchange's
    // handler calls render(lastData) directly (to react instantly,
    // without waiting for a fresh fetch), v.unitsTemp still held the
    // OLD units at that point -- so switching units appeared to do
    // nothing until the next scheduled refresh() rebuilt lastData with
    // the new value, which is exactly the "slow to react" symptom
    // reported (every other card already reads its live currentUnits
    // directly at render time, e.g. cardClockOutlook's
    // computeOutlookHtml(json, currentUnits) -- this brings
    // cardTemperature in line with that same, correct pattern).
    var unitsTemp = currentUnits.temp;
    function tc(c){ return unitsTemp === 'F' ? (c * 9 / 5 + 32) : c; }
    function td(d){ return unitsTemp === 'F' ? (d * 9 / 5) : d; }

    var currentTemp   = tc(v.currentTemp);
    var globalMaxTemp = tc(v.globalMaxTemp);
    var globalMinTemp = tc(v.globalMinTemp);
    var inTemp        = tc(v.inTemp);
    var appTemp       = tc(v.appTemp);
    var heatIndex      = tc(v.heatIndex);
    var avgToday        = tc(v.avgToday);
    var dewpoint         = tc(v.dewpoint);
    var windChill        = tc(v.windChill);
    var trend_outTemp   = td(v.trend_outTemp);

    titleLabel.textContent = DivumWXI18N.t('Temperature') + ' (\u00B0' + unitsTemp + ')';

    var defs = svg.append('defs');
    var bulbGradient = defs.append('radialGradient')
      .attr('id', 'bulbGradient').attr('cx', '50%').attr('cy', '50%').attr('r', '50%')
      .attr('fx', '50%').attr('fy', '50%');
    bulbGradient.append('stop').attr('offset', '0%').style('stop-color', innerBulbColor);
    bulbGradient.append('stop').attr('offset', '90%').style('stop-color', v.tempColor);

    // Tube outline height is derived from bulb_cy (rather than the old
    // hardcoded 100, which was only correct for the original height=150
    // layout) so it keeps visually containing the fill at any canvas height.
    var outlineHeight = bulb_cy - bulbRadius / 2 - 6.75;
    svg.append('rect')
      .attr('rx', 7.5).attr('x', 37.5).attr('y', 7)
      .attr('width', tubeWidth + 1 - tubeBorderWidth - 1.5).attr('height', outlineHeight)
      .style('stroke', tubeBorderColor).style('stroke-width', '0.75px').attr('fill', 'none');

    var step = (unitsTemp === 'F') ? 8 : 5;
    var domain = [ step * Math.floor(globalMinTemp / step), step * Math.ceil(globalMaxTemp / step) ];
    if (globalMinTemp - domain[0] < 0.66 * step) domain[0] -= step;
    if (domain[1] - globalMaxTemp < 0.66 * step) domain[1] += step;

    var yScale = d3.scaleLinear().domain(domain).range([bulb_cy - bulbRadius / 2 - 8.5, top_cy + 5]);
    var tubeFill_bottom = bulb_cy, tubeFill_top = yScale(currentTemp);

    svg.append('rect').attr('class', 'mx')
      .attr('x', 45 - (tubeWidth - tubeBorderWidth) / 2)
      .attr('y', yScale(currentTemp))
      .attr('width', tubeWidth - tubeBorderWidth)
      .attr('height', tubeFill_bottom - tubeFill_top)
      .attr('fill', v.tempColor);

    svg.append('circle').attr('class', 'bulb')
      .attr('cx', bulb_cx).attr('cy', bulb_cy).attr('r', bulbRadius - 6)
      .style('fill', 'url(#bulbGradient)').style('stroke-width', '2px');

    var tickValues = d3.range((domain[1] - domain[0]) / step + 1).map(function(n){ return domain[0] + n * step; });
    var axis = d3.axisLeft(yScale).tickValues(tickValues).tickSize(7).tickPadding(5);
    var tAxis = svg.append('g').attr('class', 'y-axis')
      .attr('transform', 'translate(' + (45 - tubeWidth / 2 - 5) + ', 0)').call(axis);

    tAxis.selectAll('.tick text').style('fill', 'var(--bs-body-color)').style('font-family', 'inherit').style('font-size', '8px');
    tAxis.select('path').style('stroke', 'none').style('fill', 'none');
    tAxis.selectAll('.tick line').style('stroke', tubeBorderColor).style('stroke-linecap', 'round').style('stroke-width', '2px');

    svg.append('text').attr('class', 'temperature-label').attr('id', 'tempText')
      .attr('x', 45).attr('y', bulb_cy + 5.5).style('text-anchor', 'middle').style('font-family', 'inherit')
      .style('font-weight', '600').style('font-size', '16px').style('fill', 'black')
      .text(currentTemp.toFixed(1));

    svg.append('text').attr('class', 'min-temp-label')
      .attr('x', 45 + tubeWidth / 2 + 13).attr('y', yScale(globalMinTemp) + 8.5)
      .attr('text-anchor', 'middle').style('font-family', 'inherit').style('fill', 'var(--bs-body-color)').style('font-size', '8px')
      .text(DivumWXI18N.t('Min'));
    svg.append('text').attr('class', 'max-temp-label')
      .attr('x', 45 + tubeWidth / 2 + 13).attr('y', yScale(globalMaxTemp) - 3)
      .attr('text-anchor', 'middle').style('font-family', 'inherit').style('fill', 'var(--bs-body-color)').style('font-size', '8px')
      .text(DivumWXI18N.t('Max'));

    svg.append('line').attr('class', 'min-temp-line')
      .attr('x1', 45 - tubeWidth / 2 + 20).attr('x2', 45 + tubeWidth / 2 + 22)
      .attr('y1', yScale(globalMinTemp)).attr('y2', yScale(globalMinTemp))
      .attr('stroke', tubeBorderColor).style('stroke-linecap', 'round').attr('stroke-width', 1.0);
    svg.append('line').attr('class', 'max-temp-line')
      .attr('x1', 45 - tubeWidth / 2 + 20).attr('x2', 45 + tubeWidth / 2 + 22)
      .attr('y1', yScale(globalMaxTemp)).attr('y2', yScale(globalMaxTemp))
      .attr('stroke', tubeBorderColor).style('stroke-linecap', 'round').attr('stroke-width', 1.0);

    // ---- Right pane: label/value/trend rows ----
    // Indoor Temp is dropped from this list in media mode (2 or 1
    // dashboard columns) — the freed row is redistributed across the
    // remaining ones (see the height/media-mode loop below) rather than
    // left as blank space, so the text zone gets more breathing room
    // where the card is narrowest and legibility matters most.
    var mediaMode = isMediaMode();
    // Each row carries a stable `id` (never translated, never shown --
    // used only for the media-mode filter below) separate from `label`
    // (translated display text). Before this change the filter compared
    // against the English label text directly ("Indoor Temp") -- once
    // that label is translated, that comparison would silently stop
    // matching and Indoor Temp would stop being hidden in media mode.
    // Filtering on `id` instead means the label can be translated to
    // anything without touching the filter logic at all.
    var weatherData = [
      { id: 'maxMin',      label: DivumWXI18N.t('Max | Min'),   value: globalMaxTemp.toFixed(1) + '\u00B0' + unitsTemp + ' | ' + globalMinTemp.toFixed(1) + '\u00B0' + unitsTemp, color: 'transparent', trend: 0 },
      { id: 'trend',       label: DivumWXI18N.t('Trend'),       value: trend_outTemp.toFixed(1) + '\u00B0' + unitsTemp, color: v.tempColor, trend: trend_outTemp },
      { id: 'indoorTemp',  label: DivumWXI18N.t('Indoor Temp'), value: inTemp.toFixed(1) + '\u00B0' + unitsTemp, color: v.colorInTemp, trend: v.trend_inTemp },
      { id: 'apparent',    label: DivumWXI18N.t('Apparent'),    value: appTemp.toFixed(1) + '\u00B0' + unitsTemp, color: v.colorAppTemp, trend: getTrend(v.appTemp, v.appTemp_3hours) },
      { id: 'humidity',    label: DivumWXI18N.t('Humidity'),    value: v.humidity.toFixed(0) + '%', color: v.colorHumidityOut, trend: v.trend_outHumidity },
      { id: 'heatIndex',   label: DivumWXI18N.t('Heat Index'),  value: heatIndex.toFixed(1) + '\u00B0' + unitsTemp, color: v.colorHeatindex, trend: getTrend(v.heatIndex, v.heatIndex_3hours) },
      { id: 'avgToday',    label: DivumWXI18N.t('Avg Today'),   value: avgToday.toFixed(1) + '\u00B0' + unitsTemp, color: v.colorOutTempDayAvg, trend: getTrend(v.avgToday, v.avgToday_3hours) },
      { id: 'dewpoint',    label: DivumWXI18N.t('Dewpoint'),    value: dewpoint.toFixed(1) + '\u00B0' + unitsTemp, color: v.colorDewpoint, trend: v.trend_dewpoint },
      { id: 'windchill',   label: DivumWXI18N.t('Windchill'),   value: windChill.toFixed(1) + '\u00B0' + unitsTemp, color: v.colorWindchill, trend: getTrend(v.windChill, v.windChill_3hours) }
    ];
    if (mediaMode) {
      weatherData = weatherData.filter(function(d){ return d.id !== 'indoorTemp'; });
    }

    // Rows beyond weatherData.length (only happens in media mode) are
    // hidden; the visible ones share the pane's full 175px height evenly,
    // so hiding Indoor Temp actually grows the rest rather than just
    // leaving a gap. Border/display/height are set unconditionally from
    // scratch every render (not just adjusted incrementally) so toggling
    // in and out of media mode across a resize can't leave a stale
    // border behind on whichever row used to be last.
    var rowHeight = 175 / weatherData.length;
    for (var ri2 = 0; ri2 < readoutRows.length; ri2++){
      var visible = ri2 < weatherData.length;
      var isLastVisible = ri2 === weatherData.length - 1;
      readoutRows[ri2].row.style.display = visible ? 'flex' : 'none';
      readoutRows[ri2].row.style.height = rowHeight + 'px';
      readoutRows[ri2].row.style.borderBottom = isLastVisible ? 'none' : '1px solid var(--bs-border-color)';
    }

    for (var wi = 0; wi < weatherData.length; wi++){
      var d = weatherData[wi], ref = readoutRows[wi];
      ref.label.textContent = d.label;
      ref.value.textContent = d.value;
      if (wi === 0) {
        ref.trend.textContent = '';
      } else {
        ref.trend.style.color = d.color;
        ref.trend.textContent = parseFloat(d.trend) > 0 ? '\u279A' : (parseFloat(d.trend) < 0 ? '\u2798' : '\u2799');
      }
    }
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if(loopResult.status === 'rejected') console.warn('cardTemperature: loop.json fetch failed —', loopResult.reason.message);
      if(archResult.status === 'rejected') console.warn('cardTemperature: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var t = arch.temp || {}, dw = arch.dew || {}, h = arch.humid || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        globalMaxTemp: num(t.day_max, o.outTemp),
        globalMinTemp: num(t.day_min, o.outTemp),
        currentTemp:   num(o.outTemp, 0),
        colorInTemp:        o.inTempColor       || 'var(--bw-accent)',
        tempColor:          o.outTempColor      || 'var(--bw-accent)',
        colorAppTemp:       o.apparentTempColor || 'var(--bw-accent)',
        colorHumidityOut:   'var(--bs-body-color)',
        colorHeatindex:     o.heatIndexColor    || 'var(--bw-accent)',
        colorOutTempDayAvg: 'var(--bs-body-color)',
        colorDewpoint:      o.dewpointColor     || 'var(--bw-accent)',
        colorWindchill:     o.windChillColor    || 'var(--bw-accent)',
        windChill_3hours: num(t.wind_chill_avg_3h, o.windChill),
        avgToday_3hours:  num(t.outside_avg_3h, t.day_avg),
        heatIndex_3hours: num(t.heat_index_avg_3h, o.heatIndex),
        appTemp_3hours:   num(t.feels_like_avg_3h, o.apparentTemp),
        windChill: num(o.windChill, 0),
        trend_dewpoint: num(dw.trend, 0),
        trend_outHumidity: num(h.trend, 0),
        humidity: num(o.outHumidity, 0),
        trend_inTemp: num(t.indoor_trend, 0),
        trend_outTemp: num(t.outside_trend, 0),
        inTemp: num(o.inTemp, 0),
        avgToday: num(t.day_avg, 0),
        dewpoint: num(o.dewpoint, 0),
        appTemp: num(o.apparentTemp, 0),
        heatIndex: num(o.heatIndex, 0)
      };
      render(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardTemperature: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardTemperature.js failed:", e);
}

/* ===== cardForecast.js ===== */
try {
/*
##############################################################################################
# cardForecast.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardForecast.js =====================
(function(){
  var FORECAST_JSON_URL = './jsondata/forecastcard.txt';
  var ICON_MAP_URL = './jsondata/meteocons_wmo_map.json';
  var ICON_BASE = './meteocons/fill/svg/';
  var POLL_MS = 5 * 60 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit', weekday: 'short'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function fmtDate(fakeUtcDate){
    var y = fakeUtcDate.getUTCFullYear();
    var m = fakeUtcDate.getUTCMonth() + 1;
    var d = fakeUtcDate.getUTCDate();
    return y + '-' + (m < 10 ? '0' : '') + m + '-' + (d < 10 ? '0' : '') + d;
  }
  function addDays(fakeUtcDate, n){
    return new Date(fakeUtcDate.getTime() + n * 86400000);
  }
  var WEEKDAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  function weekdayAbbrev(dateStr){
    var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(dateStr);
    var d = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3]));
    return DivumWXI18N.t(WEEKDAYS[d.getUTCDay()]);
  }

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastForecastJson) renderCard(lastForecastJson, iconMap);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastForecastJson) renderCard(lastForecastJson, iconMap);
  });

  function toCelsius(v, sourceUnit){
    return (String(sourceUnit || '').indexOf('F') !== -1) ? (v - 32) * 5 / 9 : v;
  }
  function tempLabel(celsius){
    return currentUnits.temp === 'F'
      ? (celsius * 9 / 5 + 32).toFixed(1) + '\u00B0F'
      : celsius.toFixed(1) + '\u00B0C';
  }
  function toMM(v, sourceUnit){
    return String(sourceUnit || '').toLowerCase().indexOf('in') !== -1 ? v * 25.4 : v;
  }
  function rainLabel(mm){
    return currentUnits.rain === 'in' ? (mm / 25.4).toFixed(2) + ' in' : mm.toFixed(1) + ' mm';
  }
  function toMS(v, sourceUnit){
    var n = String(sourceUnit || 'km/h').toLowerCase().replace(/\s/g, '');
    if (n.indexOf('mph') !== -1) return v * 0.44704;
    if (n.indexOf('kmh') !== -1 || n.indexOf('kph') !== -1 || n.indexOf('km') !== -1) return v / 3.6;
    if (n.indexOf('kn') !== -1 || n.indexOf('kt') !== -1) return v * 0.514444;
    return v;
  }
  function windLabel(ms){
    switch (currentUnits.wind) {
      case 'mph': return (ms / 0.44704).toFixed(1) + ' mph';
      case 'kmh': return (ms * 3.6).toFixed(1) + ' km/h';
      case 'kt':  return (ms / 0.514444).toFixed(1) + ' kt';
      case 'ms':  return ms.toFixed(1) + ' m/s';
      case 'bf':  var b = beaufort(ms); return b.force + ' Bft (' + b.label + ')';
      default:    return (ms * 3.6).toFixed(1) + ' km/h';
    }
  }
  var COMPASS_16 = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
  function deg2compass(deg){
    return DivumWXI18N.t(COMPASS_16[Math.floor((deg / 22.5) + 0.5) % 16]);
  }

  var mount = document.getElementById('forecastCard4');
  if (!mount || typeof fetch === 'undefined') return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Forecast');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    var pad2 = function(n){ return n < 10 ? '0' + n : String(n); };
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // Grid columns are 1fr / 1px / 1fr / 1px / 1fr — the 1px tracks are the
  // two dividers themselves (added as literal grid items in renderCard()
  // below), so they land exactly on the true column boundaries regardless
  // of rounding, rather than being separately positioned over the grid.
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'grid';
  contentWrap.style.gridTemplateColumns = '1fr 1px 1fr 1px 1fr';
  contentWrap.style.gridTemplateRows = '175px';
  contentWrap.style.gap = '0';
  contentWrap.style.padding = '4px 6px 0';
  contentWrap.style.fontSize = '9px';
  contentWrap.style.lineHeight = '1.35';
  contentWrap.style.textAlign = 'left';
  contentWrap.style.color = 'var(--bs-body-color)';
  mount.appendChild(contentWrap);

  // Whole card is a click-through to the station's full 7-day forecast —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of titleBar/contentWrap and actually receives the
  // click (an earlier sibling would just sit underneath them instead).
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above) — same technique used to fix
  // the card outline extending into the card below.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'stationforecast.html';
  cardLink.setAttribute('data-modal', '7-Day Forecast');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Station 7-Day Forecast and Meteogram');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '800px');
  cardLink.setAttribute('data-url', 'stationforecast.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  function getIconHtml(code, period, map){
    var isNight = period === 'Night';
    var entry = map && map[code];
    if (!entry) return '<span style="font-size:20px">\u2753</span>';
    var iconName = isNight ? entry.night : entry.day;
    var emoji = entry.emoji || '\u2753';
    var label = entry.label || DivumWXI18N.t('Unknown');
    if (iconName) {
      return '<img src="' + ICON_BASE + iconName + '.svg" alt="' + label + '" title="' + label +
        '" width="34" height="34" style="width:34px;height:34px;display:block;margin:2px 0;">';
    }
    return '<span title="' + label + '" style="font-size:20px">' + emoji + '</span>';
  }
  function weatherText(code, map){
    return (map && map[code] && map[code].label) || DivumWXI18N.t('Unknown');
  }

  function safeSlice(arr, offset, length, fallback){
    if (!Array.isArray(arr) || !arr.length) {
      var f = []; for (var k = 0; k < length; k++) f.push(fallback);
      return f;
    }
    return arr.slice(offset, offset + length);
  }
  function maxOf(arr){ return arr.length ? Math.max.apply(null, arr) : 0; }
  function minOf(arr){ return arr.length ? Math.min.apply(null, arr) : 0; }
  function sumOf(arr){ return arr.reduce(function(a, b){ return a + b; }, 0); }
  function pickAt(obj, keys, idx, fallback){
    for (var i = 0; i < keys.length; i++){
      if (obj[keys[i]] && obj[keys[i]][idx] !== undefined) return obj[keys[i]][idx];
    }
    return fallback;
  }

  function buildSegments(data){
    var h = data.hourly;
    var hu = data.hourly_units || {};
    var hours = h.time;
    var count = hours.length;
    var segments = [];

    var tempUnit = hu.temperature_2m;
    var rainUnit = hu.precipitation;
    var windUnit = hu.windspeed_10m || hu.wind_speed_10m;

    function makeSegment(offset, period, codeIdx){
      var temps = safeSlice(h.temperature_2m, offset, 12, 0).map(function(v){ return toCelsius(v, tempUnit); });
      var rains = safeSlice(h.precipitation, offset, 12, 0).map(function(v){ return toMM(v, rainUnit); });
      var probs = safeSlice(h.precipitation_probability, offset, 12, 0);
      var winds = safeSlice(h.windspeed_10m || h.wind_speed_10m, offset, 12, 0).map(function(v){ return toMS(v, windUnit); });
      var dirs  = safeSlice(h.winddirection_10m || h.wind_direction_10m, offset, 12, 0);
      var hum   = safeSlice(h.relative_humidity_2m, offset, 12, 50);
      var uv    = safeSlice(h.uv_index, offset, 12, 0);

      return {
        date: String(hours[offset >= count ? count - 1 : offset]).slice(0, 10),
        period: period,
        tmaxC: maxOf(temps), tminC: minOf(temps),
        rainMM: sumOf(rains),
        rainProb: maxOf(probs),
        windMS: maxOf(winds),
        windDir: deg2compass(dirs[5] || 0),
        humidity: Math.round(sumOf(hum) / Math.max(hum.length, 1)),
        uv: Math.round((maxOf(uv) || 0) * 10) / 10,
        code: pickAt(h, ['weathercode', 'weather_code'], codeIdx, 0)
      };
    }

    for (var i = 0; i < count; i += 24){
      segments.push(makeSegment(i + 6, 'Day', i + 12));
      segments.push(makeSegment(i + 18, 'Night', i + 18));
    }
    return segments;
  }

  function pickStartIndex(segments){
    var now = stationNow();
    var todayStr = fmtDate(now);
    var hr = now.getUTCHours();
    for (var i = 0; i < segments.length; i++){
      var s = segments[i];
      if (s.date === todayStr && s.period === 'Day' && hr >= 6 && hr < 18) return i;
      if (s.date === todayStr && s.period === 'Night' && (hr < 6 || hr >= 18)) return i;
    }
    return 0;
  }

  function labelFor(s, todayStr, tomorrowStr){
    if (s.period === 'Day') {
      if (s.date === todayStr) return DivumWXI18N.t('Today');
      if (s.date === tomorrowStr) return DivumWXI18N.t('Tomorrow');
      return weekdayAbbrev(s.date);
    }
    if (s.date === todayStr) return DivumWXI18N.t('Tonight');
    if (s.date === tomorrowStr) return DivumWXI18N.t('Tomorrow Night');
    return weekdayAbbrev(s.date) + ' ' + DivumWXI18N.t('Night');
  }

  function renderCard(data, map){
    var segments = buildSegments(data);
    var startIdx = pickStartIndex(segments);
    var view = segments.slice(startIdx, startIdx + 3);

    var now = stationNow();
    var todayStr = fmtDate(now);
    var tomorrowStr = fmtDate(addDays(now, 1));

    titleLabel.textContent = DivumWXI18N.t('Forecast') + ' (\u00B0' + currentUnits.temp + ')';

    // wind.svg's own gradient is a pale grey (#d4d7dd→#bec1c6) — barely
    // visible against a white/light card face, so light theme darkens it
    // to a solid silhouette. It reads fine in dark theme technically, but
    // looks washed out next to the more saturated icons (thermometer's
    // reds, raindrop's blues), so this now also boosts dark theme instead
    // of leaving it untouched — inverted to a bright, unambiguous white
    // rather than left as its own dim grey.
    function miniIcon(name, size, valign, boostContrast){
      var px = size || 19;
      var va = valign || -5;
      var filterCss = '';
      if (boostContrast) {
        filterCss = document.body.classList.contains('light')
          ? 'filter:brightness(0) saturate(100%) opacity(0.6);'
          : 'filter:brightness(0) invert(1) opacity(0.9);';
      }
      return '<img src="' + ICON_BASE + name + '.svg" style="width:' + px + 'px;height:' + px + 'px;vertical-align:' + va + 'px;margin-right:3px;' + filterCss + '">';
    }

    // Divider — a literal 1px grid track (see gridTemplateColumns above),
    // not an absolutely-positioned overlay, so it lines up with the true
    // column boundary exactly. margin insets it 6px top/bottom, short of
    // the content pane's own top/bottom edges.
    var DIVIDER = '<div style="align-self:stretch;width:1px;margin:6px 0;background:var(--bs-border-color);"></div>';

    var html = '';
    for (var i = 0; i < view.length; i++){
      var s = view[i];
      var lbl = labelFor(s, todayStr, tomorrowStr);
      var isDay = s.period === 'Day';
      var tempText = tempLabel(isDay ? s.tmaxC : s.tminC);
      var extra = isDay ? ('UV-I ' + s.uv) : (s.humidity + '% ' + DivumWXI18N.t('hum'));
      var icon = getIconHtml(s.code, s.period, map);
      var text = weatherText(s.code, map);

      if (i > 0) html += DIVIDER;
      html +=
        '<div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:space-evenly;height:100%;overflow:hidden;padding:0 6px;box-sizing:border-box;">' +
          '<div style="font-size:11px;">' + lbl + '</div>' +
          icon +
          '<div style="font-size:9.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:100%;">' + text + '</div>' +
          '<div style="font-size:9px;line-height:2.3;color:var(--bw-accent);">' +
            miniIcon('thermometer', 26, -8) + tempText + '<br>' +
            miniIcon('raindrop') + rainLabel(s.rainMM) + ' (' + s.rainProb + '%)<br>' +
            miniIcon('wind', 19, -5, true) + s.windDir + ' ' + windLabel(s.windMS) + '<br>' +
            extra +
          '</div>' +
        '</div>';
    }
    contentWrap.innerHTML = html;
  }

  var lastForecastJson = null;
  var iconMap = {};
  function loadIconMap(){
    return fetch(ICON_MAP_URL + ((ICON_MAP_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(json){ iconMap = (json && json.mapping) || {}; })
      .catch(function(e){
        console.warn('cardForecast: icon map fetch failed —', e.message);
        iconMap = {};
      });
  }
  function refresh(){
    fetch(FORECAST_JSON_URL + ((FORECAST_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(json){
        lastForecastJson = json;
        renderCard(json, iconMap);
        setStatus(true);
      })
      .catch(function(e){
        console.warn('cardForecast: forecast fetch failed —', e.message);
        setStatus(false);
      });
  }

  loadIconMap().then(refresh);
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardForecast.js failed:", e);
}

/* ===== cardAnemometer.js ===== */
try {
/*
##############################################################################################
# cardAnemometer.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardAnemometer.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }

  function skynet(v){
    v = parseInt(v, 10);
    if (v < 10) return '00' + v;
    if (v < 100) return '0' + v;
    return '' + v;
  }
  var COMPASS_16 = ['North','NNE','NE','ENE','East','ESE','SE','SSE','South','SSW','SW','WSW','West','WNW','NW','NNW'];
  var COMPASS_BOUNDS = [11.25,33.75,56.25,78.75,101.25,123.75,146.25,168.75,191.25,213.75,236.25,258.75,281.25,303.75,326.25,348.75];
  function toOrdinal(deg){
    for (var i = 0; i < COMPASS_BOUNDS.length; i++){ if (deg <= COMPASS_BOUNDS[i]) return DivumWXI18N.t(COMPASS_16[i]); }
    return DivumWXI18N.t('North');
  }

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) renderCard(lastData);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });

  var WIND_UNIT_LABEL = { mph: 'mph', kmh: 'km/h', kt: 'kt', ms: 'm/s', bf: 'Bft' };
  function windFromMS(ms){
    switch (currentUnits.wind) {
      case 'mph': return ms / 0.44704;
      case 'kmh': return ms * 3.6;
      case 'kt':  return ms / 0.514444;
      case 'ms':  return ms;
      case 'bf':  return beaufort(ms).force;
      default:    return ms * 3.6;
    }
  }
  function windLabel(ms){
    if (currentUnits.wind === 'bf'){ var b = beaufort(ms); return b.force + ' Bft (' + b.label + ')'; }
    return windFromMS(ms).toFixed(1) + ' ' + (WIND_UNIT_LABEL[currentUnits.wind] || 'km/h');
  }
  var GAUGE_CONFIG = { kmh: [130, 13], mph: [80, 9], ms: [35, 9], kt: [70, 9], bf: [130, 13] };

  var mount = document.getElementById('anemometerCard5');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Anemometer');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 50:50 content split (left: gauge + hero values, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  // Vertical divider is a child of `mount` (not contentWrap) — same pattern
  // as Current Conditions — stopping 6px short of the top border line and
  // 6px short of the card's true bottom edge.
  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions: label small-caps above,
  // value (uniform accent colour, matching Current Conditions) below.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var maxGustText = addChipRow('Max Gust');
  var bearingText = addChipRow('Bearing');
  var ordinalText = addChipRow('Ordinal');
  var beaufortText = addChipRow('Beaufort');
  var windRunText = addChipRow('Wind Run');
  windRunText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the wind chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above) — same technique used on the
  // other cards' whole-card links. Class name lets the shared hover-
  // tooltip script (indexNew.html) find it and read data-modal for the
  // "click to open X" message.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=wind&embed=1';
  cardLink.setAttribute('data-modal', 'Wind');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Wind & Gust Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=wind&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var ARC_BANDS = [
    [0, 0.3, '#85a3aa'], [0.3, 1.5, '#7e98bb'], [1.5, 3.3, '#6e90d0'],
    [3.3, 5.4, '#0f94a7'], [5.4, 7.9, '#39a239'], [7.9, 10.7, '#c2863e'],
    [10.7, 13.8, '#c8420d'], [13.8, 17.1, '#d20032'], [17.1, 20.7, '#af5088'],
    [20.7, 24.4, '#754a92'], [24.4, 28.4, '#45698d'], [28.4, 32.6, '#c1fc77'],
    [32.6, 36.0, '#f1ff6c']
  ];

  // cy nudged down from 78 and hero-text baselines pulled in from the
  // canvas edges (H-16/H-4 → H-19/H-8 below) — the dial's top ticks and
  // the second hero line were both landing within ~4-10px of the canvas
  // edge, reading as cramped against the top/bottom of the pane.
  var W = 180, H = 175, cx = 90, cy = 81, R = 54;
  var ICON_BASE = './meteocons/fill/svg/';

  // ---- Damped-spring needle animation ----------------------------------
  // Not a CSS/d3 transition — a genuine second-order damped spring:
  // angular acceleration proportional to how far the needle is from its
  // target, minus a term proportional to its own angular velocity
  // (accel = k*(target-angle) - c*velocity). zeta=0.8 is deliberately
  // underdamped — the analytic overshoot for a step input is
  // exp(-zeta*pi/sqrt(1-zeta^2)), which comes out to ~1.5% here (closer
  // to ~1.3% at zeta~0.81; 0.8 is close enough to still read as "just
  // past and settling back" without being sloppy about it) — so a big
  // jump (calm to gale, say) swings a little past the target and eases
  // back, the way a real moving-coil meter does, rather than gliding to
  // a stop like an eased CSS transition would.
  function createNeedleSpring(applyAngle, opts){
    opts = opts || {};
    var zeta = opts.zeta || 0.8;
    var omega = opts.omega || 4; // rad/s — higher settles faster
    var k = omega * omega;
    var c = 2 * zeta * omega;
    var wrap = !!opts.wrap; // true for a full 360° dial (shortest-path wraparound), false for a bounded arc

    var angle = (typeof opts.initial === 'number') ? opts.initial : 0;
    var vel = 0;
    var target = angle;
    var lastT = null;
    var rafId = null;

    function tick(now){
      rafId = null;
      if (lastT === null) lastT = now;
      var dt = Math.min((now - lastT) / 1000, 0.1); // clamp so a backgrounded tab doesn't produce one huge jump on return
      lastT = now;

      var delta = target - angle;
      if (wrap){
        // Shortest path round the dial — e.g. 350° -> 10° is a +20°
        // nudge, not a -340° lap the other way round.
        delta = ((delta + 180) % 360 + 360) % 360 - 180;
      }
      var accel = k * delta - c * vel;
      vel += accel * dt;
      angle += vel * dt;

      applyAngle(angle);

      if (Math.abs(delta) > 0.02 || Math.abs(vel) > 0.02){
        rafId = requestAnimationFrame(tick);
      }
    }

    return {
      setTarget: function(deg){
        target = deg;
        if (rafId === null){ lastT = null; rafId = requestAnimationFrame(tick); }
      }
    };
  }
  var needleSpring = null, lastGaugeDomain = null;

  function renderCard(v){
    titleLabel.textContent = DivumWXI18N.t('Anemometer') + ' (' + (WIND_UNIT_LABEL[currentUnits.wind] || 'km/h') + ')';

    var gaugeCfg = GAUGE_CONFIG[currentUnits.wind] || GAUGE_CONFIG.kmh;
    var gaugeDomain = gaugeCfg[0], tickCount = gaugeCfg[1];

    // ---- Left pane: dial + hero values only. Max Gust, Bearing, Ordinal,
    // Beaufort and Wind Run all moved to the right pane's chip rows below;
    // the conversion badge is gone entirely (per requirement).
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    }

    var colorScale = d3.scaleLinear().domain([0, 36]).range([-135, 135]);
    var arcScale   = d3.scaleLinear().domain([0, gaugeDomain]).range([-135, 135]).clamp(true);

    // Static chrome (arc bands + ticks) only depends on gaugeDomain, which
    // only changes when the unit system changes — so it's rebuilt then,
    // not on every 30s data refresh. Inserted as the first child so later
    // elements (needle, gust markers) always paint on top of it regardless
    // of rebuild order.
    if (svg.select('g.a-chrome').empty() || lastGaugeDomain !== gaugeDomain){
      lastGaugeDomain = gaugeDomain;
      svg.select('g.a-chrome').remove();
      var chromeG = svg.insert('g', ':first-child').attr('class', 'a-chrome');

      var bgArc = d3.arc().innerRadius(R - 5).outerRadius(R);
      chromeG.selectAll('.a-bg-arc')
        .data(ARC_BANDS).join('path')
        .attr('class', 'a-bg-arc')
        .attr('transform', 'translate(' + cx + ',' + cy + ')')
        .attr('d', function(d){ return bgArc.startAngle(colorScale(d[0]) * Math.PI / 180).endAngle(colorScale(d[1]) * Math.PI / 180)(); })
        .style('fill', function(d){ return d[2]; });

      var tickG = chromeG.append('g').attr('transform', 'translate(' + cx + ',' + cy + ')');

      var MINOR_PER_INTERVAL = 4;
      for (var mi = 0; mi <= tickCount - 2; mi++){
        var mvStart = (gaugeDomain / (tickCount - 1)) * mi;
        var mvEnd = (gaugeDomain / (tickCount - 1)) * (mi + 1);
        for (var mj = 1; mj <= MINOR_PER_INTERVAL; mj++){
          var mtv = mvStart + (mvEnd - mvStart) * (mj / (MINOR_PER_INTERVAL + 1));
          var mang = (arcScale(mtv) - 90) * Math.PI / 180;
          var mx1 = Math.cos(mang) * (R + 3), my1 = Math.sin(mang) * (R + 3);
          var mx2 = Math.cos(mang) * (R + 5), my2 = Math.sin(mang) * (R + 5);
          tickG.append('line').attr('x1', mx1).attr('y1', my1).attr('x2', mx2).attr('y2', my2)
            .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 0.5).style('opacity', 0.55);
        }
      }

      for (var i = 0; i <= tickCount - 1; i++){
        var tv = (gaugeDomain / (tickCount - 1)) * i;
        var ang = (arcScale(tv) - 90) * Math.PI / 180;
        var x1 = Math.cos(ang) * (R + 3), y1 = Math.sin(ang) * (R + 3);
        var x2 = Math.cos(ang) * (R + 7), y2 = Math.sin(ang) * (R + 7);
        var xt = Math.cos(ang) * (R + 11), yt = Math.sin(ang) * (R + 11);
        tickG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
          .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 1);
        tickG.append('text').attr('x', xt).attr('y', yt).attr('dy', '0.32em')
          .style('text-anchor', 'middle').style('font-size', '6.5px').style('fill', overlayTextColor)
          .text(Math.round(tv));
      }
    }

    // Needle group — created once, then only ever has its rotate() angle
    // updated by needleSpring below; never destroyed and rebuilt like the
    // chrome above, since the whole point of the spring is to animate a
    // persistent element smoothly rather than re-snap a fresh one to its
    // target every refresh. Hub dot lives outside the rotating group.
    var needleG = svg.select('g.a-needle');
    if (needleG.empty()){
      needleG = svg.append('g').attr('class', 'a-needle');
      needleG.append('polygon')
        .attr('points', '0,' + (-(R - 6)) + ' 2.2,10 -2.2,10')
        .style('fill', 'red');
      svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', 4).style('fill', 'red');
    }
    if (!needleSpring){
      // Starts at -135° — this dial's own "calm" position (0° would point
      // straight up, which isn't where calm sits on this arc) — so the
      // very first refresh swings up from calm to the live reading, like
      // a meter powering on, rather than appearing mid-arc.
      needleSpring = createNeedleSpring(function(deg){
        needleG.attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + deg + ')');
      }, { zeta: 0.8, omega: 4, wrap: false, initial: -135 });
    }
    needleSpring.setTarget(arcScale(windFromMS(v.windSpeed)));

    // ---- Everything else redraws each refresh — all cheap (two thin
    // tick-lines, an icon, two lines of text), not worth persisting ----
    svg.selectAll('.a-dynamic').remove();
    var dynG = svg.append('g').attr('class', 'a-dynamic');

    var gustAngle = arcScale(windFromMS(v.windGust));
    dynG.append('line')
      .attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + gustAngle + ')')
      .attr('x1', 0).attr('y1', -(R)).attr('x2', 0).attr('y2', -(R + 7))
      .style('stroke', '#2e8b57').style('stroke-width', 2);
    var maxGustAngle = arcScale(windFromMS(v.gustMax));
    dynG.append('line')
      .attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + maxGustAngle + ')')
      .attr('x1', 0).attr('y1', -(R)).attr('x2', 0).attr('y2', -(R + 7))
      .style('stroke', '#ff6347').style('stroke-width', 2);

    var iconSize = 24.3; // 18 * 1.35
    // wind.svg's own gradient is a pale grey (#d4d7dd→#bec1c6) — barely
    // visible against either theme's card face without help. Same fix as
    // the Forecast card's wind icon: darken to a solid silhouette in
    // light theme, invert to bright white in dark theme.
    var windIconFilter = document.body.classList.contains('light')
      ? 'brightness(0) saturate(100%) opacity(0.6)'
      : 'brightness(0) invert(1) opacity(0.9)';
    dynG.append('image')
      .attr('xlink:href', ICON_BASE + 'wind.svg')
      .attr('x', cx - iconSize / 2).attr('y', cy + R - iconSize / 2)
      .attr('width', iconSize).attr('height', iconSize)
      .style('filter', windIconFilter);

    // Hero values — same styling as Current Conditions' readouts: the
    // mono numeric font as well as the accent colour, not just the colour.
    dynG.append('text').attr('x', cx).attr('y', H - 19).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(windLabel(v.windSpeed));
    dynG.append('text').attr('x', cx).attr('y', H - 8).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '9.5px').style('fill', 'var(--bw-accent)')
      .text(DivumWXI18N.t('Gust') + ' ' + windLabel(v.windGust));

    // ---- Right pane: 5 readouts as label/value chip rows ----
    maxGustText.textContent = windLabel(v.gustMax) + ' (' + timeLabelFor(v.gustMaxTime) + ')';
    bearingText.textContent = skynet(v.windDir) + '\u00B0';
    ordinalText.textContent = toOrdinal(v.windDir);
    beaufortText.textContent = v.beaufortScale + ' Bft (' + v.beaufortDesc + ')';

    var windRunUnit = (currentUnits.wind === 'mph' || currentUnits.wind === 'kt') ? 'mi' : 'km';
    var windRunVal = windRunUnit === 'mi' ? v.windRunMi : v.windRunMi * 1.609344;
    windRunText.textContent = windRunVal.toFixed(1) + ' ' + windRunUnit;
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardAnemometer: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardAnemometer: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var wind = arch.wind || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        windDir:   num(o.windDir, 0),
        windSpeed: num(o.windSpeed, 0),
        windGust:  num(o.windGust, 0),
        gustMax:   num(wind.gust_max, 0),
        gustMaxTime: num(wind.gust_maxtime, 0),
        windRunMi: num(wind.wind_run, 0),
        beaufortScale: num(o.beaufortScale, 0),
        beaufortDesc: o.beaufortDesc || '\u2014',
        speedColor: o.beaufortColorSpeed || 'var(--bw-accent)',
        gustColor: o.beaufortColorGust || 'var(--bw-accent)'
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardAnemometer: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardAnemometer.js failed:", e);
}

/* ===== cardWindCompass.js ===== */
try {
/*
##############################################################################################
# cardWindCompass.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardWindCompass.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }
  function skynet(v){
    v = parseInt(v, 10);
    if (v < 10) return '00' + v;
    if (v < 100) return '0' + v;
    return '' + v;
  }
  var COMPASS_16 = ['North','NNE','NE','ENE','East','ESE','SE','SSE','South','SSW','SW','WSW','West','WNW','NW','NNW'];
  var COMPASS_BOUNDS = [11.25,33.75,56.25,78.75,101.25,123.75,146.25,168.75,191.25,213.75,236.25,258.75,281.25,303.75,326.25,348.75];
  function toOrdinal(deg){
    for (var i = 0; i < COMPASS_BOUNDS.length; i++){ if (deg <= COMPASS_BOUNDS[i]) return DivumWXI18N.t(COMPASS_16[i]); }
    return DivumWXI18N.t('North');
  }
  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) renderCard(lastData);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });

  var WIND_UNIT_LABEL = { mph: 'mph', kmh: 'km/h', kt: 'kt', ms: 'm/s', bf: 'Bft' };
  function windFromMS(ms){
    switch (currentUnits.wind) {
      case 'mph': return ms / 0.44704;
      case 'kmh': return ms * 3.6;
      case 'kt':  return ms / 0.514444;
      case 'ms':  return ms;
      case 'bf':  return beaufort(ms).force;
      default:    return ms * 3.6;
    }
  }
  function windLabel(ms){
    if (currentUnits.wind === 'bf'){ var b = beaufort(ms); return b.force + ' Bft (' + b.label + ')'; }
    return windFromMS(ms).toFixed(1) + ' ' + (WIND_UNIT_LABEL[currentUnits.wind] || 'km/h');
  }

  var mount = document.getElementById('compassCard6');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Direction | Windspeed');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: compass + hero values, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions / Anemometer.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  // Bearing/Ordinal are already shown directly on the compass dial itself
  // (unlike Anemometer's simpler needle gauge), so the right pane only
  // needs the three readouts that don't have a natural home on the dial.
  var maxGustText = addChipRow('Max Gust');
  var beaufortText = addChipRow('Beaufort');
  var windRunText = addChipRow('Wind Run');
  windRunText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the wind chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=wind&embed=1';
  cardLink.setAttribute('data-modal', 'Wind');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Wind & Gust Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=wind&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  // Nudged up from cy=78 (same balancing treatment as Anemometer, just
  // in the other direction — this dial's bottom margin was tighter than
  // its top, not the reverse) so the ring and the hero text below it
  // split the pane's vertical space more evenly.
  var W = 180, H = 175, cx = 90, cy = 74;
  var dotR = 32, nsewR = 41, tickInnerR = 46, tickOuterR = 53, tickLabelR = 60;

  // ---- Damped-spring needle animation ----------------------------------
  // Not a CSS/d3 transition (this used to be a d3 .transition().tween(),
  // eased with d3.easePoly over a fixed 900ms) — a genuine second-order
  // damped spring instead: angular acceleration proportional to how far
  // the arrow is from its target, minus a term proportional to its own
  // angular velocity (accel = k*(target-angle) - c*velocity). zeta=0.8 is
  // deliberately underdamped — the analytic overshoot for a step input is
  // exp(-zeta*pi/sqrt(1-zeta^2)), ~1.5% here (closer to ~1.3% at
  // zeta~0.81) — so a big bearing change swings a little past the target
  // and eases back, the way a real moving-coil instrument does, rather
  // than gliding to a stop like the old eased transition. wrap:true means
  // the delta driving the spring is always the shortest way round the
  // dial — 350° -> 10° is a +20° nudge, not a -340° lap the other way.
  function createNeedleSpring(applyAngle, opts){
    opts = opts || {};
    var zeta = opts.zeta || 0.8;
    var omega = opts.omega || 4; // rad/s — higher settles faster
    var k = omega * omega;
    var c = 2 * zeta * omega;
    var wrap = !!opts.wrap;

    var angle = (typeof opts.initial === 'number') ? opts.initial : 0;
    var vel = 0;
    var target = angle;
    var lastT = null;
    var rafId = null;

    function tick(now){
      rafId = null;
      if (lastT === null) lastT = now;
      var dt = Math.min((now - lastT) / 1000, 0.1); // clamp so a backgrounded tab doesn't produce one huge jump on return
      lastT = now;

      var delta = target - angle;
      if (wrap){
        delta = ((delta + 180) % 360 + 360) % 360 - 180;
      }
      var accel = k * delta - c * vel;
      vel += accel * dt;
      angle += vel * dt;

      applyAngle(angle);

      if (Math.abs(delta) > 0.02 || Math.abs(vel) > 0.02){
        rafId = requestAnimationFrame(tick);
      }
    }

    return {
      setTarget: function(deg){
        target = deg;
        if (rafId === null){ lastT = null; rafId = requestAnimationFrame(tick); }
      }
    };
  }
  // Two independent springs — the main (blue) arrow tracks the instant
  // wind direction, the secondary (green) arrow tracks the 10-minute
  // average — created once, the first time the static compass ring is
  // built (see the arrowSpring/arrowxSpring assignments below).
  var arrowSpring = null, arrowxSpring = null;

  function ringArrowPath(ringR, len){
    var tipY = -(ringR - len * 0.62);
    var baseY = -(ringR + len * 0.38);
    var backY = -(ringR + len * 0.08);
    var w = len * 0.34;
    var pts = [[0, tipY], [w, baseY], [0, backY], [-w, baseY]];
    return 'M' + pts.map(function(p){ return p[0] + ',' + p[1]; }).join('L') + 'Z';
  }

  function renderCard(v){
    titleLabel.textContent = DivumWXI18N.t('Direction | Windspeed') + ' (' + (WIND_UNIT_LABEL[currentUnits.wind] || 'km/h') + ')';

    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    }

    svg.select('g.compass-chrome').remove();
    var chromeG = svg.append('g').attr('class', 'compass-chrome');

    var compassG = svg.select('g.compass-static');
    if (compassG.empty()){
      compassG = svg.append('g').attr('class', 'compass-static').attr('transform', 'translate(' + cx + ',' + cy + ')');

      for (var deg = 0; deg < 360; deg += 2){
        var major = (deg % 30 === 0);
        var ang = (deg - 90) * Math.PI / 180;
        var x1 = Math.cos(ang) * tickInnerR, y1 = Math.sin(ang) * tickInnerR;
        var x2 = Math.cos(ang) * tickOuterR, y2 = Math.sin(ang) * tickOuterR;
        compassG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
          .style('stroke', major ? '#ff6347' : 'var(--bs-border-color)')
          .style('stroke-width', major ? 1 : 0.5);
        if (major){
          var xt = Math.cos(ang) * tickLabelR, yt = Math.sin(ang) * tickLabelR;
          compassG.append('text').attr('x', xt).attr('y', yt).attr('dy', '0.32em')
            .style('text-anchor', 'middle').style('font-size', '6px')
            .style('fill', deg === 0 ? '#ff6347' : overlayTextColor)
            .text(deg);
        }
      }

      var numDots = 60;
      for (var i = 0; i < numDots; i++){
        var a2 = (i / numDots) * 2 * Math.PI;
        compassG.append('circle')
          .attr('cx', Math.cos(a2) * dotR).attr('cy', Math.sin(a2) * dotR)
          .attr('r', 0.6).style('fill', 'var(--bs-border-color)');
      }

      DivumWXI18N.applyLabel(compassG.append('text').attr('x', 0).attr('y', -nsewR).attr('dy', '0.32em').style('text-anchor', 'middle')
        .style('font-size', '9px').style('font-weight', '700').style('fill', '#ff6347').node(), 'N');
      DivumWXI18N.applyLabel(compassG.append('text').attr('x', nsewR).attr('y', 0).attr('dy', '0.32em').style('text-anchor', 'middle')
        .style('font-size', '8px').style('fill', overlayTextColor).node(), 'E');
      DivumWXI18N.applyLabel(compassG.append('text').attr('x', 0).attr('y', nsewR).attr('dy', '0.32em').style('text-anchor', 'middle')
        .style('font-size', '8px').style('fill', overlayTextColor).node(), 'S');
      DivumWXI18N.applyLabel(compassG.append('text').attr('x', -nsewR).attr('y', 0).attr('dy', '0.32em').style('text-anchor', 'middle')
        .style('font-size', '8px').style('fill', overlayTextColor).node(), 'W');

      var arrowG = compassG.append('g').attr('class', 'arrow').attr('transform', 'rotate(0)');
      arrowG.append('path').attr('d', ringArrowPath(tickInnerR, 15)).attr('fill', '#007fff');
      var arrowxG = compassG.append('g').attr('class', 'arrowx').attr('transform', 'rotate(0)');
      arrowxG.append('path').attr('d', ringArrowPath(dotR, 12)).attr('fill', '#2e8b57');

      arrowSpring = createNeedleSpring(function(deg){
        arrowG.attr('transform', 'rotate(' + deg + ')');
      }, { zeta: 0.8, omega: 4, wrap: true, initial: 0 });
      arrowxSpring = createNeedleSpring(function(deg){
        arrowxG.attr('transform', 'rotate(' + deg + ')');
      }, { zeta: 0.8, omega: 4, wrap: true, initial: 0 });
    }

    arrowSpring.setTarget(v.windDir);
    arrowxSpring.setTarget(v.windDir10);

    // Bearing/ordinal readouts stay on the dial itself — same accent colour
    // as every other value on the card now, per the "values are always
    // coloured" rule (the arrows keep their own blue/green so the two rings
    // stay visually distinguishable; that's graphic encoding, not text).
    chromeG.append('text').attr('x', cx).attr('y', cy - 4).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '9px').style('fill', 'var(--bw-accent)')
      .text(skynet(v.windDir) + '\u00B0');
    chromeG.append('text').attr('x', cx).attr('y', cy + 8).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '9px').style('fill', 'var(--bw-accent)')
      .text(toOrdinal(v.windDir));
    chromeG.append('text').attr('x', cx).attr('y', cy + 20).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '9px').style('fill', 'var(--bw-accent)')
      .text(skynet(v.windDir10) + '\u00B0');

    // Hero values — stacked below the ring (there's no room to flank it
    // left/right in a 60%-width pane the way the old 310-wide canvas did),
    // same accent colour + mono font as Current Conditions.
    var heroY = cy + tickLabelR + 14;
    chromeG.append('text').attr('x', cx).attr('y', heroY).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(DivumWXI18N.t('Currently') + ' ' + windFromMS(v.windSpeed).toFixed(1) + ' ' + (WIND_UNIT_LABEL[currentUnits.wind] || 'km/h'));
    chromeG.append('text').attr('x', cx).attr('y', heroY + 15).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '9.5px').style('fill', 'var(--bw-accent)')
      .text(DivumWXI18N.t('Gust') + ' ' + windFromMS(v.windGust).toFixed(1) + ' ' + (WIND_UNIT_LABEL[currentUnits.wind] || 'km/h'));

    // ---- Right pane: 3 readouts as label/value chip rows ----
    maxGustText.textContent = windLabel(v.gustMax) + ' (' + timeLabelFor(v.gustMaxTime) + ')';
    beaufortText.textContent = v.beaufortScale + ' Bft (' + v.beaufortDesc + ')';

    var windRunUnit = (currentUnits.wind === 'mph' || currentUnits.wind === 'kt') ? 'mi' : 'km';
    var windRunVal = windRunUnit === 'mi' ? v.windRunMi : v.windRunMi * 1.609344;
    windRunText.textContent = windRunVal.toFixed(1) + ' ' + windRunUnit;
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardWindCompass: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardWindCompass: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var wind = arch.wind || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        windDir:   num(o.windDir, 0),
        windDir10: num(wind.direction_10m_avg, 0),
        windSpeed: num(o.windSpeed, 0),
        windGust:  num(o.windGust, 0),
        gustMax:   num(wind.gust_max, 0),
        gustMaxTime: num(wind.gust_maxtime, 0),
        windRunMi: num(wind.wind_run, 0),
        beaufortScale: num(o.beaufortScale, 0),
        beaufortDesc: o.beaufortDesc || '\u2014',
        speedColor: o.beaufortColorSpeed || 'var(--bw-accent)',
        gustColor: o.beaufortColorGust || 'var(--bw-accent)'
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardWindCompass: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardWindCompass.js failed:", e);
}

/* ===== cardBarometer.js ===== */
try {
/*
##############################################################################################
# cardBarometer.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardBarometer.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }
  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm', pressure: 'hpa' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) renderCard(lastData);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });

  var PRESSURE_CONFIG = {
    hpa:  { factor: 1,                  dp: 1, domain: [940, 1060], ticks: 12, tickDp: 0, label: 'hPa',  badgeUnit: 'inHg', badgeFactor: 0.029529983071445, badgeDp: 2 },
    mbar: { factor: 1,                  dp: 1, domain: [940, 1060], ticks: 12, tickDp: 0, label: 'mbar', badgeUnit: 'inHg', badgeFactor: 0.029529983071445, badgeDp: 2 },
    inhg: { factor: 0.029529983071445,  dp: 2, domain: [27.5, 31.5], ticks: 8,  tickDp: 1, label: 'inHg', badgeUnit: 'hPa',  badgeFactor: 33.863886666667,   badgeDp: 1 },
    kpa:  { factor: 0.1,                dp: 2, domain: [94, 106],   ticks: 12, tickDp: 0, label: 'kPa',  badgeUnit: 'inHg', badgeFactor: 0.29529983071445,  badgeDp: 2 },
    mmhg: { factor: 0.750062,           dp: 1, domain: [705, 795],  ticks: 12, tickDp: 0, label: 'mmHg', badgeUnit: 'inHg', badgeFactor: 0.039370079,       badgeDp: 2 }
  };
  function pressureConfig(){ return PRESSURE_CONFIG[currentUnits.pressure] || PRESSURE_CONFIG.hpa; }

  var mount = document.getElementById('barometerCard7');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Barometer');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gauge + hero value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var maxText = addChipRow('Max');
  var minText = addChipRow('Min');
  var trendText = addChipRow('Trend');
  var altitudeText = addChipRow('Station Alt');
  var airDensityText = addChipRow('Air Density');
  airDensityText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the barometer chart/records page —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=barometer&embed=1';
  cardLink.setAttribute('data-modal', 'Barometer');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Barometer Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=barometer&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);


  var W = 180, H = 175, cx = 90, cy = 81, R = 54;
  var ICON_BASE = './meteocons/fill/svg/';
  var ARC_BANDS_HPA = [
    [940, 970, '#ff00ff'], [970, 990, '#f8d747'], [990, 1010, '#007fff'],
    [1010, 1030, '#2e8b57'], [1030, 1060, '#ff6347']
  ];



  var TREND_LABELS = [
    { name: 'STORMY', offset: '6.25%', anchor: 'middle' },
    { name: 'RAIN', offset: '16.7%', anchor: 'middle' },
    { name: 'CHANGE', offset: '25%', anchor: 'middle' },
    { name: 'FAIR', offset: '33.3%', anchor: 'middle' },
    { name: 'VERY DRY', offset: '43.75%', anchor: 'middle' }
  ];

  function renderCard(v){
    var cfg = pressureConfig();
    titleLabel.textContent = DivumWXI18N.t('Barometer') + ' (' + cfg.label + ')';

    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    var currentDisp = v.current * cfg.factor;
    var maxDisp = v.max * cfg.factor;
    var minDisp = v.min * cfg.factor;

    var arcScale = d3.scaleLinear().domain(cfg.domain).range([-135, 135]).clamp(true);
    var colorScale = d3.scaleLinear().domain([940, 1060]).range([-135, 135]);

    var bgArc = d3.arc().innerRadius(R - 5).outerRadius(R);
    svg.selectAll('.b-bg-arc')
      .data(ARC_BANDS_HPA).join('path')
      .attr('class', 'b-bg-arc')
      .attr('transform', 'translate(' + cx + ',' + cy + ')')
      .attr('d', function(d){ return bgArc.startAngle(colorScale(d[0]) * Math.PI / 180).endAngle(colorScale(d[1]) * Math.PI / 180)(); })
      .style('fill', function(d){ return d[2]; });

    var tickG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ')');

    var MINOR_PER_INTERVAL = 4;
    for (var mi = 0; mi < cfg.ticks - 1; mi++){
      var mvStart = cfg.domain[0] + ((cfg.domain[1] - cfg.domain[0]) / (cfg.ticks - 1)) * mi;
      var mvEnd = cfg.domain[0] + ((cfg.domain[1] - cfg.domain[0]) / (cfg.ticks - 1)) * (mi + 1);
      for (var mj = 1; mj <= MINOR_PER_INTERVAL; mj++){
        var mtv = mvStart + (mvEnd - mvStart) * (mj / (MINOR_PER_INTERVAL + 1));
        var mang = (arcScale(mtv) - 90) * Math.PI / 180;
        var mx1 = Math.cos(mang) * (R + 3), my1 = Math.sin(mang) * (R + 3);
        var mx2 = Math.cos(mang) * (R + 5), my2 = Math.sin(mang) * (R + 5);
        tickG.append('line').attr('x1', mx1).attr('y1', my1).attr('x2', mx2).attr('y2', my2)
          .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 0.5).style('opacity', 0.55);
      }
    }

    for (var i = 0; i < cfg.ticks; i++){
      var tv = cfg.domain[0] + ((cfg.domain[1] - cfg.domain[0]) / (cfg.ticks - 1)) * i;
      var ang = (arcScale(tv) - 90) * Math.PI / 180;
      var x1 = Math.cos(ang) * (R + 3), y1 = Math.sin(ang) * (R + 3);
      var x2 = Math.cos(ang) * (R + 7), y2 = Math.sin(ang) * (R + 7);
      var xt = Math.cos(ang) * (R + 11), yt = Math.sin(ang) * (R + 11);
      tickG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 1);
      tickG.append('text').attr('x', xt).attr('y', yt).attr('dy', '0.32em')
        .style('text-anchor', 'middle').style('font-size', '6px').style('fill', overlayTextColor)
        .text(tv.toFixed(cfg.tickDp));
    }

    var textArc = d3.arc().innerRadius(R - 4.25).outerRadius(R - 4.25)
      .startAngle(-135 * Math.PI / 180).endAngle(135 * Math.PI / 180);
    svg.append('path').attr('id', 'barometerLabelPath')
      .attr('transform', 'translate(' + cx + ',' + cy + ')')
      .attr('d', textArc()).style('fill', 'none').style('stroke', 'none');
    var labelContainer = svg.append('g')
      .style('font-family', 'inherit').style('font-weight', '700').style('font-size', '5px').style('fill', '#1c4263');
    TREND_LABELS.forEach(function(l){
      labelContainer.append('text').append('textPath')
        .attr('xlink:href', '#barometerLabelPath').attr('startOffset', l.offset)
        .style('text-anchor', l.anchor).text(DivumWXI18N.t(l.name));
    });

    var needleAngle = arcScale(currentDisp);
    var needleG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + needleAngle + ')');
    needleG.append('polygon').attr('points', '0,' + (-(R - 6)) + ' 2.2,10 -2.2,10').style('fill', 'red');
    svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', 4).style('fill', 'red');

    var minAngle = arcScale(minDisp);
    svg.append('line').attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + minAngle + ')')
      .attr('x1', 0).attr('y1', -R).attr('x2', 0).attr('y2', -(R + 7))
      .style('stroke', '#2e8b57').style('stroke-width', 2);
    var maxAngle = arcScale(maxDisp);
    svg.append('line').attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + maxAngle + ')')
      .attr('x1', 0).attr('y1', -R).attr('x2', 0).attr('y2', -(R + 7))
      .style('stroke', '#ff6347').style('stroke-width', 2);

    var iconSize = 24.3; // 18 * 1.35
    svg.append('image')
      .attr('xlink:href', ICON_BASE + 'barometer.svg')
      .attr('x', cx - iconSize / 2).attr('y', cy + R - iconSize / 2)
      .attr('width', iconSize).attr('height', iconSize);

    // Hero value — same accent colour + mono font as Current Conditions.
    svg.append('text').attr('x', cx).attr('y', H - 16).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(currentDisp.toFixed(cfg.dp) + ' ' + cfg.label);

    // ---- Right pane: 5 readouts as label/value chip rows ----
    maxText.textContent = maxDisp.toFixed(cfg.dp) + ' ' + cfg.label + ' (' + timeLabelFor(v.maxTime) + ')';
    minText.textContent = minDisp.toFixed(cfg.dp) + ' ' + cfg.label + ' (' + timeLabelFor(v.minTime) + ')';
    var trendArrow = v.trendCode > 0 ? '\u279a' : v.trendCode < 0 ? '\u2798' : '\u2799';
    trendText.textContent = trendArrow + ' ' + v.trendDesc;
    altitudeText.textContent = v.altitude.toFixed(1) + ' m';
    airDensityText.textContent = v.airDensity.toFixed(3) + ' kg/m\u00B3';
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardBarometer: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardBarometer: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var barom = arch.barom || {};
      var airDensityObj = arch.air_density || {};
      var meta = arch.meta || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      var trendCode = num(barom.trend_code, 0);

      lastData = {
        // Same live-vs-archive priority fix as cardSolarRadiation/
        // cardUvIndex elsewhere in this file -- loop.json's own reading
        // is live, archive.json's barom.current only a periodic
        // snapshot, used here only as a fallback.
        current: num(o.barometer, num(barom.current, 1013.25)),
        max: num(barom.day_max, 1013.25),
        min: num(barom.day_min, 1013.25),
        maxTime: num(barom.day_maxtime, 0),
        minTime: num(barom.day_mintime, 0),
        trendCode: trendCode,
        trendDesc: barom.trend_desc || DivumWXI18N.t('Steady'),
        trendColor: trendCode > 0 ? '#3b9cac' : trendCode < 0 ? '#ff7c39' : '#90b12a',
        currentColor: o.barometerColor || 'var(--bw-accent)',
        dayMaxColor: overlayTextColor,
        dayMinColor: overlayTextColor,
        airDensity: num(airDensityObj.current, 0),
        altitude: num(meta.elevation_m, 0)
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardBarometer: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardBarometer.js failed:", e);
}

/* ===== cardPiezoRain.js ===== */
try {
/*
##############################################################################################
# cardPiezoRain.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardPiezoRain.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  // Fixed water colour — same blue as the raindrop icons, used for the
  // tube fill regardless of rain amount (no longer a range-based colour).
  var WATER_COLOR = '#007fff';

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) renderCard(lastData);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function mm2in(mm){ return mm / 25.400013716; }
  function rainLabel(mm){
    return currentUnits.rain === 'in'
      ? mm2in(mm).toFixed(2) + ' in'
      : mm.toFixed(2) + ' mm';
  }

  var mount = document.getElementById('rainCard8');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Piezo Rain');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gauge + hero value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var yearText = addChipRow(String(stationNow().getUTCFullYear()));
  var monthText = addChipRow('This Month');
  var hourText = addChipRow('Last Hour');
  var last24hText = addChipRow('Last 24hr');
  var rateText = addChipRow('Rain Rate');
  var eventText = addChipRow('Rain Event');
  eventText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the rain chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=rain&embed=1';
  cardLink.setAttribute('data-modal', 'Rain');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Rain & Rain Rate Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=rain&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  // Shared across any cards on the page — guarded so a second card
  // doesn't insert a duplicate <style> block.
  if (!document.getElementById('raindrop-pulse-style')) {
    var pulseStyle = document.createElement('style');
    pulseStyle.id = 'raindrop-pulse-style';
    pulseStyle.textContent =
      '@keyframes raindropPulse {' +
      '0%, 100% { opacity: 1; transform: scale(1); }' +
      '50% { opacity: 0.35; transform: scale(1.3); }' +
      '}' +
      '.raindrop-pulse { transform-box: fill-box; transform-origin: center; ' +
      'animation: raindropPulse 1.3s ease-in-out infinite; }';
    document.head.appendChild(pulseStyle);
  }

  var THRESHOLDS = [
    { limit: 2, val: 1 }, { limit: 5, val: 2 }, { limit: 10, val: 3 },
    { limit: 20, val: 5 }, { limit: 30, val: 7 }, { limit: 40, val: 9 },
    { limit: 50, val: 11 }, { limit: 60, val: 13 }, { limit: 70, val: 15 },
    { limit: 80, val: 17 }, { limit: 90, val: 19 }, { limit: 100, val: 21 }
  ];

  function renderCard(v){
    titleLabel.textContent = DivumWXI18N.t('Piezo Rain') + ' (' + (currentUnits.rain === 'in' ? 'in' : 'mm') + ')';

    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      // Cropped viewport (nonzero min-x) rather than rewriting every
      // hardcoded line()/rect() coordinate below — the funnel/tube/
      // raindrop drawing was already a self-contained subsystem sitting
      // inside the old 310-wide canvas, so this just frames that region
      // directly.
      //
      // Vertically: the visible content's true top isn't the funnel rim
      // (y=13) but the decorative arc above it, whose apex reaches y=3.5
      // (radius 70, centred at y=73.5) — 9.5 units higher. min-y is
      // -13.75 so that full 3.5->144 span (140.5 tall) sits centred
      // within the 175-tall pane (17.25px margin top and bottom).
      //
      // Horizontally: the device itself (funnel/housing/tube/arc, not
      // the raindrops off to the side) spans x=40.25->109.5, centred on
      // x≈74.9 — not x=85, the old viewBox's own centre (25->145,
      // sized to include the raindrops without compensating on the
      // left). min-x is 5 (not 25) so the device sits centred at x≈75
      // within the wider 140-unit-wide viewBox; the right edge stays at
      // 145, so the raindrops keep exactly the margin they had before.
      svg = svgSel.append('svg').attr('viewBox', '5 -13.75 140 175').attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    // Fixed at the light theme's own value (was var(--bs-secondary-color),
    // which is a different, lighter grey in dark mode) — the funnel and
    // housing lines should read the same regardless of theme, matching
    // Tipping Rain's own housingColor, which was never theme-dependent.
    var lineColor = '#5C6672';

    var arc = d3.arc().innerRadius(70).outerRadius(70);
    svg.append('path')
      .attr('fill', 'none').attr('stroke-width', 1.5).attr('stroke-linejoin', 'round')
      .attr('stroke', lineColor)
      .attr('d', arc({ startAngle: -29 * Math.PI / 180, endAngle: 29 * Math.PI / 180 }))
      .attr('transform', 'translate(75,73.5)');

    function line(x1, y1, x2, y2){
      svg.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', lineColor).style('stroke-width', 1.5).style('fill', 'none').style('stroke-linecap', 'round');
    }

    line(40.5, 13, 109, 13);
    line(40.25, 12.75, 42.5, 28.25);
    line(109.5, 12.75, 107.5, 28.25);
    line(42.5, 28.25, 107.5, 28.25);
    line(42.5, 39.4, 107.5, 39.4);
    line(42.5, 39.5, 46.5, 53);
    line(107.5, 39.5, 103.25, 53);
    line(46.5, 53.5, 103.25, 53.5);

    line(51.5, 54, 47.5, 59.5);
    line(98.25, 54, 102, 59.5);
    line(47.5, 60, 102, 60);

    line(52.25, 60, 48.75, 65);
    line(96.5, 60, 100.75, 65);
    line(48.75, 65, 100.75, 65);

    line(53.5, 65.5, 50.5, 70);
    line(96.5, 65.5, 99.5, 70);
    line(50.5, 70.5, 99.5, 70.5);

    line(54.75, 71, 51.75, 76);
    line(95, 71, 98.25, 76);
    line(51.75, 76, 98.25, 76);

    line(56.75, 76, 56.75, 144);
    line(93.25, 76, 93.25, 144);
    line(57, 144, 93.25, 144);

    line(69.5, 28.5, 69.5, 39);
    line(80, 28.5, 80, 39);

    // The outer <g> carries position/base-size as an SVG transform
    // attribute; the inner <path> carries the CSS pulse animation. Kept
    // separate because a CSS transform on an element overrides (rather
    // than composes with) its SVG "transform" attribute — animating the
    // same element that's positioned via translate/scale would snap it
    // back to the SVG viewport origin every frame.
    function raindrop(x, y, scale, pulsing){
      var g = svg.append('g').attr('transform', 'translate(' + x + ',' + y + ') scale(' + scale + ')');
      var path = g.append('path')
        .attr('d', 'M0,-8 C4,-2 4,3 0,3 C-4,3 -4,-2 0,-8 Z')
        .style('fill', '#007fff');
      if (pulsing) path.attr('class', 'raindrop-pulse');
    }
    // Pulse only while it's actually raining (rate > 0).
    raindrop(122, 112, 1.15, v.rate > 0);
    raindrop(131, 122, 0.85, v.rate > 0);

    var displayIsIn = currentUnits.rain === 'in';
    var currentRainMm = v.day;
    var currentDisplay = displayIsIn ? mm2in(currentRainMm) : currentRainMm;

    svg.append('text')
      .text(rainLabel(currentRainMm))
      .attr('x', 75.5).attr('y', 24.5).attr('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-weight', '600').style('font-size', '11px')
      .style('fill', 'var(--bw-accent)');

    var bottomY = 150 + 38, topY = 65, bulbRadius = 25.5, tubeWidth = 25.5;
    var bulb_cy = bottomY - bulbRadius, top_cy = topY + tubeWidth / 2;
    var tubeBorderColor = '#999999';

    var currentRainMmForThreshold = (currentUnits.rain === 'mm') ? currentRainMm : currentRainMm * 25.4;
    var threshold = THRESHOLDS.find(function(t){ return currentRainMmForThreshold < t.limit; });
    var baseValue = threshold ? threshold.val : 23;
    var stepping = (currentUnits.rain === 'mm') ? baseValue : baseValue / 25.4;

    var domain = [0, stepping * Math.ceil(currentDisplay / stepping)];
    if (domain[1] - currentDisplay < 0.66 * stepping) domain[1] += stepping;

    var scale = d3.scaleLinear()
      .range([bulb_cy - bulbRadius / 2 - 8.5, top_cy])
      .domain(domain);

    var fillTop = scale(currentDisplay);

    // Both the fill and its meniscus only draw once there's actually
    // some rain to show — at 0mm the tube reads as genuinely empty
    // rather than showing a thin sliver of water with nothing behind it.
    if (currentRainMm > 0) {
      svg.append('rect')
        .attr('x', 76.5 - 18.75).attr('y', fillTop - 2)
        .attr('width', 34.5).attr('height', Math.max(0, bulb_cy - 17.5 - fillTop))
        .style('fill', WATER_COLOR);

      svg.append('rect')
        .attr('x', 76.5 - 18.75).attr('y', fillTop - 4)
        .attr('rx', 3).attr('width', 34.5).attr('height', 4)
        .style('fill', 'var(--card-bg)');
    }

    var tickValues = d3.range((domain[1] - domain[0]) / stepping + 1)
      .map(function(n){ return domain[0] + n * stepping; });

    var svgAxis = svg.append('g').attr('class', 'RainScale')
      .attr('transform', 'translate(' + (75.5 - tubeWidth / 2 - 12) + ', 0)')
      .call(d3.axisLeft(scale).tickSize(7).tickValues(tickValues));

    svgAxis.selectAll('.tick text')
      .style('fill', overlayTextColor).style('font-family', 'inherit').style('font-size', '8px');
    svgAxis.select('path').style('stroke', 'none').style('fill', 'none');
    svgAxis.selectAll('.tick line')
      .style('stroke', tubeBorderColor).style('stroke-linecap', 'round').style('stroke-width', 2);

    // Hero value already appears above the funnel (currentDisplay text) —
    // no conversion badge here any more.

    // ---- Right pane: 6 readouts as label/value chip rows ----
    monthText.textContent = rainLabel(v.month);
    hourText.textContent = rainLabel(v.hour);
    last24hText.textContent = rainLabel(v.last24h);
    rateText.textContent = rainLabel(v.rate) + '/hr';
    yearText.textContent = rainLabel(v.year);
    eventText.textContent = v.event > 0
      ? rainLabel(v.event) + (v.stormStart ? ' (since ' + v.stormStart + ')' : '')
      : '\u2014';
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardPiezoRain: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardPiezoRain: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var pRain = arch.p_rain || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        day: num(pRain.day, 0),
        hour: num(pRain.hour, 0),


        last24h: num(pRain['24hour'], num(pRain.last24h, 0)),
        month: num(pRain.month, 0),
        year: num(pRain.year, 0),
        rate: num(pRain.rate, 0),
        event: num(pRain.event, 0),
        stormStart: pRain.storm_start || '',
        rainColor: o.rainColor || 'var(--bw-accent)',
        rateColor: o.rainRateColor || 'var(--bw-accent)'
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardPiezoRain: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardPiezoRain.js failed:", e);
}

/* ===== cardRainfall.js ===== */
try {
/*
##############################################################################################
# cardRainfall.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardRainfall.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return null;
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }
  // Fixed water colour — same blue as the raindrop icons, used for the
  // tube fill regardless of rain amount (no longer a range-based colour).
  var WATER_COLOR = '#007fff';

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) renderCard(lastData);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function mm2in(mm){ return mm / 25.400013716; }
  function rainLabel(mm){
    return currentUnits.rain === 'in'
      ? mm2in(mm).toFixed(2) + ' in'
      : mm.toFixed(2) + ' mm';
  }

  var mount = document.getElementById('rainCard9');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Rainfall');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gauge + hero value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var yearText = addChipRow(String(stationNow().getUTCFullYear()));
  var monthText = addChipRow('This Month');
  var hourText = addChipRow('Last Hour');
  var last24hText = addChipRow('Last 24hr');
  var rateText = addChipRow('Rain Rate');
  var eventText = addChipRow('Rain Event');
  eventText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the rain chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=rain&embed=1';
  cardLink.setAttribute('data-modal', 'Rain');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Rain & Rain Rate Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=rain&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  // Shared across any cards on the page — guarded so a second card
  // doesn't insert a duplicate <style> block.
  if (!document.getElementById('raindrop-pulse-style')) {
    var pulseStyle = document.createElement('style');
    pulseStyle.id = 'raindrop-pulse-style';
    pulseStyle.textContent =
      '@keyframes raindropPulse {' +
      '0%, 100% { opacity: 1; transform: scale(1); }' +
      '50% { opacity: 0.35; transform: scale(1.3); }' +
      '}' +
      '.raindrop-pulse { transform-box: fill-box; transform-origin: center; ' +
      'animation: raindropPulse 1.3s ease-in-out infinite; }';
    document.head.appendChild(pulseStyle);
  }


  var THRESHOLDS = [
    { limit: 2, val: 1 }, { limit: 5, val: 2 }, { limit: 10, val: 3 },
    { limit: 20, val: 5 }, { limit: 30, val: 7 }, { limit: 40, val: 9 },
    { limit: 50, val: 11 }, { limit: 60, val: 13 }, { limit: 70, val: 15 },
    { limit: 80, val: 17 }, { limit: 90, val: 19 }, { limit: 100, val: 21 }
  ];

  function renderCard(v){
    titleLabel.textContent = DivumWXI18N.t('Rainfall') + ' (' + (currentUnits.rain === 'in' ? 'in' : 'mm') + ')';

    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      // Cropped viewport (nonzero min-x), same window as Piezo Rain's
      // sibling card — the tube/raindrop drawing below is untouched.
      // min-y is -9.5 (not 0): this drawing's own content spans roughly
      // y=8 (tubeTop) to y=148 (tubeBottom), which read as sitting near
      // the top of the 175-tall pane with all the slack at the bottom —
      // shifting the viewport's origin up by 9.5 recenters that same
      // content vertically (~17.5px margin top and bottom) without
      // touching any of the drawing's own coordinates.
      svg = svgSel.append('svg').attr('viewBox', '25 -9.5 120 175').attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    var tubeBorderColor = '#999999';
    // Shifted right from the original cx=52 to match Piezo Rain's visual
    // center (~75.5) — the funnel was sitting noticeably further left
    // than the piezo device, unbalancing the two sibling cards.
    var cx = 75.5;
    var bodyHalfW = 18.25;
    var topHalfW = 30;
    var tubeTop = 8;
    var taperBottomY = 46;
    var tubeBottom = 148;

    // The outer <g> carries position/base-size as an SVG transform
    // attribute; the inner <path> carries the CSS pulse animation. Kept
    // separate because a CSS transform on an element overrides (rather
    // than composes with) its SVG "transform" attribute — animating the
    // same element that's positioned via translate/scale would snap it
    // back to the SVG viewport origin every frame.
    function raindrop(x, y, scale, pulsing){
      var g = svg.append('g').attr('transform', 'translate(' + x + ',' + y + ') scale(' + scale + ')');
      var path = g.append('path')
        .attr('d', 'M0,-8 C4,-2 4,3 0,3 C-4,3 -4,-2 0,-8 Z')
        .style('fill', '#007fff');
      if (pulsing) path.attr('class', 'raindrop-pulse');
    }
    // x shifted by the same +23.5 as cx, to preserve the original gap
    // between the tube's right edge and the raindrops. Pulse only while
    // it's actually raining (rate > 0).
    raindrop(126.5, 118, 1.15, v.rate > 0);
    raindrop(135.5, 128, 0.85, v.rate > 0);

    var currentRainMm = v.day;
    var displayIsIn = currentUnits.rain === 'in';
    var currentDisplay = displayIsIn ? mm2in(currentRainMm) : currentRainMm;

    var baseVal = 23;
    for (var i = 0; i < THRESHOLDS.length; i++){ if (currentRainMm < THRESHOLDS[i].limit){ baseVal = THRESHOLDS[i].val; break; } }
    var stepping = displayIsIn ? baseVal / 25.4 : baseVal;

    var domain = [0, stepping * Math.ceil(Math.max(currentDisplay, stepping) / stepping)];
    if (domain[1] - currentDisplay < 0.66 * stepping) domain[1] += stepping;

    var yScale = d3.scaleLinear().domain(domain).range([tubeBottom, taperBottomY + 3]);
    // Piezo Rain's tube math has a built-in ~3.75px floor (an accidental
    // byproduct of its bulb geometry) that keeps a sliver of water
    // visible at the bottom even at 0mm. Rainfall's math has no such
    // floor and collapses to a flat, empty-looking bottom at 0mm —
    // clamping fillTop here reproduces the same visible baseline, in
    // both light and dark themes (WATER_COLOR and --card-bg below are
    // already theme-safe, this just guarantees they're never 0-height).
    var MIN_WATER_HEIGHT = 3.75;
    var fillTop = Math.min(yScale(currentDisplay), tubeBottom - MIN_WATER_HEIGHT);
    // Water rect's bottom edge is pinned to tubeBottom (the actual floor
    // line drawn below) via this height formula, rather than derived
    // independently from fillTop — the old height calc always left the
    // water sitting a fixed 5px above the floor at every fill level,
    // not just at 0mm, it just wasn't visually obvious until empty.
    var waterHeight = (tubeBottom - fillTop) + 2;

    svg.append('rect')
      .attr('x', cx - bodyHalfW + 1).attr('y', fillTop - 2)
      .attr('width', bodyHalfW * 2 - 2).attr('height', waterHeight)
      .style('fill', WATER_COLOR);

    svg.append('rect')
      .attr('x', cx - bodyHalfW + 1).attr('y', fillTop - 4)
      .attr('rx', 3).attr('width', bodyHalfW * 2 - 2).attr('height', 4)
      .style('fill', 'var(--card-bg)');

    svg.append('line').attr('x1', cx - topHalfW).attr('y1', tubeTop).attr('x2', cx + topHalfW).attr('y2', tubeTop)
      .style('stroke', tubeBorderColor).style('stroke-width', 1.5).style('stroke-linecap', 'round');
    svg.append('line').attr('x1', cx - topHalfW).attr('y1', tubeTop).attr('x2', cx - bodyHalfW).attr('y2', taperBottomY)
      .style('stroke', tubeBorderColor).style('stroke-width', 1.5).style('stroke-linecap', 'round');
    svg.append('line').attr('x1', cx + topHalfW).attr('y1', tubeTop).attr('x2', cx + bodyHalfW).attr('y2', taperBottomY)
      .style('stroke', tubeBorderColor).style('stroke-width', 1.5).style('stroke-linecap', 'round');
    svg.append('line').attr('x1', cx - bodyHalfW).attr('y1', taperBottomY).attr('x2', cx - bodyHalfW).attr('y2', tubeBottom)
      .style('stroke', tubeBorderColor).style('stroke-width', 1.5);
    svg.append('line').attr('x1', cx + bodyHalfW).attr('y1', taperBottomY).attr('x2', cx + bodyHalfW).attr('y2', tubeBottom)
      .style('stroke', tubeBorderColor).style('stroke-width', 1.5);
    svg.append('line').attr('x1', cx - bodyHalfW).attr('y1', tubeBottom).attr('x2', cx + bodyHalfW).attr('y2', tubeBottom)
      .style('stroke', tubeBorderColor).style('stroke-width', 1.5).style('stroke-linecap', 'round');

    var tickValues = d3.range(domain[1] / stepping + 1).map(function(n){ return n * stepping; });
    var axis = d3.axisLeft(yScale).tickValues(tickValues).tickSize(7);
    var tAxis = svg.append('g').attr('class', 'y-axis')
      .attr('transform', 'translate(' + (cx - bodyHalfW - 12) + ', 0)').call(axis);
    tAxis.selectAll('.tick text').style('fill', overlayTextColor).style('font-family', 'inherit').style('font-size', '8px');
    tAxis.select('path').style('stroke', 'none').style('fill', 'none');
    tAxis.selectAll('.tick line').style('stroke', tubeBorderColor).style('stroke-linecap', 'round').style('stroke-width', 2);

    svg.append('text')
      .attr('x', cx).attr('y', (tubeTop + taperBottomY) / 2 + 3).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-weight', '600').style('font-size', '11px').style('fill', overlayTextColor)
      .text(currentDisplay.toFixed(displayIsIn ? 2 : 1) + ' ' + (displayIsIn ? 'in' : 'mm'));

    // Hero value already appears inside the tube (currentDisplay text) —
    // no conversion badge here any more.

    // ---- Right pane: 6 readouts as label/value chip rows ----
    monthText.textContent = rainLabel(v.month);
    hourText.textContent = rainLabel(v.hour);
    last24hText.textContent = rainLabel(v.last24h);
    rateText.textContent = rainLabel(v.rate) + '/hr';
    yearText.textContent = rainLabel(v.year);
    var stormLabel = timeLabelFor(v.stormStart);
    eventText.textContent = v.event > 0
      ? rainLabel(v.event) + (stormLabel ? ' (since ' + stormLabel + ')' : '')
      : '\u2014';
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardRainfall: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardRainfall: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var rain = arch.rain || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        day: num(rain.day, 0),
        hour: num(rain.hour, 0),


        last24h: num(rain.last24h, 0),
        month: num(rain.month, 0),
        year: num(rain.year, 0),
        rate: num(rain.rate, 0),
        event: num(rain.event, 0),
        stormStart: typeof rain.storm_start === 'number' ? rain.storm_start : 0,
        rainColor: o.rainColor || 'var(--bw-accent)',
        rateColor: o.rainRateColor || 'var(--bw-accent)'
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardRainfall: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardRainfall.js failed:", e);
}

/* ===== cardTippingRain.js ===== */
try {
/*
##############################################################################################
# cardTippingRain.js version 0.0.1
#  Alternative to cardRainfall.js — same data (loop.json/archive.json rain block), same 60:40
#  layout and right-pane chip rows, but the left pane's "measuring cylinder" (a static tube
#  with a rising water level) is replaced with a cross-sectional, animated tipping-bucket
#  rain gauge: a funnel feeds a small pivoted seesaw with a bucket on each end; whichever
#  bucket sits under the funnel throat fills, and on reaching its calibrated volume the seesaw
#  snaps over, dumping that bucket out through the drain at the base and swinging the empty
#  bucket into place — which is literally how a real tipping-bucket gauge measures rain (each
#  tip is a fixed volume, so tip frequency is what rate is derived from). The animation's tip
#  rate is driven by the station's actual rain rate rather than being decorative.
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardTippingRain.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return null;
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }
  var WATER_COLOR = '#007fff';

  var currentUnits = loadStoredUnits();
  function loadStoredUnits(){
    try {
      var key = localStorage.getItem('dashboardUnitSystem') || 'uk';
      if (typeof SYSTEMS !== 'undefined' && SYSTEMS[key]) return SYSTEMS[key];
    } catch (e) {}
    return { temp: 'C', wind: 'mph', rain: 'mm' };
  }
  window.addEventListener('unitsystemchange', function(e){
    if (e.detail && e.detail.config) {
      currentUnits = e.detail.config;
      if (lastData) renderCard(lastData);
    }
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function mm2in(mm){ return mm / 25.400013716; }
  function rainLabel(mm){
    return currentUnits.rain === 'in'
      ? mm2in(mm).toFixed(2) + ' in'
      : mm.toFixed(2) + ' mm';
  }

  var mount = document.getElementById('tippingRainCard9');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Tipping Rain');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: tipping-bucket cross-section, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Rainfall/Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var yearText = addChipRow(String(stationNow().getUTCFullYear()));
  var monthText = addChipRow('This Month');
  var hourText = addChipRow('Last Hour');
  var last24hText = addChipRow('Last 24hr');
  var rateText = addChipRow('Rain Rate');
  var eventText = addChipRow('Rain Event');
  eventText.parentElement.style.borderBottom = 'none';

  // Whole card is a click-through to the same rain chart/records page the
  // Rainfall card opens — this is an alternative visualisation of the same
  // underlying data, not a different data source.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=rain&embed=1';
  cardLink.setAttribute('data-modal', 'Tipping Rain');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Rain & Rain Rate Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=rain&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  // Shared with cardRainfall.js — guarded so whichever card loads first
  // creates it, and neither inserts a duplicate.
  if (!document.getElementById('raindrop-pulse-style')) {
    var pulseStyle = document.createElement('style');
    pulseStyle.id = 'raindrop-pulse-style';
    pulseStyle.textContent =
      '@keyframes raindropPulse {' +
      '0%, 100% { opacity: 1; transform: scale(1); }' +
      '50% { opacity: 0.35; transform: scale(1.3); }' +
      '}' +
      '.raindrop-pulse { transform-box: fill-box; transform-origin: center; ' +
      'animation: raindropPulse 1.3s ease-in-out infinite; }';
    document.head.appendChild(pulseStyle);
  }

  // ---- Tipping-bucket cross-section geometry --------------------------
  // Drawn as a cutaway: the funnel and housing are open profiles (we're
  // looking straight into the mechanism, not at a solid case), so the
  // pivoted seesaw and its two buckets are genuinely visible inside,
  // the same way Piezo Rain's tube shows its water level through an open
  // cross-section rather than a sealed cylinder.
  //
  // Vertical layout now matches Piezo Rain's own coordinate scheme
  // exactly, so the two cards read as the same size device: funnel/
  // housing top at y=13, the point where the mechanism empties into the
  // collection tube at y=76 (a height of 63 for the "upper section" —
  // identical to Piezo Rain's own 13->76 funnel span), and the tube
  // floor at y=144 (another 68, identical to Piezo Rain's own 76->144
  // tube span). The original mechanism (funnel, housing, seesaw, drain)
  // is still drawn in its own local coordinates below, unchanged, then
  // wrapped in one scaled <g> (see mechG in buildStatic) that maps its
  // local 6->143 span onto the shared 13->76 span — every relative
  // proportion inside the mechanism (funnel taper, pivot position,
  // bucket size) is preserved automatically by the uniform scale, only
  // its overall size shrinks. Stroke widths are the one thing scaling
  // would otherwise shrink along with the shapes, so every stroke-width
  // used inside that group is pre-divided by the same factor (see
  // MECH_LINE_W), so they come out matching Piezo Rain's 1.5px lines
  // once rendered.
  var W = 130, H = 175;
  var cx = 65;
  var housingColor = '#5C6672'; // matches Piezo Rain's own fixed lineColor

  var bodyHalfW = 32;
  var funnelTopY0 = 6, funnelHalfTop = 44;
  // funnelThroatRefY0/funnelHalfThroatRef is the funnel's own original full
  // endpoint (same taper angle and length as before the housing-touching
  // change) — drawn in full, independent of the housing.
  var funnelThroatRefY0 = 52, funnelHalfThroatRef = 7;
  // Housing vertical walls shortened by 25px at the bottom (152 -> 127).
  var bodyBottom0 = 127;
  // Drain spout keeps its own original ~16px length rather than stretching
  // when bodyBottom0 moved up.
  var spoutY0 = bodyBottom0 + 16;
  var pivotY0 = 76;
  var beamHalfLen = 21;
  var bucketW = 15, bucketH = 17, bucketTaper = 0.55; // taper: bottom width as a fraction of top width
  var tiltAngle = 14; // degrees off horizontal at rest, either side
  var drainHalfW = 7;
  // The drain spout no longer closes to a single point — it stops short
  // on each side, leaving a small gap at the tip: the outlet where
  // dumped water actually leaves the housing on its way into the tube.
  var spoutGapHalf = 3;

  // Shared vertical reference points, matching Piezo Rain's own y values
  // exactly for the mechanism and tube (13/76/144) — the canvas's own
  // vertical centring is computed separately below, once the hero text's
  // position above the image is known too.
  var mechTopY = 13, mechBottomY = 76, tubeBottomY = 144;
  var MECH_SCALE = (mechBottomY - mechTopY) / (spoutY0 - funnelTopY0); // local mechanism height -> its new target height
  var MECH_LINE_W = 1.5 / MECH_SCALE; // pre-compensated so rendered stroke width matches Piezo Rain's 1.5px

  // Collection tube — same construction as Piezo Rain's: a short taper
  // down from the drain's small opening to the tube's full width, then
  // straight walls to the floor, with a d3-scaled fill + axis inside
  // that redraw on every refresh.
  var tubeHalfW = 18.25, tubeFillHalfW = 17.25;
  var tubeTaperBottomY = mechBottomY + 8;
  var tubeOpenHalfW = spoutGapHalf * MECH_SCALE + 1; // matches the mechanism's own opening width once scaled

  // Widen the top section (funnel + housing) so its widest point — the
  // funnel's open top rim — renders at exactly twice the collection
  // tube's width, matching Piezo Rain's own top-section/tube proportions.
  // Applied to every horizontal-extent constant used inside the
  // mechanism group (never to bucketH/tiltAngle/bucketTaper, which
  // aren't widths), so the whole upper section — housing and seesaw
  // alike — widens evenly with no distortion. funnelJunctionY0, computed
  // below in buildStatic, still comes out at the same relative height
  // either way, since it only depends on ratios between three of these
  // values, all widened equally.
  var WIDEN = (2 * tubeHalfW) / (funnelHalfTop * MECH_SCALE);
  funnelHalfTop *= WIDEN;
  funnelHalfThroatRef *= WIDEN;
  bodyHalfW *= WIDEN;
  drainHalfW *= WIDEN;
  beamHalfLen *= WIDEN;
  bucketW *= WIDEN;

  // Hero value — moved down into the funnel's own vertical section (the
  // straight-walled collar above the tapered funnel rim, mechTopY)
  // rather than floating above the whole image, per feedback on the
  // rendered card. The collar height is now an independent, fixed value
  // (funnelCollarH) — previously funnelExtTopY was derived FROM the
  // hero text's position; that's inverted now, since the hero sits
  // inside the collar rather than above it. heroY (baseline) is placed
  // so the glyphs sit vertically centred within the collar: roughly one
  // font-size of ascent above the baseline (11px font, so ~9px) and
  // negligible descent (digits and "mm" have no descenders), so the
  // glyphs' own visual centre sits ~heroAscent/2 above the baseline —
  // matches the same 11px/9px assumption the original heroY/heroTopY
  // comment already used.
  var funnelCollarH = 26;
  var funnelExtTopY = mechTopY - funnelCollarH;
  var heroAscent = 9;
  var heroY = (funnelExtTopY + mechTopY) / 2 + heroAscent / 2; // baseline
  var heroTopY = heroY - heroAscent; // visual top edge estimate, kept for any other reader relying on it

  // Recentre the whole drawing (funnel collar top down to the tube
  // floor) vertically within the fixed H-tall canvas — equal margins
  // top and bottom. Anchored to funnelExtTopY (the collar's top edge)
  // rather than heroTopY now, since the collar is the topmost thing on
  // the card once the hero text moved down inside it — heroTopY no
  // longer marks the top of the drawing. H itself is left untouched
  // (still matching Piezo Rain's own canvas height, so the mechanism/
  // tube keep the same rendered size as Piezo Rain's).
  var CONTENT_H = tubeBottomY - funnelExtTopY;
  var V_MARGIN = (H - CONTENT_H) / 2;
  var VIEW_MIN_Y = funnelExtTopY - V_MARGIN;

  var THRESHOLDS = [
    { limit: 2, val: 1 }, { limit: 5, val: 2 }, { limit: 10, val: 3 },
    { limit: 20, val: 5 }, { limit: 30, val: 7 }, { limit: 40, val: 9 },
    { limit: 50, val: 11 }, { limit: 60, val: 13 }, { limit: 70, val: 15 },
    { limit: 80, val: 17 }, { limit: 90, val: 19 }, { limit: 100, val: 21 }
  ];

  var svg, mechG, beamG, bucketFillL, bucketFillR, dripG, funnelDropsG, ambientDropsG, tubeDynG;
  var tipping = false;          // true only during the brief flip animation
  var activeSide = 0;           // 0 = left bucket currently under the funnel, 1 = right
  var fillStartTime = null;
  var rafId = null;

  function bucketOutlinePath(){
    var wTop = bucketW, wBot = bucketW * bucketTaper;
    return 'M' + (-wTop/2) + ',0 L' + (-wBot/2) + ',' + bucketH + ' L' + (wBot/2) + ',' + bucketH + ' L' + (wTop/2) + ',0';
  }

  function raindrop(g, x, y, scale, pulsing){
    var dg = g.append('g').attr('transform', 'translate(' + x + ',' + y + ') scale(' + scale + ')');
    var path = dg.append('path')
      .attr('d', 'M0,-8 C4,-2 4,3 0,3 C-4,3 -4,-2 0,-8 Z')
      .style('fill', WATER_COLOR);
    if (pulsing) path.attr('class', 'raindrop-pulse');
    return dg;
  }

  function buildStatic(){
    // VIEW_MIN_Y centres the whole card (hero text down to the tube
    // floor) inside the fixed H-tall canvas with equal margins top and
    // bottom — see its computation above, alongside heroY/heroTopY.
    svg = d3.select(leftPane).append('svg').attr('viewBox', '0 ' + VIEW_MIN_Y + ' ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    // Mechanism group — everything below in this function up to the tube
    // is drawn in the original local coordinate space (cx=65,
    // funnelTopY0=6 .. spoutY0=143); this one transform maps that whole
    // span onto mechTopY..mechBottomY, shrinking the mechanism to match
    // Piezo Rain's own upper-section height while preserving every
    // internal proportion.
    mechG = svg.append('g').attr('transform',
      'translate(' + (cx - cx * MECH_SCALE) + ',' + (mechTopY - funnelTopY0 * MECH_SCALE) + ') scale(' + MECH_SCALE + ')');

    function mline(x1, y1, x2, y2){
      // Piezo Rain's own line() helper applies round linecap to every
      // stroke unconditionally, funnel taper and straight tube walls
      // alike — matched here the same way, no exceptions.
      return mechG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', housingColor).style('stroke-width', MECH_LINE_W).style('fill', 'none').style('stroke-linecap', 'round');
    }

    // Funnel — open top (no horizontal cap: the two vertical extension
    // walls below continue the opening upward instead, see
    // funnelExtTopY/svg extension lines just after), tapers down its
    // full original length to its own spout end, which hangs visibly
    // inside the wider housing below.
    mline(cx - funnelHalfTop, funnelTopY0, cx - funnelHalfThroatRef, funnelThroatRefY0);
    mline(cx + funnelHalfTop, funnelTopY0, cx + funnelHalfThroatRef, funnelThroatRefY0);

    // Vertical extension on top of the funnel, using the gap that used
    // to sit empty between the funnel's rim and the hero value text
    // above it. Drawn at the outer (unscaled) level directly from the
    // mechanism's own rendered rim position — cx ± funnelHalfTop scaled
    // by MECH_SCALE, since that's where the taper lines above actually
    // land once mechG's transform is applied — so these come out as
    // true vertical walls rather than being warped by that transform.
    svg.append('line').attr('x1', cx - funnelHalfTop * MECH_SCALE).attr('y1', mechTopY)
      .attr('x2', cx - funnelHalfTop * MECH_SCALE).attr('y2', funnelExtTopY)
      .style('stroke', housingColor).style('stroke-width', 1.5).style('stroke-linecap', 'round').style('fill', 'none');
    svg.append('line').attr('x1', cx + funnelHalfTop * MECH_SCALE).attr('y1', mechTopY)
      .attr('x2', cx + funnelHalfTop * MECH_SCALE).attr('y2', funnelExtTopY)
      .style('stroke', housingColor).style('stroke-width', 1.5).style('stroke-linecap', 'round').style('fill', 'none');

    // Where the housing's vertical walls actually cross the funnel's
    // slant (x = bodyHalfW) — this is where they start, extending down
    // to bodyBottom0. The funnel's own slant is drawn in full down to
    // funnelThroatRefY0 regardless, so its narrow spout end hangs
    // visibly inside the wider housing below the wall-junction point.
    var funnelJunctionY0 = funnelTopY0 + (funnelHalfTop - bodyHalfW) / (funnelHalfTop - funnelHalfThroatRef) * (funnelThroatRefY0 - funnelTopY0);

    // Housing side walls, cross-sectioned (open) from the funnel junction
    // down to the base, and the base floor itself with a gap in the
    // middle for the drain — a solid floor would trap the dumped water
    // on-screen.
    mline(cx - bodyHalfW, funnelJunctionY0, cx - bodyHalfW, bodyBottom0);
    mline(cx + bodyHalfW, funnelJunctionY0, cx + bodyHalfW, bodyBottom0);
    mline(cx - bodyHalfW, bodyBottom0, cx - drainHalfW, bodyBottom0);
    mline(cx + drainHalfW, bodyBottom0, cx + bodyHalfW, bodyBottom0);

    // Drain spout — tapers from the base gap almost to a point, but stops
    // short on each side, leaving a small opening at the tip where dumped
    // water actually exits the housing on its way into the tube below,
    // rather than closing to a fully sealed point.
    mline(cx - drainHalfW, bodyBottom0, cx - spoutGapHalf, spoutY0);
    mline(cx + drainHalfW, bodyBottom0, cx + spoutGapHalf, spoutY0);

    // Pivot support column, rising from the drain gap to the seesaw.
    mechG.append('line').attr('x1', cx).attr('y1', bodyBottom0).attr('x2', cx).attr('y2', pivotY0 + 3)
      .style('stroke', housingColor).style('stroke-width', MECH_LINE_W).style('stroke-linecap', 'round');

    // The seesaw itself — beam + pivot dot + two buckets — all inside one
    // rotatable group so a tip is a single transform change, not a
    // per-element animation.
    beamG = mechG.append('g').attr('class', 'tip-beam').attr('transform', 'translate(' + cx + ',' + pivotY0 + ') rotate(' + tiltAngle + ')');
    beamG.append('rect').attr('x', -beamHalfLen).attr('y', -1.5 / MECH_SCALE).attr('width', beamHalfLen * 2).attr('height', 3 / MECH_SCALE)
      .attr('rx', 1.5 / MECH_SCALE).style('fill', housingColor);

    var leftBucketG = beamG.append('g').attr('transform', 'translate(' + (-beamHalfLen) + ',0)');
    leftBucketG.append('path').attr('d', bucketOutlinePath()).style('fill', 'var(--card-bg)').style('stroke', housingColor).style('stroke-width', 1.4 / MECH_SCALE);
    bucketFillL = leftBucketG.append('rect').attr('x', -(bucketW * bucketTaper) / 2 + 0.5).attr('width', bucketW * bucketTaper - 1)
      .attr('y', bucketH).attr('height', 0).style('fill', WATER_COLOR);

    var rightBucketG = beamG.append('g').attr('transform', 'translate(' + beamHalfLen + ',0)');
    rightBucketG.append('path').attr('d', bucketOutlinePath()).style('fill', 'var(--card-bg)').style('stroke', housingColor).style('stroke-width', 1.4 / MECH_SCALE);
    bucketFillR = rightBucketG.append('rect').attr('x', -(bucketW * bucketTaper) / 2 + 0.5).attr('width', bucketW * bucketTaper - 1)
      .attr('y', bucketH).attr('height', 0).style('fill', WATER_COLOR);

    // Pivot dot drawn last so it sits on top of the beam visually.
    mechG.append('circle').attr('cx', cx).attr('cy', pivotY0).attr('r', 2.6 / MECH_SCALE).style('fill', housingColor);

    // ---- Collection tube — same construction as Piezo Rain's ----------
    // A short taper down from the drain's small opening to the tube's
    // full width, then straight walls to the floor. Drawn at the outer
    // (unscaled) level, directly below the mechanism group, so its line
    // weight matches Piezo Rain's 1.5px without any compensation.
    function tline(x1, y1, x2, y2){
      svg.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', housingColor).style('stroke-width', 1.5).style('stroke-linecap', 'round').style('fill', 'none');
    }
    tline(cx - tubeOpenHalfW, mechBottomY, cx - tubeHalfW, tubeTaperBottomY);
    tline(cx + tubeOpenHalfW, mechBottomY, cx + tubeHalfW, tubeTaperBottomY);
    tline(cx - tubeHalfW, tubeTaperBottomY, cx - tubeHalfW, tubeBottomY);
    tline(cx + tubeHalfW, tubeTaperBottomY, cx + tubeHalfW, tubeBottomY);
    tline(cx - tubeHalfW, tubeBottomY, cx + tubeHalfW, tubeBottomY);

    // Drops entering the funnel, the ambient pair beside the tube (same
    // convention as Piezo Rain/Rainfall's floating drops, only shown/
    // pulsing while it's actually raining), a group for the brief
    // dribble each time a bucket dumps, and the tube's fill+axis group
    // (rebuilt every refresh, since the reading changes).
    funnelDropsG = svg.append('g');
    ambientDropsG = svg.append('g');
    dripG = svg.append('g');
    tubeDynG = svg.append('g');

    // Hero value sits inside the funnel's vertical collar section (see
    // heroY, computed above alongside the collar/vertical recentring
    // maths) rather than floating above the whole image.
    svg.append('text').attr('class', 'tr-hero').attr('x', cx).attr('y', heroY)
      .style('text-anchor', 'middle').style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-weight', '600')
      .style('font-size', '11px').style('fill', 'var(--bw-accent)');
  }

  function setBeamAngle(deg, animated){
    var sel = beamG;
    if (animated) sel = sel.transition().duration(220).ease(d3.easeCubicInOut);
    sel.attr('transform', 'translate(' + cx + ',' + pivotY0 + ') rotate(' + deg + ')');
  }

  function updateBucketFill(frac){
    var h = Math.max(0, Math.min(1, frac)) * bucketH;
    var el = activeSide === 0 ? bucketFillL : bucketFillR;
    el.attr('y', bucketH - h).attr('height', h);
  }

  function spawnDrip(localX){
    // localX is the dumping bucket's x-offset from the pivot at rest tilt
    // — approximated at the drain rather than tracked through the beam's
    // own rotation, since the drop only needs to read as "coming from
    // about that side", not trace the exact bucket path. dripG lives at
    // the outer (unscaled) level, same as the tube below it, so this
    // drops from the mechanism's opening (mechBottomY) into the tube's
    // taper — not the mechanism's own local coordinates.
    var x = cx + (localX > 0 ? drainHalfW * MECH_SCALE * 1.4 : -drainHalfW * MECH_SCALE * 1.4);
    var d = dripG.append('circle').attr('cx', x).attr('cy', mechBottomY - 2).attr('r', 2.2)
      .style('fill', WATER_COLOR).style('opacity', 0.9);
    d.transition().duration(450).ease(d3.easeCubicIn)
      .attr('cy', tubeTaperBottomY + 4).style('opacity', 0)
      .on('end', function(){ d.remove(); });
  }

  function doTip(){
    var dumpingSide = activeSide;
    var toAngle = dumpingSide === 0 ? -tiltAngle : tiltAngle;
    setBeamAngle(toAngle, true);
    spawnDrip(dumpingSide === 0 ? -1 : 1);
    // The bucket that just dumped empties instantly (a real bucket empties
    // in the same snap the tip happens); the newly-lowered bucket starts
    // collecting from empty on the next animation frame.
    (dumpingSide === 0 ? bucketFillL : bucketFillR).attr('y', bucketH).attr('height', 0);
    activeSide = dumpingSide === 0 ? 1 : 0;
    updateBucketFill(0);
  }

  function computeMsPerTip(){
    var rate = (lastData && lastData.rate) || 0;
    if (rate <= 0) return null;
    // Not the literal physical tip interval — at a real 0.2mm bucket size,
    // even a respectable 5mm/hr only tips once every ~2.4 minutes, which
    // would just look static on a glanceable card. Instead this maps rate
    // onto a fixed, perceptible animation-speed range (9s at a trickle
    // down to 1.2s at/above a heavy 30mm/hr), still monotonic with rate —
    // heavier rain visibly tips faster, just compressed to be watchable
    // rather than physically literal.
    var SATURATION_RATE = 30, MAX_MS = 9000, MIN_MS = 1200;
    var frac = Math.min(rate / SATURATION_RATE, 1);
    return MAX_MS - (MAX_MS - MIN_MS) * Math.sqrt(frac);
  }

  function animFrame(now){
    rafId = null;
    if (tipping) return; // resumes itself once the flip's pause elapses (see doTip's caller below)
    var msPerTip = computeMsPerTip();
    if (msPerTip == null) return; // not raining — sit at rest with whatever partial fill it had
    if (fillStartTime == null) fillStartTime = now;
    var frac = (now - fillStartTime) / msPerTip;
    if (frac >= 1){
      tipping = true;
      doTip();
      setTimeout(function(){
        tipping = false;
        fillStartTime = null;
        ensureAnimating();
      }, 260); // matches setBeamAngle's 220ms transition plus a beat to let the tip read clearly
    } else {
      updateBucketFill(frac);
      rafId = requestAnimationFrame(animFrame);
    }
  }

  function ensureAnimating(){
    if (rafId == null && !tipping && computeMsPerTip() != null){
      rafId = requestAnimationFrame(animFrame);
    }
  }

  function renderCard(v){
    titleLabel.textContent = DivumWXI18N.t('Tipping Rain') + ' (' + (currentUnits.rain === 'in' ? 'in' : 'mm') + ')';

    if (!svg) buildStatic();

    var currentRainMm = v.day;
    var displayIsIn = currentUnits.rain === 'in';
    var currentDisplay = displayIsIn ? mm2in(currentRainMm) : currentRainMm;
    svg.select('.tr-hero').text(rainLabel(currentRainMm));

    // Funnel-entry drops, pulsing only while it's actually raining —
    // sized down a little from Piezo Rain/Rainfall's own drops to suit
    // the now-smaller funnel opening they're falling into. Positioned
    // relative to funnelExtTopY (the top of the new vertical extension)
    // rather than mechTopY, so they still read as falling in from above
    // the funnel's rim rather than appearing already inside it.
    funnelDropsG.selectAll('*').remove();
    if (v.rate > 0){
      raindrop(funnelDropsG, cx - 14, funnelExtTopY + 5, 0.65, true);
      raindrop(funnelDropsG, cx + 10, funnelExtTopY + 2, 0.55, true);
    }

    // Ambient pair beside the tube — same convention and position as
    // Piezo Rain's own two floating drops.
    ambientDropsG.selectAll('*').remove();
    raindrop(ambientDropsG, cx + tubeHalfW + 20, 112, 1.15, v.rate > 0);
    raindrop(ambientDropsG, cx + tubeHalfW + 29, 122, 0.85, v.rate > 0);

    // ---- Collection tube fill + scale, rebuilt every refresh — same
    // domain/threshold logic as Piezo Rain, so the two gauges' tubes
    // read the same amount of rain at the same fill height. ----
    tubeDynG.selectAll('*').remove();

    var currentRainMmForThreshold = (currentUnits.rain === 'mm') ? currentRainMm : currentRainMm * 25.4;
    var threshold = THRESHOLDS.find(function(t){ return currentRainMmForThreshold < t.limit; });
    var baseValue = threshold ? threshold.val : 23;
    var stepping = (currentUnits.rain === 'mm') ? baseValue : baseValue / 25.4;

    var domain = [0, stepping * Math.ceil(currentDisplay / stepping)];
    if (domain[1] - currentDisplay < 0.66 * stepping) domain[1] += stepping;

    var tubeScale = d3.scaleLinear()
      .range([tubeBottomY - 2, tubeTaperBottomY + 3])
      .domain(domain);

    var fillTop = tubeScale(currentDisplay);

    // The fill and both its menisci only draw once there's actually some
    // rain to show — at 0mm the tube reads as genuinely empty rather
    // than showing a thin sliver of water with nothing behind it.
    if (currentRainMm > 0) {
      tubeDynG.append('rect')
        .attr('x', cx - tubeFillHalfW).attr('y', fillTop - 2)
        // The fill's bottom edge is fixed (the fillTop terms cancel out
        // of y+height), by the same construction Piezo Rain's own tube
        // fill uses — only the top moves with the reading. What matters
        // is landing that fixed bottom close to the floor line so the
        // meniscus below has real water to cut into: Piezo Rain's own
        // math lands 1 unit short of its floor; this matches that
        // exactly (tubeBottomY+1-fillTop, so bottom = tubeBottomY-1).
        .attr('width', tubeFillHalfW * 2).attr('height', Math.max(0, tubeBottomY + 1 - fillTop))
        .style('fill', WATER_COLOR);

      // Water's surface — same construction as Piezo Rain's own: a
      // rounded card-bg rect notched into the top of the fill, reading
      // as the water's curved top edge rather than a flat cut-off. Its
      // own bottom edge sits 2 units inside the fill's straight sides,
      // never at one of the fill's corners, so it always nests cleanly.
      tubeDynG.append('rect')
        .attr('x', cx - tubeFillHalfW).attr('y', fillTop - 4)
        .attr('rx', 3).attr('width', tubeFillHalfW * 2).attr('height', 4)
        .style('fill', 'var(--card-bg)');

      // No bottom meniscus here, deliberately — unlike the top surface,
      // where a curved highlight reads as water meeting the glass, the
      // bottom of the fill rests against a flat floor with nothing to
      // curve against. A matching card-bg notch was tried here previously
      // but just painted a dark gap across the bottom of the water,
      // making it look like the level stopped short of the floor instead
      // of sitting flush on it. The fill rect above already lands its
      // fixed bottom edge 1 unit short of tubeBottomY (matching Piezo
      // Rain's own tube fill construction) — that's the correct visual
      // floor contact, and needs nothing drawn on top of it.
    }

    var tickValues = d3.range((domain[1] - domain[0]) / stepping + 1)
      .map(function(n){ return domain[0] + n * stepping; });

    var svgAxis = tubeDynG.append('g').attr('class', 'RainScale')
      .attr('transform', 'translate(' + (cx - tubeHalfW - 12) + ', 0)')
      .call(d3.axisLeft(tubeScale).tickSize(7).tickValues(tickValues));

    svgAxis.selectAll('.tick text')
      .style('fill', overlayTextColor).style('font-family', 'inherit').style('font-size', '8px');
    svgAxis.select('path').style('stroke', 'none').style('fill', 'none');
    svgAxis.selectAll('.tick line')
      .style('stroke', housingColor).style('stroke-linecap', 'round').style('stroke-width', 2);

    ensureAnimating();

    // ---- Right pane: 6 readouts as label/value chip rows ----
    monthText.textContent = rainLabel(v.month);
    hourText.textContent = rainLabel(v.hour);
    last24hText.textContent = rainLabel(v.last24h);
    rateText.textContent = rainLabel(v.rate) + '/hr';
    yearText.textContent = rainLabel(v.year);
    var stormLabel = timeLabelFor(v.stormStart);
    eventText.textContent = v.event > 0
      ? rainLabel(v.event) + (stormLabel ? ' (since ' + stormLabel + ')' : '')
      : '\u2014';
  }

  var lastData = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardTippingRain: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardTippingRain: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var rain = arch.rain || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        day: num(rain.day, 0),
        hour: num(rain.hour, 0),
        last24h: num(rain.last24h, 0),
        month: num(rain.month, 0),
        year: num(rain.year, 0),
        rate: num(rain.rate, 0),
        event: num(rain.event, 0),
        stormStart: typeof rain.storm_start === 'number' ? rain.storm_start : 0
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardTippingRain: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardTippingRain.js failed:", e);
}

/* ===== cardSolarRadiation.js ===== */
try {
/*
##############################################################################################
# cardSolarRadiation.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardSolarRadiation.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var ASTRO_JSON_URL   = './jsondata/almanac.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }

  var mount = document.getElementById('solarCard10');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Solar Radiation');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gauge + hero value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var dayMaxText = addChipRow('Max');
  var alltimeMaxText = addChipRow('All-Time Max');
  var yesterdayMaxText = addChipRow('Yesterday Max');
  var monthMaxText = addChipRow('Month Max');
  var sunshineText = addChipRow('Sunshine Today');
  var luxText = addChipRow('Illuminance');
  luxText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  // Solar Radiation shares a chart page with UV Index (data-modal keeps
  // the tooltip saying "Solar Radiation" even though the page covers
  // both).
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=solaruv&embed=1';
  cardLink.setAttribute('data-modal', 'Solar Radiation');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Solar & UV Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=solaruv&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175, cx = 90, cy = 81, R = 54;
  var ICON_BASE = './meteocons/fill/svg/';
  var ARC_BANDS = [
    [0, 100, '#808080'], [100, 300, '#6abc62'], [300, 600, '#f8d747'],
    [600, 900, '#f36633'], [900, 1200, '#ff0000'], [1200, 1500, '#b8125f'], [1500, 1800, '#ff00ff']
  ];
  var GAUGE_DOMAIN = 1800, TICK_COUNT = 12;

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    var colorScale = d3.scaleLinear().domain([0, GAUGE_DOMAIN]).range([-135, 135]);
    var arcScale = d3.scaleLinear().domain([0, GAUGE_DOMAIN]).range([-135, 135]).clamp(true);

    var bgArc = d3.arc().innerRadius(R - 5).outerRadius(R);
    svg.selectAll('.s-bg-arc')
      .data(ARC_BANDS).join('path')
      .attr('class', 's-bg-arc')
      .attr('transform', 'translate(' + cx + ',' + cy + ')')
      .attr('d', function(d){ return bgArc.startAngle(colorScale(d[0]) * Math.PI / 180).endAngle(colorScale(d[1]) * Math.PI / 180)(); })
      .style('fill', function(d){ return d[2]; });

    var tickG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ')');

    var MINOR_PER_INTERVAL = 4;
    for (var mi = 0; mi < TICK_COUNT - 1; mi++){
      var mvStart = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * mi;
      var mvEnd = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * (mi + 1);
      for (var mj = 1; mj <= MINOR_PER_INTERVAL; mj++){
        var mtv = mvStart + (mvEnd - mvStart) * (mj / (MINOR_PER_INTERVAL + 1));
        var mang = (arcScale(mtv) - 90) * Math.PI / 180;
        var mx1 = Math.cos(mang) * (R + 3), my1 = Math.sin(mang) * (R + 3);
        var mx2 = Math.cos(mang) * (R + 5), my2 = Math.sin(mang) * (R + 5);
        tickG.append('line').attr('x1', mx1).attr('y1', my1).attr('x2', mx2).attr('y2', my2)
          .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 0.5).style('opacity', 0.55);
      }
    }

    for (var i = 0; i < TICK_COUNT; i++){
      var tv = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * i;
      var ang = (arcScale(tv) - 90) * Math.PI / 180;
      var x1 = Math.cos(ang) * (R + 3), y1 = Math.sin(ang) * (R + 3);
      var x2 = Math.cos(ang) * (R + 7), y2 = Math.sin(ang) * (R + 7);
      var xt = Math.cos(ang) * (R + 11), yt = Math.sin(ang) * (R + 11);
      tickG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 1);
      tickG.append('text').attr('x', xt).attr('y', yt).attr('dy', '0.32em')
        .style('text-anchor', 'middle').style('font-size', '6px').style('fill', overlayTextColor)
        .text(Math.round(tv));
    }

    var needleAngle = arcScale(v.current);
    var needleG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + needleAngle + ')');
    needleG.append('polygon').attr('points', '0,' + (-(R - 6)) + ' 2.2,10 -2.2,10').style('fill', 'red');
    svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', 4).style('fill', 'red');
    var iconSize = 24.3; // 18 * 1.35
    svg.append('image')
      .attr('xlink:href', ICON_BASE + (v.isDay ? 'clear-day' : 'clear-night') + '.svg')
      .attr('x', cx - iconSize / 2).attr('y', cy + R - iconSize / 2)
      .attr('width', iconSize).attr('height', iconSize);

    // Hero value — same accent colour + mono font as Current Conditions.
    svg.append('text').attr('x', cx).attr('y', H - 16).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(Math.round(v.current) + ' W/m\u00B2');

    // ---- Right pane: 6 readouts as label/value chip rows ----
    dayMaxText.textContent = Math.round(v.dayMax) + ' W/m\u00B2 (' + timeLabelFor(v.dayMaxTime) + ')';
    alltimeMaxText.textContent = Math.round(v.alltimeMax) + ' W/m\u00B2';
    yesterdayMaxText.textContent = Math.round(v.yesterdayMax) + ' W/m\u00B2';
    monthMaxText.textContent = Math.round(v.monthMax) + ' W/m\u00B2';
    var sunHrs = Math.floor(v.sunMinutes / 60), sunMins = Math.round(v.sunMinutes % 60);
    sunshineText.textContent = sunHrs + 'h ' + sunMins + 'm';
    luxText.textContent = Math.round(v.lux).toLocaleString() + ' lux';
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1], astroResult = results[2];
      if (loopResult.status === 'rejected') console.warn('cardSolarRadiation: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardSolarRadiation: archive.json fetch failed —', archResult.reason.message);
      if (astroResult.status === 'rejected') console.warn('cardSolarRadiation: almanac.json fetch failed —', astroResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};
      var o = loop.observations || {};
      var solar = arch.solar || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        // loop.json's own radiation reading is the live, instantaneous
        // value (correctly 0 after dark); archive.json's solar.current
        // is only a periodic snapshot (as stale as the last archive
        // interval) and used here only as a fallback if loop.json
        // itself is unavailable -- previously this was backwards, so
        // solar.current's stale non-zero reading always won even once
        // loop.json had already dropped to 0 after sunset.
        current: num(o.radiation, num(solar.current, 0)),
        dayMax: num(solar.day_max, 0),
        dayMaxTime: num(solar.day_maxtime, 0),
        yesterdayMax: num(solar.yesterday_max, 0),
        monthMax: num(solar.month_max, 0),
        alltimeMax: num(solar.alltime_max, 0),
        sunMinutes: num(solar.sun_duration_minutes, 0),
        lux: num(solar.lux, num(o.illuminance, 0)),
        // Primary source: almanac.json's actual sun altitude -- "day" is
        // precisely "between sunrise and sunset" by definition (sun
        // above the horizon). Falls back to loop.json's own
        // observations.isDay only if almanac.json's fetch failed.
        isDay: (typeof alm['almanac.sun.alt'] === 'number' && !isNaN(alm['almanac.sun.alt']))
          ? (alm['almanac.sun.alt'] > 0) : (o.isDay === 1),
        currentColor: o.radiationColor || 'var(--bw-accent)'
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardSolarRadiation: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardSolarRadiation.js failed:", e);
}

/* ===== cardUvIndex.js ===== */
try {
/*
##############################################################################################
# cardUvIndex.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardUvIndex.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var ASTRO_JSON_URL   = './jsondata/almanac.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }

  var mount = document.getElementById('uvCard11');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'UV-Index');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gauge + hero value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var dayMaxText = addChipRow('Max');
  var riskText = addChipRow('Risk');
  var yesterdayMaxText = addChipRow('Yesterday Max');
  var monthMaxText = addChipRow('Month Max');
  var yearMaxText = addChipRow('Year Max');
  var alltimeMaxText = addChipRow('All-Time Max');
  alltimeMaxText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  // UV Index shares a chart page with Solar Radiation (data-modal keeps
  // the tooltip saying "UV Index" even though the page covers both).
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=solaruv&embed=1';
  cardLink.setAttribute('data-modal', 'UV Index');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Solar & UV Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=solaruv&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175, cx = 90, cy = 81, R = 54;
  var ICON_BASE = './meteocons/fill/svg/';



  function uvIconName(uv){
    var n = Math.max(1, Math.min(11, Math.round(uv)));
    return 'uv-index-' + n;
  }
  var ARC_BANDS = [
    [0, 1, '#808080'], [1, 3, '#6abc62'], [3, 6, '#f8d747'],
    [6, 9, '#f36633'], [9, 12, '#ff0000'], [12, 15, '#b8125f'], [15, 18, '#ff00ff']
  ];
  var GAUGE_DOMAIN = 18, TICK_COUNT = 10;

  function riskLabel(uvNow, isDay){
    if (uvNow >= 10) return DivumWXI18N.t('Extreme');
    if (uvNow >= 8)  return DivumWXI18N.t('Very High');
    if (uvNow >= 6)  return DivumWXI18N.t('High');
    if (uvNow >= 3)  return DivumWXI18N.t('Moderate');
    if (!isDay)      return DivumWXI18N.t('Below Horizon');
    return DivumWXI18N.t('Low');
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    var colorScale = d3.scaleLinear().domain([0, GAUGE_DOMAIN]).range([-135, 135]);
    var arcScale = d3.scaleLinear().domain([0, GAUGE_DOMAIN]).range([-135, 135]).clamp(true);

    var bgArc = d3.arc().innerRadius(R - 5).outerRadius(R);
    svg.selectAll('.uv-bg-arc')
      .data(ARC_BANDS).join('path')
      .attr('class', 'uv-bg-arc')
      .attr('transform', 'translate(' + cx + ',' + cy + ')')
      .attr('d', function(d){ return bgArc.startAngle(colorScale(d[0]) * Math.PI / 180).endAngle(colorScale(d[1]) * Math.PI / 180)(); })
      .style('fill', function(d){ return d[2]; });

    var tickG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ')');

    var MINOR_PER_INTERVAL = 4;
    for (var mi = 0; mi < TICK_COUNT - 1; mi++){
      var mvStart = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * mi;
      var mvEnd = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * (mi + 1);
      for (var mj = 1; mj <= MINOR_PER_INTERVAL; mj++){
        var mtv = mvStart + (mvEnd - mvStart) * (mj / (MINOR_PER_INTERVAL + 1));
        var mang = (arcScale(mtv) - 90) * Math.PI / 180;
        var mx1 = Math.cos(mang) * (R + 3), my1 = Math.sin(mang) * (R + 3);
        var mx2 = Math.cos(mang) * (R + 5), my2 = Math.sin(mang) * (R + 5);
        tickG.append('line').attr('x1', mx1).attr('y1', my1).attr('x2', mx2).attr('y2', my2)
          .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 0.5).style('opacity', 0.55);
      }
    }

    for (var i = 0; i < TICK_COUNT; i++){
      var tv = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * i;
      var ang = (arcScale(tv) - 90) * Math.PI / 180;
      var x1 = Math.cos(ang) * (R + 3), y1 = Math.sin(ang) * (R + 3);
      var x2 = Math.cos(ang) * (R + 7), y2 = Math.sin(ang) * (R + 7);
      var xt = Math.cos(ang) * (R + 11), yt = Math.sin(ang) * (R + 11);
      tickG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 1);
      tickG.append('text').attr('x', xt).attr('y', yt).attr('dy', '0.32em')
        .style('text-anchor', 'middle').style('font-size', '6px').style('fill', overlayTextColor)
        .text(Math.round(tv));
    }

    var needleAngle = arcScale(v.current);
    var needleG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + needleAngle + ')');
    needleG.append('polygon').attr('points', '0,' + (-(R - 6)) + ' 2.2,10 -2.2,10').style('fill', 'red');
    svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', 4).style('fill', 'red');
    var iconSize = 24.3; // 18 * 1.35
    svg.append('image')
      .attr('xlink:href', ICON_BASE + uvIconName(v.current) + '.svg')
      .attr('x', cx - iconSize / 2).attr('y', cy + R - iconSize / 2)
      .attr('width', iconSize).attr('height', iconSize);

    // Hero value — same accent colour + mono font as Current Conditions.
    svg.append('text').attr('x', cx).attr('y', H - 16).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text('UV-I ' + v.current.toFixed(0));

    // ---- Right pane: 6 readouts as label/value chip rows ----
    dayMaxText.textContent = 'UV-I ' + v.dayMax.toFixed(0) + ' (' + timeLabelFor(v.dayMaxTime) + ')';
    riskText.textContent = riskLabel(v.current, v.isDay);
    yesterdayMaxText.textContent = 'UV-I ' + v.yesterdayMax.toFixed(0);
    monthMaxText.textContent = 'UV-I ' + v.monthMax.toFixed(0);
    yearMaxText.textContent = 'UV-I ' + v.yearMax.toFixed(0);
    alltimeMaxText.textContent = 'UV-I ' + v.alltimeMax.toFixed(0);
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1], astroResult = results[2];
      if (loopResult.status === 'rejected') console.warn('cardUvIndex: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardUvIndex: archive.json fetch failed —', archResult.reason.message);
      if (astroResult.status === 'rejected') console.warn('cardUvIndex: almanac.json fetch failed —', astroResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};
      var o = loop.observations || {};
      var uv = arch.uv || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        // Same fix as cardSolarRadiation's identical bug just above in
        // this file -- loop.json's own UV reading is live and correctly
        // 0 after dark; archive.json's uv.current is only a periodic
        // snapshot, used here only as a fallback.
        current: num(o.UV, num(uv.current, 0)),
        dayMax: num(uv.day_max, 0),
        dayMaxTime: num(uv.day_maxtime, 0),
        yesterdayMax: num(uv.yesterday_max, 0),
        monthMax: num(uv.month_max, 0),
        yearMax: num(uv.year_max, 0),
        alltimeMax: num(uv.alltime_max, 0),
        // Primary source: almanac.json's actual sun altitude -- "day" is
        // precisely "between sunrise and sunset" by definition (sun
        // above the horizon). Falls back to loop.json's own
        // observations.isDay only if almanac.json's fetch failed.
        isDay: (typeof alm['almanac.sun.alt'] === 'number' && !isNaN(alm['almanac.sun.alt']))
          ? (alm['almanac.sun.alt'] > 0) : (o.isDay === 1),
        currentColor: o.uvColor || 'var(--bw-accent)'
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardUvIndex: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardUvIndex.js failed:", e);
}

/* ===== cardHumidity.js ===== */
try {
/*
##############################################################################################
# cardHumidity.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardHumidity.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function timeLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return p.hour + ':' + p.minute;
  }

  var mount = document.getElementById('humidityCard12');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Humidity');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gauge + hero value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var dayMaxText = addChipRow('Max');
  var zoneText = addChipRow('Zone');
  var vpdText = addChipRow('Vapour P D');
  var evapoTText = addChipRow('Evapo T');
  var trendText = addChipRow('Trend');
  var dayMinText = addChipRow('Min');
  dayMinText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  // Humidity has no chart page of its own — it's charted alongside
  // temperature, so this points there (data-modal keeps the tooltip
  // saying "Humidity" even though the page itself is titled Temperature).
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=temperature&embed=1';
  cardLink.setAttribute('data-modal', 'Humidity');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Humidity & Temperature Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=temperature&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175, cx = 90, cy = 81, R = 54;
  var ICON_BASE = './meteocons/fill/svg/';
  var ARC_BANDS = [
    [0, 30, '#ff6347'], [30, 70, '#2e8b57'], [70, 100, '#007fff']
  ];
  var GAUGE_DOMAIN = 100, TICK_COUNT = 11;

  function comfortZone(h){
    if (h < 30) return DivumWXI18N.t('Very Dry');
    if (h < 70) return DivumWXI18N.t('Comfortable');
    return DivumWXI18N.t('Very Humid');
  }
  function trendInfo(t){
    if (t > 0.5) return { label: DivumWXI18N.t('Rising'), arrow: '\u279a' };
    if (t < -0.5) return { label: DivumWXI18N.t('Falling'), arrow: '\u2798' };
    return { label: DivumWXI18N.t('Steady'), arrow: '\u2799' };
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    if (svg.empty()){
      svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    }
    svg.selectAll('*').remove();

    var colorScale = d3.scaleLinear().domain([0, GAUGE_DOMAIN]).range([-135, 135]);
    var arcScale = d3.scaleLinear().domain([0, GAUGE_DOMAIN]).range([-135, 135]).clamp(true);

    var bgArc = d3.arc().innerRadius(R - 5).outerRadius(R);
    svg.selectAll('.h-bg-arc')
      .data(ARC_BANDS).join('path')
      .attr('class', 'h-bg-arc')
      .attr('transform', 'translate(' + cx + ',' + cy + ')')
      .attr('d', function(d){ return bgArc.startAngle(colorScale(d[0]) * Math.PI / 180).endAngle(colorScale(d[1]) * Math.PI / 180)(); })
      .style('fill', function(d){ return d[2]; });

    var textArc = d3.arc().innerRadius(R - 4.25).outerRadius(R - 4.25)
      .startAngle(-135 * Math.PI / 180).endAngle(135 * Math.PI / 180);
    svg.append('path').attr('id', 'humidityLabelPath')
      .attr('transform', 'translate(' + cx + ',' + cy + ')')
      .attr('d', textArc()).style('fill', 'none').style('stroke', 'none');
    var zoneLabels = [
      { name: DivumWXI18N.t('VERY DRY'), offset: '4.5%', anchor: 'start' },
      { name: DivumWXI18N.t('COMFORTABLE'), offset: '25%', anchor: 'middle' },
      { name: DivumWXI18N.t('VERY HUMID'), offset: '46%', anchor: 'end' }
    ];
    var labelContainer = svg.append('g')
      .style('font-family', 'inherit').style('font-weight', '700').style('font-size', '5px').style('fill', '#1c4263');
    zoneLabels.forEach(function(d){
      labelContainer.append('text').append('textPath')
        .attr('xlink:href', '#humidityLabelPath').attr('startOffset', d.offset)
        .style('text-anchor', d.anchor).text(d.name);
    });

    var tickG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ')');

    var MINOR_PER_INTERVAL = 4;
    for (var mi = 0; mi < TICK_COUNT - 1; mi++){
      var mvStart = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * mi;
      var mvEnd = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * (mi + 1);
      for (var mj = 1; mj <= MINOR_PER_INTERVAL; mj++){
        var mtv = mvStart + (mvEnd - mvStart) * (mj / (MINOR_PER_INTERVAL + 1));
        var mang = (arcScale(mtv) - 90) * Math.PI / 180;
        var mx1 = Math.cos(mang) * (R + 3), my1 = Math.sin(mang) * (R + 3);
        var mx2 = Math.cos(mang) * (R + 5), my2 = Math.sin(mang) * (R + 5);
        tickG.append('line').attr('x1', mx1).attr('y1', my1).attr('x2', mx2).attr('y2', my2)
          .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 0.5).style('opacity', 0.55);
      }
    }

    for (var i = 0; i < TICK_COUNT; i++){
      var tv = (GAUGE_DOMAIN / (TICK_COUNT - 1)) * i;
      var ang = (arcScale(tv) - 90) * Math.PI / 180;
      var x1 = Math.cos(ang) * (R + 3), y1 = Math.sin(ang) * (R + 3);
      var x2 = Math.cos(ang) * (R + 7), y2 = Math.sin(ang) * (R + 7);
      var xt = Math.cos(ang) * (R + 11), yt = Math.sin(ang) * (R + 11);
      tickG.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
        .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 1);
      tickG.append('text').attr('x', xt).attr('y', yt).attr('dy', '0.32em')
        .style('text-anchor', 'middle').style('font-size', '6px').style('fill', overlayTextColor)
        .text(Math.round(tv));
    }

    var needleAngle = arcScale(v.current);
    var needleG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ') rotate(' + needleAngle + ')');
    needleG.append('polygon').attr('points', '0,' + (-(R - 6)) + ' 2.2,10 -2.2,10').style('fill', 'red');
    svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', 4).style('fill', 'red');
    var iconSize = 24.3; // 18 * 1.35
    svg.append('image')
      .attr('xlink:href', ICON_BASE + 'humidity.svg')
      .attr('x', cx - iconSize / 2).attr('y', cy + R - iconSize / 2)
      .attr('width', iconSize).attr('height', iconSize);

    // Hero value — same accent colour + mono font as Current Conditions.
    svg.append('text').attr('x', cx).attr('y', H - 16).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(Math.round(v.current) + ' %');

    // ---- Right pane: 6 readouts as label/value chip rows ----
    dayMaxText.textContent = Math.round(v.dayMax) + ' % (' + timeLabelFor(v.dayMaxTime) + ')';
    zoneText.textContent = comfortZone(v.current);
    vpdText.textContent = v.vpd.toFixed(2) + ' kPa';
    evapoTText.textContent = v.evapoT.toFixed(2) + ' mm';
    var trend = trendInfo(v.trend);
    trendText.textContent = trend.arrow + ' ' + trend.label;
    dayMinText.textContent = Math.round(v.dayMin) + ' % (' + timeLabelFor(v.dayMinTime) + ')';
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1];
      if (loopResult.status === 'rejected') console.warn('cardHumidity: loop.json fetch failed —', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardHumidity: archive.json fetch failed —', archResult.reason.message);

      var loop = loopResult.status === 'fulfilled' ? loopResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var o = loop.observations || {};
      var humid = arch.humid || {};
      var vpdObj = arch.vpd || {};
      var etObj = arch.et || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      lastData = {
        // Same live-vs-archive priority fix as cardSolarRadiation/
        // cardUvIndex elsewhere in this file.
        current: num(o.outHumidity, num(humid.current, 0)),
        dayMax: num(humid.day_max, 0),
        dayMaxTime: num(humid.day_maxtime, 0),
        dayMin: num(humid.day_min, 0),
        dayMinTime: num(humid.day_mintime, 0),
        trend: num(humid.trend, 0),
        vpd: num(o.vpd, num(vpdObj.current, 0)),
        evapoT: num(etObj.day, 0)
      };
      renderCard(lastData);
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardHumidity: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardHumidity.js failed:", e);
}

/* ===== cardEarthDaylight.js ===== */
try {
/*
##############################################################################################
# cardEarthDaylight.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardEarthDaylight.js =====================
(function(){
  var ASTRO_JSON_URL   = './jsondata/almanac.json';
  var OVATION_URL      = './jsondata/ovation.txt';
  var WORLDMAP_JSON_URL = './jsondata/worldmap.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 60 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }

  function toDegrees(x){ return x * (180 / Math.PI); }
  function toRadians(x){ return x * (Math.PI / 180); }
  function meanObliquityOfEcliptic(T){
    return toRadians(23 + (26 + (21.448 - T * (46.815 + T * (0.00059 - T * 0.001813))) / 60) / 60);
  }
  function eccentricityEarthOrbit(T){ return 0.016708634 - T * (0.000042037 + 0.0000001267 * T); }
  function solarGeometricMeanAnomaly(T){ return toRadians(357.52911 + T * (35999.05029 - 0.0001537 * T)); }
  function solarGeometricMeanLongitude(T){
    var l = (280.46646 + T * (36000.76983 + T * 0.0003032)) % 360;
    return (l < 0 ? l + 360 : l) * Math.PI / 180;
  }
  function solarEquationOfCenter(T){
    var m = solarGeometricMeanAnomaly(T);
    return toRadians(
      Math.sin(m) * (1.914602 - T * (0.004817 + 0.000014 * T)) +
      Math.sin(2 * m) * (0.019993 - 0.000101 * T) +
      Math.sin(3 * m) * 0.000289
    );
  }
  function obliquityCorrection(T){
    return meanObliquityOfEcliptic(T) + toRadians(0.00256 * Math.cos(toRadians(125.04 - 1934.136 * T)));
  }
  function solarTrueLongitude(T){ return solarGeometricMeanLongitude(T) + solarEquationOfCenter(T); }
  function solarApparentLongitude(T){
    return solarTrueLongitude(T) - toRadians(0.00569 + 0.00478 * Math.sin(toRadians(125.04 - 1934.136 * T)));
  }
  function solarDeclination(T){
    return Math.asin(Math.sin(obliquityCorrection(T)) * Math.sin(solarApparentLongitude(T)));
  }
  function equationOfTime(T){
    var e = eccentricityEarthOrbit(T), m = solarGeometricMeanAnomaly(T), l = solarGeometricMeanLongitude(T);
    var y = Math.tan(obliquityCorrection(T) / 2); y *= y;
    return y * Math.sin(2 * l) - 2 * e * Math.sin(m) + 4 * e * y * Math.sin(m) * Math.cos(2 * l)
         - 0.5 * y * y * Math.sin(4 * l) - 1.25 * e * e * Math.sin(2 * m);
  }
  function solarPosition(time){
    var T = (time - Date.UTC(2000, 0, 1, 12)) / 864e5 / 36525;
    var dayStart = Math.floor(time / 864e5) * 864e5;
    var longitude = (dayStart - time) / 864e5 * 360 - 180;
    return [longitude - toDegrees(equationOfTime(T)), toDegrees(solarDeclination(T))];
  }
  function antipode(pos){ return [pos[0] + 180, -pos[1]]; }



  function almDateTimeFromEpoch(ts){
    // almanac.json now gives raw unix_epoch timestamps rather than a
    // pre-formatted string (the old string format this used to parse,
    // "DD/MM/YY HH:MM", didn't actually match what the server produced
    // anyway -- "DD-Mon-YYYY HH:MM" -- so this also fixes a pre-existing
    // latent formatting mismatch as a side effect).
    if (ts == null) return { date: '\u2014', time: '' };
    var dOpts = { day: '2-digit', month: '2-digit', year: 'numeric' };
    var tOpts = { hour: '2-digit', minute: '2-digit', hour12: false };
    try {
      if (window.StationTime) { dOpts.timeZone = window.StationTime.getTZ(); tOpts.timeZone = window.StationTime.getTZ(); }
    } catch (e) {}
    var d = new Date(ts * 1000);
    var parts = {};
    new Intl.DateTimeFormat('en-GB', dOpts).formatToParts(d).forEach(function(p){ parts[p.type] = p.value; });
    return { date: parts.day + '.' + parts.month + '.' + parts.year, time: d.toLocaleTimeString(undefined, tOpts) };
  }

  var mount = document.getElementById('earthCard13');
  if (!mount || !window.d3 || !window.d3.geoOrthographic || !window.topojson) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Earth Daylight');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: globe, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // 8 items in a fixed-row-height list, same idiom as Current Conditions'
  // 8-row layout.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '20px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var distanceText = addChipRow('Sun Distance');
  var auroraText = addChipRow('Aurora Activity');
  var equinoxText = addChipRow('Next Equinox');
  var solsticeText = addChipRow('Next Solstice');
  var sunDecText = addChipRow('Sun Dec \u03B4');
  var eclipticText = addChipRow("Earth's Ecliptic Angle");
  var sunRaText = addChipRow('Sun Ra \u03BB');
  sunRaText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the world daylight map — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'modalDaylightMap.html';
  cardLink.setAttribute('data-modal', 'World Daylight Map');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'World Daylight Map');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1300px');
  cardLink.setAttribute('data-modal-height', '780px');
  cardLink.setAttribute('data-url', 'modalDaylightMap.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175, cx = 90, cy = 87, R = 65;

  var SUN_SCALE_RATIO = 61 / 50, MOON_SCALE_RATIO = 53.5 / 50;

  var worldData = null;
  function loadWorldMap(){
    return fetch(WORLDMAP_JSON_URL + ((WORLDMAP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(json){ worldData = json; })
      .catch(function(e){ console.warn('cardEarthDaylight: worldmap.json fetch failed —', e.message); });
  }

  function auroraProbabilityAt(ovationCoords, lat, lon){
    if (!ovationCoords) return null;
    var lonIdx = Math.round(((lon % 360) + 360) % 360);
    var latIdx = Math.round(Math.max(-90, Math.min(90, lat)));
    var idx = lonIdx * 181 + (latIdx + 90);
    var entry = ovationCoords[idx];
    return entry ? entry[2] : null;
  }



  function parallelAt(latDeg){
    var coords = [];
    for (var lon = -180; lon <= 180; lon += 5) coords.push([lon, latDeg]);
    return { type: 'LineString', coordinates: coords };
  }
  function meridianAt(lonDeg){
    var coords = [];
    for (var lat = -90; lat <= 90; lat += 5) coords.push([lonDeg, lat]);
    return { type: 'LineString', coordinates: coords };
  }

  function renderCard(v, lat, lon){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    var defs = svg.append('defs');

    var now = stationNow();
    var eclipticDeg = v.eclipticAngle;

    var sunPos = solarPosition(now.getTime());


    var moonPos = [sunPos[0] + v.moonEclipticAngle, v.moonDec];
    var antiSunPos = antipode(sunPos);

    var rotSign = (lat > 0) ? -1 : 1;
    var rotBase = [-lon, rotSign * eclipticDeg, -eclipticDeg];

    var projection = d3.geoOrthographic().scale(R).translate([cx, cy]).rotate(rotBase).clipAngle(90).precision(0.3);
    var path = d3.geoPath().projection(projection);
    var graticule = d3.geoGraticule();


    var sunProjection  = d3.geoOrthographic().scale(R * SUN_SCALE_RATIO).translate([cx, cy]).rotate(rotBase).precision(0.3);
    var moonProjection = d3.geoOrthographic().scale(R * MOON_SCALE_RATIO).translate([cx, cy]).rotate(rotBase).precision(0.3);
    var sunScale  = d3.scaleLinear().domain([0, 1]).range([1.5 * (R / 50), 9 * (R / 50)]);
    var moonScale = d3.scaleLinear().domain([0, 1]).range([0.6 * (R / 50), 3.5 * (R / 50)]);

    function degreesFromCenter(pos, proj){
      return toDegrees(d3.geoDistance(pos, (proj || projection).invert([cx, cy])));
    }


    var sunXYForGrad = projection(sunPos) || [cx, cy];
    function dayNightGradient(id, light, dark){
      var g = defs.append('radialGradient').attr('id', id).attr('gradientUnits', 'userSpaceOnUse')
        .attr('cx', sunXYForGrad[0]).attr('cy', sunXYForGrad[1]).attr('r', R * 2.2);
      g.append('stop').attr('offset', '0%').attr('stop-color', light);
      g.append('stop').attr('offset', '100%').attr('stop-color', dark);
      return g;
    }
    dayNightGradient('oceanGradient', '#d4e2ff', '#5b7fc7');
    dayNightGradient('landGradient', '#5fd18f', '#1f5c3d');
    var sunGrad = defs.append('radialGradient').attr('id', 'sunGradientMini').attr('cx', '35%').attr('cy', '30%');
    sunGrad.append('stop').attr('offset', '5%').attr('stop-color', '#ffcfc7');
    sunGrad.append('stop').attr('offset', '150%').attr('stop-color', '#ff6347');
    var moonGrad = defs.append('radialGradient').attr('id', 'moonGradientMini').attr('cx', '35%').attr('cy', '30%');
    moonGrad.append('stop').attr('offset', '5%').attr('stop-color', '#f5f5f5');
    moonGrad.append('stop').attr('offset', '150%').attr('stop-color', '#999999');

    var globeG = svg.append('g');
    globeG.append('path').datum({ type: 'Sphere' }).attr('d', path).style('fill', 'url(#oceanGradient)');

    if (worldData && worldData.objects && worldData.objects.land){
      globeG.append('path').datum(topojson.feature(worldData, worldData.objects.land)).attr('d', path)
        .style('fill', 'url(#landGradient)').style('stroke', 'none');
    }
    if (worldData && worldData.objects && worldData.objects.countries){
      globeG.append('path').datum(topojson.mesh(worldData, worldData.objects.countries, function(a, b){ return a !== b; })).attr('d', path)
        .style('fill', 'none').style('stroke', '#1c3d29').style('stroke-width', 0.1);
    }

    globeG.append('path').datum(graticule).attr('d', path).style('fill', 'none').style('stroke', '#888').style('stroke-width', 0.2).style('stroke-opacity', 0.5);


    function refLine(datum, stroke, dash){
      var p = globeG.append('path').datum(datum).attr('d', path)
        .style('fill', 'none').style('stroke', stroke).style('stroke-width', 0.6);
      if (dash) p.style('stroke-dasharray', dash);
    }
    refLine(parallelAt(0), '#ff4444');
    refLine(meridianAt(0), '#ff4444');
    refLine(meridianAt(180), '#ff4444');
    refLine(parallelAt(23.436556), '#3b82f6', '2,2');
    refLine(parallelAt(-23.436556), '#3b82f6', '2,2');
    refLine(parallelAt(66.563444), '#e64bd6', '2,2');
    refLine(parallelAt(-66.563444), '#e64bd6', '2,2');


    globeG.append('path')
      .datum({ type: 'LineString', coordinates: [[-180, 0], [-90, -eclipticDeg], [0, 0], [90, eclipticDeg], [180, 0]] })
      .attr('d', path).style('fill', 'none').style('stroke', '#f8d747').style('stroke-width', 0.7);



    var bands = [
      { r: 90, opacity: 0.20 }, { r: 84, opacity: 0.35 }, { r: 78, opacity: 0.50 }, { r: 72, opacity: 0.60 }
    ];
    bands.forEach(function(b){
      globeG.append('path').datum(d3.geoCircle().radius(b.r).center(antiSunPos)()).attr('d', path)
        .style('fill', '#0e1116').style('fill-opacity', b.opacity).style('stroke', 'none');
    });

    var stationXY = projection([lon, lat]);
    if (stationXY){
      svg.append('circle').attr('cx', stationXY[0]).attr('cy', stationXY[1]).attr('r', 1.8).style('fill', '#ff4444');
    }





    var sphereSilhouetteD = path({ type: 'Sphere' });
    function addMaskedCircle(id, xy, r, fillUrl, hideOverlap){
      if (hideOverlap){
        var maskId = id + 'Mask';
        var mask = defs.append('mask').attr('id', maskId);
        mask.append('rect').attr('x', 0).attr('y', 0).attr('width', W).attr('height', H).style('fill', '#fff');
        mask.append('path').attr('d', sphereSilhouetteD).style('fill', '#000');
        svg.append('circle').attr('cx', xy[0]).attr('cy', xy[1]).attr('r', r).style('fill', fillUrl).attr('mask', 'url(#' + maskId + ')');
      } else {
        svg.append('circle').attr('cx', xy[0]).attr('cy', xy[1]).attr('r', r).style('fill', fillUrl);
      }
    }

    var sunXY = sunProjection(sunPos);
    if (sunXY){
      var sunSizeFactor = 1 - degreesFromCenter(sunPos, sunProjection) / 180;
      addMaskedCircle('sun', sunXY, sunScale(sunSizeFactor), 'url(#sunGradientMini)', degreesFromCenter(sunPos, sunProjection) > 90);
    }
    var moonXY = moonProjection(moonPos);
    if (moonXY){
      var moonSizeFactor = 1 - degreesFromCenter(moonPos, moonProjection) / 180;
      addMaskedCircle('moon', moonXY, moonScale(moonSizeFactor), 'url(#moonGradientMini)', degreesFromCenter(moonPos, moonProjection) > 90);
    }

    // ---- Right pane: 7 readouts as label/value chip rows ----
    distanceText.textContent = Math.round(v.sunDistanceKm).toLocaleString() + ' km';
    auroraText.textContent = v.auroraPct === null ? '\u2014' : v.auroraPct.toFixed(1) + ' nT';

    var eqParts = almDateTimeFromEpoch(v.nextEquinoxTs), solParts = almDateTimeFromEpoch(v.nextSolsticeTs);
    equinoxText.textContent = eqParts.date + (eqParts.time ? ' ' + eqParts.time : '');
    solsticeText.textContent = solParts.date + (solParts.time ? ' ' + solParts.time : '');

    sunDecText.textContent = v.sunDec.toFixed(3) + '\u00B0';
    eclipticText.textContent = eclipticDeg.toFixed(5) + '\u00B0';
    sunRaText.textContent = v.sunRa.toFixed(3) + '\u00B0';
  }

  var lastData = null, stationLat = 51.94, stationLon = -0.987;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData, stationLat, stationLon);
  });
  function refresh(){
    Promise.allSettled([
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(OVATION_URL + ((OVATION_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var almResult = results[0], archResult = results[1], ovationResult = results[2];
      if (almResult.status === 'rejected') console.warn('cardEarthDaylight: almanac.json fetch failed —', almResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardEarthDaylight: archive.json fetch failed —', archResult.reason.message);
      if (ovationResult.status === 'rejected') console.warn('cardEarthDaylight: ovation.txt fetch failed —', ovationResult.reason.message);

      var alm = almResult.status === 'fulfilled' ? almResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var ovation = ovationResult.status === 'fulfilled' ? ovationResult.value : null;
      var meta = arch.meta || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      stationLat = num(meta.latitude, stationLat);
      stationLon = num(meta.longitude, stationLon);

      lastData = {
        sunRa: num(alm['almanac.sun.ra'], 0),
        sunDec: num(alm['almanac.sun.dec'], 0),
        moonDec: num(alm['almanac.moon.dec'], 0),
        moonEclipticAngle: num(alm['almanac.moon.ecliptic_angle'], 0),
        eclipticAngle: num(alm['almanac.ecliptic_obliquity'], 0),
        moonPhaseName: alm['almanac.moon.phase_name'] || '\u2014',
        moonFullness: num(alm['almanac.moon.phase'], 0),
        nextEquinoxTs: alm['almanac.next_equinox.unix_epoch.raw'],
        nextSolsticeTs: alm['almanac.next_solstice.unix_epoch.raw'],
        // almanac.sun.earth_distance is in AU (standardised schema); this
        // card's label is hardcoded "km" (see distanceText above), so
        // convert here rather than showing an AU figure under a km label.
        sunDistanceKm: num(alm['almanac.sun.earth_distance'], 0) * 149597870.7,
        auroraPct: ovation ? auroraProbabilityAt(ovation.coordinates, stationLat, stationLon) : null
      };
      renderCard(lastData, stationLat, stationLon);
      setStatus(almResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardEarthDaylight: refresh failed —', e.message);
      setStatus(false);
    });
  }

  loadWorldMap().then(refresh);
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardEarthDaylight.js failed:", e);
}

/* ===== cardSolarDial.js ===== */
try {
/*
##############################################################################################
# cardSolarDial.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardSolarDial.js =====================
(function(){
  var ASTRO_JSON_URL = './jsondata/almanac.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }

  // almanac.json now gives raw unix_epoch timestamps rather than
  // pre-formatted "HH:MM"/date strings -- these two replace
  // timeToDegrees()'s string-parsing job going straight from epoch,
  // reading hour/minute in the station's own timezone.
  function epochParts(ts){
    if (ts == null) return null;
    var p = {};
    try {
      new Intl.DateTimeFormat('en-GB', {
        timeZone: StationTime.getTZ(), hourCycle: 'h23',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      }).formatToParts(new Date(ts * 1000)).forEach(function(x){ p[x.type] = x.value; });
    } catch (e) { return null; }
    return p;
  }
  function epochToDegrees(ts){
    var p = epochParts(ts);
    if (!p) return 0;
    return (360 / 24) * (+p.hour) + (360 / 60 * (+p.minute)) / 24;
  }
  function epochToHHMM(ts, withSeconds){
    var p = epochParts(ts);
    if (!p) return '--:--';
    return p.hour + ':' + p.minute + (withSeconds ? ':' + p.second : '');
  }


  // almanac.sun.daylight_seconds is now raw seconds rather than a
  // pre-formatted "HH:MM" string -- format the same way here.
  function secondsToHHMM(totalSeconds){
    if (totalSeconds == null || isNaN(totalSeconds)) return '00:00';
    var h = Math.floor(totalSeconds / 3600);
    var m = Math.floor((totalSeconds % 3600) / 60);
    return pad2(h) + ':' + pad2(m);
  }

  function darknessLabelFrom(daylightStr){
    var lightHours = parseInt(daylightStr.substr(0, 2), 10) || 0;
    var lightMins = parseInt(daylightStr.substr(-2), 10) || 0;
    var darkHours = 23 - lightHours;
    var darkMins = 60 - lightMins;
    var darkMinsStr = darkMins < 10 ? '0' + darkMins : String(darkMins);
    return darkHours + ':' + darkMinsStr;
  }

  var mount = document.getElementById('solarDialCard14');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Solar Dial');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: day/night dial, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // 8 items in a fixed-row-height list, same idiom as Current Conditions —
  // several pairs of readouts that used to sit side-by-side around the
  // dial (Daylight/Darkness, Sunrise/First Light, Sunset/Last Light,
  // Moonrise/Moonset) are combined into one row's value each, to keep the
  // row count at a familiar 8 instead of the original 12 separate figures.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '20px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var daylightText   = addChipRow('Daylight | Darkness');
  var azimuthText    = addChipRow('Sun Azimuth');
  var elevationText  = addChipRow('Sun Elevation');
  var sunriseText    = addChipRow('Sunrise (First Light)');
  var sunsetText     = addChipRow('Sunset (Last Light)');
  var moonPhaseText  = addChipRow('Moon Phase');
  var moonRiseSetText = addChipRow('Moonrise | Moonset');
  var illumText      = addChipRow('Illumination');
  illumText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the celestial modal — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'modalCelestial.html';
  cardLink.setAttribute('data-modal', 'Celestial');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Celestial Data \u2013 Radio Aurora | Northern Lights - Meteor Showers - Moon Data');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '720px');
  cardLink.setAttribute('data-url', 'modalCelestial.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175, cx = 90, cy = 85, R = 65;

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');




    var sunAngle = ((v.hourSun % 360) + 360) % 360;
    var moonAngle = ((v.hourMoon % 360) + 360) % 360;
    var sunriseAngle = epochToDegrees(v.sunRiseTs);
    var transitAngle = epochToDegrees(v.sunTransitTs);
    var sunsetAngle = epochToDegrees(v.sunSetTs);

    var dialG = svg.append('g').attr('transform', 'translate(' + cx + ',' + cy + ')');

    dialG.append('circle').attr('r', R).style('fill', 'none').style('stroke', 'var(--bs-border-color)').style('stroke-width', R * (7 / 52.5));




    function tickPoint(angleDeg, r){
      var rad = angleDeg * Math.PI / 180;
      return [-r * Math.sin(rad), r * Math.cos(rad)];
    }

    var arcGen = d3.arc().innerRadius(R - 0.5).outerRadius(R + 0.5);
    var a0 = (sunriseAngle + 180) * Math.PI / 180, a1 = (sunsetAngle + 180) * Math.PI / 180;
    if (a1 < a0) a1 += 2 * Math.PI;
    dialG.append('path').attr('d', arcGen({ startAngle: a0, endAngle: a1 })).style('fill', '#007fff');


    for (var i = 0; i < 24; i++){
      var p1 = tickPoint(i / 24 * 360, R * (43 / 52.5));
      var p2 = tickPoint(i / 24 * 360, R * (43 / 52.5) + R * (3 / 52.5));
      dialG.append('line')
        .attr('x1', p1[0]).attr('y1', p1[1]).attr('x2', p2[0]).attr('y2', p2[1])
        .style('stroke', 'var(--bs-secondary-color)').style('stroke-width', 1).style('stroke-linecap', 'round');
    }
    [sunriseAngle, transitAngle, sunsetAngle].forEach(function(deg){
      var p1 = tickPoint(deg, R * (48 / 52.5));
      var p2 = tickPoint(deg, R * (48 / 52.5) + R * (9 / 52.5));
      dialG.append('line')
        .attr('x1', p1[0]).attr('y1', p1[1]).attr('x2', p2[0]).attr('y2', p2[1])
        .style('stroke', '#ff6347').style('stroke-width', 1.5).style('stroke-linecap', 'round');
    });

    var moonRad = (moonAngle - 90) * Math.PI / 180;
    var moonCx = Math.cos(moonRad) * R, moonCy = Math.sin(moonRad) * R;
    dialG.append('circle').attr('cx', moonCx).attr('cy', moonCy).attr('r', R * (3 / 52.5))
      .style('fill', '#fff').style('stroke', '#fff').style('stroke-width', 1);
    dialG.append('circle').attr('cx', moonCx).attr('cy', moonCy).attr('r', R * (3.5 / 52.5))
      .style('fill', 'none').style('stroke', '#000').style('stroke-width', 0.5);

    var sunRad = (sunAngle - 90) * Math.PI / 180;
    var sunColor = v.sunAlt > 0.5 ? '#ff7c39' : (v.sunAlt > -4 ? 'rgba(255,112,50,0.5)' : 'rgba(86,95,103,0.7)');
    dialG.append('circle').attr('cx', Math.cos(sunRad) * R).attr('cy', Math.sin(sunRad) * R).attr('r', R * (5.5 / 52.5))
      .style('fill', sunColor).style('stroke', sunColor).style('stroke-width', 1);



    var isDay = v.sunAlt > 0;
    var futureMs = isDay ? v.sunSetTs * 1000 : v.sunRiseTs * 1000;
    var nowMs = Date.now();
    var diff = futureMs - nowMs;
    if (diff < 0) diff += 86400000;
    var totalHours = Math.floor(diff / (1000 * 60 * 60));
    var days = Math.floor(totalHours / 24);
    var hrs = totalHours - days * 24;
    var mins = Math.floor(diff / (1000 * 60)) - totalHours * 60;
    dialG.append('text').attr('y', -4).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '11px').style('fill', overlayTextColor)
      .text(isDay ? DivumWXI18N.t('Sun Set') : DivumWXI18N.t('Sun Rise'));
    dialG.append('text').attr('y', 10).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '8.5px').style('fill', overlayTextColor)
      .text(hrs + ' hrs ' + mins + ' mins');

    // ---- Right pane: 8 readouts as label/value chip rows ----
    var daylightHHMM = secondsToHHMM(v.daylightSeconds);
    daylightText.textContent = daylightHHMM + ' | ' + darknessLabelFrom(daylightHHMM);
    azimuthText.textContent = v.sunAz.toFixed(2) + '\u00B0';
    elevationText.textContent = v.sunAlt.toFixed(2) + '\u00B0';
    sunriseText.textContent = epochToHHMM(v.sunRiseTs, true) + ' (' + epochToHHMM(v.civilTwilightBeginTs, false) + ')';
    sunsetText.textContent = epochToHHMM(v.sunSetTs, true) + ' (' + epochToHHMM(v.civilTwilightEndTs, false) + ')';
    moonPhaseText.textContent = v.moonPhaseName;
    moonRiseSetText.textContent = epochToHHMM(v.moonRiseTs, false) + ' | ' + epochToHHMM(v.moonSetTs, false);
    illumText.textContent = v.luminancePct.toFixed(2) + ' %';
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(alm){
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

        lastData = {
          sunRiseTs: alm['almanac.sun.next_rising.unix_epoch.raw'],
          sunSetTs: alm['almanac.sun.next_setting.unix_epoch.raw'],
          sunTransitTs: alm['almanac.sun.next_transit.unix_epoch.raw'],
          sunAz: num(alm['almanac.sun.az'], 0),
          sunAlt: num(alm['almanac.sun.alt'], 0),
          hourSun: num(alm['almanac.sun.hour_angle'], 0),
          hourMoon: num(alm['almanac.moon.hour_angle'], 0),
          moonRiseTs: alm['almanac.moon.next_rising.unix_epoch.raw'],
          moonSetTs: alm['almanac.moon.next_setting.unix_epoch.raw'],
          moonPhaseName: alm['almanac.moon.phase_name'] || '\u2014',
          luminancePct: num(alm['almanac.moon.phase'], 0),
          civilTwilightBeginTs: alm['almanac(horizon=-6).sun.next_rising.unix_epoch.raw'],
          civilTwilightEndTs: alm['almanac(horizon=-6).sun.next_setting.unix_epoch.raw'],
          daylightSeconds: alm['almanac.sun.daylight_seconds']
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
      console.warn('cardSolarDial: refresh failed —', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardSolarDial.js failed:", e);
}

/* ===== cardGeocentric.js ===== */
try {
/*
##############################################################################################
# cardGeocentric.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardGeocentric.js =====================
(function(){
  var ASTRO_JSON_URL = './jsondata/almanac.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function toDegrees(x){ return x * (180 / Math.PI); }
  function toRadians(x){ return x * (Math.PI / 180); }

  function computeSkyPath(dec, lat, hemisphere, numPoints){
    var cosdec = Math.cos(toRadians(dec)), sindec = Math.sin(toRadians(dec));
    var coslat = Math.cos(toRadians(lat)), sinlat = Math.sin(toRadians(lat));
    var pts = [];
    for (var i = 0; i < numPoints; i++){
      var sha = 180 + i * (360 / numPoints);
      var cossha = Math.cos(toRadians(sha));
      var sinelevation = cossha * cosdec * coslat + sindec * sinlat;
      var alt = toDegrees(Math.asin(sinelevation));
      var shaWrapped = sha - 360;

      var azmathA = Math.cos(toRadians(shaWrapped)) * cosdec * sinlat - sindec * coslat;
      var azmathB = Math.cos(toRadians(alt));
      var cosaz = azmathA / azmathB;
      cosaz = Math.max(-1, Math.min(1, cosaz));
      var azRaw = toDegrees(Math.acos(cosaz));
      var az;
      if (hemisphere === 0){
        az = (shaWrapped < 0) ? (azRaw - 180) * -1 : azRaw + 180;
      } else {
        az = (shaWrapped < 0) ? (azRaw - 360) * -1 : azRaw;
      }
      if (isNaN(az)) az = 180;
      pts.push({ x: az, y: alt });
    }
    pts.sort(function(a, b){ return a.x - b.x; });
    return pts;
  }

  var mount = document.getElementById('geocentricCard15');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card — override the shared
  // .card CSS's 18px border-bottom just for this mount so the content pane
  // can reclaim that space. Card height stays 195px: 20px title band
  // (border-top, unchanged) + 175px content (was 157px). Now on the same
  // 60:40 split as the rest of the redone cards (left: sky-path chart,
  // narrowed to fit 60%; right: analemma), plus the whole-card
  // click-through link (see cardLink, appended after both panes).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar --------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Geocentric');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: sky-path chart, right: analemma) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.height = '175px';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.overflow = 'hidden';
  rightPane.style.display = 'flex';
  rightPane.style.alignItems = 'center';
  rightPane.style.justifyContent = 'center';
  contentWrap.appendChild(rightPane);

  // Whole card is a click-through to the geocentric chart (Meeus Live) —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of both panes and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above) — same technique used on the
  // METAR and Celestial cards. Class name lets the shared hover-tooltip
  // script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'modalMeeusLive.html';
  cardLink.setAttribute('data-modal', 'Geocentric Live Meeus Calculation');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Live Meeus Calculation');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1600px');
  cardLink.setAttribute('data-modal-height', '1400px');
  cardLink.setAttribute('data-url', 'modalMeeusLive.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  // -- Left pane: sky-path chart, squeezed to fit the 60% column --------
  // Was a full-card-width viewBox (W=310) when this was the only thing on
  // the card; narrowed to W=176 to fit the 60% pane (padding included)
  // without visually stretching/distorting — the viewBox itself is redrawn
  // at the narrower width rather than non-uniformly scaling the old one, so
  // the sun/moon discs stay circular and the axis ticks stay evenly spaced.
  // The x-axis tick list is thinned from 9 labels to 5 (0/90/180/270/360)
  // since 9 labels at this width would collide.
  var chartWrap = document.createElement('div');
  chartWrap.style.height = '100%';
  chartWrap.style.width = '100%';
  chartWrap.style.boxSizing = 'border-box';
  leftPane.appendChild(chartWrap);

  var W = 176, H = 175;
  var padLeft = 22, padRight = 4, padTop = 10, padBottom = 16;

  function renderCard(v, lat){
    var svgSel = d3.select(chartWrap);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    var hemisphere = lat >= 0 ? 0 : 1;
    var xDomain = hemisphere === 0 ? [0, 360] : [360, 0];
    var yDomain = hemisphere === 0 ? [-80, 80] : [-100, 100];
    var yTicks = hemisphere === 0 ? [-80, -40, 0, 40, 80] : [-100, -60, -20, 20, 60, 100];

    var xScale = d3.scaleLinear().domain(xDomain).range([padLeft, W - padRight]);
    var yScale = d3.scaleLinear().domain(yDomain).range([H - padBottom, padTop]);

    var xAxis = d3.axisBottom(xScale).tickValues([0, 90, 180, 270, 360]).tickSize(3).tickPadding(2).tickFormat(function(d){ return d + '\u00B0'; });
    var yAxis = d3.axisLeft(yScale).tickValues(yTicks).tickSize(3).tickPadding(2).tickFormat(function(d){ return d + '\u00B0'; });
    var xAxisG = svg.append('g').attr('transform', 'translate(0,' + (H - padBottom) + ')').call(xAxis);
    var yAxisG = svg.append('g').attr('transform', 'translate(' + padLeft + ',0)').call(yAxis);
    [xAxisG, yAxisG].forEach(function(g){
      g.selectAll('text').style('fill', 'var(--bs-secondary-color)').style('font-family', 'inherit').style('font-size', '6px');
      g.selectAll('line').style('stroke', 'var(--bs-border-color)');
      g.select('path').style('stroke', 'var(--bs-border-color)');
    });

    svg.append('line').attr('x1', xScale(xDomain[0])).attr('y1', yScale(0)).attr('x2', xScale(xDomain[1])).attr('y2', yScale(0))
      .style('stroke', '#007fff').style('stroke-width', 1);
    svg.append('line').attr('x1', xScale(180)).attr('y1', yScale(yDomain[0])).attr('x2', xScale(180)).attr('y2', yScale(yDomain[1]))
      .style('stroke', '#2e8b57').style('stroke-width', 1);
    svg.append('text').attr('x', xScale(180)).attr('y', padTop - 2).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '6px').style('fill', overlayTextColor).text(DivumWXI18N.t('Zenith'));
    svg.append('text').attr('x', padLeft + 3).attr('y', yScale(0) - 4)
      .style('font-family', 'inherit').style('font-size', '6px').style('fill', overlayTextColor).text(DivumWXI18N.t('Horizon'));

    var lineGen = d3.line().x(function(d){ return xScale(d.x); }).y(function(d){ return yScale(d.y); }).curve(d3.curveBasisOpen);
    var sunPath = computeSkyPath(v.sunDec, lat, hemisphere, 360);
    var moonPath = computeSkyPath(v.moonDec, lat, hemisphere, 360);
    svg.append('path').datum(moonPath).attr('d', lineGen).style('fill', 'none').style('stroke', 'silver').style('stroke-width', 1);
    svg.append('path').datum(sunPath).attr('d', lineGen).style('fill', 'none').style('stroke', 'rgba(255,99,71,1)').style('stroke-width', 1);

    var sunX = hemisphere === 0 ? v.sunAz : (v.sunAz < 180 ? 180 - v.sunAz : 360 + 180 - v.sunAz);
    var moonX = hemisphere === 0 ? v.moonAz : (v.moonAz < 180 ? 180 - v.moonAz : 360 + 180 - v.moonAz);
    var defs = svg.append('defs');
    var sunGrad = defs.append('radialGradient').attr('id', 'geoSunGrad');
    sunGrad.append('stop').attr('offset', '0%').style('stop-color', 'rgb(230,200,200)');
    sunGrad.append('stop').attr('offset', '90%').style('stop-color', 'tomato');
    var moonGrad = defs.append('radialGradient').attr('id', 'geoMoonGrad');
    moonGrad.append('stop').attr('offset', '0%').style('stop-color', 'rgb(230,200,200)');
    moonGrad.append('stop').attr('offset', '90%').style('stop-color', '#555');

    svg.append('circle').attr('cx', xScale(moonX)).attr('cy', yScale(v.moonAlt)).attr('r', 3.5).style('fill', 'url(#geoMoonGrad)');
    svg.append('circle').attr('cx', xScale(sunX)).attr('cy', yScale(v.sunAlt)).attr('r', 5.5).style('fill', 'url(#geoSunGrad)');
  }

  // -- Right pane: dynamic analemma ---------------------------------------
  // almanac.json is a single live snapshot (today's sun position only), not
  // a year of daily history, so there's no stored feed this card could pull
  // a year of declination samples from. The analemma's *shape* is instead
  // computed locally from the same NOAA/Meeus solar-position formulae
  // already duplicated in modalSolarTerminator.html and
  // modalAuroraTerminator.html elsewhere in this project (equation of time
  // vs. solar declination, sampled once per day across the current year) —
  // that shape is fixed by orbital mechanics and doesn't need live data.
  // What *is* dynamic and *is* sourced from almanac.json is the "today"
  // marker: its declination comes straight from the live sun_declination
  // field on every refresh() poll, the same field the sky-path chart on the
  // left already uses, so the dot tracks the station's own live feed rather
  // than a locally-computed value.
  var analemmaWrap = document.createElement('div');
  analemmaWrap.style.height = '100%';
  analemmaWrap.style.width = '100%';
  analemmaWrap.style.boxSizing = 'border-box';
  rightPane.appendChild(analemmaWrap);

  function meanObliquityOfEcliptic(T){
    return toRadians(23 + (26 + (21.448 - T * (46.815 + T * (0.00059 - T * 0.001813))) / 60) / 60);
  }
  function eccentricityEarthOrbit(T){ return 0.016708634 - T * (0.000042037 + 0.0000001267 * T); }
  function solarGeoMeanAnomaly(T){ return toRadians(357.52911 + T * (35999.05029 - 0.0001537 * T)); }
  function solarGeoMeanLongitude(T){
    var l = (280.46646 + T * (36000.76983 + T * 0.0003032)) % 360;
    return (l < 0 ? l + 360 : l) * Math.PI / 180;
  }
  function solarEquationOfCenter(T){
    var m = solarGeoMeanAnomaly(T);
    return toRadians(
      Math.sin(m) * (1.914602 - T * (0.004817 + 0.000014 * T)) +
      Math.sin(2 * m) * (0.019993 - 0.000101 * T) +
      Math.sin(3 * m) * 0.000289
    );
  }
  function obliquityCorrection(T){
    return meanObliquityOfEcliptic(T) + toRadians(0.00256 * Math.cos(toRadians(125.04 - 1934.136 * T)));
  }
  function solarApparentLongitude(T){
    var trueLon = solarGeoMeanLongitude(T) + solarEquationOfCenter(T);
    return trueLon - toRadians(0.00569 + 0.00478 * Math.sin(toRadians(125.04 - 1934.136 * T)));
  }
  function solarDeclinationDeg(T){
    return toDegrees(Math.asin(Math.sin(obliquityCorrection(T)) * Math.sin(solarApparentLongitude(T))));
  }
  function equationOfTimeMinutes(T){
    var e = eccentricityEarthOrbit(T), m = solarGeoMeanAnomaly(T), l = solarGeoMeanLongitude(T);
    var y = Math.tan(obliquityCorrection(T) / 2); y *= y;
    var raw = y * Math.sin(2 * l) - 2 * e * Math.sin(m) + 4 * e * y * Math.sin(m) * Math.cos(2 * l)
            - 0.5 * y * y * Math.sin(4 * l) - 1.25 * e * e * Math.sin(2 * m);
    return 4 * toDegrees(raw);
  }
  function julianCenturies(date){ return (date.getTime() - Date.UTC(2000, 0, 1, 12)) / 864e5 / 36525; }
  function isLeapYear(y){ return (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0; }

  // Sampled once per day (noon UTC, arbitrary but consistent) across the
  // current calendar year — computed once at load, not on every refresh,
  // since the curve itself never changes within a session.
  var analemmaCurve = (function(){
    var year = stationNow().getUTCFullYear();
    var n = isLeapYear(year) ? 366 : 365;
    var pts = [];
    for (var d = 0; d < n; d++){
      var T = julianCenturies(new Date(Date.UTC(year, 0, 1, 12) + d * 864e5));
      pts.push({ x: equationOfTimeMinutes(T), y: solarDeclinationDeg(T) });
    }
    return pts;
  })();

  var W2 = 100, H2 = 175;
  var padLeft2 = 20, padRight2 = 6, padTop2 = 12, padBottom2 = 14;
  var xs = analemmaCurve.map(function(p){ return p.x; });
  var ys = analemmaCurve.map(function(p){ return p.y; });
  var xExtent = [Math.min.apply(null, xs), Math.max.apply(null, xs)];
  var yExtent = [Math.min.apply(null, ys), Math.max.apply(null, ys)];
  var xPad2 = (xExtent[1] - xExtent[0]) * 0.14;
  var yPad2 = (yExtent[1] - yExtent[0]) * 0.06;

  var xScale2 = d3.scaleLinear().domain([xExtent[0] - xPad2, xExtent[1] + xPad2]).range([padLeft2, W2 - padRight2]);
  var yScale2 = d3.scaleLinear().domain([yExtent[0] - yPad2, yExtent[1] + yPad2]).range([H2 - padBottom2, padTop2]);

  function renderAnalemma(v){
    var svgSel = d3.select(analemmaWrap);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W2 + ' ' + H2).attr('width', '100%').attr('height', '100%');

    svg.append('text').attr('x', W2 / 2).attr('y', padTop2 - 4).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '6px').style('fill', overlayTextColor)
      .style('font-variant-caps', 'small-caps').style('letter-spacing', '.04em').text(DivumWXI18N.t('Analemma'));

    // Light celestial-equator reference line (dec = 0), matching the
    // horizon/zenith crosshair convention used in the sky-path chart.
    svg.append('line').attr('x1', padLeft2).attr('y1', yScale2(0)).attr('x2', W2 - padRight2).attr('y2', yScale2(0))
      .style('stroke', 'var(--bs-border-color)').style('stroke-width', 1).style('stroke-dasharray', '2,2');

    var yAxis = d3.axisLeft(yScale2).tickValues([-20, 0, 20]).tickSize(2).tickPadding(2).tickFormat(function(d){ return d + '\u00B0'; });
    var yAxisG = svg.append('g').attr('transform', 'translate(' + padLeft2 + ',0)').call(yAxis);
    yAxisG.selectAll('text').style('fill', 'var(--bs-secondary-color)').style('font-family', 'inherit').style('font-size', '6px');
    yAxisG.selectAll('line').style('stroke', 'var(--bs-border-color)');
    yAxisG.select('path').style('stroke', 'var(--bs-border-color)');

    var lineGen2 = d3.line().x(function(d){ return xScale2(d.x); }).y(function(d){ return yScale2(d.y); }).curve(d3.curveCatmullRomClosed);
    svg.append('path').datum(analemmaCurve).attr('d', lineGen2).style('fill', 'none').style('stroke', 'rgba(255,99,71,0.75)').style('stroke-width', 1);

    var defs2 = svg.append('defs');
    var sunGrad2 = defs2.append('radialGradient').attr('id', 'geoAnalemmaSunGrad');
    sunGrad2.append('stop').attr('offset', '0%').style('stop-color', 'rgb(230,200,200)');
    sunGrad2.append('stop').attr('offset', '90%').style('stop-color', 'tomato');

    // "Today" marker — the one part of this pane actually driven by the
    // live almanac.json feed rather than the locally-computed curve (see
    // the comment above analemmaWrap).
    var todayT = julianCenturies(stationNow());
    var todayX = equationOfTimeMinutes(todayT);
    svg.append('circle').attr('cx', xScale2(todayX)).attr('cy', yScale2(v.sunDec)).attr('r', 3.5).style('fill', 'url(#geoAnalemmaSunGrad)');
  }

  var lastData = null, stationLat = 51.94;
  function refresh(){
    Promise.allSettled([
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var almResult = results[0], archResult = results[1];
      if (almResult.status === 'rejected') console.warn('cardGeocentric: almanac.json fetch failed --', almResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardGeocentric: archive.json fetch failed --', archResult.reason.message);

      var alm = almResult.status === 'fulfilled' ? almResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var meta = arch.meta || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      stationLat = num(meta.latitude, stationLat);

      lastData = {
        sunDec: num(alm['almanac.sun.dec'], 0),
        sunAz: num(alm['almanac.sun.az'], 0),
        sunAlt: num(alm['almanac.sun.alt'], 0),
        moonDec: num(alm['almanac.moon.dec'], 0),
        moonAz: num(alm['almanac.moon.az'], 0),
        moonAlt: num(alm['almanac.moon.alt'], 0)
      };
      renderCard(lastData, stationLat);
      renderAnalemma(lastData);
      setStatus(almResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardGeocentric: refresh failed --', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
  // No prior unitsystemchange/resize re-render pattern existed in this
  // card to follow -- this is the first such listener here, same idea as
  // cardTemperature.js's.
  window.addEventListener('i18nready', function(){
    if (lastData) { renderCard(lastData, stationLat); renderAnalemma(lastData); }
  });
})();
} catch (e) {
  console.error("cardsBundle: cardGeocentric.js failed:", e);
}

/* ===== cardMoonPhase.js ===== */
try {
/*
##############################################################################################
# cardMoonPhase.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardMoonPhase.js =====================
(function(){
  var ASTRO_JSON_URL = './jsondata/almanac.json';
  var POLL_MS = 30 * 1000;
  function toRadians(x){ return x * (Math.PI / 180.0); }
  function toDegrees(x){ return x * (180.0 / Math.PI); }

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }

  // almanac.json now gives raw unix_epoch timestamps rather than
  // pre-formatted strings.
  function epochToHHMM(ts, withSeconds){
    if (ts == null) return '--:--';
    var opts = { hour: '2-digit', minute: '2-digit', hour12: false };
    if (withSeconds) opts.second = '2-digit';
    try { if (window.StationTime) opts.timeZone = window.StationTime.getTZ(); } catch (e) {}
    return new Date(ts * 1000).toLocaleTimeString(undefined, opts);
  }
  function fmtEpochDate(ts){
    if (ts == null) return null;
    var opts = { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: false };
    try { if (window.StationTime) opts.timeZone = window.StationTime.getTZ(); } catch (e) {}
    return new Date(ts * 1000).toLocaleString(undefined, opts);
  }

  var METEOR_EVENTS = [
    { s: [1,1], e: [1,2], t: 'Quadrantids' },
    { s: [1,3], e: [1,4], t: 'Quadrantids peak' },
    { s: [1,5], e: [1,12], t: 'Quadrantids' },
    { s: [4,9], e: [4,20], t: 'Approaching Lyrids' },
    { s: [4,21], e: [4,22], t: 'Lyrids peak' },
    { s: [5,5], e: [5,6], t: 'ETA Aquarids' },
    { s: [7,20], e: [7,27], t: 'Delta Aquarids soon' },
    { s: [7,28], e: [7,29], t: 'Delta Aquarids peak' },
    { s: [8,1], e: [8,10], t: 'Perseids active' },
    { s: [8,11], e: [8,13], t: 'Perseids peak' },
    { s: [8,14], e: [8,18], t: 'Perseids passed' },
    { s: [10,7], e: [10,7], t: 'Draconids peak' },
    { s: [10,20], e: [10,21], t: 'Orionids peak' },
    { s: [11,4], e: [11,5], t: 'South Taurids peak' },
    { s: [11,11], e: [11,11], t: 'North Taurids peak' },
    { s: [11,17], e: [11,18], t: 'Leonids peak' },
    { s: [12,13], e: [12,14], t: 'Geminids peak' },
    { s: [12,17], e: [12,20], t: 'Ursids active' },
    { s: [12,21], e: [12,22], t: 'Ursids peak' },
    { s: [12,23], e: [12,25], t: 'Ursids active' }
  ];
  function currentMeteorShower(now){
    var m = now.getUTCMonth() + 1, d = now.getUTCDate();
    var todayNum = m * 100 + d;
    for (var i = 0; i < METEOR_EVENTS.length; i++){
      var ev = METEOR_EVENTS[i];
      var startNum = ev.s[0] * 100 + ev.s[1], endNum = ev.e[0] * 100 + ev.e[1];
      if (todayNum >= startNum && todayNum <= endNum) return DivumWXI18N.t(ev.t);
    }
    return DivumWXI18N.t('No Meteor Showers');
  }

  var MOON_TEXTURE_MARKUP =
    '  <!-- moon -->\n' +
    '  <linearGradient id="moonTexB" x1="1327.9395" x2="1156.0339" y1="103.897" y2="398.9926" gradientUnits="userSpaceOnUse" gradientTransform="matrix(.938 0 0 -.938 -679.11 677.625)">\n' +
    '    <stop offset="0" stop-color="#ADADB0"/>\n' +
    '    <stop offset="1" stop-color="#B7B7B9" stop-opacity=".3878"/>\n' +
    '  </linearGradient>\n' +
    '  <linearGradient id="moonTexC" x1="522.8037" x2="333.1784" y1="492.1099" y2="544.0406" gradientUnits="userSpaceOnUse" gradientTransform="matrix(.938 0 0 -.938 28.573 676.39)">\n' +
    '    <stop offset="0" stop-color="#ADADB0"/>\n' +
    '    <stop offset="1" stop-color="#B7B7B9" stop-opacity=".3878"/>\n' +
    '  </linearGradient>\n' +
    '  <radialGradient id="moonTexD" cx="227.7266" cy="351.9722" r="171.9204" gradientTransform="matrix(.96 .1 .172 -1.667 -50.283 919.35)" gradientUnits="userSpaceOnUse">\n' +
    '    <stop offset="0" stop-color="#7D7D7F"/>\n' +
    '    <stop offset="1" stop-color="#515056"/>\n' +
    '  </radialGradient>\n' +
    '  <linearGradient id="moonTexE" x1="325.3955" x2="325.6149" y1="154.312" y2="272.1671" gradientUnits="userSpaceOnUse" gradientTransform="matrix(.973 0 0 -.973 9.094 686.87)">\n' +
    '    <stop offset="0" stop-color="#4A4A4C"/>\n' +
    '    <stop offset="1" stop-color="#626266"/>\n' +
    '  </linearGradient>\n' +
    '  <radialGradient id="moonTexF" cx="680.7578" cy="-76.7183" r="171.9202" gradientTransform="matrix(.798 .387 .112 -.23 10.146 158.745)" gradientUnits="userSpaceOnUse">\n' +
    '    <stop offset="0" stop-color="#7D7D7F"/>\n' +
    '    <stop offset="1" stop-color="#515056"/>\n' +
    '  </radialGradient>\n' +
    '  <radialGradient id="moonTexG" cx="-50.1562" cy="618.5278" r="171.9207" gradientTransform="matrix(.2 -.154 -.285 -.372 654.266 432.98)" gradientUnits="userSpaceOnUse">\n' +
    '    <stop offset="0" stop-color="#8B8B8E"/>\n' +
    '    <stop offset="1" stop-color="#58575D"/>\n' +
    '  </radialGradient>\n' +
    '  <radialGradient id="moonTexH" cx="390.7393" cy="163.7007" r="125.1765" gradientTransform="matrix(1.056 .173 .35 -2.148 -84.647 856.22)" gradientUnits="userSpaceOnUse">\n' +
    '    <stop offset="0" stop-color="#FDFDFE"/>\n' +
    '    <stop offset="1" stop-color="#F1F1F2" stop-opacity=".4694"/>\n' +
    '  </radialGradient>\n' +
    '  <radialGradient id="moonTexI" cx="356.7021" cy="151.3325" r="125.1816" gradientTransform="matrix(.81 .317 .366 -.937 40.5 600.824)" gradientUnits="userSpaceOnUse">\n' +
    '    <stop offset="0" stop-color="#FDFDFE"/>\n' +
    '    <stop offset="1" stop-color="#F1F1F2" stop-opacity=".4694"/>\n' +
    '  </radialGradient>\n' +
    '  <radialGradient id="moonTexJ" cx="1208.7109" cy="115.5049" r="8.3684" gradientTransform="matrix(1.683 -.495 -.513 -1.375 -1589.063 1329.948)" gradientUnits="userSpaceOnUse">\n' +
    '    <stop offset="0" stop-color="#8C8C8C"/>\n' +
    '    <stop offset=".6731" stop-color="#8C8C8C"/>\n' +
    '    <stop offset="1" stop-color="#8C8C8C" stop-opacity="0"/>\n' +
    '  </radialGradient>\n' +
    '  <symbol viewBox="152.555 141.918 512 512" id="moonPhase16HighResMoon">\n' +
    '    <path fill="#D1D1D2" d="M664.555 398.161c.021 139.406-114.577 252.434-255.961 252.455-141.385.021-256.018-112.974-256.039-252.38v-.075c-.021-139.406 114.577-252.434 255.962-252.455 141.385-.021 256.017 112.974 256.038 252.379v.076z"/>\n' +
    '    <path fill="#bfbfc0" d="M168.597 486.176c-20.082-57.297-24.044-122.035 6.608-191.814 1.288-2.931 5.252 7.267 5.252 10.446v13.852c0 5.926.778 7.096 2.77 11.081 1.425 2.85 2.989 5.976 4.618 9.234 1.821 3.642 1.383 5.532 2.771 11.081.808 3.234 1.736 9.014 3.694 12.928 2.506 5.015 2.953 10.524 5.541 15.698 2.216 4.433 5.163 10.496 6.464 15.699 1.333 5.331 3.868 10.506 6.464 15.698 1.956 3.911 2.745 9.209 5.541 12.006 2.386 2.387 4.585 4.378 9.234 5.541 5.752 1.437 14.759 2.761 20.316 5.54 3.705 1.854 9.308 2.807 12.928 4.617 2.73 1.366 5.183 3.977 8.311 5.541 4.618 2.309 7.086 4.315 10.158 7.387 1.53 1.53 6.464 6.187 6.464 9.234v13.852c0 2.195-1.38 12.472 0 13.852 2.093 2.094 3.6 6.973 8.311 4.617 3-1.5 5.586-2.815 8.311-5.54 1.577-1.576 4.765-6.428 8.312-5.541 1.803.451 5.427.81 8.311 3.693 2.378 2.379 4.565 3.644 7.387 6.465 1.742 1.741 4.759 5.149 7.388 6.464 1.313.656 7.841 3.662 8.311 5.54.82 3.28 1.879 8.073-2.77 9.235-4.169 1.041-6.074 1.651-10.158 3.693-2.253 1.126-6.346 3.895-9.235 4.617-4.945 1.235-7.169 1.411-9.234 5.541-1.646 3.293-3.573 4.496-6.463 7.386-2.24 2.24-5.858 4.301-1.848 8.312 2.015 2.014 4.272 4.905 7.388 6.463 3.635 1.818 6.479 3.702 10.157 5.541 2.447 1.224 7.289 2.672 9.235 4.617 4.171 4.171 2.695 4.138-2.771 2.771-4.081-1.021-7.522-2.806-11.081-3.694-4.084-1.021-7.925-.923-12.929-.923-5.016 0-8.831-.923-13.851-.923-5.604 0-10.313-.925-15.699-.925-2.773 0-11.933.711-8.311 5.54 3.343 4.459 4.401 4.618 11.081 4.618 4.693 0 8.284-.026 11.082 2.771 2.469 2.469 3.858 4.947 5.541 8.311 2.022 4.045 4.578 7.309 6.464 11.081 2.179 4.358 3.454 6.226 7.387 10.158 2.38 2.38 6.981 4.025 12.005 2.77 3.081-.77 11.718-4.267 12.928-1.846-1.663-3.326-1.987 7.526-4.618 10.157-3.312 3.312-7.385 1.387-10.157 0-3.151-1.576-7.164-2.196-10.158-3.693-3.771-1.886-5.825-3.837-9.234-5.541-5.386-2.692-4.569-2.961-3.694-6.465 1.582-6.323-.754-3.693-6.463-3.693-5.174 0-8.543.981-12.004 1.848-5.99 1.497-2.391 4.073 0 6.464 5.062 5.062-2.632.069-4.618-.923-3.309-1.655-6.241-4.395-8.311-6.466-3.178-3.177-6.33-4.481-9.234-7.386-2.634-2.635-5.472-4.121-8.311-5.541-3.331-1.666-5.252-4.329-7.388-6.465-1.931-1.931 3.694-3.216 3.694-8.311 0-6.494-.683-7.146-3.694-10.158-2.548-2.547-1.146 7.611-3.693 10.158-.743.743-8.773-4.387-9.234-4.617-3.356-1.678-5.847-3.076-8.312-5.541-2.504-2.503-3.701-6.78-5.541-9.233-2.43-3.24-3.499-5.492-7.387-6.464-1.975-.495-5.276-6.2-6.464-7.388-2.821-2.821-4.086-5.01-6.464-7.388-3.024-3.024-1.259-7.641 0-10.158 1.557-3.113 4.451-5.374 6.464-7.388.061-.06-8.049 2.474-9.234 2.771-5.26 1.315-4.648-1.877-7.388-4.616-2.115-2.116-1.847-7.891-1.847-12.006-.004-3.37-6.479-3.587-9.093-4.242z"/>\n' +
    '    <path fill="url(#moonTexB)" d="M576.838 504.029c-9.562 8.733-1.756 22.77 8.361 25.704-1.598 13.859-17.187 10.598-24.836 18.721-3.89 7.558 25.33 1.455 10.477 9.57-7.657.782-17.566 29.471-8.543 15.504 9.216-1.451 16.391-16.34 25.786-9.395 7.934-2.815 8.692.397 1.978 4.546-6.456 5.635-11.055 20.145-19.595 13.828-6.35 9.193-18.704 14.052-29.735 14.126-8.129 1.425 5.1 13.699-7.391 14.745-12.32 6.354-1.907-15.091 4.695-15.264-.008-9.392 5.42-13.79 14.906-12.991 9.294-13.903 5.078-17.385-3.314-8.411-7.525 7.673-19.243 11.37-29.867 10.586-3.881-9.669-15.718-4.406-21.508-8.927 10.072-10.691 26.336-11.242 38.236-19.22 6.947-3.139 18.778-2.205 14.654-12.9 3.105-6.907 17.028-6.703 17.775-11.057-6.354-.772-10.577-.342-11.255-5.604-10.298 2.208-11.205 10.56-10.12 19.031-5.68 10.438-18.792 5.837-27.822 10.653-11.336 2.043-24.049 3.341-34.53-2.217-7.587-3.538-19.957 7.23-23.89 3.726 7.007-8.155 18.789-5.14 27.583-10.189 9.258-5.513 24.856-18.661 20.254-29.831-13.896-3.216-19.453 16.313-27.848 21.335-6.814-2.657-11.503-7.32-19.168-4.487.881-6.621.637-12.967-8.966-13.163-12.066 4.136-23.757-9.827-10.444-17.254.891-8.533-14.441 1.033-18.305 3.237-15.669 6.247-4.521-15.075.317-20.128.606-10.416 5.156-22.717-.642-32.133-7.066-3.264-20.024-17.791-6.326-20.582 11.374 5.229 27.564-1.464 19.447-15.57-2.184-6.472-9.584-22.555 2.05-22.161 3.226-6.786-6.219-17.833 6.229-21.022 6.538-8.43 12.817-15.92 23.683-18.215 11.085-7.214 18.934-17.523 24.297-29.544 3.148-14.628 21.739-4.051 31.688-3.442 7.279 5.26 2.206 20.046.264 28.191-3.766 10.045-2.905 21.63-.929 31.993 4.699 10.88 15.433 17.625 21.646 27.333 5.179 10.996-1.49 27.187 9.35 34.426 8.952 8.976 15.183 29.978 20.189 41.359 1.335 1.647 20.009 4.67 26.487-4.684 7.785 3.184 13.006 9.909 12.391 10.671-1.797 2.229 8.926 11.505-1 15.49-4.861 1.951-7.351-6.777-11.5-9.335-4.149-2.556-10.942 1.551-12.601 1.609-6.626.23-11.619-.648-12.608 1.342z"/>\n' +
    '    <path fill="#B3B3B5" d="M464.875 363.909c-6.202 3.69-3.985 11.711-4.914 17.616-1.96 6.176-9.018 7.635-14.603 8.768-6.573 1.13-11.548 7.325-18.668 6.037-6.043-.266-12.885.926-17.778-3.541-45.154-49.696-22.15-88.81-17.912-98.714-18.712-36.003 116.302-22.75 100.268 23.291 1.581 5.168.552 10.73.149 15.976-2.874 4.406-8.476 7.001-8.742 12.861-2.344 5.612-15.568 16.747-17.8 17.706z"/>\n' +
    '    <path fill="#FBFBFC" d="M519.357 332.511c-1.146 1.17-3.108 1.21-4.65 1.679-2.924.554-5.896 1.18-8.884 1.041-3.533-.778-5.801-3.909-8.849-5.594-2.075-1.234-4.085.851-4.271 2.879-.172 1.792-1.049 4.529.898 5.62 2.86 1.44 6.107 1.75 9.133 2.687 2.01 1.054.303 3.372-.79 4.472-1.417 1.462-2.985 2.818-4.751 3.838-1.821.399-4.178 1.468-4.021 3.68-.203 1.854 2.06 3.48.967 5.213-1.955 2.942-5.856 4.253-7.045 7.752-.765 1.321.693 2.547 1.52.986 1.038-1.546 1.832-3.377 3.725-4.087 2.173-1.166 4.551-1.876 6.913-2.541 1.447-1.137 1.521-3.208 1.869-4.888.395-2.161.292-4.406.831-6.533 1.206-2.379 4.308-2.668 5.896-4.706 2.043-2.251 4.028-4.708 6.893-5.958 1.772-.553 3.489-1.198 3.743-3.323.288-.74.578-1.479.873-2.217z"/>\n' +
    '    <path fill="url(#moonTexC)" d="M526.698 258.167c3.346-7.127 5.086-10.586 0-15.671-5.218-5.219-11.753 3.792-11.753-7.835 0-4.709 6.694-7.186 7.836-11.753 1.112-4.453-3.51-12.244-5.224-15.671-3.275-6.55-3.338-7.255-7.836-11.753-5.478-5.478 1.062-21.304 4.57-24.813 2.79-2.789-68.9-38.439-164.547-13.711-.981.253-6.351 16.798-7.183 17.63-4.76 4.76-6.901 5.042-5.223 11.753.169.677 23.129-2.54 24.898-2.54 6.53 0 7.778 3.154 14.279 2.54 29.504-2.771 124.634 16.573 133.205 19.589 6.578 2.314 4.602 7.773 2.611 11.753-1.222 2.444-10.007 6.09-11.753 7.835-1.146 1.146 4.485 8.008 2.612 11.753-2.456 4.91-5.804 5.95 0 11.753 4.231 4.232 4.942 7.554 9.141 11.753 2.279 2.277 13.71-2.349 14.367-2.612z"/>\n' +
    '    <path fill="url(#moonTexD)" d="M385.791 330.104c-.23.459 6.591-1.648 7.835-1.959 3.177-.794 4.939-2.143 7.183-3.265 4.573-2.287 1.736-1.306 4.571-1.306 3.112 0 4.379-.924 5.876-3.917 1.102-2.203 3.146-3.799 4.571-5.224 1.33-1.33 3.236-1.952 5.877-2.612 3.797-.95 4.995-.425 7.183-2.612 2.905-2.906-5.906-2.612-7.183-2.612-3.903 0-4.983.997-3.918-3.265.05-.2-8.412-1.287-8.488-1.306-2.63-.658-4.637-3.625-6.53-4.571-2.811-1.406-4.564-2.61-7.183-3.265-3.073-.769-3.353-4.269-3.918-6.529-.838-3.353 2.266-5.292 3.918-6.53 2.613-1.96 3.788-1.764 7.183-2.612 3.691-.923 6.389.512 7.835 1.959 2.013 2.013 5.168 1.944 7.835 2.612 2.141.535 1.18 3.917 5.877 3.917 5.14 0 5.384.814 6.529 1.959.884.884-13.618 5.877-1.958 5.877 3.296 0 2.744 3.917 7.183 3.917 3.146 0 4.431-1.924 7.183-2.612 1.565-.392 2.948-5.263 3.265-6.53.653-2.61 2.597-3.856 3.265-6.529.75-2.999 1.923-4.425 2.612-7.183.727-2.908 2.611-3.593 2.611-7.183 0-3.68.907-5.352 3.266-6.53 2.187-1.094-.586-2.995-1.307-5.876-.873-3.495-1.833-5.373-2.611-8.489-.703-2.811 1.741-5.441 2.611-7.183 1.889-3.775-2.797-4.78-4.57-5.224-1.452-.363-3.777 3.393-5.877 3.918-3.147.786-2.611-3.188-2.611-6.53 0-3.836-1.366-4.601-3.918-5.877-2.229-1.114-4.814 1.959-7.836 1.959-1.961 0-3.426-4.079-4.571-5.224-1.578-1.578-1.987-4.685-2.612-7.183-.897-3.588-3.06-2.612-7.183-2.612-1.074 0-4.483-2.753-6.53-3.265-2.83-.708-5.517-1.379-7.835-1.959-2.601-.65-4.19-1.864-7.183-2.612-2.736-.684-5.946-.636-8.488 0-.727.181-4.01-3.357-4.571-3.918-4.189-4.188-14.1 1.5-16.324 2.612-2.26 1.13-4.964 2.058-7.183 2.612-3.242.811-5.08 2.867-7.183 3.918-2.231 1.116-3.983 2.954-5.224 3.265-.469.118-5.247-2.944-6.53-3.265-3.079-.77-6.388-.653-9.795-.653-3.351 0-4.907 1.474-7.183 2.612-1.938.969-3.109 4.261-3.918 5.876-1.426 2.851-2.612 2.824-2.612 7.183 0 3.062 2.146 3.823 1.306 7.183-.64 2.562-5.796-2.245-6.53-2.612-.235-.118-4.228 4.322-5.224 4.571-2.48.62-2.788 4.622-3.265 6.53-.538 2.15-3.377 4.03-4.571 5.224-2.074 2.075-3.472 3.68-4.57 5.876-1.905 3.809-3.925 1.276-4.571-1.305-.511-2.043 4.014-5.225-1.959-5.225-2.303 0-4.656 2.329-6.53 3.265-3.16 1.58-4.782-2.802-5.224-4.571-.538-2.15 1.863-5.685 2.612-7.183 1.396-2.791 1.148-5.244 1.959-8.489.588-2.351 2.523-2.258 3.265-5.224.852-3.406 5.264.733 2.612-4.571-1.47-2.939 2.447-7.885 3.265-9.794 1.441-3.364 3.427-2.734 6.529-1.959 2.848.712 6.842 0 9.794 0 4.255 0 4.527-1.262 6.53-3.265 1.713-1.712 4.134-3.046 5.877-3.917 3.128-1.564 5.016-.445 7.183-2.612 1.362-1.363 5.441-2.394 7.183-3.265 2.707-1.354 5.006-1.251 7.835-1.959 2.533-.633 5.209-1.625 7.183-2.612 3.235-1.618-4.053-3.788-4.571-3.917-2.773-.693-4.8-1.306-8.488-1.306h-9.795c-3.178 0-5.215 1.302-7.835 2.612-2.403 1.202-4.657 1.817-7.835 2.612-2.743.686-4.543 1.952-7.183 2.612-2.255.563-2.204 3.04-6.53 1.959-1.917-.479-4.553 1.959-7.835 1.959-3.44 0-4.505-1.696-5.224-4.571-.823-3.292 1.945-4.517 2.612-7.183.687-2.745-5.305 1.999-7.836 3.265-2.505 1.253-4.533 2.266-6.529 3.265-2.71 1.355-4.167 2.208-5.877 3.917-3.488 3.488-10.404 7.911-16.324 10.448-2.312.99-3.644 2.801-5.877 3.917-1.985.993-3.269 3.922-4.571 5.224-2.15 2.151-3.407 3.549-4.571 5.877-1.505 3.01 3.818-.929 4.571-1.306 4.707-2.354 2.117 1.148.652 2.612-1.995 1.996-3.542 2.889-5.224 4.571-1.995 1.996-2.889 3.543-4.571 5.224-2.674 2.675-3.933 1.275-5.224-1.306-.111-.223-4.695 5.349-5.224 5.876-1.586 1.586-3.044 4.35-4.571 5.877-2.596 2.596-3.196 4.227.653 3.265 2.295-.573-2.525 3.179-3.265 3.918-2.649 2.649-1.846-.192-5.224.653-1.869.467-4.163 3.509-5.224 4.571-3.274 3.275-1.708 2.962-1.306 4.571.361 1.444 1.59 1.674-1.305 4.571-1.996 1.995-3.543 2.889-5.224 4.571-1.927 1.926-2.858 3.755-3.918 5.876-1.012 2.023-3.677 3.677-5.877 5.877-.829.829 1.831 2.216 1.306 3.265-1.292 2.583-2.191 4.382-3.265 6.53-1.675 3.349 4.453-6.026 6.529-9.142 1.757-2.635 2.919-4.878 4.571-6.53l5.224-5.223c1.241-1.241.132 3.654-.653 5.223-1.247 2.494-2.789 3.62-3.918 5.877-1.146 2.292-3.42 2.923-4.57 5.224-1.218 2.437-3.387 2.855-4.571 5.224-1.729 3.457-2.667 2.393-1.959 5.224.561 2.244 3.536-3.154 4.571-5.224 1.335-2.672 4.234-.613 5.224-4.571.725-2.902 3.522-3.656 4.571-3.918 1.702-.426 2.868 2.187 4.571 2.612 2.274.569-2.611 4.186-2.611 6.53 0 6.09-3.758-2.602-5.225 3.265-.469 1.877-3.01 4.062-3.917 5.877-1.247 2.493-2.789 3.619-3.918 5.876-1.477 2.953-3.265-5.175-3.265-6.529 0-3.6-.289-1.67-2.612.653-1.616 1.617-2.479 4.957-3.265 6.53-1.191 2.384-.528 6.376 0 8.488.919 3.676 3.822 2.333 5.877 1.306 3.273-1.637 1.959 1.371 1.959 5.224 0 4.445-3.308 1.959-6.53 1.959-1.799 0-3.451 4.942-3.918 5.876-1.11 2.222-.743 6.236-1.306 8.489-.865 3.46-2.958 4.811 1.306 5.876 1.483.371 4.059-5.194 4.571-5.876 1.363-1.817 1.306-5.565 1.306-8.489 0-3.055.653 6.086.653 9.142 0 4.138 3.662 1.959 6.53 1.959 2.481 0-.26 6.073 1.959 7.183 3.704 1.852.128 2.548-2.612 3.917-.459.23 2.612 3.287 2.612 4.571 0 5.645-3.918-1.048-3.918 4.571 0 4.444.538 5.109 2.612 7.183 1.995 1.995 2.889 3.542 4.571 5.224 2.514 2.515.5 5.377-.653 6.53-1.282 1.282-3.028-6.17-4.571 0-.422 1.688 0 6.639 0 8.488 0 3.183-1.738 5.434-2.612 7.183-1.084 2.168-3.167 3.723-3.265 3.918-1.035 2.069 3.536 3.154 4.571 5.224 1.668 3.335 4.434 1.443 5.877 0 3.655-3.654 2.612.274 2.612 3.918 0 4.26 1.995 3.936 4.571 5.224 4.671 2.336 3.917 2.293 3.917 10.448 0 2.882 2.269 4.537 3.266 6.53 1.532 3.063 2.902 2.754 1.958 6.529-.679 2.718-1.38 4.926-.653 7.836.67 2.681 2.906 3.559 4.571 5.224 1.927 1.926 6.442.044 7.835-.653 1.196-.599.654-7.244.654-9.142 0-.726 5.22-3.754 5.876-3.918.322-.08 1.306 6.985 1.306 8.489 0 2.527-4.307 1.058-3.266 5.224.637 2.545 3.918.441 3.918 5.876 0 4.908 3.416 1.155 4.571 0 1.629-1.629 5.585-1.813 7.183-2.612 2.33-1.164-2.336-5.428-2.612-6.529-.653-2.61-2.597-3.856-3.266-6.529-.955-3.821 4.751 1.667 5.224 2.611 1.252 2.505 2.502 3.891 5.225 4.571 2.813.703.32-9.164 1.958-2.611 1.149 4.594 7.093 1.677 9.142.652 1.574-.787 3.03-3.474 5.224-4.57.235-.118 5.209 4.236 5.877 4.57 3.053 1.526-1.231 4.421-1.959 5.877-2.066 4.133 34.373 24.394 35.26 23.507 2.65-2.65 4.983-.894 6.529.652 3.992 3.991 4.765 1.354 8.489-1.305.163-.117-5.523.6-3.265-3.919 1.159-2.317.569-7.012 1.306-8.488 1.176-2.353 2.059 3.037 5.55 2.285 2.774-.598 1.959-2.386 1.959-5.224 0-1.66 3.386-.112 4.244-.327 4.518-1.129 2.102-2.212 5.224-.652.629.315 0 8.738 0 9.794 0 2.435-.715 4.822-1.305 7.184-.426 1.703-3.655-.132-5.225.652-1.292.646-1.959-2.755-1.959 3.919 0 2.913-3.265 2.059-3.265 6.528 0 3.056-2.692 1.96-6.53 1.96-1.971 0 .741-4.483-.653-5.878-2.699-2.698-4.777 1.481-5.224 3.266-.985 3.941 25.11 8.747-.653 5.876-3.059 1.529-1.782 4.271-.653 6.531.62 1.238 74.6-51.771 75.092-76.397.062-3.151 1.999-9.756 2.612-9.143 1.371 1.371 8.247.654 10.447.654 3.41 0 6.583-.667 9.142-1.307 4.82-1.205 1.057-2.674-1.306-3.265-3.974-.994-4.913-.342-7.183-2.611-2.123-2.123-.28-1.679 1.958-3.918.067-.067-2.657-2.764 1.959-3.917 3.013-.754 3.265-5.098 3.265-7.836 0-5.41 2.778.985 3.265 1.959.948 1.898 4.456 3.86 5.876 4.571.025.013 3.141-6.281 3.265-6.53 1.089-2.178 1.958-4.95 1.958-7.835 0-3.871-4.722-1.104-6.529-.653-3.412.853-4.571.443-4.571-3.918 0-4.377.47-4.489 1.306-7.835.15-.6 4.486-3.095 5.224-4.571.794-1.589-3.252-.653-6.53-.653-4.407 0-1.046-3.983 1.306-4.571 3.45-.862 6.169.726 8.489 1.306 1.537.385 1.306-2.986 1.306-4.571 0-2.22 4.376 1.844 6.53 1.306 3.693-.924.802-2.864 5.224-.653 2.203 1.101 5.925-1.983 7.183-2.612 2.896-1.448 4.016-2.098 7.183-1.306 2.428.607 4.497 3.191 5.877 4.571 2.521 2.521 2.022 3.576 5.877 2.612 3.579-.895 6.238.743 8.488 1.306 2.369.592 2.511 3.729 5.877 4.571 3.198.8 5.679-.049 8.488.653 5.366 1.341 3.842-1.459 2.612-3.918-.371-.74 3.176-4.87 3.265-5.224.517-2.068-2.63-3.845-1.959-6.53.055-.217 11.74-1.309 3.918-3.265-2.941-.735-6.135-.588-8.489 0-3.683.921-.641 6.542-5.224 1.959-3.294-3.294 1.414-4.761 3.265-5.224 2.058-.515 7.048-.523 1.307-1.959-3.116-.779-4.885-1.62-6.53-3.265-1.12-1.12-3.033-.922-4.57-1.306 1.088-.653 2.176-1.306 3.265-1.959 1.572-.943 2.137-5.401 3.265-6.53 1.721-1.72 4.169 2.674 6.53 3.265 3.848.962 2.05 3.827.652 5.224-2.992 2.992 3.295 4.571 5.224 4.571 2.179 0 3.266 6.786 3.266 0 0-2.426 2.102-5.224 4.57-5.224 3.931 0 3.468-3.105 2.612-6.53-.955-3.818-3.266-2.846-3.266 1.306 0 4.223-2.884 3.265-5.224 3.265-4.073 0-5.045-.591-5.876-3.918-.653-2.612 5.427.755 7.835 1.959 2.508 1.253-1.618-4.074-2.611-4.571-1.247-.623 3.598-2.28 2.611-3.265-1.768-1.768-6.511 2.276-7.183 2.612-3.547 1.773-4.81-.772-7.183-1.959-2.942-1.471-2.747-2.973-6.529-3.918-3.861-.965-4.622 2.162-5.224 4.571-.582 2.327 4.398 1.478.652 5.224-2.914 2.915-4.635-2.023-5.876-3.265-1.835-1.834-5.552-1.306-8.489-1.306-3.997 0-1.733-4.166-1.306-5.877.487-1.947-3.613-4.267-4.57-5.224-2.833-2.833-3.096-1.331-3.919 1.959-.911 3.649-2.578 4.571 2.612 4.571 4.379 0 1.527 4.35.653 5.224-2.771 2.771-5.104-1.546-7.183 2.612-1.716 3.432-4.789.87-2.612 5.224 1.028 2.056-2.337 2.509-.653 5.877 1.313 2.625 2.565 3.73 3.265 6.529 1.333 5.335-1.78 4.808-5.224 6.53-4.123 2.061-3.918-1.038-3.918-4.571 0-3.306.653-5.409.653-9.142 0-4.478-.943-4.317-4.571-5.224-3.375-.843-6.917.521-8.488 1.306-3.436 1.718-2.347 1.765-5.877 0-2.134-1.067-1.306-5.467-1.306-8.489 0-2.692-5.224 1.306-7.835 1.959-3.451.866-6.914-.52-9.788-1.957zm-69.295 91.939c.631 2.355-3.17 3.247-2.96 4.693.104.797.427 1.542.765 2.265-.421 1.237-1.558 1.987-2.42 2.896-1.036.896-2.109 1.943-2.358 3.353-.41 1.865 2.111 9.242 1.495 11.076-.309.61-3.215-4.759-3.654-5.246-.805-.642-1.569-1.487-2.653-1.601-1.452-.345-3.58-.119-5.017-.533-.923-.557-1.767-2.198-1.881-3.187-.14-1.022-.234-2.109.174-3.083.426-.513 1.156-.583 1.775-.502.641-.12.861-1.022.449-1.487-.471-1.225-.812-3.729-.781-5.094.175-1.253 1.18-2.15 2.121-2.891.941-.703 2.154-1.12 2.797-2.162.332-.464.468-1.159 1.084-1.342.693-.021 1.099.657 1.349 1.209.424.886 1.451 3.434 1.587 4.404-.337.554.433 1.363.974.931 1.099-.726 1.693-1.957 2.66-2.823.788-.493 3.026-1.117 4.494-.876z"/>\n' +
    '    <path fill="url(#moonTexE)" d="M260.395 441.698c.234-.118 5.208 4.235 5.876 4.569 3.052 1.526-1.231 4.419-1.959 5.875-2.065 4.133 4.109 3.264 6.529 3.264 4.037 0 2.137 4.51 1.958 5.224-.774 3.096-1.464 4.253 0 7.181 1.394 2.788-.653 4.048-.653 7.835 0 3.659-2.964 3.264-6.528 3.264-4.052 0-5.58-.801-8.487.652-.565.283-2.932 5.863-3.264 6.529-1.096 2.19-4.166 3.061-5.875 3.917-2.501 1.25.365 7.259.653 7.833 1.056 2.114 3.887 2.553 5.223 5.224 1.791 3.581-.462 5.412 1.958 7.833 2.074 2.075 1.688 3.687 5.223 4.571 3.21.802 1.988-.335 5.875-1.308 2.244-.561 2.934 3.588 4.57 5.224.834.834.651 5.872 1.305 7.181 1.638 3.275 5.365-2.044 6.529 2.611.962 3.85 4.459.71 5.875 0 2.219-1.108.849 4.962 1.958 7.182 1.251 2.5 6.104 2.188 2.612-1.306-1.801-1.801 1.994-4.711 2.611-7.182.244-.976-7.228-1.807-7.834-1.958-4.009-1.002-1.763-4.112-.653-5.223 1.952-1.951 5.649 3.466 5.875 3.917.004.009 2.777-4.569 5.222-4.569 4.795 0 3.449-3.08 1.307-5.224-3.977-3.976-.054-3.21 0-3.264 1.077-1.076 3.888 1.362 4.569 0 2.04-4.08 1.958 3.27 1.958 5.224 0 .791 5.385-.939 6.529-.653 4.53 1.132 4.011-.377 4.57-2.612.123-.489-5.552-3.594-5.875-3.917-2.756-2.755-3.917-1.977-3.917-6.527 0-5.112-.782-5.095-3.265-2.612-2.125 2.126-2.757-.798-4.569-2.611-2.117-2.116-3.265 6.209-3.265 0 0-2.661-1.646-5.251-2.611-7.181-1.119-2.237.282-7.658.653-9.14.896-3.581.802-.653 3.917-.653 1.871 0 3.684-4.336 4.57-5.222 2.65-2.65 4.982-.895 6.528.652 3.99 3.99 4.764 1.354 8.487-1.306.163-.116-5.523.601-3.264-3.918 1.158-2.316.568-7.011 1.305-8.486 1.176-2.353 2.058 3.038 5.549 2.286 2.773-.599 1.958-2.387 1.958-5.224 0-1.66 3.386-.111 4.244-.326 4.516-1.13 2.102-2.214 5.222-.652.628.313 0 8.735 0 9.791 0 2.434-.715 4.821-1.306 7.183-.426 1.701-3.653-.132-5.223.652-1.292.646-1.958-2.756-1.958 3.917 0 2.913-3.264 2.059-3.264 6.529 0 3.053-2.692 1.958-6.528 1.958-1.971 0 .74-4.483-.653-5.877-2.7-2.698-4.777 1.479-5.223 3.265-.985 3.941 4.435 3.332-.653 5.876-3.058 1.529-1.781 4.271-.652 6.527.619 1.24 4.479 3.873 5.875 4.571.972.484 2.531 5.796 3.264 6.527.87.871 2.118-.666 3.264 3.917.51 2.04 1.67.803 2.612 4.57 1.192 4.77-3.251 3.265-5.876 3.265-3.627 0-4.067 2.257-5.223 4.569-1.807 3.612.771 5.742-3.917 4.57-2.748-.688-4.348-4.127-1.959.653.919 1.835-2.095 3.779.654 6.527 2.156 2.157 2.083 3.917 5.875 3.917 4.474 0 5.096-.399 6.528-3.265 1.562-3.123 2.214-3.916 6.528-3.916 7.728 0-1.621 1.35-.653 5.222.687 2.746 5.292 1.779 7.181 1.306 2.489-.622-5.725-5.021 1.306-3.264 3.108.777 4.568.001 6.528-1.958 1.836-1.836 3.608-5.223-.653-5.223-2.91 0-5.909.645-8.486 0-2.936-.734-4.725-1.307-8.487-1.307-4.974 0-.309-2.954.653-3.917.985-.985 1.364-2.64 2.611-3.264 1.185-.592 3.325.531 3.917-.652.486-.973-1.556-1.696-2.612-1.959-2.079-.52 4.36 1.096 5.875 2.611 1.231 1.231 3.666.778 5.223 0 1.159-.58 9.7 3.824 11.098 5.223 1.548 1.549 1.954.004 3.917-1.959 1.226-1.225-1.865-4.847-1.306-2.61.64 2.562 6.539.977 7.834.652 2.468-.616 1.599-3.264 6.528-3.264 3.738 0 5.408-.653 9.14-.653 3.631 0 5.936-1.974 8.486-2.61 2.08-.521.468 5.875 2.612 5.875 1.477 0 3.178-5.137 3.917-5.875 3.09-3.091 3.917-.312 3.917 3.264 0 2.272 4.497-.943 6.529-1.959 2.612-1.307.637-5.906 0-7.182-1.771-3.54.759-4.054 3.917-3.264 2.36.59.415-5.46-1.306-7.181-1.466-1.468-5.612-2.22-7.181-2.612-2.936-.733-5.79-.368-1.306-2.611 3.667-1.832 3.036-6.273-.653-8.485-2.059-1.236 1.478-5.568 1.958-6.529 1.387-2.773 2.825-3.478 4.57-5.223 1.994-1.994 3.541-2.889 5.222-4.57 1.871-1.869 1.958-4.801 1.958-7.834 0-2.584.654-5.755.654-9.14 0-.59-4.204 2.756-5.223 3.266-3.071 1.534-4.763 1.534-7.834 0-3.126-1.564-3.777-2.705-4.57-5.877-1.136-4.546.925-5.127 3.917-5.875 2.507-.628 3.642 5.429 3.917 6.528.773 3.091 0-3.976 0-5.875 0-2.685-2.461-4.925-3.264-6.529-1.167-2.334-3.036-3.207-5.875-3.917-3.06-.765-4.101-2.326-5.223-4.569-1.359-2.719-1.305-5.026-1.305-8.487-6.488 3.245-9.544 3.316-16.983 3.316-2.083 0-4.5-9.139-4.922-10.827-.98-3.923-2.953-8.314-2.953-12.796 0-3.464-4.327-7.28-5.906-8.858-3.368-3.369-4.798-3.937-10.827-3.937-4.602 0-5.533 4.549-7.874 6.89-3.25 3.249-6.478 1.306-7.875 6.89-1.043 4.173-3.204 6.409-4.921 9.843-1.992 3.985 2.123 4.058.984 8.613-.994 3.975 6.852 4.582-7.136 13.779-3.575 2.352 4.419 10.188 2.707 12.304-2.538 3.137-4.779 1.022-6.397-2.214-2.023-4.047 3.108-3.027-1.477-3.938-4.045-.803-11.793-6.144-15.256-7.874-4.249-2.125-5.495-2.953-11.812-2.953-5.846 0-10.789 1.458-15.256 3.69-1.315.661-4.526 4.336-2.908 7.659z"/>\n' +
    '    <path fill="#E5E5E6" d="M587.151 321.813c2.188 1.132 4.009 3.036 6.439 3.644 2.619.28 3.914-2.522 5.904-3.663.899 1.126-1.046 3.584-1.17 5.202-.213 1.497-1.714 5.073.726 5.138 2.001-.898 3.566-2.592 4.921-4.275.741-1.779 2.657-3.612 4.273-1.506 1.269 2.16 3.362.856 4.763-.426 1.357-1.244 2.344.13 1.211 1.337-1.173 2.31-3.035 4.479-3.11 7.174-.12 1.528 2.011 3.312 2.016 4.078-1.158-.013-3.521-1.511-3.989-.612 1.423 2.25 3.341 4.19 5.395 5.872 1.08.468 4.525 2.334 1.824 2.934-1.379.656-3.776-.807-4.513.593.477 2.497 2.123 4.572 3.224 6.824.497 1.07 1.704 2.94 1.409 3.603-1.373-.718-3.483-1.476-4.63.012-1.321 1.909-.091 4.48-1.265 6.408-1.766-.479-2.149-3.031-3.112-4.475-1.639-3.155-2.075-7.037-4.878-9.497-2.489-2.54-4.478-5.521-7.025-8.008-2.798-3.002-3.084-7.24-4.308-10.96-1.114-2.972-3.37-5.445-3.939-8.639-.06-.252-.116-.505-.166-.758z"/>\n' +
    '    <path fill="url(#moonTexF)" d="M451.607 273.185c2.959 1.229 5.066-.574 7.836-1.959 2.677-1.338 2.434-3.057 5.877-3.918.793-.198 1.216-8.132 1.306-8.488.909-3.638-.222-5.658 3.265-6.53 2.51-.627 3.64-2.986 5.224-4.571 1.462-1.462 6.592 1.306 8.488 1.306 4.284 0 5.822-.055 7.836 1.959 2.257 2.257 3.923 2.45 7.183 3.265 3.725.931 4.671 2.662 7.183 3.918 2.155 1.077 7.88-.338 9.142-.653 4.3-1.075-2.555-5.848-2.612-5.876-2.396-1.199-4.342-2.384-5.876-3.918-2.651-2.65-2.612-1.649-2.612-3.265 0-.676-7.39-2.07 0-3.918 4.123-1.03 5.528-.348 2.612-3.265-2.479-2.478 5.773.601 5.876.653 3.036 1.518 3.984 2.025 5.877 3.918 1.802 1.801-6.043.333-7.183 2.612-.811 1.622 4.256 4.255 5.224 5.224 3.258 3.257-2.086 4.376 3.918 5.877 1.394.349 4.743-4.744 5.224-5.224 2.923-2.923 3.918-1.596 3.918 1.306 0 2.539-4.407 3.959.653 5.224 3.587.896 3.624 2.479 2.611 6.53-.752 3.011-1.959 4.148-1.959 8.488 0 2.689 2.887 3.987 5.224 4.571 3.608.902 3.879.827 7.183 0 2.543-.635-1.288 5.293-.652 7.835.61 2.443 4.588 3.936 5.876 5.225 2.333 2.331-1.884 4.534-3.265 5.224-3.316 1.658-1.61 4.614-.652 6.53 1.384 2.767 2.728 3.497 3.917 5.876 1.716 3.43 3.421 4.166-1.306 6.53-1.569.784 1.894 3.982.653 5.224-2.766 2.765-3.846 1.449-1.959 5.224.473.945 5.32-.653 7.835-.653 2.434 0-.217 5.006-1.305 7.183-.725 1.449 6.149 0 7.182 0 2.66 0 5.194 3.143 3.918-1.959-.856-3.427-.532-5.465.653-7.835.255-.51 6.852 4.24 7.183 4.571 1.847 1.847 1.168-5.5 0-7.836-1.086-2.172-2.074-5.684-2.611-7.835-1.271-5.081 4.225-1.152 5.224-.653 3.332 1.666 5.876-.411 5.876 4.571 0 2.303-2.611 3.871-2.611 7.183 0 3.1 4.498 1.306 7.183 1.306 3.222 0 3.918 2.492 3.918 5.877 0 2.313-4.663 2.326-5.224 4.57-.199.795 3.581 4.533 3.917 5.877.712 2.848 0 6.842 0 9.794 0 2.752 3.862 3.265 6.53 3.265 2.5 0 5.816 1.455 7.835 1.959 1.284.321-.632 3.597.653 3.918 5.669 1.417 3.951-.133 3.265 2.612-.103.411-1.294 6.952 2.915 6.952 3.025 0 7.319 1.756 9.491 2.842 1.545.772 0 6.829 0 7.183 0 2.771 4.639 3.399 5.877 5.876 1.321 2.643 2.15 4.3 3.265 6.53 1.288 2.576 2.283 4.242 3.918 5.877 2.481 2.481 3.146-1.306 7.183-1.306 2.743 0 3.265 4.274 3.265 5.224 0 2.347-4.221.7-1.959 5.224 1.068 2.136 2.073 5.028 2.612 7.183.733 2.937 1.306 4.726 1.306 8.49 0 3.737.653 5.407.653 9.14 0 3.787-1.307 5.37-1.307 9.143 0 3.815-.689 6.021-1.306 8.488-.845 3.379-1.34 4.639-2.611 7.184-1.281 2.562-2.146 4.29-3.266 6.529-1.306 2.611-2.432 5.47-3.738 8.081-1.332 2.665-2.958 3.422-4.02 5.545-1.021 2.045-1.553 6.641-2.036 8.575-.684 2.732-2.159 4.569-5.224 4.569-1.358 0 .507-8.196-2.612-1.958-1.317 2.636-.317 6.513-1.306 8.488-.903 1.809-4.521 3.239-5.877 3.919-2.507 1.253-4.23 1.873-7.183 2.61-.612.153.653-3.396.653-6.529 0-4.569-2.342-2.837-3.265-6.53-.571-2.282 4.159-4.69 5.224-5.224 2.663-1.332 5.877.277 5.877-3.916 0-5.716 1.726-2.556 2.959-.088.231.463 6.246-3.443 6.246-5.094 0-2.512 4.379-4.26 5.276-6.055 2.188-4.377-8.061-6.287-9.911-4.437-2.726 2.725-2.646 2.922-5.877 1.307-3.021-1.511-1.193-3.727-4.57-4.57-.054-.014-4.678 4.676-5.224 5.224-2.804 2.804-5.203 1.263-3.265-2.612 1.696-3.396.877-3.693-1.307-5.877-2.649-2.65-.826-5.051.653-6.529 2.17-2.171 3.819-2.637 6.529-1.959 1.946.486-2.776 3.02-4.57 3.918-3.95 1.976 5.519 3.265 6.529 3.265 3.116 0 .245-6.938-1.959-9.142-2.243-2.243-2.57-3.306-5.224-.652-.953.952-4.124.858-5.877 2.611-3.124 3.123-5.224-.677-5.224-3.265 0-3.164 2.826-3.972 5.224-4.57 1.805-.451 3.909.644 5.225 1.958 1.636 1.636-2.936-3.588-4.571-5.224-2.342-2.342-2.408-3.102-3.265-6.529-.835-3.341 3.068-3.918 5.877-3.918 2.932 0 2.611 4.369 2.611 7.183 0 4.703 1.13 2.663 1.959-.653.962-3.848-1.028-5.974-1.959-7.836-1.199-2.396-2.804-3.648-3.918-5.877-1.329-2.657 2.707-5.971-1.306-1.958-3.462 3.461-1.737.863-.653-1.307 1.229-2.456 2.21-4.42 3.266-6.53 1.099-2.198 1.996-4.72 2.611-7.183.515-2.061 2.988-4.017 3.918-5.876 1.229-2.459.944-5.808 1.959-7.836 1.598-3.195.41-5.467 3.265-2.612 2.021 2.022 1.822 5.12 1.306 7.183-.885 3.541-.563 4.749 0 5.877 1.14 2.279 1.994-4.711 2.612-7.183.634-2.537.694-6.042 1.306-8.489 1.122-4.489-1.218-5.898-3.918-5.224-1.478.37 0 3.047 0 4.571 0 2.188-4.573-1.631-6.529-.653-1.829.914-2.544 5.087-3.265 6.53-1.389 2.776.015 5.286.652 7.835.465 1.86-1.099 1.72.653 5.224.866 1.732-5.857.019-7.183-1.306-3.884-3.883-1.521 4.142-1.306 4.571 2.878 5.757-2.889 1.683-3.918.653-1.772-1.772-3.854-1.243-5.877-3.265-.778-.778-3.771 2.085-5.876 2.612-3.895.974-2.602-3.22-3.266-5.876-.598-2.389-4.122-3.021-5.224-5.224-2.531-5.062-3.161-.237-2.611 1.958.879 3.517 1.959 4.569.652 7.183-1.874 3.75.211 5.171-5.224 6.53-1.442.361-2.953 5.283-3.265 6.53-.836 3.346-.139 4.672.652 7.836.307 1.224-2.09 5.097-2.611 7.184-1.004 4.015 1.22 5.979 2.611 7.835 2.035 2.713 2.112 4.611 1.307 7.836-.592 2.367 4.941 2.612 7.183 2.612 4.274 0 5.825-.053 7.836 1.958 2.755 2.756-1.039 4.639 3.917 5.877 4.024 1.006 3.697 2.382 4.57 5.876.265 1.056-2.589 5.833-3.264 7.184-1.058 2.114-.209 7.361-.653 9.142-.89 3.559-1.21 4.84-1.959 7.835-.212.852 2.612 4.587 2.612 7.183 0 3.646.057 4.456-1.307 7.183-1.408 2.816-1.094 4.354-4.57 5.224-4.755 1.188-1.387-3.104-.653-4.571 1.435-2.868 1.179-4.715 1.959-7.835.59-2.361-5.441-1.088-6.53-3.265-.026-.055-3.082 3.173-4.569 3.918-1.771.885-3.83-4.481-4.571-5.224-5.919-5.919-4.054-.136-1.959 1.958 2.21 2.211 1.995 4.428 1.306 7.184-.638 2.551-5.323-1.405-7.183-3.266-3.099-3.099-3.534-1.689-.653-4.57 2.223-2.222 4.89-.686 3.919-4.571-.552-2.203-4.326 2.511-6.53 1.96-1.964-.492-5.326-10.466-.653-23.507.738-2.06 5.497-.216 7.453-1.194 2.264-1.132 3.081-1.135 4.953-2.071 3.752-1.875-.671-4.979-.731-5.224-.18-.717-3.728-3.807-4.491-4.57-2.246-2.245-.344-3.885-2.612.652-1.251 2.502-2.623 3.921-5.224 4.571-3.332.832-3.266-3.573-3.266-6.529 0-4.551-1.148-5.721-3.264-7.836-1.696-1.696-4.495 1.45-6.53 1.959-4.388 1.097-3.918-2.439-3.918-5.877 0-2.426.626-5.986 0-8.488-.339-1.357-3.71.653-5.224.653-2.313 0 2.936-3.588 4.57-5.224 2.448-2.447 8.489-3.287 8.489-7.184 0-3.461.016-5.91.652-7.183 1.14-2.279-4.903-1.473-7.183-2.612-2.81-1.405-2.544.517-3.918 3.265-1.247 2.494-4.799-2.84-5.224-3.265-3.476-3.476-1.032-1.959-6.529-1.959-.636 0-2.958.959-5.224-1.306-2.602-2.602-.7-4.559 1.959-5.224 3.853-.962 4.535 3.779 4.57 3.918.618 2.472 1.994-4.711 2.612-7.183.152-.61-5.502-6.904-7.836-4.571-1.547 1.547.325 4.573-.652 6.53-1.14 2.279-2.612-4.635-2.612-7.183 0-2.632-4.231-2.011-3.265-5.877.423-1.689 2.196-3.623 1.306-7.183-.354-1.419 5.524.552 4.571-3.265-.606-2.426-2.596-4.554-3.918-5.876-2.491-2.491-4.011-2.894-3.265-5.877.476-1.902 5.513 2.537 2.611-3.265-1.16-2.32-2.832-4.359-3.918-6.53-1.516-3.032-2.611-2.86-2.611-5.876 0-1.755 3.982.588 5.224-.653 2.521-2.521 2.113-2.956 3.918.653 2.231 4.463 2.611-.219 2.611-3.265 0-3.698 1.733-4.891 3.918-6.529.871-.653 1.843 2.728 2.612 1.959 2.312-2.312 2.71-2.612 5.876-2.612 1.154 0 2.85 4.779 7.183 2.612 2.774-1.387-.191-4.685-.652-6.53-.105-.424-6.058 3.029-6.53 3.265-4.755 2.377-2.784-2.265-1.959-3.917 1.846-3.69-2.561-.503-4.57 0-2.937.734-4.726 1.306-8.488 1.306-2.823 0-4.332-1.899-7.183-2.612-3.526-.881-5.514-1.777-8.489-3.265-2.278-1.14-4.678-2.639-7.183-3.266-2.61-.652-3.856-2.596-6.529-3.264-3.94-.985-.445 2.819.653 3.918 2.825 2.826-2.89 3.265-5.224 3.265-4.664 0-.854-3.817.652-4.571.106-.053-4.598-3.292-5.876-4.571-2.835-2.835-2.239-1.49-3.266 2.612-.506 2.024-4.86-.147-5.876 3.918-.451 1.804-3.42-1.508-5.224-1.959-2.282-.57-1.745 4.132-5.224.652-2.514-2.512-2.695-2.944-3.266-5.223-.694-2.778 5.626 1.306 8.489 1.306 2.62 0-.52-5.492.652-7.836.824-1.647-.514-6.432 0-8.488.277-1.111 3.194-2.982 3.918-5.877.569-2.274-.905-4.216-.653-5.224.295-1.178 2.612-2.85 2.612-7.183 0-3.343-4.168-1.305-7.183-1.305-4.772 0-.793.372-3.265-4.571-1.319-2.655-.44-4.44.658-7.189z"/>\n' +
    '    <path fill="#504F55" d="M643.58 324.117c-2.555-5.11 3.838 10.781 5.224 16.324 1.378 5.514-4.838.779-5.877 0-.261-.196 2.01 6.58 2.612 7.183 2.101 2.101-.919 5.49-1.959 6.529-1.325 1.325-3.097 4.235-3.918 5.877-1.368 2.736-4.453 1.959-7.836 1.959-4.417 0-4.88-2.92-7.183-5.224-2.334-2.334-.521-4.179-3.918-5.877-2.413-1.207-4.3-4.03-5.224-5.876-1.228-2.458-2.762-3.565-3.918-5.877-2.182-4.364-.471-2.521 1.959-1.306 4.965 2.482 1.217-3.444.653-4.571-1.881-3.762-3.844-2.107-1.959-5.877 1.388-2.775 4.352-2.395 0-4.571-2.827-1.414-3.918-2.229-3.918-5.876 0-2.638 2.52-4.479 3.918-5.876 2.271-2.27 2.209-4.821 3.918-6.53 1.979-1.978 4.518-1.959 7.836-1.959 3.987 0 4.859 2.104 7.183 3.264 2.029 1.015 3.243 3.897 4.57 5.225 1.995 1.995 2.236 4.195 3.918 5.876 2.591 2.59 3.253 6.353 6.529 10.447"/>\n' +
    '    <path fill="url(#moonTexG)" d="M519.516 200.053c-3.146.786-1.576 6.641-.652 8.488 1.54 3.08-2.456.528-4.571 0-2.916-.729-3.198-2.302-7.183-1.306-4.358 1.09-5.887-.675-3.917 3.265 1.827 3.655-4.241 1.144-5.225.653-2.042-1.021-4.57-6.288-4.57 0 0 3.211-2.711 3.943-5.224 4.571-4.153 1.038-4.57.434-4.57 5.224 0 4.235-.775 3.53-3.918 1.959-2.669-1.334-4.676-1.169-7.836-1.959-2.892-.723-4.863-1.452-7.183-2.612-2.779-1.39-2.382-3.044-5.877-3.918-2.724-.681-4.562-2.281-6.529-3.265-2.58-1.291-4.104-2.144-6.529-4.571-2.901-2.9-3.603-3.581-1.307-5.876 3.277-3.278-.621-1.967-3.265-1.306-2.416.604-3.746 2.568-6.53 3.265-3.735.933-4.396.047-5.224-3.265-.921-3.686 2.883-3.455-1.959-5.876-2.712-1.357-7.661-1.099-11.1-1.959-3.402-.851-4.875-1.872-7.836-2.612-3.05-.762-5.586-.726-8.488 0-2.768.692-4.472 2.587-7.183 3.265-2.569.643-4.586 2.293-6.53 3.265-2.848 1.424-6.029.25-7.835-.653-3.148-1.575-5.123.276-7.183 1.306-4.158 2.079-2.677.718-.653-1.306 2.171-2.171 2.793-2.331 6.529-3.265 5.133-1.283-2.648-1.306-4.571-1.306-4.083 0-4.866 1.217-7.835 1.959-4.336 1.084-3.628 1.125-5.876 0-1.846-.922 11.158-2.82.653-4.571-3.892-.648-.625-3.946.652-5.223.927-.926-6.041-.653-7.835-.653-5.112 0-5.306-.736-6.53-1.959-.292-.292 8.001-1.306 9.142-1.306 2.866 0 5.644-1.412 7.835-1.959 4.057-1.014.414 3.265 5.877 3.265 1.062 0 3.312-4.094 5.224-4.571 2.22-.555 3.377.788 6.53 0 3.55-.887 5.118.706 7.835-.652 2.019-1.01 3.045-3.481 5.224-4.571 3.583-1.792 1.645 3.967 1.959 5.224.234.939 4.427 2.037 5.224 5.224.569 2.275 4.255-3.18 6.53-2.611 2.243.561 2.667 1.619 6.529.653 2.538-.635 6.041-.694 8.489-1.306 3.268-.817 1.581-4.296 5.224-.653 2.676 2.676-.427 4.357 3.918 6.529 2.458 1.23 5.687 1.422 7.835 1.959 2.739.685 4.359 2.179 6.53 3.265 2.292 1.146 2.923 3.42 5.224 4.57 4.731 2.367 1.524-3.046.652-3.917-3.361-3.362 2.202-4.143 3.918-4.571 2.766-.692 9.907 5.28 12.406 6.53 2.503 1.251 4.997-3.039 5.877-3.918 1.25-1.25 7.382 1.029 8.488 1.306 3.93.982 6.043 1.97 5.224-1.306-.638-2.551 6.545.713 7.183 3.265.294 1.175 7.423.653 9.142.653 1.17 0 4.902 3.43 5.877 3.917 3.139 1.57 3.853 2.024 5.877 0 3.575-3.579 3.917 4.312 3.917 5.222z"/>\n' +
    '    <path fill="#4F4F4F" d="M178.667 405.411c-3.776 1.383-4.851 5.996-4.683 9.612.208 3.231-.166 6.796 1.956 9.521 1.497 2.373 5.271 4.114 7.529 1.762 2.571-3.27 1.581-7.694 1.686-11.538.177-4.073-2.078-8.739-6.488-9.357z"/>\n' +
    '    <path fill="#4D4D53" d="M408.185 202.174c.003 2.074-3.209 3.757-7.176 3.759-3.966.001-7.186-1.678-7.189-3.751v-.008c-.004-2.073 3.208-3.756 7.175-3.758 3.967-.001 7.186 1.677 7.19 3.751v.007z"/>\n' +
    '    <path fill="#5D5C63" d="M417.653 394.637c1.565-2.243 3.097-5.206 3.918-8.488.993-3.972 3.586-1.97 4.571 0 1.499 2.997 1.684 4.15 5.224 3.265 4.379-1.094-1.772-4.804-2.612-5.224-3.53-1.765-2.507-3.37-.653-5.224 2.65-2.65 3.775-1.449 5.877.653.941.941 2.125 6.208 2.612 7.183 1.159 2.318 2.018-4.453 1.306-5.876-1.934-3.868 3.768-3.629 5.224-3.265 4.441 1.11 1.015 9.071.653 9.794-1.602 3.202-2.229 3.379-.653 6.53 1.402 2.805 2.747 3.375 1.959 6.53-.163.652-7.762.653-9.142.653-.47 0-4.862 4.209-5.224 4.571-2.353 2.354-1.773 3.708-5.224 4.571-3.114.778-4.62-2.624-7.183-3.265-2.559-.64-3.265-3.841-3.265-6.529.001-2.541.926-3.067 2.612-5.879z"/>\n' +
    '    <path fill="#5F5E64" d="M558.041 257.514c1.213 1.213 5.117-.106 7.183 1.959 3.274 3.274-2.655 5.561-3.917 5.877-3.73.932-3.638-.747-5.224-3.918-.675-1.349-6.365-1.794-2.613 1.959 3.247 3.247.394 2.612-3.917 2.612-.625 0 3.26 3.913 3.917 4.571.544.543-6.99.422-7.835 0-2.942-1.471-3.789-2.098-4.571-5.223-.935-3.739 3.183-3.96-1.959-6.53-.108-.055.475 4.392-2.611 1.306-3.301-3.301 1.767-5.224-3.265-5.224-3.813 0-1.892 2.32-5.224.653-1.582-.792 4.456-5.109 4.57-5.224.803-.802 7.102-1.592 7.836-1.958 2.27-1.135-3.303-4.067-3.918-6.53-.32-1.284 2.634-.332 3.918-.653 1.53-.382 2.328.369 4.57 2.612 1.612 1.612 1.948-5.889 7.184-.653 1.24 1.241-1.895 3.983-.653 5.224 1.863 1.864 3.364 2.097-1.307 3.265-2.196.548 7.244 5.638 7.836 5.875zm94.027 108.392c2.054-2.053.095 6.436-1.959 8.489-2.147 2.147.594 6.41 1.306 7.835 1.39 2.778.248 6.033-.652 7.835-1.311 2.62-2.077 5.697-2.612 7.836-.743 2.972-1.959 3.562-1.959 7.835 0 2.435 5.224.344 5.224 4.571 0 4.095 3.265.206 3.265-3.265 0-2.498-3.265-4.461-3.265-8.488v-9.794c0-1.669 5.808 3.778 2.612-2.612-1.715-3.429 2.937 1.955 3.265 2.612 1.27 2.538-.653-5.65-.653-8.489 0-1.505 2.808-3.135 1.959-6.529-.099-.392-6.234-7.464-6.531-7.836z"/>\n' +
    '    <path fill="#69686F" d="M595.261 477.564c0-2.92-2.612 5.225-3.918 7.836-1.604 3.205.275 3.17 3.265 3.918 3.848.962 4.017 1.763 2.612 4.57-1.179 2.357-2.906 3.853-3.918 5.877-1.375 2.75-4.011 2.962-5.224 3.266-2.361.59 2.177-4.354 3.265-6.531 1.592-3.182-.99-5.896-1.959-7.835-1.362-2.725-1.523-5.006-.653-8.488 1.652-6.611 6.663-2.39 4.571-5.877-.654-1.089 1.959 1.994 1.959 3.264z"/>\n' +
    '    <path fill="#5F5E64" d="M585.466 233.354c-.255-.424-6.003-3.328-7.183-3.917-1.144-.572-3.067-5.679-3.918-6.53-2.198-2.199-4.492-3.552-6.53-4.571-2.67-1.335-2.63-3.274-5.224-4.571-5.075-2.538-3.97 2.506-3.265 3.918.641 1.279 5.644 2.822 6.53 3.265 3.34 1.67 3.573 2.303 1.306 4.571-2.521 2.521.745 6.061 1.307 7.183 1.511 3.022 1.811 3.77 3.917 5.877 3.467 3.467 3.644.104 2.612-1.959-1.263-2.526-3.309-2.698-4.57-5.224-2.173-4.347 1.433-3.886 3.917-3.265 1.37.342 4.396 3.744 5.224 4.571 1.692 1.692 3.247 1.178 5.877.652zm7.182-.979c-2.573-3.725-8.111-9.917-5.877-.979.27 1.076 4.789.761 5.877.979z"/>\n' +
    '    <path fill="#56555B" d="M262.247 565.715c-.704-2.817-5.891-.66-8.488-1.96-3.769-1.884-2.505 3.039-1.959 5.224.901 3.605 8.309 2.612 9.142 2.612 3.395 0 2.51-2.862 1.305-5.876z"/>\n' +
    '    <path fill="#6B6A71" d="M262.9 552.002c3.769 0 3.265-5.925 3.265-9.794 0-3.806-2.442-3.239-3.265-6.529-.93-3.721 6.977.446 7.183.652 1.546 1.547 4.407-1.185 6.529-.652 2.123.53-1.631 4.572-.653 6.529.85 1.699 8.303-1.261 8.489-1.306 3.542-.887-.713 3.978-1.306 4.57-2.378 2.378.139 4.196 1.306 6.529 1.956 3.911-.689 4.58-3.265 5.224-1.477.369-4.093-4.921-4.571-5.876-1.163-2.328-3.729-3.077-5.224-4.571-2.229-2.229-2.866 1.161-3.917 3.266-.742 1.483-3.032 1.342-4.571 1.958zm31.996-5.223c-1.724 3.446 4.748 4.615 7.183 5.224 2.542.636-.018-5.294-.653-7.836-1.538-6.15-4.346 3.048-6.53 2.612zm20.895-2.612c-.792-3.168 4.916 4.417 7.835 5.876 2.276 1.14-1.245-3.886-2.612-4.569-2.274-1.139-1.847-1.307-5.223-1.307zm6.529 13.712c-4.073-2.036-5.699-1.249-4.57 3.265.794 3.175 6.027.289 7.183 0 2.458-.614 4.875 3.09 6.529 3.918 2.622 1.311 6.575-1.328 7.836-1.959 2.109-1.055-3.273-1.636-5.224-2.611-3.114-1.557-4.209-2.612-7.836-2.612h-3.918zm-49.625 16.325c-3.743 0-3.918-2.488-3.918 4.569 0 3.858 2.969 5.102 3.918 1.308.33-1.324 0-4.962 0-5.877zm-37.872-40.485c.472-1.889-5.224.666-5.224 2.612 0 1.44 5.013 3.486 5.876 3.917 1.957.979-.223-4.383-.652-6.529zm0 16.978c2.538 1.27-3.302-4.646-4.571-7.183-2.686-5.373-3.629 1.806-3.264 3.265.849 3.396.057 3.413-1.959 3.918-3.168.792 6.994 1.679 9.794 0 2.086-1.253-1.055 6.592 1.306 7.183 3.913.977 2.563-4.972-1.306-7.183zm7.182-48.32c1.352.338-2.95 1.912-2.612 3.264.347 1.388 5.691 2.847 6.53 3.266 1.051.525 1.788 7.149 1.959 7.836.51 2.043 3.265 6.548 3.265 1.306 0-3.924-1.428-4.813-2.612-7.184-1.669-3.337-2.389-5.175-6.53-8.488zm-53.543-37.872c-1.088-2.178.904 5.939 3.265 6.529 4.547 1.138-3.305-6.73-3.917-9.795-.593-2.964-3.178-4.396-4.571-7.182-2.304-4.607-6.803-.273-1.306 5.224 2.16 2.161 3.556 2.845 6.529 5.224zm37.219-7.836c1.46 1.46-2.882 4.03-1.958 5.876 1.545 3.093 1.511 4.366-.654 6.53-1.246 1.246 2.515 6.266 3.918.653.954-3.814 1.018-4.428 3.917-5.877.533-.266-3.888-4.958-5.223-7.182z"/>\n' +
    '    <path fill="#56555B" d="M167.893 397.249c-4.482 4.039-1.131 8.02-.652 8.979.544 1.088 2.73-1.263 3.591-2.123.915-.915-.546-4.03-.653-4.245-.699-1.395-.12-1.745-2.286-2.611z"/>\n' +
    '    <path fill="#A8A7AA" d="M462.056 240.537c-1.518 2.611-.041 6.866-3.918 7.836-1.287.321-6.54-2.291-7.183-2.612-3.132-1.565-1.663-3.666-5.877-2.612-2.012.503-1.599 7.048-1.959 8.489-.638 2.551-.62 6.01 0 8.488.47 1.878 3.505 4.158 4.571 5.225 2.202 2.202 3.268 3.933 3.917 6.529.607 2.429.849 6.659 1.307 8.489.102.411-2.908 5.814-3.266 6.53-1.929 3.859-5.366-.796-6.529-1.959-1.931-1.932-3.582-3.182-6.529-3.918-2.478-.619-3.834-3.243-6.53-3.917-3.804-.952-5.259 1.446-5.876 3.917-.996 3.982 2.389 4.349 3.917 5.877 2.65 2.649 2.171 3.705 0 5.876-1.985 1.985-6.119-2.854-6.529-3.264-1.567-1.568-2.167-5.4-2.612-7.183-.571-2.284-4.212-2.253-5.877-3.918-2.302-2.302.159-6.848.653-7.835 1.775-3.55 11.172-.653 14.365-.653 2.314 0 2.501-4.189 4.571-5.224 4.212-2.106 2.012-.653-2.612-.653-2.438 0-5.067 2.083-7.183 2.612-4.303 1.076-1.852-4.786-1.306-5.876 1.801-3.603 1.398-4.479-.653-6.53-1.692-1.693-4.859.581-7.183 0-2.102-.526-4.053-3.399-5.224-4.571-2.177-2.177-4.098-2.792-5.876-4.571-2.739-2.739 2.187-4.685 3.265-5.224 2.394-1.197 5.415-1.306 8.489-1.306 4.002 0 4.571-1.417 4.571-5.224 0-3.572-2.52-3.917 3.265-3.917 2.585 0 4.233 2.528 7.183 3.265.875.219 5.277-1.959 7.835-1.959 3.421 0 5.797-.333 6.53-3.265 1.392-5.567 4.485 2.923 4.57 3.265 1.142 4.566.065 4.636 2.612 7.183 2.439 2.441 4.26-3.327 4.57-4.571.896-3.588 5.721 3.295 5.877 3.918.269 1.075.435 2.175.654 3.263zm-38.827 33.954c0 1.803-1.913 3.266-4.27 3.266s-4.269-1.463-4.269-3.266c0-1.802 1.912-3.265 4.269-3.265s4.27 1.463 4.27 3.265zm17.931-15.508c0 2.073-1.316 3.755-2.938 3.755s-2.938-1.682-2.938-3.755c0-2.072 1.315-3.755 2.938-3.755s2.938 1.683 2.938 3.755z"/>\n' +
    '    <path fill="#959595" stroke="#B9B9B9" stroke-width="1.987" d="M483.612 207.774c-.643 2.992-4.801 4.628-9.286 3.656-4.485-.973-7.602-4.187-6.958-7.179.001-.008.003-.016.005-.024.643-2.992 4.8-4.628 9.286-3.656 4.485.973 7.602 4.186 6.958 7.177-.001.01-.003.018-.005.026z"/>\n' +
    '    <path fill="#A7A7A7" stroke="#CACACA" stroke-width="1.6096" d="M541.854 276.732c-2.979 1.869-7.763-.163-10.686-4.539-2.923-4.375-2.879-9.438.101-11.307.008-.005.016-.01.023-.015 2.979-1.869 7.762.163 10.686 4.538 2.923 4.375 2.878 9.438-.101 11.307-.007.006-.015.011-.023.016z"/>\n' +
    '    <path fill="#959595" stroke="#B9B9B9" stroke-width="3.8944" d="M482.685 228.726c-1.309 1.906-4.542 1.884-7.222-.05-2.679-1.934-3.79-5.046-2.482-6.953.004-.005.008-.01.012-.016 1.309-1.905 4.541-1.884 7.221.051 2.68 1.934 3.791 5.046 2.482 6.952-.005.005-.008.01-.011.016z"/>\n' +
    '    <path fill="#CACACA" d="M636.376 452.772c-2.525-1.176-3.222-6.154-1.556-11.119 1.666-4.966 5.064-8.038 7.59-6.862l.018.009c2.524 1.176 3.222 6.153 1.556 11.119-1.667 4.967-5.064 8.037-7.59 6.861-.006-.002-.012-.005-.018-.008z"/>\n' +
    '    <path fill="#CDCDCD" d="M616.513 501.579c-2.306-1.563-2.198-6.59.238-11.226 2.438-4.637 6.282-7.126 8.588-5.562.005.003.01.006.016.011 2.305 1.563 2.198 6.59-.239 11.226-2.438 4.637-6.281 7.126-8.587 5.562-.007-.004-.011-.008-.016-.011z"/>\n' +
    '    <path fill="#E7E6E8" stroke="#A9A9A9" stroke-width="3.6222" d="M471.494 338.724c0 1.782-1.446 3.227-3.23 3.227s-3.229-1.445-3.229-3.227c0-1.782 1.445-3.227 3.229-3.227s3.23 1.445 3.23 3.227z"/>\n' +
    '    <path fill="#ECECED" d="M475.548 475.469c-.594.866-1.4 1.554-2.127 2.303-1.019 1.037-2.087 2.069-2.792 3.357-.331.615-.566 1.458-.104 2.069.473.497 1.246.445 1.862.324 1.898-.416 3.548-1.994 3.857-3.942.29-1.704-.305-4.522-.696-4.111z"/>\n' +
    '    <path fill="url(#moonTexH)" d="M503.197 524.125c3.516-1.758 5.646-3.484 8.311 0 2.204 4.407-6.242 5.716-9.234 6.464-4.042 1.011-6.655 2.866-10.158 4.617-4.294 2.146 1.714-3.626 3.694-4.617 3.315-1.658 3.871-4.706 7.387-6.464z"/>\n' +
    '    <path fill="#ECECED" d="M535.979 412.85c2.662-1.316 3.336 6.583-.448 8.764-3.374 3.715-10.197 3.712-11.526 9.312-1.56 4.185-3.189 9.195.749 12.688 1.973 4.256 6.633 4.292 10.664 4.752 5.507-1.023 6.696 4.977 9.466 8.354 4.044.244 8.018-12.092 13.259-4.255 3.51 2.524 2.734 10.038-.955 11.315-3.38 1.647-8.065-1.36-9.472 2.588.14 3.537-3.164 9.245-3.046 14.409 2.953 3.326 1.326.8 1.623 5.745 1.534 3.314 4.914 7.646 1.072 8.859-4.023 2.249-5.001 8.725-8.89 11.181-3.266 3.458-7.684 5.468-10.998 8.865-2.616 1.944-8.619 5.188-10.786 2.668 2.554-3.571 6.203-6.232 9.408-9.198 3.537-2.001 6.233-5.109 9.718-7.149 3.269-1.694 6.409-6.514 3.82-9.906-2.853-2.842-8.609-3.208-9.031-8.058-1.72-4.97 3.93-7.988 3.527-12.758.2-2.426-2.192-10.088-3.685-4.281-.312 2.951-3.918 4.858-4.471.85-2.041-4.466-.912-10.565-4.767-14.048-3.901-.643-8.001.842-11.98 1.033-5.144 1.29-6.859 6.745-9.396 10.719-4.928 2.938-4.741-6.681-4.424-9.699.396-3.367-.855-11.975 5.066-8.874 3.473 1.646 10.48 6.905 11.208-.265 1.828-4.962-1.179-9.437-6.37-9.888-5.396-1.465.12-7.737 1.751-10.411 3.024-2.532 3.352-7.377 6.902-9.254 4.311-.866 8.457 1.596 12.701 1.349 2.919-.878 6.386-4.549 9.311-5.407zm-48.806-22.135c-1.656-2.149-8.864-5.655-10.242-5.818-1.914-.125-3.837-.284-5.715-.679-.636.958.022 2.324.136 3.406.511 2.362.813 10.838.177 12.708-.64 1.679-1.164 3.458-1.095 5.269.689 1.439 6.045 5.644 7.636 6.433 1.377.287 1.964-1.34 2.441-2.324.805-1.673 5.2-4.997 6.233-5.732 1.923-1.088 2.177-3.572 2.054-5.559-.098-1.677-1.243-6.988-1.625-7.704zm-14.911 44.299c1.521.286 1.468 2.329 1.138 3.52-.598 2.396-7.362 5.596-7.867 6.679-1.143 3.048-1.6 6.309-1.646 9.551-.096 1.821-6.771 4.893-7.754 3.493-.307-1.657 4.377-10.521 4.588-13.832.408-3.263-1.014-6.334-1.564-9.48-.345-3.726 1.42-7.222 3.144-10.396 1.451-1.126 8.137 7.456 9.961 10.465zm-16.621 45.247c.454-.513.106-1.299-.603-.984-1.773.446-3.404 1.312-5.119 1.932-1.734.624-3.551 1.342-5.429 1.153-.962-.154-1.769-.761-2.618-1.198-1.355-.778-2.729-1.806-3.203-3.368-.264-.883-.54-1.778-.649-2.694.23-.82 1.204-.001 1.665.255 2.354 1.56 4.487 3.521 7.131 4.6 1.15.489 2.42.25 3.631.306 1.73-.002 3.462-.002 5.194-.002z"/>\n' +
    '    <path fill="url(#moonTexI)" d="M373.455 565.217c-4.316-5.397-10.018-14.621-15.699-20.777-7.159-7.098-12.23-15.824-16.479-24.879-4.563-6.491-8.19-18.667-13.455-21.897.493 4.86-6.327 7.824 1.599 10.093 8.236 3.605 4.666 14.505 11.801 18.858 3.832 8.027 8.782 15.603 14.185 22.722 3.734 4.487 14.175 12.303 13.378 15.56-3.523-8.799-13.891-13.574-22.835-13.885-7.023 3.297 7.115 6.92 10.555 5.572 4.524-.016 13.695 8.28 4.371 4.418-5.39 4.113-11.07-4.888-15.561-1.07 1.508 2.783 13.682 4.03 10.583 5.548-3.722.102-12.887-6.322-12.438.22 4.583 2.801 11.945.837 15.332 4.33-2.623 4.032-6.568 3.495-9.951 5.653-.913 6.981 13.314.464 5.872 6.471-8.121 7.939 8.998-.047 8.158.398-4.667 5.515 2.107 3.851 4.154 3.634-3.489 7.554-8.75 13.934-12.962 21.049-4.986 6.107-8.845 13.16-14.775 18.469-2.741 5.896 5.326-2.411 6.81-3.972 8.173-4.972 13.158-13.636 18.123-21.424 2.616-4.497 6.003-10.451 8.85-13.735 4.583 6.507-.388 14.563 1.308 22.51 6.655 4.747-1.721 13.139 1.792 17.423 11.602.562 7.348-12.153 6.991-19.07-1.169-4.422 3.129-8.365 4.655-2.367-.461 4.075 2.629 7.202 2.622 1.39-.144-3.896.439-6.142 1.581-.862 1.078 2.963 7.741 13.989 6.369 4.41.072-4.913-7.769-12.47-1.758-15.862 5.331 3.005 9.539 6.764 4.545 12.511-5.288 2.583 4.499 11.053 7.105 5.371 5.052.93 6.739-3.257 8.421-6.293 5.991 5.275 16.953-.198 20.431-5.247-2.796.368-10.577-3.297-3.191-2.022 3.104 1.261 11.182 2.746 4.858-1.71.83-3.892 10.751.864 14.756.332-.202-5.17-10.871-2.75-7.697-10.43-2.002-4.571-9.768 8.803-9.01-.601-2.658-5.854-5.344 2.594-3.904 5.215-5.221-2.824-4.908 8.758-6.052 1.19 1.559-3.828 5.711-10.9 2.582-14.261-3.253 3.744-10.401 3.083-13.718 1.502 8.132-.117 16.049-2.263 23.785.729 6.614-.073 9.467-4.812 13.354-8.667 5.094.361-2.2-12.731-3.979-5.342-2.277 8.2-9.052 5.462-14.514 2.953-2.063 1.805-10.935 3.871-10.124 1.87 6.525-2.202 15.669-1.415 20.138-5.873-1.257-2.099 4.24-6.322.477-5.848 3.941-5.113 7.074 5.084 10.749-2.155 8.792-2.792 18.548-3.572 26.755-7.573 4.661-4.087 17.271-5.891 17.156-11.707-8.136.648-14.74 10.289-24.09 9.906-8.271 4.286-17.212 8.172-26.747 7.99-3.194.202-12.309.304-4.155-1.848 6.773-.45 16.625.734 20.567-5.996-2.613-5.479-15.197 6.497-16.745.289 6.061-5.329 12.489-9.979 18.676-14.995 4.732-3.267 15.068-7.608 6.969-13.074 4.16-5.981 23.617-4.188 18.102-15.358-9.317-7.55-10.066 9.114-17.48 9.536-3.281-3.068-12.871 1.081-9.853-5.887-6.095-2.11-14.792 9.618-3.614 8.548-.075 4.166 4.531 11.933 7.839 8.251-1.849-6.804 7.676-2.562 1.857 1.445-4.11 2.974-8.808 8.805-13.833 5.026-5.759 2.589-5.865-2.12-2.439-5.144-5.338-2.836-11.563 1.985-11.294-7.315-4.44-.524-13.314 1.189-6.14-5.034 7.572-5.256 8.756-14.642 14.846-21.093 3.974-2.025 6.594-10.055-.321-5.849-4.291 5.386-4.664 18.162-11.975 18.858.365-7.921-6.521 5.819-3.848-2.689 1.883-3.568.389-7.582-1.155-1.804 1.155 6.112-10.096 11.594-5.518 16.134 4.831-.269-3.98 13.463-4.957 3.369-1-2.94-.437-10.446-4.618-4.618-1.328 7.509-8.202 2.664-5.31-3.066-1.424-5.445 4.407-11.603 3.691-15.551-6.803 6.341-5.097-6.776-10.504-4.893-1.978 7.084 10.751 8.551 5.762 16.771-1.917 9.655-2.975-7.609-7.614-.392-6.52 1.278-3.961 13.297-12.632 10.966-1.651-7.29-6.172-3.416-6.364 1.827-4.811.454-5.889-6.859-11.354-2.431-3.701-1.089-6.014-4.104-7.387 1.848-3.561 1.32-10.257-5.184-9.859 3.145.1 6.138 8.746 3.231 8.059 10.194 4.239 2.496 1.05.916.098 2.809 4.674 2.821 11.71 8.532 7.464 14.274-5.745-6.256-13.113-10.939-16.381-19.341-5.85-6.536-6.765-15.819-11.681-22.967-9.508-5.269-3.498-15.064-6.788-23.204-5.078-7.599-11.224 4.387-4.283 7.529 3.955 7.255 7.64 14.141 11.208 21.559 2.156 8.458 7.272 15.512 11.538 22.949 4.81 5.966 11.77 10.53 14.973 17.661-7.019-4.385-10.522-12.279-16.815-17.525-.833-1.891-6.791-11.134-6.224-6.273 4.879 7.839 11.931 13.896 17.221 21.428 1.239 4.169 7.359 6.715 7.009 11.163z"/>\n' +
    '    <path fill="#E3E3E3" fill-opacity=".4464" d="M519.107 237.189c.715 5.257-2.568 10.03-1.281 15.256.03 5.246 1.382 11.216-2.625 15.426-2.794 4.112-3.369 9.062-4.622 13.746-1.248 3.584-2.935 6.991-3.604 10.763-2 4.103-1.861 8.792-3.226 13.099-.713 3.797-.795 7.691-.996 11.54.996 2.47 1.943-3.74 2.55-4.81 1.124-4.575 3.916-8.569 4.741-13.198-.197-4.753 3.559-8.404 4.016-13.083 1.228-3.86 3.346-7.357 4.469-11.259 1.787-4.561 1.316-9.666 2.238-14.44.938-4.766 1.403-9.549 1.653-14.384.89-5.065 1.359-10.184 2.022-15.286 1.395-4.96.965-10.198 2.581-15.092.268-1.716 2.809-8.798.154-4.363-2.094 4.459-3.326 9.315-4.755 14.034-.412 1.262-4.57 6.272-3.315 12.051z"/>\n' +
    '    <path fill="#54535A" d="M379.572 526.432c.003 2.997-3.042 5.428-6.804 5.431s-6.813-2.422-6.817-5.419v-.012c-.004-2.995 3.042-5.427 6.803-5.43 3.761-.003 6.813 2.423 6.818 5.42v.01z"/>\n' +
    '    <path fill="#E7E6E8" stroke="#A9A9A9" stroke-width="3.6222" d="M510.741 397.824c0 1.782-1.447 3.227-3.23 3.227-1.785 0-3.231-1.445-3.231-3.227 0-1.782 1.446-3.227 3.231-3.227 1.783 0 3.23 1.445 3.23 3.227z"/>\n' +
    '    <path fill="#E7E6E8" stroke="#A9A9A9" stroke-width="4.12" d="M529.383 336.877c0 1.782-1.118 3.227-2.497 3.227s-2.496-1.445-2.496-3.227c0-1.782 1.117-3.227 2.496-3.227s2.497 1.445 2.497 3.227z"/>\n' +
    '    <path fill="#959595" stroke="#B9B9B9" stroke-width="3.8944" d="M386.624 289.018c-.025 2.311-2.724 4.087-6.027 3.971-3.302-.118-5.96-2.085-5.935-4.395v-.018c.025-2.31 2.724-4.087 6.026-3.971 3.303.118 5.96 2.085 5.935 4.395.001.006.001.012.001.018z" opacity=".658"/>\n' +
    '    <path fill="#94939A" fill-opacity=".6548" d="M365.321 496.652c0 2.494-1.94 4.517-4.333 4.517s-4.333-2.022-4.333-4.517 1.939-4.516 4.333-4.516 4.333 2.022 4.333 4.516zm-8.773-40.401c0 2.494-1.94 4.517-4.333 4.517s-4.333-2.022-4.333-4.517 1.939-4.516 4.333-4.516 4.333 2.022 4.333 4.516z"/>\n' +
    '    <path fill="#94939A" fill-opacity=".6548" d="M352.854 458.099c0 2.494-1.94 4.517-4.333 4.517-2.393 0-4.333-2.022-4.333-4.517 0-2.494 1.94-4.518 4.333-4.518 2.393.001 4.333 2.024 4.333 4.518zm5.541 33.243c0 2.494-1.94 4.517-4.333 4.517-2.393 0-4.333-2.022-4.333-4.517 0-2.495 1.94-4.516 4.333-4.516 2.393.001 4.333 2.021 4.333 4.516zm-61.871-19.392c.002 2.494-1.936 4.519-4.329 4.521-2.393.002-4.334-2.019-4.336-4.513v-.009c-.003-2.494 1.935-4.518 4.329-4.521 2.393-.002 4.334 2.018 4.337 4.512-.001.004-.001.007-.001.01zm280.096-26.232c.004 2.494-1.932 4.521-4.325 4.525-2.393.005-4.336-2.014-4.341-4.508v-.018c-.004-2.494 1.932-4.52 4.325-4.524 2.393-.004 4.336 2.014 4.341 4.508v.017z"/>\n' +
    '    <path fill="#FBFBFC" d="M186.503 478.218c.336 1.657 2.911 7.126 3.918 9.14 2.586 5.173 4.644-.799 5.224-1.958 1.404-2.809 7.409 4.797 7.835 5.224 2.256 2.256 5.928-.678 7.183-1.306 3.596-1.798 6.671-.725 2.612 1.306-3.13 1.565-2.631 3.88-1.305 6.53 1.324 2.647.993 5.904 1.958 7.835 1.101 2.202-3.293 2.612-5.876 2.612-2.952 0-4.978-2.265-7.183-3.918-1.302-.977-5.648 2.824-6.53 3.266-2.022 1.01-2.432 7.898-3.917 1.958-.461-1.845 3.947-4.253 5.224-4.571 3.885-.971 2.822-3.701-.652-4.57-4.059-1.015-.963-2.956.652-4.571 1.665-1.664-4.212-2.253-5.877-3.918-2.146-2.146-2.512-3.517-3.265-6.529-1.045-4.177 1.162-.718-.001-6.53zm12.406-31.996c-.037-.149 4.555 5.191 5.224 6.529 1.582 3.163 3.224 6.363 3.918 9.142 1.239 4.956-2.463-.504-3.264-1.306-2.799-2.798-4.125-2.509-6.53-1.306-5.555 2.777-1.204-3.367-.653-3.918 2.036-2.035 1.305-5.748 1.305-9.141zm309.007-280.959c53.808 22.229 94.997 60.462 120.806 103.914-7.328.682-12.859-4.829-17.37-10.025-4.231-4.591-3.708-15.087-11.049-15.413-5.378 4.057-.237 10.322 2.389 14.356 3.289 4.571-2.896 9.349-4.617 2.77-3.812-6.331-4.171-14.09-8.613-20.077-3.041-6.467-6.144-13.321-12.685-16.989-5.343-4.146-12.123-6.032-17.863-9.602-7.055-.726 1.502 9.369.023 13.078-3.572 3.954-4.031-11.074-6.256-3.017-.397 2.332-1.587 6.227-1.02 1.282.378-5.264 1.21-11.805 6.831-13.349-.974-8.455-11.815-1.467-15.166-8.363-4.106-4.271-10.546-3.044-14.748-2.38-.239 8.412-7.72-2.375-8.581-5.417-5.144-2.78-10.234-6.616-15.165-9.078-2.229 2.012-13.708 4.459-7.289-.62 2.959-2.251 9.731-6.23 1.975-5.063-6.459-.331-12.93.406-18.438 4.043 2.702-5.091 7.604-10.16 13.997-8.676 5.548.705 14.371 1.143 12.619-7.052.245-1.282-.046-3.044.22-4.322zm-222.751 11.459c48.054-26.008 114.539-42.72 188.796-22.775-6.058 1.171-17.435 8.7-22.853 3.741-4.847-2.704-13.789-1.609-9.842 5.761-4.727 2.558-11.692-5.187-15.875.458-1.48 2.819 6.371 6.957-.949 6.453-7.428 1.681-6.604-9.624-13.896-6.987-8-.567-3.049-9.083 2.776-8.467 4.713-.037 4.647-3.725-.155-3.75-3.809-.835-16.835-4.291-10.54 3.22-2.223 2.466-11.98.75-5.702 5.422 3.29 6.043-9.266 3.78-7.548-1.431-5.156.957-8.095-.94-4.288-4.906-6.471-.418-12.965 1.721-19.523 1.398-8.142 1.355-15.388 6.016-23.712 7.078-11.385-.107.801 12.665-7.663 16.553-5.29 3.269-12.004.393-17.66 2.706-6.664 1.358-13.528 3.116-17.948 8.737-4.112 3.117-15.449 6.67-12.772-2.682.178-2.843-.054-7.702-.646-10.529zM660.76 354.336c-5.235-30.978-17.148-60.346-29.331-80.61-1.081-3.775-9.069-2.529-9.471 1.08-1.463 3.384 4.039 6.424 1.672 9.153-2.737.406-4.576 2.387-1.525 4.358 2.738 2.833 6.504 4.178 9.677 6.376 2.541 1.795 3.982 6.084-.32 6.566-3.365 1.891-2.071 6.982 1.517 7.745 2.953 1.567 6.935 1.06 9.194 3.801 2.176 3.599.13 8.144 2.277 11.794 2.127 3.71 1.418 8.032 2.256 12.054.776 4.569 1.323 9.648 4.999 12.932 2.154 2.702 5.966 3.407 9.055 4.751zm-24.825 154.011c5.346-2.674-7.485 9.43-10.157 14.774-1.835 3.668-3.878 6.647-6.465 9.235-3.132 3.131-3.668 4.604-7.387 6.463-4.653 2.327.302-6.766.923-7.388 2.178-2.177 5.208-5.206 7.388-7.386 2.575-2.576 4.173-5.576 5.541-8.312 1.927-3.855 5.227-5.414 10.157-7.386zm-41.555 26.78c1.658-3.315-4.072 6.652-7.387 8.311-3.84 1.919-5.541 4.759-5.541-2.771 0-3.011 3.856-7.55 5.541-9.235 2.575-2.574 5.575-4.172 8.311-5.54 2.767-1.383.225 6.362-.924 9.235zm18.469-16.622c3.579-1.79-3.646 7.198-4.617 11.081-1.082 4.331-4.674 4.616-9.234 4.616-1.235 0-4.201 7.181-6.464 8.311-5.445 2.724-6.482 5.506-4.617 9.235 2.005 4.009 7.802-3.185 8.312-3.693 2.821-2.821 3.162-5.934 5.54-8.312 2.916-2.914 5.122-5.121 7.388-7.386 1.883-1.883 2.992-7.354 3.693-10.158.297-1.195-.001-2.462-.001-3.694zm-337.773 95.203c71.513 43.37 161.71 49.156 239.937 14.365-5.113-1.547-14.303-4.419-21.348-2.491-6.986.428-9.117 12.522-18.476 8.385-5.576-5.707-14.39-2.368-20.316-6.219-4.028 1.483-8.043 5.667-10.479.864-7.315.277-13.483 5.905-20.471.548-8.878-3.896-10.527 3.889-5.282 6.58-7.601 1.696-16.476-2.413-21.066-8.339 1.176-4.528 1.536-12.32-5.418-9.458.237 6.146-6.003 9.286-9.084 11.509-6.13-7.971-12.492-1.126-17.995 3.249-7.586 1.295-14.452-5.813-22.225-2.103-5.423 2.177-10.456-1.189-9.001-7.157-5.155-.314-17.277-4.928-8.267-10.059-7.488-1.817-15.834-.177-21.977-5.868-2.749-3.061-16.265-5.18-8.413.499 7.091 1.173 11.785 12.778 1.163 10.665-7.72 1.218-14.304-12.095-21.282-4.97zm305.166-201.441c-2.373-.593-5.666-2.42-6.529-5.877-.67-2.677.688-6.017 1.305-8.488.582-2.323-4.858.581-7.183 0-3.106-.777-2.602-3.303-3.917 1.958-.985 3.944.633 5.917-.652 8.489-.701 1.401-4.903 2.944-5.878 3.918-2.819 2.82-.723 5.154.653 6.529.139.14 6.243-3.703 6.53-3.917 2.78-2.086 3.779-1.684 5.223-4.571 1.084-2.167 6.405 4.447 6.53 4.571 2.769 2.768 3.918 2.5 3.918-2.612z"/>\n' +
    '    <path fill="#5F5E64" d="M551.838 202.665c.587-2.025-5.685-6.769-8.815-6.53-3.965.302-.596 4.079 0 4.571 6.393 5.278 9.033 3.047 8.815 1.959z"/>\n' +
    '    <path fill="#98989E" d="M587.922 325.585c.214.853 4.494 3.57 5.541 4.618.953.953 3.546 2.465 5.079 3.232 1.717.858 2.952 2.03 4.155 3.231 1.636 1.637 2.539 3.079 3.694 4.618.956 1.276.923 3.907.923 6.002 0 2.263-1.847 2.746-1.847 5.541 0 1.241 3.393-.742 3.693.462.403 1.611 1.681 3.361 2.309 4.617 1.271 2.54-.041 3.704-1.847 4.155-1.847.462-.039 4.195-1.386 5.542-1.527 1.528-2.389 1.846-5.079 1.846-3.579 0-1.067 2.893 0 3.694 1.635 1.225 1.848 3.043 1.848 4.155 0 3.537-6.742-2.804-6.927-3.232-1.155-2.699-4.133 2.266-4.155 2.309-1.353 2.707-1.944-1.651-1.385-2.77.429-.857 5.535-.692 6.464-.924 1.965-.491-.018-4.191-.924-6.002-.554-1.11 2.491-.763 3.694-.462 1.669.418 1.093-3.4 2.309-4.617 1.759-1.759 1.175-3.694-1.385-3.694-2.958 0-3.601-.549-4.155-2.771-.503-2.008-3.336-1.411-5.079-1.846-2.192-.548-3.532-.993-4.849-2.309-1.469-1.469-2.032-2.129-2.539-4.155-.494-1.979-.969-4.338-1.385-6.002-.698-2.792-.487-3.746-1.386-5.541-1.16-2.321-1.799-2.356 0-4.156 1.812-1.81 2.171-2.48 4.619-5.541z"/>\n' +
    '    <path fill="url(#moonTexJ)" d="M400.12 568.994c2.384 6.351-1.985 13.362-9.76 15.659s-16.009-.988-18.393-7.34c-.004-.012-.009-.023-.013-.035-2.385-6.351 1.985-13.362 9.759-15.66 7.775-2.297 16.01.988 18.394 7.34.004.01.008.024.013.036z" opacity=".5959"/>\n' +
    '    <path fill="#FFF" d="M392.64 571.715c1.175 3.137-.983 6.598-4.821 7.731-3.837 1.133-7.901-.492-9.076-3.629-.002-.005-.004-.011-.006-.015-1.176-3.137.982-6.6 4.82-7.731 3.838-1.133 7.901.491 9.077 3.628.002.007.005.01.006.016z" opacity=".8497"/>\n' +
    '    <path fill="#A8A7AA" fill-opacity=".8671" d="M366.069 322.158c-.101-7.974 2.144-15.792 1.306-23.833-.794-8.732 2.246-17.525.392-26.15-3.447-2.939-2.864 9.408-3.004 12.52 1.808 7.122-3.419 14.418-.05 20.842 2.216 6.096-2.128 14.039-5.705 18.586-10.24.542-2.794-12.763-.994-17.743.141-8.865-1.576-17.525-4.073-25.998-.225-4.173-5.456-16.781-4.401-5.674-.7 7.812 2.601 14.992 3.386 22.619.034 7.83.597 15.996-1.206 23.569-4.315 2.499-17.589-.041-14.093-5.722 9.806-.137-9.788-12.426 2.55-13.517 4.591-1.35 8.656 3.958 8.66-3.862 4.145-11.234-9.304-7.786-14.282-3.765-.887-4.987-.417-15.507-4.072-17.538-4.886 6.749 2.786 13.802.999 21.003-.737 3.133 4.931 6.549-1.324 5.944 1.022 5.643.728 10.97-5.989 12.677-1.973 9.116-11.084 1.424-16.299-.155-2.132-8.894-14.061-1.845-18.235-9.092-2.07-2.94-6.938-3.326-1.922.271 6.105 6.129 16.374 6.998 20.479 15.281-7.084 1.362-13.304-5.708-20.104-7.635-2.443-2.529-16.82-4.788-7.659-.699 7.984 4.779 16.977 7.97 24.228 13.928-2.523 4.07-13.71-.914-19.229-1.771-2.331 1.088 9.356 5.903 12.857 6.549-3.084 1.148-14.691 5.304-6.319 6.012 5.23 1.243 12.796-8.275 15.446-3.25-1.533 4.768-.064 8.5-1.756 12.666 1.465 4.146 14.88 7.849 4.288 8.899-4.387-.437-12.668 2.683-3.947 4.216 5.938-.328 14.508-.256 18.796-1.767-6.475 2.822-13.761 4.573-20.494 6.267-4.075-1.374-15.437-4.762-15.979-1.094 4.734 5.242 15.474 1.583 18.243 6.545-5.087-1.292-16.562-3.791-12.619 5.388-2.24 4.944-3.241 9.644 2.444 12.664.552 7.731 13.596 1.443 10.583 9.245 6.332 1.713 13.163 3.002 15.074 10.86 2.001 2.774 1.182 12.326 6.333 6.184-2.103-6.8-2.342-13.992-6.117-20.251-2.885-6.775 1.982 6.731 4.776 7.294 4.355 5.255 6.086-9.009 10.675-1.71 7.267 2.072-6.516-9.603 2.703-10.311 9.03-1.959 5.874 12.143 5.858 17.656.075 4.156-.144 9.876-5.577 5.346-7.309-2.336-2.103 6.228-6.041 8.826-1.68 6.347 3.607 13.45 8.809 6.546 2.75 7.214 8.295 12.412 14.542 16.293 5.011 4.626 7.007 10.501 4.933 17.075 4.417.285 5.345-7.754 11.027-7.137 3.052-6.285-.052-16.114-6.466-18.593-4.617-4.778 8.564-4.527 6.244-11.335 2.243-3.608.384-16.398-3.596-9.641 3.629 4.937-1.257 12.012-2.773 3.763 3.75-6.366-2.345-9.433-5.396-13.16 2.008-9.299-6.643-15.805-2.412-25.088.514-3.284 5.017 4.59 8.582 2.664 7.105 1.415 14.413.965 21.602 1.639 10.958-.899-5.771-8.514-9.772-7.789-6.451 2.604-13.207.323-18.396-3.808-9.538-5.479 6.425-8.231 10.674-5.043 5.138 1.982 8.301 9.355 12.184 10.359 4.783-4.349 2.034-13.19 8.881-14.702.029-4.161-12.529-8.869-1.787-9.716 7.928-5.463-7.248-6.135-10.589-8.448 1.034-4.571 11.025-9.338 3.633-14.749-5.59-.189-4.217-9.646-9.291-3.646-8.978 5.842-1.004-6.864-1.995-8.671-2.816-1.517-4.926 1.886-7.224 2.867z"/>\n' +
    '    <path fill="#B9B8BA" d="M347.786 338.482c-2.594-3.587-4.685-8.541-9.569-9.346-3.222-1.114-7.298-.535-8.279 3.238-1.469 3.767 5.416 2.5 7.505 3.84 1.777 1.917 4.677 4.635 4.558 7.035-3.301 1.624-6.812 3.212-10.594 3.05-3.709.957-5.428-2.973-6.712-5.66-1.663-3.442-5.334-5.834-5.586-9.906.175-2.566-1.832-8.638-3.936-3.617-1.774 3.098.216 6.314 2.174 8.714 1.981 3.424-4.609 2.916-5.089 5.861-1.692 3.738 1.558 8.062 5.474 8.242 2.031.331 9.141-.182 5.414 2.993-2.109.343-3.77 1.849-.604 2.287 3.204 1.562 10.07-.603 10.026 4.5-2.833 2.681-7.278 4.174-8.196 8.408-1.141 2.345-1.739 7.861 2.466 5.821 2.225-.297 4.1-6.956 3.968-1.924.22 3.356-1.201 7.594 1.715 10.147 2.015.743 5.596-3.708 4.602.835-1.203 2.518-.007 5.191 2.989 3.402 4.245-3.047 3.776 5.618 8.33 4.972 4.489.717 3.286-4.569 1.037-6.366-3.232-.63-4.969-3.314-4.889-6.573-.995-3.276-7.206.615-7.346-3.755.461-2.991 2.152-7.492-2.565-7.653-4.968-1.611 2.33-4.807 4.581-5.176 4.311-.872 9.186 1.527 13.079-1.313 3.555-1.832 6.793-4.991 11.082-4.655 3.079 1.369 6.578 1.161 9.578 2.565 2.193 1.803 3.806 4.736 6.973 4.913 2.049.629 6.736 1.528 4.248-2.037-1.303-3.136-4.521-4.315-7.315-5.718-1.778-1.227-6.668-4.289-1.61-4.056 3.569.98 6.216-.709 5.144-4.577.905-3.85-6.014-4.302-5.807-.642-.563 3.185-2.053 7.24-5.684 7.747-3.914.253-7.921-.377-11.625-1.6-2.141-1.611-5.679-6.542-.819-6.729 2.075-.608 6.959-1.417 3.547-4.175-1.832-2.686-6.145-3.137-6.82-6.608 1.231-3.57-3.101-7.573-4.624-2.74-.696 2.002-.804 4.152-.821 6.256z"/>\n' +
    '    <path fill="#E5E5E6" d="M348.438 346.97c.127-.992-1.024-1.296-1.781-1.01-2.464.577-4.666 2.061-7.213 2.313-1.595-.093-2.924 1.082-3.633 2.413-.715 1.362-1.287 2.877-1.261 4.435.021 1.361 1.103 2.427 2.317 2.874 1.675.635 3.449.959 5.178 1.412 2.701.62 5.518.796 8.269.462 1.403-.165 2.746-.916 3.467-2.155.933-1.396 1.513-3.188.913-4.832-.429-1.254-1.203-2.351-1.819-3.514-1.095-1.17-2.688-1.67-4.126-2.277-.104-.04-.207-.079-.311-.121z"/>\n' +
    '    <path fill="#A8A7AA" fill-opacity=".8671" d="M289.672 345.665c-3.799-9.733-15.49-13.356-7.183-25.466-17.721-2.755-1.496-14.282-12.713-24.162 9.281-.049 14.467 10.352 9.2-4.566 5.449-13.343 13.326 4.724 22.516-6.352 17.876-5.191 3.66-16.425-7.903-8.669-8.802 4.304-24.021-4.397-5.876-5.224 22.587-6.038-3.659-14.676-14.017-13.251-12.466-.04 7.284-29.521-8.037-16.27 5.373 23.653-31.869 10.015-29.53 20.379 12.253 6.314-20.371 14.74-24.16 25.466-7.971 7.404-21.553 21.264-3.266 7.836 15.817-4.687 37.554-37.432 49.523-20.666-8.168 13.179-16.109 27.464-32.458 15.189-10.44 8.039 21.756 7.535 7.026 21.437-1.286 19.069 13.227-16.987 11.026 5.283 4.083-16.801 5.188-3.548 10.203-1.47 18.637-11.022 5.721 10.833-4.784 14.333-23.411 12.521 24.183 16.628 3.173 26.611-2.395 6.775-44.562-.799-18.896 5.885 12.703-1.549 23.514 2.529 5.235 5.536-14.254 19.155 17.762-5.068 17.757 9.878 1.385 11.093 20.118 17.414 11.654.224 3.196-16.694 9.174 5.989 8.757 6.389 5.571-11.527 14.699-24.538 12.753-38.35z"/>\n' +
    '    <path fill="#B9B8BA" d="M287.06 352.847c-1.897.581-3.78 2.149-5.849 1.532-2-.69-.33-3.763-2.394-4.084-1.999-.616-3.666-2.441-3.511-4.63.021-1.633.071-4.693-2.35-4.356-1.796.075-4.31.803-3.78 3.136.094 1.872 1.161 3.632.927 5.514-1.316 1.702-3.72-1.926-4.779.382-.831 1.466-1.611 3.608-3.705 3.324-1.089.221-2.653-.682-3.417-.394 1.203 1.434 2.629 2.683 3.987 3.953.49 2.244-2.683 2.002-4.129 2.407-2.409.479-4.91.177-7.291.777-1.892.314-.597 1.692.641 1.497 3.809.373 7.591-.462 11.281-1.272 1.999.666 1.666 3.35 2.173 5.028.107 1.182.877 2.654 1.206 3.448-.28-2.205 3.487-1.7 3.925-.026.225 1.063 1.277 2.243 1.562.499.456-1.249.743-4.719 2.69-3.13 1.186 1.08 1.289 2.943 2.182 4.068 1.184-1.242 1.34-3.151 1.944-4.723.413-1.855 2.704-1.796 4.191-1.659 1.629-.067 3.747.957 5.077.13-1.184-1.698-3.183-2.626-4.729-3.967-.846-.516-1.517-1.25-.025-1.119 2.537-.271 5.155-.327 7.66-.515-2.453-.738-5.038-1.052-7.581-1.295.493-1.562 2.204-2.568 3.315-3.776.263-.247.537-.482.779-.749z"/>\n' +
    '    <path fill="#F3F3F4" d="M257.677 288.856c-3.461-.382-5.604.107-7.183 3.266-.08.158-5.378 3.418-5.877 3.917-3.365 3.365 1.602.357 2.612-.652 1.34-1.34 6.639-.653 9.142-.653 3.239-.001 2.352-3.26 1.306-5.878z"/>\n' +
    '    <path fill="#E5E5E6" d="M258.33 281.021c.893 0 2.553 6.025-3.265 4.571-2.349-.587-.084-6.361 0-6.53.567-1.135 2.176 1.306 3.265 1.959zM274 355.459c.143-.285-6.709 4.571.653 4.571 3.014 0 .307-2.971-.653-4.571z"/>\n' +
    '    <path fill="#CDCDCD" fill-opacity=".4345" d="M664.555 398.161c0 139.406-114.615 252.417-256 252.417-141.384 0-256-113.011-256-252.417s114.616-252.417 256-252.417c141.384 0 256 113.01 256 252.417z"/>\n' +
    '  </symbol>\n' +
    '';

  var MOON_TEXTURE_SYMBOL_ID = 'moonPhase16HighResMoon';

  function ensureMoonTextureSprite(){
    if (document.getElementById(MOON_TEXTURE_SYMBOL_ID)) return;
    var sprite = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    sprite.setAttribute('aria-hidden', 'true');
    sprite.style.position = 'absolute';
    sprite.style.width = '0';
    sprite.style.height = '0';
    sprite.style.overflow = 'hidden';
    var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    defs.innerHTML = MOON_TEXTURE_MARKUP;
    sprite.appendChild(defs);
    document.body.appendChild(sprite);
  }
  ensureMoonTextureSprite();

  var mount = document.getElementById('moonPhaseCard16');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Current Moonphase');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: moon disc + phase name, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Fixed row height (rather than sizing purely to font metrics). Loosened
  // from 20px to 22px — the label+value combination lands at ~19.8px of
  // natural content height, which left only ~0.2px of slack inside the
  // old 20px/overflow:hidden box; any small font-metric variance between
  // browsers could clip a row's own bottom pixel or two, especially on
  // the lower rows where that stacks with the pane's own bottom edge.
  // 7 * 22px = 154px, still comfortably inside the 175px pane.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '22px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    labelEl.style.whiteSpace = 'nowrap';
    row.appendChild(labelEl);

    // white-space/overflow/text-overflow are the actual fix for the
    // clipping — without them, a long value (the meteor shower text
    // especially, e.g. "Perseids Aug 1st-24th") wraps onto a second
    // line, which pushes the row's natural content height well past its
    // fixed 22px/overflow:hidden box, and the centered content gets
    // clipped top and bottom instead of the row just holding one line.
    // Forcing nowrap + ellipsis guarantees every value stays one line,
    // whatever its length.
    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap';
    valueEl.style.overflow = 'hidden';
    valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var riseText        = addChipRow('Moon Rise');
  var setText          = addChipRow('Moon Set');
  var distanceText     = addChipRow('Distance');
  var illumText        = addChipRow('Illumination');
  var fullMoonText     = addChipRow('Next Full Moon');
  var newMoonText      = addChipRow('Next New Moon');
  var meteorText       = addChipRow('Meteor Shower');
  meteorText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the celestial data modal — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'modalCelestial.html';
  cardLink.setAttribute('data-modal', 'Celestial');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Celestial Data \u2013 Radio Aurora | Northern Lights - Meteor Showers - Moon Data');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '720px');
  cardLink.setAttribute('data-url', 'modalCelestial.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  // R nudged up from 50 to 56 ("slightly increase size of image"), cy
  // recomputed so the disc + hero label beneath it still centre as a
  // group within the pane (was cy=72; the label position is derived from
  // cy+R so it stays correctly attached to the disc's new size
  // automatically, no separate adjustment needed there).
  var W = 180, H = 175, cx = 90, cy = 76, R = 56;

  function getX(phase, angle, radius, centerX){
    var f = Math.cos(phase * Math.PI / 180);
    var cosi = Math.cos(angle * Math.PI / 180);
    var x = f * radius * cosi + centerX;
    if ((phase <= 180 && cosi < 0) || (phase > 180 && cosi > 0)){
      x = radius * cosi + centerX;
    }
    return x;
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    var tiltGroup = svg.append('g').attr('transform', 'rotate(' + v.tiltDeg + ',' + cx + ',' + cy + ')');

    var useSize = (1.8 * R) / 0.91;
    tiltGroup.append('use')
      .attr('xlink:href', '#' + MOON_TEXTURE_SYMBOL_ID)
      .attr('width', useSize).attr('height', useSize)
      .attr('x', cx - useSize / 2).attr('y', cy - useSize / 2);

    var moonPathD = 'M';
    for (var i = 0; i <= 360; i += 2){
      var x = getX(v.phasex, i, R, cx);
      var y = R * Math.sin(i * Math.PI / 180) + cy;
      moonPathD += (i === 0 ? '' : 'L') + x.toFixed(2) + ',' + y.toFixed(2);
    }
    moonPathD += 'Z';
    tiltGroup.append('path').attr('d', moonPathD).style('fill', 'rgba(41,46,53,0.80)').style('pointer-events', 'none');

    // Hero value — phase name, below the disc, same accent colour + mono
    // font as Current Conditions.
    svg.append('text').attr('x', cx).attr('y', cy + R + 20).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(v.phaseName);

    // ---- Right pane: 7 readouts as label/value chip rows ----
    riseText.textContent = v.moonRise;
    setText.textContent = v.moonSet;
    distanceText.textContent = v.distanceKm.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' km';
    illumText.textContent = v.luminancePct.toFixed(2) + ' %';
    fullMoonText.textContent = v.fullMoonLabel;
    newMoonText.textContent = v.newMoonLabel;
    meteorText.textContent = v.meteorShower;
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(alm){
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }
        var now = stationNow();

        lastData = {
          moonRise: epochToHHMM(alm['almanac.moon.next_rising.unix_epoch.raw'], false),
          moonSet: epochToHHMM(alm['almanac.moon.next_setting.unix_epoch.raw'], false),
          phaseName: alm['almanac.moon.phase_name'] || '--',
          luminancePct: num(alm['almanac.moon.phase'], 0),
          tiltDeg: num(alm['almanac.moon.parallactic_angle'], 0),
          phasex: num(alm['almanac.moon.ecliptic_angle'], 0),
          // almanac.moon.earth_distance is in AU (standardised schema);
          // this card's label is hardcoded "km" (see distanceText
          // below), so convert here rather than showing AU under a km label.
          distanceKm: num(alm['almanac.moon.earth_distance'], 0) * 149597870.7,
          fullMoonLabel: fmtEpochDate(alm['almanac.next_full_moon.unix_epoch.raw']) || '--',
          newMoonLabel: fmtEpochDate(alm['almanac.next_new_moon.unix_epoch.raw']) || '--',
          meteorShower: currentMeteorShower(now)
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardMoonPhase: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardMoonPhase.js failed:", e);
}

/* ===== cardLightning.js ===== */
try {
/*
##############################################################################################
# cardLightning.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardLightning.js =====================
(function(){
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return new Date(Date.UTC(+p.year, months.indexOf(p.month), +p.day, +p.hour, +p.minute, +p.second));
  }
  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function ordinalSuffix(day){
    if (day % 10 === 1 && day !== 11) return 'st';
    if (day % 10 === 2 && day !== 12) return 'nd';
    if (day % 10 === 3 && day !== 13) return 'rd';
    return 'th';
  }
  function dateLabelFor(epochMs){
    if (!epochMs) return '\u2014';
    var p = stationParts(new Date(epochMs));
    return (+p.day) + ordinalSuffix(+p.day) + ' ' + p.month + ' ' + p.hour + ':' + p.minute;
  }

  var mount = document.getElementById('lightningCard17');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar --------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Lightning');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: bolt graphic, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Fixed-height/nowrap/ellipsis by default. Last Detected passes
  // wrap:true instead — it's a full date+time string (e.g. "21:18:45 4
  // Aug 2026"), long enough to need more than one line at this column
  // width, and there's slack in the pane (7 rows at 20px only use 140 of
  // the 175px available) to just let it wrap rather than truncating a
  // date with an ellipsis.
  function addChipRow(label, opts){
    var wrap = opts && opts.wrap;
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.boxSizing = 'border-box';
    row.style.borderBottom = '1px solid var(--bs-border-color)';
    if (wrap) {
      row.style.minHeight = '20px';
      row.style.padding = '2px 0';
    } else {
      row.style.height = '20px';
      row.style.overflow = 'hidden';
    }

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    if (wrap) {
      valueEl.style.whiteSpace = 'normal';
      valueEl.style.wordBreak = 'break-word';
      valueEl.style.lineHeight = '1.25';
    } else {
      valueEl.style.whiteSpace = 'nowrap';
      valueEl.style.overflow = 'hidden';
      valueEl.style.textOverflow = 'ellipsis';
    }
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var currentText   = addChipRow('Current');
  var todayText      = addChipRow('Today');
  var hourText        = addChipRow('Last Hour');
  var yearText          = addChipRow(String(stationNow().getUTCFullYear()) + ' Total');
  var alltimeText        = addChipRow('All-Time Total');
  var lastDetectedText     = addChipRow('Last Detected', { wrap: true });
  var lastDistanceText      = addChipRow('Last Distance');
  lastDistanceText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the lightning chart/records page —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=lightning&embed=1';
  cardLink.setAttribute('data-modal', 'Lightning');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Lightning Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=lightning&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175;

  // -- Fractal bolt, ported from the PHP's own recursive algorithm ------
  function midpointPath(startX, startY, endX, endY, displace){
    if (displace < 2) return [[startX, startY], [endX, endY]];
    var midX = (startX + endX) / 2 + (Math.random() - 0.5) * displace;
    var midY = (startY + endY) / 2 + (Math.random() - 0.5) * displace;
    return midpointPath(startX, startY, midX, midY, displace / 2)
      .concat(midpointPath(midX, midY, endX, endY, displace / 2));
  }

  function drawBranch(svg, lineGen, startX, startY, width){
    var branchEndX = startX + (Math.random() - 0.5) * 36;
    var branchEndY = startY + Math.random() * 24;
    var points = midpointPath(startX, startY, branchEndX, branchEndY, 9);
    svg.append('path').datum(points).attr('d', lineGen)
      .attr('fill', 'none').attr('stroke', '#6ca6cd').attr('stroke-width', width)
      .style('opacity', 0.8);
  }

  function drawStrike(svg, trunkX, trunkTopY, trunkBottomY){
    var lineGen = d3.line().x(function(d){ return d[0]; }).y(function(d){ return d[1]; });
    var points = midpointPath(trunkX, trunkTopY, trunkX, trunkBottomY, 18);

    // Trunk stroke was a literal 'white', which is invisible against the
    // light theme's white card face — swapped for the theme's own body-
    // text variable, which is dark ink in light mode and pale parchment in
    // dark mode, so the bolt reads against either card background. The
    // steel-blue glow (drop-shadow) is unaffected — it's already a
    // saturated colour that shows up on both backgrounds.
    svg.append('path').datum(points).attr('d', lineGen)
      .attr('fill', 'none').attr('stroke', 'var(--bs-body-color)').attr('stroke-width', 1)
      .style('filter', 'drop-shadow(0 0 3px #6ca6cd)');

    points.forEach(function(pt, i){
      if (Math.random() < 0.55 && i > 3 && i < points.length - 3){
        drawBranch(svg, lineGen, pt[0], pt[1], 0.8);
      }
    });
  }

  function drawGroundGlow(svg, gx, gy){
    var rings = [
      { rx: 34, ry: 10, color: '#3b9cac', opacity: 0.35 },
      { rx: 24, ry: 7,  color: '#2e8b57', opacity: 0.55 },
      { rx: 13, ry: 4,  color: '#6ca6cd', opacity: 0.85 }
    ];
    rings.forEach(function(r){
      svg.append('ellipse').attr('cx', gx).attr('cy', gy).attr('rx', r.rx).attr('ry', r.ry)
        .style('fill', 'none').style('stroke', r.color).style('stroke-width', 2).style('opacity', r.opacity);
    });
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    var trunkX = 90, trunkTopY = 20, trunkBottomY = 130;
    svg.append('line').attr('x1', trunkX).attr('x2', trunkX).attr('y1', trunkBottomY).attr('y2', trunkBottomY + 18)
      .style('stroke', '#2e8b57').style('stroke-width', 3).style('stroke-linecap', 'round');
    drawGroundGlow(svg, trunkX, trunkBottomY + 18);
    drawStrike(svg, trunkX, trunkTopY, trunkBottomY);

    // ---- Right pane: 7 readouts as label/value chip rows ----
    currentText.textContent = v.currentStrikes.toLocaleString();
    todayText.textContent = v.dayStrikes.toLocaleString();
    hourText.textContent = v.hourStrikes.toLocaleString();
    yearText.textContent = v.yearStrikes.toLocaleString();
    alltimeText.textContent = v.alltimeStrikes.toLocaleString();
    lastDetectedText.textContent = v.lastDetected;
    lastDistanceText.textContent = v.lastDistance.toFixed(1) + ' km';
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(arch){
        var l = arch.lightning || {};
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

        lastData = {
          currentStrikes: num(l.current_strike_count, 0),
          dayStrikes: num(l.day_strike_count, 0),
          hourStrikes: num(l.hour_strike_count, 0),
          yearStrikes: num(l.year_strike_count, 0),
          alltimeStrikes: num(l.alltime_strike_count, 0),
          lastDetected: dateLabelFor(num(l.last_time, 0)),
          lastDistance: num(l.last_distance, 0),
          year: stationNow().getUTCFullYear()
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardLightning: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardLightning.js failed:", e);
}

/* ===== cardPollen.js ===== */
try {
/*
##############################################################################################
# cardPollen.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardPollen.js =====================
(function(){
  var LOOP_JSON_URL = './jsondata/loop.json';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }

  // -- Risk classifier, ported from the PHP's pollenRisk() ---------------
  var GRASS_BANDS = [
    [0,        'None',      '#59C239'],
    [4,        'Low',       '#FFE000'],
    [19,       'Moderate',  '#F19E38'],
    [200,      'High',      '#EA3323'],
    [Infinity, 'Very High', '#621e2f']
  ];
  var TREE_WEED_BANDS = [
    [0,        'None',      '#59C239'],
    [9,        'Low',       '#FFE000'],
    [49,       'Moderate',  '#F19E38'],
    [500,      'High',      '#EA3323'],
    [Infinity, 'Very High', '#621e2f']
  ];
  function pollenRisk(value, bands){
    if (value === null || typeof value === 'undefined') return { risk: 'No Data', color: 'var(--bs-secondary-color)' };
    for (var i = 0; i < bands.length; i++){
      if (value <= bands[i][0]) return { risk: bands[i][1], color: bands[i][2] };
    }
    var last = bands[bands.length - 1];
    return { risk: last[1], color: last[2] };
  }


  var GRASS_ICON_SVG = '<svg width="50" height="450" preserveAspectRatio="xMidYMid" version="1.0" viewBox="0 0 150 337.5" xmlns="http://www.w3.org/2000/svg"><path d="M7061 12738c-31-98-153-512-231-783-313-1092-638-2386-890-3540-51-231-94-427-97-435-7-19 0-38-118 365-281 961-650 2059-1043 3100-153 405-176 464-180 461-2-3 4-172 12-378 31-746 50-1377 66-2238 11-591 13-2362 3-2602l-6-156-99 231c-119 278-391 830-518 1052-215 373-445 705-648 933-36 40-66 71-68 70-1-2 19-84 46-183 142-532 274-1191 365-1825 31-220 95-726 95-754 0-12-47 69-103 179-352 682-713 1213-1157 1700-153 168-254 266-246 240 34-123 186-801 250-1115 251-1242 426-2505 486-3515 6-99 12-204 15-234 2-30 0-50-5-45-4 5-50 81-100 169-673 1168-1562 2195-2475 2860-127 92-277 193-360 241l-50 29 30-40c191-254 284-382 379-523 792-1179 1356-2660 1645-4317 83-474 148-1021 166-1400 3-66 8-157 11-202l6-83h7663l2 191c5 562 136 1105 408 1698 297 648 804 1345 1380 1899 81 78 142 142 135 142s-94-27-194-61c-978-329-1789-826-2511-1541l-141-138 38 332c114 1020 181 2007 196 2892 23 1340-52 2423-233 3378-143 755-371 1428-612 1802-24 37-46 66-48 64-3-2 2-119 10-259 29-509 38-857 38-1479 0-882-26-1514-99-2380-47-573-159-1554-176-1544-4 3-8 20-8 39 0 40-29 298-66 575-104 804-266 1613-465 2335-45 163-166 559-202 660l-14 40-7-60c-3-33-25-226-47-430-101-931-267-2184-393-2965-30-190-32-179-51 420-81 2527-13 4801 210 6930 12 124 22 226 20 228s-8-12-14-30z" fill="var(--grass-color)" stroke="var(--stroke-color)" stroke-width="20" transform="matrix(.01268 0 0 -.02643 -.063 337.5)"/></svg>';
  var TREE_ICON_SVG = '<svg width="50" height="449.991" viewBox="0 0 52.917 119.06" xmlns="http://www.w3.org/2000/svg"><path d="m24.113 0-.03.748a453 453 0 0 0-.059 2.76l-.028 2.012-.865-1.169c-.998-1.36-1.132-1.415-1.127-.48.002.38.408 1.272.9 1.985.787 1.142.897 1.502.944 3.019.035 1.145-.033 1.657-.197 1.524-.135-.11-.24-.4-.24-.642-.001-.244-.106-.441-.235-.441-.13 0-.235-.535-.235-1.189 0-1.36-.59-2.312-.96-1.552-.118.242-.167.602-.108.805.124.447-.55.155-1.005-.431-.17-.22-.443-.394-.614-.394-.636 0-.53.866.24 2.023.92 1.376.969 1.65.36 1.86-.298.101-.401.34-.322.737.132.66.76.778 1.186.22.319-.417.328-.395.38.758.047.962.004.983-.797.383-.785-.588-.937-.573-.937.125 0 .325.342.94.761 1.37.572.585.635.74.263.613-.324-.11-.544 0-.638.317-.201.688-.363.631-1.924-.7-.766-.653-1.509-1.133-1.65-1.064-.487.245-.177 1.416.507 1.907l.689.49-.63.095c-.345.054-.68.289-.748.518-.131.448.455.887.806.604.117-.095.22-.026.22.153 0 .178.185.331.41.345.298.017.32.068.08.192-.248.128-.2.302.213.709l.549.536-.982.22c-1.008.224-1.24.717-.635 1.371q.336.37.73.077c.444-.324 1.276-.142 1.276.278 0 .149.21.278.468.278s.469-.138.469-.307.237-.303.527-.297l.527.01-.578.623c-.45.49-.506.68-.256.881.352.285.959.077 1.259-.43.107-.185.138.046.058.517-.117.702-.063.843.33.843.26 0 .481-.172.49-.383q.016-.393.148.048c.094.313.213.228.431-.307l.3-.738.293.843c.162.463.218.941.124 1.064-.093.123-.932.274-1.867.336-1.561.103-1.788.041-2.82-.815-1.026-.853-1.778-.995-1.778-.335 0 .145.29.483.645.747l.644.48-.82-.202c-.811-.194-2.177-1.114-2.777-1.878-.169-.218-.511-.394-.762-.394-.475 0-.6.594-.225 1.084.14.184-.22.332-.982.392-.663.053-1.295.166-1.399.25-.77.623 1.128 1.755 2.505 1.495l.945-.173-.659.49c-.4.294-.635.693-.586 1.006.113.748 1.125.497 1.772-.442.3-.437.705-.723.937-.651.324.1.282.202-.213.47-.797.43-1.04.936-.555 1.178a.52.52 0 0 1 .262.586c-.063.217.042.472.228.564.185.092.337.049.337-.095 0-.252.757-.681 1.324-.758.15-.021.492-.303.762-.622.27-.32.492-.479.492-.356 0 .319-.513.945-1.12 1.37-.289.203-.52.504-.52.673 0 .58.721.668 1.164.144.422-.503.436-.497.314.113-.178.896.3.807.776-.144.297-.592.443-.687.593-.383.164.33.23.328.396-.01a.65.65 0 0 1 .644-.334c.947.157 2.189-.948 2.226-1.984.021-.558.045-.542.19.126.14.631.026.938-.666 1.754-.459.54-1.488 1.432-2.284 1.981l-1.45.997-1.128-.736c-.621-.405-1.258-.767-1.42-.807-.42-.104-.363.49.117 1.227.368.57.366.586.007.22-.22-.22-.49-.327-.6-.238-.112.089-.497-.034-.858-.28-.862-.585-1.131-.555-.974.099.105.432-.005.527-.57.506-.386-.016-.757-.108-.827-.2-.286-.374-1.04.175-1.04.758 0 .47.112.576.49.448.47-.16.473-.145.081.423-.424.613-.356.696.535.604.407-.043.492.058.372.47-.126.432-.044.502.44.383.486-.123.568-.05.477.41-.127.63.023.697.606.289.575-.399 1.301.87.828 1.447-.265.323-.345.31-.446-.04-.07-.239-.487-.435-.946-.46-.45-.02-.764.083-.694.23.068.15-.08.197-.337.108-.593-.202-.612.264-.03.736.308.251.402.589.323 1.14-.143.973-.179.98-1.04.032-.383-.424-.85-.767-1.034-.767-.392 0-.413.15-.108.89.246.6.091.616-1.685.174-.579-.147-.879-.104-.879.114 0 .395 1.216 1.275 1.772 1.275.32 0 .34.077.103.451-.234.365-.225.494.052.632.187.095.44.144.555.113.49-.12.794.056.74.414-.037.234.19.375.586.362.495-.012.645.13.652.614.007.607.025.6.468-.095.366-.574.462-.617.462-.212 0 .282.105.432.234.328.13-.104.335.166.462.604.127.436.389.868.586.966.194.099.358.368.358.605 0 .236.17.432.38.432.213 0 .518.233.682.528.281.5.333.496.848-.077l.55-.604-.416.64c-.253.393-1.034.838-2.023 1.15-2.062.654-2.56.633-3.404-.094-.78-.675-1.163-.55-.952.316.117.484.028.595-.527.595-.37 0-.778-.138-.91-.307q-.233-.312-.686.123c-.436.417-.426.457.3.996.546.408.72.721.621 1.123-.087.359-.019.533.183.497.68-.12.942.03.813.472-.225.767.63 1.132 1.282.546.426-.387.49-.405.279-.058-.396.644-.017 1.318.6 1.064.888-.368 1.127.22.257.632-1.118.527-2.233.518-2.652-.028-.462-.607-1.18-.613-1.003-.01.11.372-.054.42-.914.268-1.261-.227-1.42.285-.258.834.565.267.764.524.689.901-.124.626.614.973 1.186.556.311-.227.389-.138.389.469 0 .622.084.727.503.586.415-.145.476-.083.345.361-.127.439-.061.525.316.396.26-.089.476-.04.476.114 0 .536.494.266 1.12-.605.752-1.049 1.197-1.282 1.75-.91.643.432.48.782-.292.634q-1.215-.234-.345 1.399c.34.635.34.68-.096.68-.255 0-.626.206-.82.46-.192.252-.506.46-.703.46-.195 0-.415.13-.49.286-.075.16-.546.28-1.047.27-.502-.01-1.048.095-1.217.23-.166.135-.646.218-1.061.18-.623-.054-.764.056-.827.636-.054.5-.237.702-.659.727-.323.018-.691.07-.82.116-.129.043-.42.05-.644.018-.225-.03-.41.068-.41.212 0 .405 1.295 1.313 1.881 1.313.513 0 .523.015.11.613s-.41.613.19.613c.46 0 .581.114.476.47-.113.386-.014.438.534.267.373-.114.727-.203.792-.2.731.019.9.123.79.497-.244.834.363.813.71-.028.333-.8.351-.81.36-.162.006.377.095.7.19.718 1.432.272 1.667.184 1.955-.727l.293-.92.206.726.204.74.455-.767c.45-.767.447-.767.454-.077.007.93.424.877.607-.076.082-.424.25-.767.373-.767.124 0 .147.147.058.334-.11.236.07.276.603.126.562-.163.738-.126.665.162-.143.565-1.612 1.525-2.336 1.525-.342 0-.67.171-.731.383-.07.233-.528.368-1.195.353-.596-.013-1.083.082-1.083.211 0 .59.813 1.23 1.458 1.141.382-.052.712.025.73.181.064.512-1.31.797-4.649.97-3.763.19-5.272.407-5.272.738 0 .126-.265.166-.586.086-.333-.086-.586-.006-.586.181 0 .43.767.97 1.383.97.267 0 .492.137.492.306s-.115.307-.248.307c-.136 0-.478.285-.762.64l-.513.654.761-.135c.504-.092.762-.012.762.24 0 .226.187.315.469.22.262-.089.468-.01.468.18 0 .498.596.424.938-.113.159-.254.393-.46.527-.46s.073.246-.14.556c-.214.306-.387.653-.387.766 0 .325.893.246 1.054-.095.232-.494.542-.356.703.307.188.776.455.77 1.303-.022l.682-.631-.148.938c-.175 1.156.251 1.248 1.202.27.624-.644.643-.644.394-.04-.29.715-.15 1.052.338.807.182-.092.49.003.682.211.24.261.351.276.351.037 0-.19.157-.343.352-.343.192 0 .351.19.351.429 0 .298.206.411.644.347 2.44-.362-.717 1.493-3.552 2.088-1.854.39-2.039.374-2.65-.153-.361-.313-.783-.564-.945-.564-.485-.003-.33.61.235.929.386.218.246.242-.528.086-.58-.117-1.319-.194-1.64-.172-.323.021-1.108-.163-1.743-.405-.635-.24-1.188-.399-1.23-.343-.244.319 1.255 1.585 2.094 1.763l.996.212-.879.037c-.611.027-.878.177-.878.5 0 .25.185.48.41.505.225.028.515.077.644.117.13.04.385.086.572.104.248.028.216.15-.117.47-.581.555-.305.953.342.5.405-.283.492-.255.492.162 0 .393.166.472.762.365.42-.077.972-.242 1.23-.365.462-.22.455-.22.007.258-.457.487-.359.6.403.5.232-.031.41.156.41.429 0 .656.19.604.748-.209l.475-.69-.382.834c-.206.457-.372.973-.372 1.15 0 .473 1.148.123 1.699-.518.459-.54.464-.527.286.221-.249 1.04-.537 1.257-2.695 2.107-1.795.705-1.903.715-2.87.23-1.058-.527-2.513-.678-2.513-.258 0 .135.122.418.279.623.19.248-.124.209-.96-.117-.685-.263-1.405-.404-1.604-.306-.307.156-.278.306.176.941l.534.746-.792-.295c-1.02-.383-2.256-.383-2.437 0-.187.393 1.38 1.233 2.306 1.236h.74l-.688.718c-.68.711-.673 1.644.007 1.303.227-.113.33.07.33.595 0 .543.122.758.445.758.251 0 .617.208.813.469.3.39.439.399.837.077.263-.215.483-.497.483-.623 0-.129.185.01.417.307.447.567.576 1.303.227 1.303-.115 0-.133.147-.044.334.112.237-.061.28-.572.135-.466-.135-.731-.098-.731.114 0 .19-.457.337-1.055.337-1.099 0-1.305.264-.703.92.235.255.731.322 1.465.2l1.113-.181-.872.604c-.604.42-.83.751-.733 1.082.089.298.283.396.52.267a3.2 3.2 0 0 1 .733-.276c.193-.043.22-.015.06.058-.594.267-.29.758.35.565.462-.141.547-.11.3.104-.62.54-.07.696.673.193.4-.27.726-.362.726-.202s-.13.356-.292.432c-.162.074-.211.252-.11.393.1.14.485.107.857-.077.553-.273.659-.254.555.095-.09.31-.007.365.307.221.366-.169.39-.129.134.23-.241.334-1.047.414-3.69.365-2.049-.04-3.385.052-3.385.23 0 .163.164.298.366.298.204 0 .516.193.696.429.281.371.276.457-.038.613-.199.101-.696.181-1.106.184-.86.003-1.45.405-1.244.844.255.54 1.42.638 2.38.199 1.283-.583 1.35-.549.69.316-.827 1.083-.766 1.736.124 1.402.382-.145.804-.353.937-.46.132-.108.251.058.27.361.02.307.051.712.065.911.017.2.099.463.186.577.182.242 1.595-.795 1.595-1.169 0-.144.096-.178.22-.076.144.116.144.46 0 .957-.124.426-.22.913-.22 1.082 0 .405 1.106-.644 2.22-2.098.829-1.085.946-.901.175.276-.19.292-.274.76-.192 1.046.136.46.213.433.748-.22l.609-.728-.082.767c-.075.703-.234.795-1.955 1.169-1.209.26-2 .3-2.233.113-.588-.472-1.867-.754-1.867-.41 0 .171.185.373.41.45.225.074-.117.074-.762 0-.942-.107-1.115-.061-.879.248.448.592.347.813-.293.654-.756-.19-1.82.162-1.64.546.077.162.38.297.673.297 1.003 0 .862.42-.359 1.064-2.078 1.092-1.624 1.905.513.92 1.083-.5 1.505-.414.71.141-.27.19-.482.562-.482.825 0 .372.14.436.62.28.554-.181.596-.154.324.276-.633.996.038 1 1.083 0 .49-.467.508-.46.375.22-.127.641-.101.672.255.289.354-.378.377-.362.234.153-.14.51-.084.49.424-.113.598-.712.966-.654.741.113-.178.61.382.53 1.275-.181.424-.34.761-.457.761-.27 0 .209.246.264.645.144.436-.129.53-.1.293.086-.195.154-.933.433-1.64.623-1.526.414-2.002.748-1.765 1.248.162.34.476.304 1.882-.193.358-.126.29.067-.293.806-.42.528-.762 1.025-.762 1.11 0 .304 1.303-.073 2.05-.594.82-.57.947-.396.404.545-.476.825-.432 1.187.124.997.304-.104.468 0 .468.28 0 .573.214.542.762-.105.431-.512.452-.51.373.077-.125.935 1.174.653 1.867-.405l.542-.822-.153.997c-.204 1.318.385 1.352 1.296.076.361-.506.687-.92.731-.92.047 0 .099.356.117.795.054 1.205.436 1.53.924.785.384-.59.44-.598.724-.114.283.479.314.473.447-.144.087-.399.272-.604.462-.509.202.101.375-.138.476-.66.107-.567.218-.708.344-.441.1.215.403.435.673.488.382.076.607-.203 1.005-1.273.513-1.383 1.055-1.917.776-.767-.18.742-.045.76.71.114.318-.273.586-.789.6-1.15.02-.525.053-.546.14-.114.178.892.542.273.549-.942.004-.917.046-1.027.24-.591.132.294.46.536.725.536.462 0 .48.11.33 2.377-.105 1.583-.351 2.874-.73 3.861a32 32 0 0 0-.507 1.38h5.828c.26-.27.143-.745-.124-1.582-.415-1.3-.574-3.96-.249-4.1.13-.056.415.306.645.815.426.945.951 1.294.71.47-.277-.945.16-.43.476.564.253.791.457 1.027.878.987.305-.03.706.09.886.267.24.233.359.224.427-.037.06-.242.246-.165.548.23.602.789.905.752.762-.095-.068-.405-.033-.558.08-.365.271.454 1.019.782 1.019.451 0-.31 1.293-.2 1.968.163.356.19.422.132.31-.258-.08-.273-.024-.549.13-.626.214-.1.216-.236-.006-.545-.532-.736-.312-.832.562-.249.968.647 1.24.552.77-.257-.25-.436-.254-.47-.008-.184.17.199.452.29.63.202.178-.089.33-.04.33.113 0 .157.143.543.321.865.282.503.385.525.72.163.335-.365.429-.322.761.297l.38.709.213-.74c.195-.683.232-.699.511-.199.373.657.537.666.996.068.286-.374.302-.561.075-.92-.157-.249-.218-.451-.134-.451.087 0 .033-.313-.117-.69-.227-.58-.14-.546.52.239 1.46 1.724 1.556 1.788 1.971 1.245.206-.27.293-.66.197-.87-.096-.222.024-.185.279.085 1.24 1.3 2.493 1.558 1.647.334-.382-.552-.368-.595.242-.794l.637-.212-.689-.726c-.9-.963-.417-.966.872 0 .562.417 1.157.763 1.32.766.473.003.334-.484-.235-.843-.476-.3-.438-.337.417-.356.523-.012 1.155-.22 1.406-.47.433-.428.426-.45-.241-.46-1.003-.014-4.575-.864-5.317-1.266-.912-.49-1.083-1.239-.183-.794.79.39 1.188.067.607-.488-.338-.325-.228-.362.668-.27 1.14.12 1.384-.257.534-.825-.457-.303-.38-.34.614-.276 1.342.086 1.72-.49.565-.864l-.77-.249.762-.113q1.337-.21.235-.951c-.431-.288-.249-.328.996-.19 1.401.153 1.48.129 1.061-.307-.25-.26-.64-.47-.864-.47s-.476-.152-.563-.336c-.098-.209.047-.316.394-.276.98.104 1.612.002 1.612-.27 0-.148-.743-.362-1.647-.479-.905-.116-1.596-.325-1.53-.46.063-.138.738-.432 1.5-.653 1.436-.417 2.345-1.04 2.057-1.417-.146-.193-1.45.086-3.192.69-.537.184-.523.129.21-.7 1.21-1.36.528-1.502-1.213-.248-1.75 1.258-2.751 1.359-4.687.488-2.198-.99-5.762-3.358-6.027-4.005-.328-.804-.143-.782.455.055.262.37.483.558.483.423 0-.138.157-.077.351.135.29.313.352.285.352-.175s.12-.405.73.347l.734.901-.14-.987c-.139-.975-.13-.985.499-.632.349.193.794.512.989.709.909.914 1.014.895.848-.184-.124-.813-.099-.954.124-.66.408.534 1.371.472 1.371-.086 0-.573.377-.6.63-.049.103.227.48.65.835.948.531.45.644.466.644.098 0-.248-.21-.693-.469-.997-.257-.306-.468-.702-.468-.874 0-.168.058-.218.138-.113.08.104.312-.025.513-.289.253-.33.635-.432 1.244-.325.607.108.921.025 1.003-.26.094-.313.307-.344.896-.123 2.066.776 2.446-.11.452-1.055-1.348-.638-1.516-.963-.256-.488 1.603.601 3.689.145 2.805-.613-.194-.169-.827-.325-1.406-.347-.895-.033-.953-.064-.375-.23.378-.107.732-.386.792-.622.092-.359-.278-.399-2.27-.221-2.346.212-2.383.212-2.095-.393.431-.898.136-1.055-1.031-.536-.63.279-1.442.398-2.074.297-1.355-.218-5.835-2.183-6.362-2.788-.551-.634-.52-1.076.058-.834.263.108.469.052.469-.125 0-.332.351-.237 1.523.432.387.22 1.235.405 1.889.41.654.007 1.286.038 1.406.068.548.138.607-.073.197-.708q-.439-.69.271.153c.811.96 1.04 1.04 1.04.374 0-.404.076-.404.6.046.355.304.67.4.762.23.087-.159.471-.466.858-.69.673-.386.682-.417.248-.834-.53-.506-.396-.549.439-.132.454.224.609.203.609-.098 0-.242.595-.561 1.523-.813.837-.23 1.523-.542 1.523-.69 0-.15.099-.18.228-.076.126.101.419.049.644-.117.356-.26.323-.331-.241-.518-.49-.16-.614-.347-.514-.767.099-.408-.004-.573-.403-.65-.38-.074-.11-.288.924-.718 1.6-.668 1.994-1.272.593-.91-1.296.33-4.511.407-4.511.104 0-.144.105-.258.234-.258s.234-.14.234-.307c0-.168-.501-.294-1.113-.279-1.537.04-3.995-.631-4.145-1.14-.085-.286.068-.42.461-.42.32 0 .58-.136.58-.298 0-.166.156-.224.35-.126s.352.015.352-.18c0-.237.176-.191.52.122.286.264.788.475 1.114.472l.592-.012-.54-.592-.538-.586 1.123-.095 1.127-.107-.82-.516-.82-.518.717-.009c.457-.01.925-.291 1.275-.776.302-.423.658-.767.796-.767.14 0 .394-.282.558-.622.429-.899-.117-1.08-1.465-.482-1.452.647-1.755.629-1.602-.122.147-.743.107-.746-1.364-.117-1.118.478-1.235.466-3.023-.2-2.414-.901-3.03-1.741-1.172-1.6a.58.58 0 0 0 .511-.316c.117-.245.312-.187.637.2.368.435.675.508 1.423.346 1.064-.236 1.13-.353.541-.988-.422-.454-.267-.441.738.086.361.187.422.132.303-.279-.113-.377-.024-.506.335-.506q.49 0 .358-.442c-.07-.242-.007-.509.148-.595.197-.107.169-.282-.103-.573-.356-.383-.34-.472.176-1.123.843-1.064.688-1.285-.549-.766-3.077 1.29-4.204 1.367-4.958.316-.427-.595-.453-.758-.169-.902.192-.095.663-.018 1.054.175 1.003.493 1.217 0 .382-.884-.585-.62-.625-.733-.234-.69.82.092 1.289-.027 1.289-.343 0-.169-.185-.316-.41-.328-.333-.016-.321-.074.08-.285.271-.145.557-.479.637-.749.124-.426.08-.448-.373-.116-.818.6-5.701.509-6.73-.123-.72-.442-.757-.506-.272-.528.446-.021.535-.147.434-.662-.115-.573-.091-.598.262-.2.514.574 1.217.896 1.217.556 0-.147.21-.203.468-.117.263.092.469.01.469-.18 0-.188.262-.335.586-.335.778 0 .74-.307-.162-1.276l-.755-.803 1.254.257c1.291.267 1.76.157 1.514-.365-.239-.509.391-1.187 1.099-1.187.8 0 .91-.506.155-.72-.3-.083-.174-.138.293-.114.531.025.799-.083.761-.325-.033-.205.052-.374.197-.374.178 0 .169-.156-.035-.481-.195-.304-.525-.427-.91-.326-.332.09-.468.092-.306 0 .54-.306.3-.797-.293-.604-.476.157-.549.095-.424-.325.11-.377.06-.445-.17-.257-.173.138-.583.279-.915.315a6 6 0 0 0-1.186.276l-.586.212.644-.718c.746-.837.853-1.328.19-.861-.653.457-1.134.417-.768-.059.525-.69.318-1.233-.22-.576-.275.337-.828.816-1.23 1.064-1.433.89-5.294-.322-5.294-1.656 0-.27-.132-.653-.286-.856-.188-.245-.195-.362-.021-.362.14 0 .314.206.386.46.087.292.422.445.91.42.421-.018 1.005.068 1.295.194.434.184.528.1.528-.424v-.631l.796.613c.574.442.849.525.968.267.091-.193.024-.485-.155-.641-.276-.245 1.09-.212 2.023.049.129.034.473-.04.768-.163.495-.208.504-.272.162-.766-.344-.5-.326-.55.227-.73.675-.221.811-.798.19-.816-.258-.006-.15-.163.293-.43.387-.232 1.012-.757 1.392-1.168l.68-.748-.856-.212c-.475-.116-.864-.343-.864-.509 0-.172-.275-.215-.645-.095-.562.184-.614.156-.351-.258.452-.72-.223-.938-.917-.297-.323.3-1.028.524-1.706.537-.923.018-1.054-.043-.651-.28.455-.266.501-.515.3-1.686-.01-.055-.37.187-.806.536-.434.353-1.146.635-1.582.635-1.46-.006-3.974-1.282-5.573-2.827-.614-.598-.485-.721.228-.221.37.257.454.214.461-.23.007-.525.02-.528.249-.01.133.298.337.467.454.375.118-.095.48.055.807.334.585.503.588.494.592-.374.003-.485.08-.724.176-.537.204.402 1.09 1.046 1.441 1.046.139 0 .267-.313.28-.69.023-.656.027-.653.182.077.091.42.213.702.272.631.058-.07.326.025.593.212.396.276.546.23.806-.24.176-.318.41-.502.52-.413s.127-.193.042-.632c-.145-.764-.126-.785.492-.478.762.377.999.19.586-.46-.246-.39-.223-.46.124-.46.394 0 .39-.034.007-.586-.222-.322-.302-.66-.182-.758.119-.098.403.092.63.423.356.516.586.562 1.493.325.049-.012-.024-.205-.17-.432-.304-.481-.123-.813.448-.813.223 0 .654-.273.959-.604l.558-.604-.703-.058a8 8 0 0 0-1.275.009c-.495.061-.532.003-.263-.42.511-.81.15-.807-.754 0-.982.874-1.575 1.024-1.106.288.248-.393.234-.442-.09-.28-.445.225-.51-.192-.11-.717.603-.788-.035-.668-.775.141-1.455 1.598-1.636 1.65-3.28.997-1.726-.68-2.883-1.536-3.23-2.386-.213-.518-.164-.592.345-.555.32.025.632.258.695.518.064.258.338.491.607.519.396.036.467-.086.359-.614-.117-.589-.068-.567.417.172.3.457.778.834 1.071.834.46 0 .513-.116.394-.892-.176-1.16.372-1.482.946-.546.39.638 1.709 1.085 1.713.586 0-.126-.342-.595-.761-1.055-.584-.638-.663-.838-.345-.844.23-.006.476.187.549.442.075.251.225.392.33.306.105-.088.255-.018.337.154.08.169.43.316.77.316.59 0 .594-.025.16-.442-.44-.423-.43-.448.205-.767.363-.18.785-.33.937-.334.152-.006.014-.208-.307-.442-.572-.414-.565-.423.321-.315.577.067 1.033-.05 1.247-.329.513-.671.15-1.051-1.013-1.051-.646 0-.995-.13-.906-.316.295-.626-.57-.638-1.547-.022-1.195.758-1.415.794-1.237.184.084-.291-.003-.46-.235-.46s-.318-.172-.234-.46c.16-.54.077-.558-.696-.175-.501.252-.555.212-.452-.306.122-.617-.272-.832-.527-.286-.082.17-.58.605-1.107.957-.85.568-1.07.595-1.905.27-.778-.307-.876-.423-.548-.662.218-.16.403-.5.403-.758 0-.362-.241-.448-1.061-.383-.8.064-1.167-.065-1.51-.519-.513-.68-1.012-3.11-.688-3.372.199-.162.311.492.227 1.333-.021.215.185.804.461 1.313.401.739.572.85.835.564.18-.196.337-.715.351-1.16.021-.637.056-.677.148-.19.176.951.785 1.687 1.399 1.687.478 0 .522-.086.314-.595-.307-.748-.31-1.03-.007-.785.129.104.234.043.234-.135s.106-.325.234-.325c.13 0 .235.193.235.43 0 .552 1.368 2.082 1.654 1.849.117-.095.22-.439.22-.767 0-.325.106-.592.242-.592s.185-.175.103-.383c-.084-.212.042-.07.286.316.241.386.52.604.614.478.096-.126.4-.273.689-.325.635-.116 1.582-.727 1.582-1.027 0-.12-.314-.387-.696-.592-.382-.209-.673-.537-.645-.73.028-.195-.27-.366-.665-.383-.82-.036-2.013-.764-1.568-.959.16-.07.293-.326.293-.565 0-.486-1.172-.61-1.537-.163-.356.433-1.275.717-1.275.393 0-.162.267-.38.6-.489.499-.164.555-.283.323-.651-.232-.365-.197-.443.16-.45.522-.013 1.403-.828 1.195-1.103-.082-.106-.63-.259-1.217-.345-1.171-.173-1.167-.168-.82-1.016.225-.55.281-.558.8-.115.52.447.564.432.71-.163.084-.35.252-.557.372-.46s.22.035.22-.144.211-.326.469-.326.469-.196.469-.43c0-.47-.495-.796-1.196-.796-.531 0-.602-.563-.1-.815.192-.097.3-.277.241-.402-.058-.126.155-.408.476-.633.79-.553.595-1.064-.41-1.064h-.827l.592-1.016c.462-.793.525-1.09.3-1.38-.222-.289-.45-.131-1.054.728-.717 1.02-.79 1.059-1.04.537-.148-.31-.373-.556-.497-.556-.28 0-1.02 1.716-1.18 2.741-.119.758-2.274 3.176-2.6 2.914-.092-.075-.138-.894-.11-1.821.028-.928-.04-1.687-.153-1.687s-.17-.51-.13-1.13c.058-.944.177-1.172.723-1.362.567-.197.638-.153.52.336-.213.881.359 1.105.814.316.239-.418.466-.574.555-.384.16.339 1.092.16 1.092-.21 0-.286-1.167-1.714-1.37-1.678-.085.016-.34-.038-.563-.115-.661-.226-.232-.657.468-.47 1.174.316 2.234.257 2.337-.133.054-.211-.054-.384-.242-.384-.19 0-.47-.209-.63-.46s-.384-.38-.5-.287c-.112.092-.203.06-.203-.077 0-.136.316-.525.703-.863.71-.62.904-1.073.462-1.073-.427 0-1.962.929-2.168 1.313-.122.227-.145.11-.059-.297.078-.365.04-.745-.082-.843-.255-.207-1.2 1.766-1.2 2.501 0 .271-.07.407-.154.297s-.3.033-.476.326c-.279.464-.302.338-.197-.968.096-1.214.317-1.806 1.172-3.115.579-.89 1.062-1.815 1.062-2.06 0-.737-1.18-.253-1.42.584-.39 1.332-.814.87-.727-.786.066-1.208-.019-1.664-.41-2.214zm-1.589 10.763c.106.008.361.274.652.7.48.706.703 1.343.703 2.041 0 .56-.045 1.025-.096 1.025-.127 0-.427-.95-.535-1.677a3 3 0 0 0-.43-1.092c-.19-.275-.345-.692-.345-.92 0-.057.014-.08.051-.077zm-.572 4.505c.446.042 1.528 1.019 1.7 1.61.304 1.05.13 1.246-.397.44-.23-.353-.55-.642-.71-.642-.272 0-.911-1.186-.74-1.37.035-.036.084-.045.147-.038zm6.4 2.501q.085 0 .2.038c.386.133.295.315-.61 1.218-.586.584-1.27 1.087-1.516 1.111-.244.024-.438.145-.438.278a.26.26 0 0 1-.258.25c-.136 0-.216-.311-.176-.69.066-.598.2-.682.961-.595.675.077.879-.011.879-.383 0-.266.157-.48.351-.48.192 0 .352-.194.352-.43 0-.208.086-.318.255-.317zm-2.716 4.39.549.354c.302.192.803.265 1.113.163.32-.105.564-.052.564.125 0 .172-.1.316-.22.316-.117 0-.365.071-.548.163s-.584-.125-.895-.48zm2.57.757c.052.01.132.081.242.2.263.285.352.29.352.02 0-.2.115-.264.262-.144.188.151.138.346-.16.633-.604.577-.805.515-.805-.24 0-.34.02-.487.11-.47zm3.53 1.12c.201 0 .307.139.227.308-.187.396-.586.396-.586 0 0-.17.157-.307.359-.307zm-5.514 4.91c.129 0 .234.69.234 1.533 0 1.554-.035 1.686-.316 1.321-.248-.328-.176-2.855.082-2.855zm-1.788 4.09c.084-.018.148.05.148.181 0 .181-.106.328-.235.328-.3 0-.3-.217 0-.46a.2.2 0 0 1 .087-.049zm1.891.654c.012-.004.035.024.05.076.065.206.067.62.009.92-.061.298-.113.132-.117-.374-.005-.38.016-.62.058-.622zm-2.915.162c.129 0 .234.058.234.132 0 .077-.105.224-.234.328s-.234.043-.234-.135.105-.325.234-.325zm-1.172.613c.13 0 .235.059.235.132 0 .077-.106.224-.235.329-.129.104-.234.042-.234-.135s.105-.326.234-.326zm-4.876.01c.16.018.358.16.51.41.476.786 1.264.89 1.7.221.386-.588.937-.598.66-.009-.075.157.026.289.218.289a.6.6 0 0 0 .5-.307c.19-.402.585-.405.585 0 0 .172-.29.463-.644.64-.354.179-1.247.393-1.978.48-1.162.137-1.359.085-1.582-.46-.14-.341-.248-.767-.248-.948 0-.233.117-.338.279-.316zm21.5 16.245c.136 0 .248.138.248.306 0 .393-.166.393-.351 0-.08-.168-.035-.306.103-.306zm-19.59 3.68c.143 0-.134.276-.614.613-.85.595-1.34.764-1.34.46 0-.19 1.61-1.073 1.954-1.073zm20.235 13.102c.258-.003.513.03.672.113.321.166.162.234-.54.249-.55.012-.997-.03-.997-.095 0-.163.434-.264.865-.267zm5.242 1.656a.53.53 0 0 1 .265.058c.185.098.126.166-.148.181-.246.016-.384-.061-.307-.162.038-.05.108-.07.19-.077zm.059 7.64c.011-.006.028 0 .044 0 .129 0 .391.132.586.297.192.163.246.298.117.298s-.394-.135-.586-.298c-.169-.144-.234-.27-.162-.297zm-40.82 12.39c.113-.022.143.043.083.174-.08.166-.24.307-.359.307-.337 0-.267-.23.138-.432.052-.028.103-.04.139-.05zm3.787 2.637.535.749.527.745.403-.804c.366-.733.417-.748.63-.248.174.408.282.438.417.153.13-.276.223-.2.338.267.16.66.6.923.6.356 0-.169.29-.31.644-.307h.645l-.703.488c-.455.313-1.06.426-1.7.334-.545-.077-.934-.012-.864.135.185.392-.164.319-.886-.172-.368-.251-.658-.307-.703-.135-.042.163-.267.203-.499.086-.387-.193-.377-.258.103-.93zm5.449.334c.049 0 .096 0 .138.019.176.077.068.218-.248.328-.39.132-.48.092-.307-.135.1-.135.27-.208.417-.212zm-2.381 12.642c.084-.018.148.05.148.181 0 .181-.106.329-.235.329-.3 0-.3-.218 0-.46a.2.2 0 0 1 .087-.05z" fill="var(--tree-color)" stroke="var(--stroke-color)" stroke-width=".268"/></svg>';
  var WEED_ICON_SVG = '<svg width="50" height="450" preserveAspectRatio="xMidYMid" version="1.0" viewBox="0 0 200 450" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="pollenWeedGrad" x1="200.2" x2="6197.9" y1="6396.5" y2="6396.5" gradientUnits="userSpaceOnUse"><stop offset="0"/></linearGradient></defs><path d="M2548 12784c-33-17-35-44-13-116 8-27 21-103 30-170 28-206 90-440 137-516 11-18 18-36 15-39-12-11-149 30-192 59-67 45-134 124-174 208-33 69-36 82-36 169 0 84-2 95-21 107-18 13-25 12-55-9-43-29-47-57-15-99 13-18 38-68 55-111 16-43 42-99 57-124 43-74 141-163 234-213 46-25 85-50 87-57 6-17-58-27-138-20-47 4-89 16-141 40-83 39-121 75-141 132-10 28-22 43-42 50-24 8-30 6-52-19-13-16-23-38-21-49 3-21 7-24 69-50 19-8 56-33 82-55 67-58 142-84 262-89 55-3 134 0 175 5s77 8 79 6-4-61-13-131-16-173-16-228c0-137 25-389 55-570 14-82 28-207 32-278 7-146 5-150-79-193-67-34-88-31-148 20-28 24-78 53-110 65s-86 42-120 67c-77 59-245 237-356 379-74 95-334 475-334 488 0 3 21 10 46 16 67 17 154 64 191 103 44 46 110 173 124 238 6 30 20 70 31 88 26 43 18 76-23 93-63 25-113-38-75-93 27-38 20-98-20-177-59-118-164-211-239-211-33 0-32 8 9 69 103 152 136 434 72 606-15 38-26 88-26 116 0 54-20 84-64 95-39 9-66-11-66-51 0-27 9-43 49-82 63-62 82-120 88-274 7-163-25-292-94-378-25-31-38-23-59 42-23 72-74 172-155 305-74 120-139 242-139 260 0 16-34 32-69 32-58 0-82-62-33-86 91-45 317-390 367-559 14-45 14-51-1-62-20-15-83 3-150 42-121 71-284 283-284 369 0 45-24 70-59 61-29-7-61-33-61-48 0-5 21-35 48-65 26-30 73-87 105-126 83-101 152-171 230-233 37-30 65-58 62-63-21-35-239-15-330 30-115 57-255 200-255 261 0 18-10 40-24 54-22 22-29 23-53 14-34-13-63-38-63-54 0-7 15-24 33-38s71-63 117-109c93-92 149-133 225-165 61-26 173-50 278-59 42-4 77-10 77-14 0-12-172-87-225-98-58-12-112-3-151 24-64 46-81 54-111 54-61 0-85-62-32-87 43-21 169-35 269-31 110 4 157 22 297 113 47 31 89 53 93 48 4-4 42-60 85-123 196-288 365-497 603-745 185-193 222-249 202-309-16-48-45-64-163-85-56-10-119-26-141-35-21-9-52-16-68-16-67 0-476 138-694 233-77 34-260 133-282 152-1 1 25 36 58 77 33 42 67 92 75 112 21 49 21 361 1 411-25 60-83 72-119 24-25-35-12-61 46-90 23-12 45-27 49-33 12-17 9-158-3-218-24-113-101-230-131-200-6 6-14 87-18 181-7 155-10 176-34 224-58 119-286 367-337 367-24 0-49-38-49-72 0-24 5-28 38-33 20-4 49-9 64-11 57-11 158-125 220-249 58-114 86-273 58-326-15-28-26-24-82 26-27 24-99 73-160 107-61 35-133 82-159 106-37 33-55 43-76 40-33-4-54-40-46-79 4-23 12-26 73-37 124-21 278-107 364-203 49-54 56-78 30-99-22-19-186 4-293 42-200 70-389 185-420 256-11 24-21 32-41 32-38 0-82-43-78-76 3-24 10-28 68-43 39-10 89-33 125-57 152-104 327-166 537-189 32-4 61-11 64-17 10-16-87-107-144-134-71-34-200-49-299-36-94 13-129 35-147 95-8 27-71 23-101-8-33-32-31-51 6-74 48-30 145-42 327-42s210 5 299 60c27 17 53 31 57 31 19 0-2-31-71-101-112-115-217-163-279-126-12 6-31 18-42 24-18 11-25 9-51-13-17-14-31-34-31-44 0-25 58-73 79-65 9 4 64 22 122 41 88 29 118 45 171 88 78 64 155 159 197 243l31 62 76-45c266-157 714-322 1069-394 61-12 117-26 126-31 24-12 30-58 13-98-36-85-186-220-309-277-190-88-637-190-917-209l-96-7-21 73c-46 161-84 233-163 305-61 57-116 87-236 130-57 20-119 48-138 60-41 28-61 30-92 8-19-13-23-23-20-62 4-55 15-61 93-42 43 10 61 10 113-4 89-23 202-77 250-120 45-41 140-212 125-227-5-5-30 7-58 27-161 117-358 158-576 119-128-22-163-37-167-69-8-64 48-74 122-21 27 19 66 40 85 45 54 15 187 12 260-6 85-20 218-84 262-125 30-28 32-32 16-44-9-6-54-15-99-18-46-4-137-20-203-36s-140-32-165-36c-84-13-113-52-70-95 28-28 50-25 101 14 78 61 233 106 364 106 70 0 105-12 105-35 0-63-275-233-455-280-33-8-115-22-183-29-67-8-129-20-138-27-24-19-14-62 18-73 20-7 42-4 90 13 35 13 132 45 215 71 178 56 285 107 400 190l83 59v-59c0-102-56-223-149-323-59-63-101-84-202-99-74-12-85-16-99-40-13-25-13-31 0-55 21-37 55-41 89-10 14 14 67 50 117 81 97 59 191 149 237 228 14 25 38 88 53 142 15 53 31 96 36 96s16-17 25-37c12-29 14-65 11-158-5-155-27-206-102-245-53-27-71-61-60-107 5-18 13-23 38-23 60 0 74 10 92 59 9 25 26 68 37 95 20 45 22 67 22 225 0 147-3 184-18 224-11 26-17 54-14 61 7 17 90 36 157 36 111 0 321 51 733 177 284 86 404 115 453 105 16-3 33-16 43-35 9-16 41-59 71-94l54-65-38-112c-22-61-47-127-57-147-47-95-281-349-494-538-154-136-288-241-309-241-9 0-28 25-47 62-67 132-118 172-297 236-162 57-184 60-211 33-37-37-19-111 27-111 9 0 33 10 52 21 104 64 345-63 394-208 9-25 14-48 12-50-2-3-30 9-62 25-88 44-158 62-270 69-132 8-247-10-352-53-46-19-105-39-132-45-39-8-49-15-54-35-4-14-1-37 7-52 17-36 53-33 109 8 142 103 186 123 301 141 105 16 257-16 340-73 37-26 34-45-10-64-108-45-152-73-363-234-58-44-129-90-157-102-50-20-53-24-53-58 0-84 67-108 104-36 58 109 461 416 547 416 25 0 31-28 19-90-15-80-63-150-172-256-74-71-115-101-168-126-52-24-72-39-77-57-7-29 7-55 34-59 59-9 301 238 392 400 63 111 84 102 89-35 5-117-15-204-73-323-53-109-99-155-179-178-56-17-76-36-76-75 0-54 88-84 111-38 6 12 40 61 75 108 146 198 181 287 192 499l7 135 42-63c52-78 67-126 67-217-1-52-7-86-27-134-33-78-34-105-5-125 56-39 83 44 76 230-7 162-19 191-153 377l-35 49 23 27c12 15 88 85 170 157 189 166 412 390 569 574 129 150 183 204 225 223 31 14 37 5 58-88 8-39 30-100 48-135 32-64 32-66 31-205-3-293-59-868-94-959l-9-24-77 30c-147 57-256 48-429-36-105-51-135-76-135-114 0-35 17-52 51-52 30 0 44 14 54 53 19 74 149 132 296 132 77 0 90-3 127-28 54-35 55-57 4-57-87 0-217-66-320-163-67-63-115-128-170-227-22-42-52-86-66-99-34-32-34-74-1-106 29-30 49-31 75-5 16 16 20 33 20 83 0 48 7 76 29 122 33 69 106 170 156 213 78 70 257 154 279 132 6-6-5-61-32-157-23-81-51-192-62-246-30-150-50-219-71-253-26-43-24-85 4-112 25-24 62-29 73-11 4 6 11 73 15 148 16 254 44 391 105 519 56 117 98 121 131 13 14-44 18-94 18-231 0-212-5-238-53-284-41-40-39-52 11-90 36-27 52-11 67 67 20 106 25 442 7 514-20 88-7 96 67 41l56-41V5861c0-1660-9-3508-26-5168l-7-693h293l1 2883c0 2706 10 4410 25 4485 5 24 14 36 38 46 61 26 75 113 21 129-49 15-59 22-69 53-11 35-14 1504-3 1584 4 30 12 62 17 70 15 24 44-2 52-47 3-21 29-84 57-139 28-54 75-165 105-245 101-269 209-492 346-710 82-131 87-160 30-170-79-13-239-105-283-163-14-18-38-61-55-97-16-35-43-81-61-103-49-61-39-136 19-136 41 0 58 30 69 127 11 100 37 158 97 213 44 40 145 95 163 88 5-2-15-57-45-122s-60-141-65-169c-12-53-7-390 5-419 11-24 64-44 85-31 29 18 25 66-13 136-24 46-36 85-41 131-15 136 42 335 118 414 54 56 69 40 81-83 12-112 44-239 104-400 30-82 65-181 77-220 24-79 51-99 100-77 42 20 34 63-24 125-73 78-123 183-172 362-48 178-50 365-5 365 28 0 80-57 127-136 67-114 100-205 108-296 8-98 19-110 69-82 20 12 38 31 40 42 2 10-10 42-27 70-16 27-62 114-101 192-40 78-93 167-117 197l-45 55 68-3c58-4 86-12 174-56 61-29 123-68 147-91 55-53 132-208 140-282 7-70 18-90 48-90 87 0 72 60-69 277-90 140-167 198-378 288-49 21-88 41-88 46s26 16 57 25c172 50 452-43 517-172 38-74 73-101 109-82 14 7 17 19 15 51-3 41-5 43-113 115-162 108-190 119-320 127-60 3-152 2-203-3-81-8-100-14-138-41-25-17-50-31-57-31-35 0-152 191-296 480-161 325-200 465-203 727-1 118 1 145 15 167 10 15 27 26 39 26s63-22 114-49c51-28 110-54 131-60 22-6 100-47 174-92 234-140 528-276 769-355 74-25 141-47 147-49 9-3 0-25-27-66-79-122-106-278-77-436 9-48 20-110 24-138 7-45 11-50 35-53 84-10 93 73 17 153-77 82-78 251-2 374 46 73 58 66 59-31 0-146 29-228 119-338 65-79 218-230 234-230 17 0 61 46 61 64 0 20-57 66-83 66-91 0-224 150-262 295-17 68-20 201-4 211 6 3 26-7 44-22 195-168 237-197 418-293 65-35 138-78 162-97 51-38 89-44 105-15 31 58-5 97-93 103-100 6-260 91-431 228-103 83-158 153-154 194l3 31 65-1c102-1 270-80 369-173 62-59 77-66 106-47 60 39 17 89-130 152-118 49-288 108-348 119-57 11-66 30-25 55 50 31 134 60 222 78 65 13 94 14 165 5 126-16 197-46 252-104 62-67 119-66 119 2 0 37-37 59-186 109-109 36-128 40-227 40-122 0-233-20-314-56-65-28-73-29-73-8 0 25 155 178 213 210 47 26 64 29 163 32 98 4 117 1 163-19 60-25 72-22 108 31 30 43 29 58-4 67-40 9-225-13-337-42-213-53-310-139-370-325l-22-65-44 2c-131 4-609 203-857 357-117 72-174 124-233 211-27 40-62 84-79 98s-31 33-31 42c0 10 13 45 30 80 43 89 60 96 280 106 96 5 272 13 390 19 305 14 388 34 680 162 74 32 139 57 146 54 6-2 26-48 45-102 57-166 138-253 311-334 88-41 153-51 176-28 7 7 12 27 12 45 0 47-36 63-86 40-69-33-133-13-227 73-59 53-144 167-133 177 3 3 27-8 53-25 65-42 166-86 233-103 68-17 287-17 350 1 50 13 60 23 60 61 0 56-65 69-100 19-49-69-267-76-387-12-50 26-201 149-202 165-1 4 29 7 67 7 141 0 394 55 546 120 50 21 117 43 149 50 42 8 62 18 73 36 15 22 15 26 1 48-30 45-88 39-144-17-40-40-173-107-296-149-110-37-265-49-381-28l-70 13 64 61c104 98 272 176 380 176 46 0 60 4 78 23 26 27 28 59 6 81-23 24-56 19-100-14-21-17-100-59-175-94-75-34-164-80-198-101-34-20-64-35-67-32-10 10 36 239 55 275 25 48 100 134 177 204 83 75 111 92 147 90 59-5 99 47 61 79-27 22-196 25-251 4s-113-12-210 34c-62 30-163 116-163 140 0 14 8 13 105-13 74-20 192-21 282-1 187 39 403 150 403 205 0 18-53 70-70 70-15 0-50-52-60-89-26-97-290-177-499-152-88 11-161 33-161 50 0 15 23 28 80 46 106 34 274 129 368 208 24 20 59 39 78 43 20 4 38 13 41 20 10 27-8 84-32 100-33 22-61 4-75-46-6-21-23-54-38-71-54-65-280-194-385-220-56-14-97-1-97 29 0 29 69 128 144 209 130 140 260 236 379 282 57 22 23 101-44 101-20 0-35-12-65-52-21-29-105-113-187-188-134-123-181-172-243-256-18-24-25-27-40-18-28 17-37 69-31 199 5 132 27 218 79 318 36 67 98 127 132 127s50 23 39 57-48 53-85 46c-22-4-30-13-38-43-5-20-29-66-52-101-63-98-96-179-114-281-20-116-15-304 9-361 14-33 14-39 2-43-8-3-29 3-48 15-76 47-150 200-151 314-1 65 0 69 32 95 49 41 45 81-13 138-34 33-45 51-45 74 0 19-6 33-16 37-26 10-82-24-107-65-34-52-89-99-134-112-60-16-143-22-208-13-85 12-80 32 15 61 190 59 233 89 318 222 30 47 70 105 89 129 55 73 37 141-38 141-30 0-39-19-39-87 0-138-141-305-297-353-59-18-193-28-193-15 0 3 30 40 66 83 96 112 116 153 194 393 38 119 77 222 85 229 17 14 20 53 5 80-11 20-77 46-88 35-4-4-8-50-8-103-1-69-9-127-28-201-50-196-113-314-232-439-56-59-64-64-80-51-14 12-16 27-11 114 9 149 54 298 109 363 32 37 35 67 12 100-18 27-65 29-89 5-13-12-15-25-10-52 11-56 8-88-19-170-14-42-34-116-46-166-27-118-38-145-61-145-19 0-62 53-119 147-92 152-123 390-61 473 11 16 21 40 21 54 0 46-59 81-89 52-13-14-16-37-14-137 6-306 69-480 225-622 43-39 76-72 75-74-8-7-175 19-214 33-48 18-138 109-160 163-10 23-13 49-8 77 5 35 2 47-14 63-52 52-120 26-101-39 12-42 172-250 213-278 53-35 125-59 202-67 39-4 76-12 83-17 42-34-43-292-202-613-128-257-200-375-243-394-15-7-58-33-94-58-80-54-113-62-254-63h-111l-56 93c-31 50-65 119-76 152-48 143-74 397-74 722 0 240 10 329 40 356 9 9 61 23 114 31 113 18 163 36 236 84 71 46 121 108 189 231 31 57 67 117 80 132 42 49 18 139-36 139-29 0-39-19-56-119-20-116-42-176-89-239-47-64-88-95-189-141-158-73-179-62-83 40 78 82 129 177 156 291 22 91 43 299 38 368l-3 45-43 3c-57 4-61-11-28-101 62-170 2-409-140-555-94-97-99-93-101 103-5 385-6 403-31 421-21 14-25 14-47 0-45-30-48-50-14-104 68-110 80-399 20-492-26-39-110 62-152 183-38 110-57 236-69 451-10 199-16 215-62 190zm2106-1111c3-10 12-79 21-153 23-206 30-232 82-301 26-34 68-77 95-97 26-20 48-43 48-51 0-58-532-460-765-578-118-60-175-79-270-92-48-7-95-23-152-52l-82-41-33 20c-18 12-53 41-77 66l-44 44 61 114c33 62 92 174 132 248 84 156 171 342 228 485 39 98 102 286 102 304 0 6 4 21 9 35l10 24 78-30c153-60 313-49 457 32 80 45 92 47 100 23zm369-690c118-161 207-218 363-233 53-5 98-11 101-14 11-10-11-48-78-137-114-150-135-201-153-377-8-68-17-126-20-130-24-24-96 100-116 200-13 69-6 168 16 210 8 16 36 39 63 53 58 29 68 50 45 99-24 50-41 52-80 11-60-64-100-241-84-370 11-87 20-111 72-198 22-37 38-70 35-73-13-13-204-90-337-135-202-70-339-92-539-87-154 3-157 4-211 35-30 17-97 43-149 58-132 37-136 40-171 153-17 53-30 104-30 113 0 30 184 160 409 288 237 136 449 287 680 485 68 58 128 106 133 106 4 0 27-26 51-57zM3042 8967c3-161 7-484 7-719l1-428h-64c-88 0-113 11-125 55-5 20-8 50-5 68 56 436 62 504 74 839 14 392 21 458 57 493 15 15 17 15 32 0 14-13 17-59 23-308zm-10-1204c13-12 15-19 7-29-17-21-79 13-79 44 0 18 46 8 72-15z" fill="var(--weed-color)" stroke-width="10" transform="matrix(.03335 0 0 -.03517 -6.694 450)" stroke="url(#pollenWeedGrad)"/></svg>';

  var mount = document.getElementById('pollenCard18');
  if (!mount) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (link removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the body can reclaim that space. Card height stays 195px: 20px
  // title band (border-top, unchanged) + 175px body (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar ("Current Pollen Risk" -- from the actual screenshot) --
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Current Pollen Risk');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: 3 pollen icons, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'stretch';
  leftPane.style.justifyContent = 'space-around';
  leftPane.style.padding = '10px 6px';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '8.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var ICONS = [
    { key: 'grass', label: 'Grass Pollen', shortKey: 'Grass', svg: GRASS_ICON_SVG, colorVar: '--grass-color' },
    { key: 'tree',  label: 'Tree Pollen',  shortKey: 'Tree',  svg: TREE_ICON_SVG,  colorVar: '--tree-color'  },
    { key: 'weed',  label: 'Weed Pollen',  shortKey: 'Weed',  svg: WEED_ICON_SVG,  colorVar: '--weed-color'  }
  ];

  var columnEls = ICONS.map(function(icon){
    var col = document.createElement('div');
    col.style.display = 'flex';
    col.style.flexDirection = 'column';
    col.style.alignItems = 'center';
    col.style.justifyContent = 'center';
    col.style.flex = '1 1 0';
    col.style.minWidth = '0';
    leftPane.appendChild(col);

    var iconWrap = document.createElement('div');
    iconWrap.style.flex = '1 1 auto';
    iconWrap.style.width = '100%';
    iconWrap.style.minHeight = '0';
    iconWrap.style.display = 'flex';
    iconWrap.style.alignItems = 'center';
    iconWrap.style.justifyContent = 'center';
    iconWrap.innerHTML = icon.svg;
    col.appendChild(iconWrap);

    var svgEl = iconWrap.querySelector('svg');
    if (svgEl){
      var bbox = svgEl.getBBox();
      var pad = Math.max(bbox.width, bbox.height) * 0.05;
      svgEl.setAttribute('viewBox',
        (bbox.x - pad) + ' ' + (bbox.y - pad) + ' ' + (bbox.width + 2 * pad) + ' ' + (bbox.height + 2 * pad));
      svgEl.removeAttribute('width');
      svgEl.removeAttribute('height');
      svgEl.style.height = '100%';
      svgEl.style.width = 'auto';
      svgEl.style.maxWidth = '100%';
    }

    // Risk badge — black text on a risk-coloured pill, not coloured text
    // on its own. Coloured text (the previous treatment) was hard to
    // read in light theme, since several risk colours (yellow, pale
    // green) have poor contrast against a white/light card face; a solid
    // colour swatch behind fixed black text reads clearly in both
    // themes regardless of how light the risk colour itself is.
    var imageLabel = document.createElement('div');
    DivumWXI18N.applyLabel(imageLabel, icon.shortKey);
    imageLabel.style.fontSize = '9px';
    imageLabel.style.fontWeight = '700';
    imageLabel.style.color = '#111111';
    imageLabel.style.marginTop = '4px';
    imageLabel.style.padding = '1px 8px';
    imageLabel.style.borderRadius = '8px';
    imageLabel.style.whiteSpace = 'nowrap';
    col.appendChild(imageLabel);

    var riskLabel = addChipRow(icon.label);

    return { col: col, iconWrap: iconWrap, imageLabel: imageLabel, riskLabel: riskLabel, icon: icon };
  });

  // Whole card is a click-through to the pollen chart/records page — an
  // absolutely-positioned transparent overlay anchor, appended last so it
  // paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=pollen&embed=1';
  cardLink.setAttribute('data-modal', 'Pollen');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Pollen Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=pollen&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  function renderCard(v){
    columnEls.forEach(function(c){
      var risk = v[c.icon.key];
      c.iconWrap.querySelector('svg').style.setProperty(c.icon.colorVar, risk.color);
      c.imageLabel.style.background = risk.color;
      c.riskLabel.textContent = DivumWXI18N.t('Risk') + ' ' + DivumWXI18N.t(risk.risk);
      c.riskLabel.style.color = 'var(--bw-accent)';
    });
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(loop){
        var o = loop.observations || {};
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

        var grass = num(o.grass_pollen, 0);
        var tree = Math.max(num(o.alder_pollen, 0), num(o.birch_pollen, 0), num(o.olive_pollen, 0));
        var weed = Math.max(num(o.mugwort_pollen, 0), num(o.ragweed_pollen, 0));

        lastData = {
          grass: pollenRisk(grass, GRASS_BANDS),
          tree: pollenRisk(tree, TREE_WEED_BANDS),
          weed: pollenRisk(weed, TREE_WEED_BANDS)
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardPollen: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardPollen.js failed:", e);
}

/* ===== cardGreenhouseGas.js ===== */
try {
/*
##############################################################################################
# cardGreenhouseGas.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardGreenhouseGas.js =====================
(function(){
  var LOOP_JSON_URL = './jsondata/loop.json';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }

  var mount = document.getElementById('greenhouseGasCard19');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar ----------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Greenhouse Gas');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: gas-bubble illustration, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Fixed row height (rather than sizing purely to font metrics) so the 6
  // rows sit with real breathing room and recenter as a group within the
  // pane, rather than each row's height being derived from font metrics
  // that left the whole list reading as cramped. Font sizes nudged down
  // slightly too (7px/9.5px → 6.5px/8.5px).
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '20px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '6.5px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    labelEl.style.whiteSpace = 'nowrap';
    labelEl.style.overflow = 'hidden';
    labelEl.style.textOverflow = 'ellipsis';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '8.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var no2Text = addChipRow('Nitrogen Dioxide');
  var coText   = addChipRow('Carbon Monoxide');
  var o3Text    = addChipRow('Ozone');
  var so2Text    = addChipRow('Sulphur Dioxide');
  var aodText     = addChipRow('Aerosol Optical Depth');
  var nh3Text      = addChipRow('Ammonia');
  nh3Text.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the greenhouse gas chart/records
  // page — an absolutely-positioned transparent overlay anchor, appended
  // last so it paints on top of everything else and actually receives
  // the click. top/bottom match the title band (-20px) and this card's
  // own border-bottom override (0, set above). Class name lets the
  // shared hover-tooltip script (indexNew.html) find it and read
  // data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=gases&embed=1';
  cardLink.setAttribute('data-modal', 'Greenhouse Gases');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Greenhouse Gases Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=gases&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175;
  var innerColor = 'rgb(230,200,200)';
  var gasColors = {
    no2: 'rgba(255,99,71,1)',
    so2: 'rgba(97,88,132,1)',
    co:  'rgba(46,139,87,1)',
    o3:  'rgba(0,127,255,1)',
    aod: 'rgba(233,0,118,1)',
    nh3: 'rgba(207,40,72,1)'
  };

  function makeGradient(defs, id, outerColor){
    var g = defs.append('radialGradient')
      .attr('id', id).attr('cx', '50%').attr('cy', '50%').attr('r', '50%').attr('fx', '50%').attr('fy', '50%');
    g.append('stop').attr('offset', '0%').style('stop-color', innerColor);
    g.append('stop').attr('offset', '90%').style('stop-color', outerColor);
    return id;
  }
  function addCircle(svg, cx, cy, r, gradId, strokeColor){
    svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', r)
      .style('fill', 'url(#' + gradId + ')').style('stroke', strokeColor).style('stroke-width', '2px');
  }
  function addLine(svg, x1, y1, x2, y2, color){
    svg.append('line').attr('x1', x1).attr('y1', y1).attr('x2', x2).attr('y2', y2)
      .style('stroke', color).style('stroke-width', '3px').style('stroke-linecap', 'round');
  }
  // Identifies which cluster is which — the numeric readings live in the
  // chip rows now, but the image needs at least the gas name to be
  // legible on its own. Centered in each cluster's main bubble (same x,y
  // as that cluster's own center, passed to clusterGroup() below) rather
  // than off to the side, so it reads as a label on the bubble itself.
  // Font-size is 13px, not the intended 9px, because every call site sits
  // inside a cluster group scaled by 0.7 (see clusterGroup()) — 13 * 0.7
  // = 9.1 actual rendered pixels, keeping the text a normal size instead
  // of shrinking along with the bubble artwork around it.
  // Fixed black, not theme-aware — it sits on the bubble's own gradient
  // fill rather than the card background, so it needs to stay readable
  // against that light-centred gradient in both themes, not flip to
  // near-white in dark mode where it would disappear.
  function addLabel(svg, x, y, text){
    svg.append('text').text(text).attr('x', x).attr('y', y).attr('dy', '0.35em').attr('text-anchor', 'middle')
      .style('font-size', '13px').style('font-family', 'inherit').style('font-weight', 'bold')
      .style('fill', '#111111');
  }

  // Recenters+rescales one gas's cluster of circles/lines (still using its
  // original hand-tuned coordinates, unchanged) into a grid cell in the new
  // narrower pane — a group transform, not a rewrite of every coordinate.
  function clusterGroup(svg, oldCx, oldCy, newCx, newCy, scale){
    return svg.append('g').attr('transform',
      'translate(' + newCx + ',' + newCy + ') scale(' + scale + ') translate(' + (-oldCx) + ',' + (-oldCy) + ')');
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');
    var defs = svg.append('defs');

    // 2 columns x 3 rows of gas clusters (was 3x2 in the old wide canvas) —
    // each cluster's own internal circle/line coordinates are untouched,
    // just recentered via clusterGroup() into its new grid cell. Scale
    // nudged down from 0.75 — slightly smaller clusters read as less
    // crowded, and happen to centre the whole grid a little more evenly
    // top-to-bottom within the pane as a side effect.
    var scale = 0.7;

    var gNo2 = makeGradient(defs, 'ghgNo2Gradient', gasColors.no2);
    var no2G = clusterGroup(svg, 40, 40, 48, 30, scale);
    addCircle(no2G, 40, 40, 20, gNo2, gasColors.no2);
    addCircle(no2G, 40, 10, 6, gNo2, gasColors.no2);
    addCircle(no2G, 54, 60, 10, gNo2, gasColors.no2);
    addCircle(no2G, 65, 25, 5.5, gNo2, gasColors.no2);
    addCircle(no2G, 70, 45, 2.5, gNo2, gasColors.no2);
    addCircle(no2G, 17, 48, 7.5, gNo2, gasColors.no2);
    addCircle(no2G, 32, 67, 3, gNo2, gasColors.no2);
    addCircle(no2G, 18, 24, 3, gNo2, gasColors.no2);
    addLabel(no2G, 40, 40, 'NO\u2082');

    var gCo = makeGradient(defs, 'ghgCoGradient', gasColors.co);
    var coG = clusterGroup(svg, 155, 40, 132, 30, scale);
    addLine(coG, 168, 7, 158.5, 30, gasColors.co);
    addLine(coG, 115, 47, 135, 44, gasColors.co);
    addCircle(coG, 155, 40, 20, gCo, gasColors.co);
    addCircle(coG, 168, 7, 6, gCo, gasColors.co);
    addCircle(coG, 108, 48, 7, gCo, gasColors.co);
    addCircle(coG, 168, 60, 10, gCo, gasColors.co);
    addCircle(coG, 140, 15, 3.5, gCo, gasColors.co);
    addCircle(coG, 180, 30, 2.5, gCo, gasColors.co);
    addCircle(coG, 138, 63, 2.5, gCo, gasColors.co);
    addLabel(coG, 155, 40, 'CO');

    var gO3 = makeGradient(defs, 'ghgO3Gradient', gasColors.o3);
    var o3G = clusterGroup(svg, 265, 40, 48, 87, scale);
    addLine(o3G, 210, 40, 265, 40, gasColors.o3);
    addLine(o3G, 300, 18, 270, 40, gasColors.o3);
    addLine(o3G, 240, 9, 265, 40, gasColors.o3);
    addCircle(o3G, 265, 40, 20, gO3, gasColors.o3);
    addCircle(o3G, 240, 9, 5, gO3, gasColors.o3);
    addCircle(o3G, 300, 18, 5, gO3, gasColors.o3);
    addCircle(o3G, 210, 40, 7, gO3, gasColors.o3);
    addCircle(o3G, 277.5, 60, 10, gO3, gasColors.o3);
    addCircle(o3G, 242.5, 62.5, 3, gO3, gasColors.o3);
    addCircle(o3G, 272, 12.5, 3.5, gO3, gasColors.o3);
    addCircle(o3G, 295, 40, 2.5, gO3, gasColors.o3);
    addLabel(o3G, 265, 40, 'O\u2083');

    var gSo2 = makeGradient(defs, 'ghgSo2Gradient', gasColors.so2);
    var so2G = clusterGroup(svg, 40, 117.5, 132, 87, scale);
    addLine(so2G, 15, 90, 40, 117.5, gasColors.so2);
    addLine(so2G, 40, 117.5, 85, 117.5, gasColors.so2);
    addCircle(so2G, 40, 117.5, 20, gSo2, gasColors.so2);
    addCircle(so2G, 54, 137, 10, gSo2, gasColors.so2);
    addCircle(so2G, 20, 143, 6, gSo2, gasColors.so2);
    addCircle(so2G, 10, 120, 3, gSo2, gasColors.so2);
    addCircle(so2G, 15, 90, 5, gSo2, gasColors.so2);
    addCircle(so2G, 85, 117.5, 5, gSo2, gasColors.so2);
    addCircle(so2G, 55, 100, 6, gSo2, gasColors.so2);
    addCircle(so2G, 40, 90, 2.5, gSo2, gasColors.so2);
    addLabel(so2G, 40, 117.5, 'SO\u2082');

    var gAod = makeGradient(defs, 'ghgAodGradient', gasColors.aod);
    var aodG = clusterGroup(svg, 155, 117.5, 48, 144, scale);
    addLine(aodG, 122, 145, 155, 117.5, gasColors.aod);
    addCircle(aodG, 155, 117.5, 20, gAod, gasColors.aod);
    addCircle(aodG, 168, 137, 10, gAod, gasColors.aod);
    addCircle(aodG, 155, 87.5, 6, gAod, gasColors.aod);
    addCircle(aodG, 180, 103, 5, gAod, gasColors.aod);
    addCircle(aodG, 185, 120, 2.5, gAod, gasColors.aod);
    addCircle(aodG, 132, 110, 7, gAod, gasColors.aod);
    addCircle(aodG, 122, 145, 5, gAod, gasColors.aod);
    addCircle(aodG, 145, 142.5, 2.5, gAod, gasColors.aod);
    addLabel(aodG, 155, 117.5, 'AOD');

    var gNh3 = makeGradient(defs, 'ghgNh3Gradient', gasColors.nh3);
    var nh3G = clusterGroup(svg, 265, 117.5, 132, 144, scale);
    addLine(nh3G, 220, 100, 265, 117.5, gasColors.nh3);
    addLine(nh3G, 265, 117.5, 290, 90, gasColors.nh3);
    addCircle(nh3G, 265, 117.5, 20, gNh3, gasColors.nh3);
    addCircle(nh3G, 277.5, 137, 10, gNh3, gasColors.nh3);
    addCircle(nh3G, 220, 100, 6, gNh3, gasColors.nh3);
    addCircle(nh3G, 290, 90, 4, gNh3, gasColors.nh3);
    addCircle(nh3G, 250, 135, 7, gNh3, gasColors.nh3);
    addCircle(nh3G, 235, 120, 2.5, gNh3, gasColors.nh3);
    addCircle(nh3G, 255, 90, 5, gNh3, gasColors.nh3);
    addCircle(nh3G, 293, 115, 3, gNh3, gasColors.nh3);
    addLabel(nh3G, 265, 117.5, 'NH\u2083');

    // ---- Right pane: 6 readouts as label/value chip rows ----
    no2Text.textContent = v.no2.toFixed(1) + ' \u00B5g/m\u00B3';
    coText.textContent = Math.round(v.co) + ' \u00B5g/m\u00B3';
    o3Text.textContent = Math.round(v.o3) + ' \u00B5g/m\u00B3';
    so2Text.textContent = v.so2.toFixed(1) + ' \u00B5g/m\u00B3';
    aodText.textContent = v.aod.toFixed(2);
    nh3Text.textContent = v.nh3.toFixed(1) + ' \u00B5g/m\u00B3';
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(loop){
        var o = loop.observations || {};
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

        lastData = {
          no2: num(o.no2, 0),
          so2: num(o.so2, 0),
          co: num(o.co, 0),
          o3: num(o.o3, 0),
          aod: num(o.aerosol_optical_depth, 0),
          nh3: num(o.nh3, 0)
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardGreenhouseGas: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardGreenhouseGas.js failed:", e);
}

/* ===== cardAirquality.js ===== */
try {
/*
##############################################################################################
# cardAirquality.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardAirquality.js =====================

(function(){
  var LOOP_JSON_URL = './jsondata/loop.json';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }

  // -- DAQI band tables, ported from the PHP's $daqiBands -----------------
  var STANDARD_BANDS = [
    { max: 11,  band: 1,  desc: 'Low' },
    { max: 22,  band: 2,  desc: 'Low' },
    { max: 33,  band: 3,  desc: 'Low' },
    { max: 44,  band: 4,  desc: 'Moderate' },
    { max: 55,  band: 5,  desc: 'Moderate' },
    { max: 66,  band: 6,  desc: 'Moderate' },
    { max: 77,  band: 7,  desc: 'High' },
    { max: 88,  band: 8,  desc: 'High' },
    { max: 100, band: 9,  desc: 'High' },
    { max: Infinity, band: 10, desc: 'Very High' }
  ];
  var PM10_BANDS = [
    { max: 16, band: 1,  desc: 'Low' },
    { max: 33, band: 2,  desc: 'Low' },
    { max: 50, band: 3,  desc: 'Low' },
    { max: 58, band: 4,  desc: 'Moderate' },
    { max: 66, band: 5,  desc: 'Moderate' },
    { max: 75, band: 6,  desc: 'Moderate' },
    { max: 83, band: 7,  desc: 'High' },
    { max: 91, band: 8,  desc: 'High' },
    { max: 100, band: 9,  desc: 'High' },
    { max: Infinity, band: 10, desc: 'Very High' }
  ];
  var DAQI_BANDS = { pm1_0: STANDARD_BANDS, pm2_5: STANDARD_BANDS, pm4_0: STANDARD_BANDS, pm10_0: PM10_BANDS };
  var DAQI_COLORS = {
    1: '#98fb98', 2: '#bfff00', 3: '#008000', 4: '#ffff00', 5: '#ffbf00',
    6: '#bfff00', 7: '#fa8072', 8: '#ff0000', 9: '#800000', 10: '#800080'
  };

  function calculateDAQI(value, pollutant){
    var bands = DAQI_BANDS[pollutant];
    for (var i = 0; i < bands.length; i++){
      if (value <= bands[i].max) return bands[i];
    }
    return bands[0];
  }

  // -- AQI category imagery, ported from cardAQI.js's UK DAQI band->image
  // assignments (bands 1-3 goodair, 4-6 modair, 7 uhfsair, 8-9 uhair, 10 vhair)
  var ICON_BASE = './svg/aqi/';
  var ICON_VER = '?ver=1.4';
  var DAQI_ICONS = {
    1: 'goodair', 2: 'goodair', 3: 'goodair',
    4: 'modair',  5: 'modair',  6: 'modair',
    7: 'uhfsair',
    8: 'uhair',   9: 'uhair',
    10: 'vhair'
  };
  function iconUrl(band){
    return ICON_BASE + (DAQI_ICONS[band] || 'goodair') + '.svg' + ICON_VER;
  }

  var POLLUTANTS = [
    { key: 'pm2_5',  label: 'PM2.5' },
    { key: 'pm10_0', label: 'PM10.0' },
    { key: 'pm1_0',  label: 'PM1.0' },
    { key: 'pm4_0',  label: 'PM4.0' }
  ];

  var mount = document.getElementById('airqualityCard20');
  if (!mount) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the body can reclaim that space. Card height stays 195px: 20px
  // title band (border-top, unchanged) + 175px body (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar ------------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Airquality (UK DAQI)');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: pollutant icons, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.flexDirection = 'column';
  leftPane.style.padding = '8px 10px';
  contentWrap.appendChild(leftPane);

  var grid = document.createElement('div');
  grid.style.display = 'grid';
  grid.style.gap = '4px';
  grid.style.width = '100%';
  grid.style.flex = '1 1 auto';
  grid.style.minHeight = '0';
  leftPane.appendChild(grid);

  // Overall reading — moved down here into the image section (was a row
  // in the chip list) so it reads as the hero value under the icons,
  // same idiom as the gauge cards' hero-value-below-the-dial.
  var overallCaption = document.createElement('div');
  overallCaption.style.flex = '0 0 auto';
  overallCaption.style.textAlign = 'center';
  overallCaption.style.marginTop = '4px';
  leftPane.appendChild(overallCaption);

  var overallLabel = document.createElement('div');
  DivumWXI18N.applyLabel(overallLabel, 'Overall');
  overallLabel.style.fontSize = '7px';
  overallLabel.style.fontVariantCaps = 'small-caps';
  overallLabel.style.letterSpacing = '.06em';
  overallLabel.style.color = overlayTextColor;
  overallLabel.style.opacity = '0.85';
  overallCaption.appendChild(overallLabel);

  // Black text on a band-coloured pill, same treatment as each
  // pollutant's own label above (and the Pollen card's image labels) —
  // coloured text alone was hard to read in light theme against some of
  // the lighter band colours.
  var overallValue = document.createElement('div');
  overallValue.style.display = 'inline-block';
  overallValue.style.fontSize = '12px';
  overallValue.style.fontWeight = '700';
  overallValue.style.color = '#111111';
  overallValue.style.padding = '1px 10px';
  overallValue.style.borderRadius = '9px';
  overallCaption.appendChild(overallValue);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Whole card is a click-through to the air quality chart/records page —
  // an absolutely-positioned transparent overlay anchor, appended last so
  // it paints on top of everything else and actually receives the click.
  // top/bottom match the title band (-20px) and this card's own
  // border-bottom override (0, set above). Class name lets the shared
  // hover-tooltip script (indexNew.html) find it and read data-modal.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=airquality&embed=1';
  cardLink.setAttribute('data-modal', 'Air Quality');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Air Quality Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=airquality&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  function gridTemplateFor(count){
    if (count <= 1) return { columns: '1fr', rows: '1fr' };
    if (count === 2) return { columns: 'repeat(2, 1fr)', rows: '1fr' };
    return { columns: 'repeat(2, 1fr)', rows: 'repeat(2, 1fr)' };
  }
  function sizeFor(count){
    if (count <= 1) return { circle: 84, icon: 111, label: 11 };
    if (count === 2) return { circle: 60, icon: 79.5, label: 9.5 };
    return { circle: 44, icon: 58.5, label: 8 };
  }

  // Icon inside a round hazard-coloured pill (icons are fixed black/dark
  // artwork, so the coloured circle behind them is what carries the
  // severity — same "colour carries meaning" idea as the rest of the
  // dashboard, just as a badge here instead of a filled row). Labelled
  // underneath with the particle size, same colour as the pill so the
  // label and its reading tie together visually.
  function buildIconCell(size){
    var cell = document.createElement('div');
    cell.style.display = 'flex';
    cell.style.flexDirection = 'column';
    cell.style.alignItems = 'center';
    cell.style.justifyContent = 'center';
    cell.style.gap = '4px';
    cell.style.minWidth = '0';
    cell.style.minHeight = '0';
    cell.style.overflow = 'hidden';

    var circle = document.createElement('div');
    circle.style.width = size.circle + 'px';
    circle.style.height = size.circle + 'px';
    circle.style.flexShrink = '0';
    circle.style.borderRadius = '50%';
    // Icon can now be larger than the pill itself (see sizeFor above), so
    // this clips it to the circle rather than letting the square icon
    // spill out past the round pill's edge.
    circle.style.overflow = 'hidden';
    circle.style.display = 'flex';
    circle.style.alignItems = 'center';
    circle.style.justifyContent = 'center';
    cell.appendChild(circle);

    var icon = document.createElement('img');
    icon.style.width = size.icon + 'px';
    icon.style.height = size.icon + 'px';
    icon.style.objectFit = 'contain';
    circle.appendChild(icon);

    // Risk badge — black text on a band-coloured pill, not coloured text
    // on its own, same treatment as the Pollen card's image labels
    // (coloured text was hard to read in light theme against several of
    // the lighter band colours).
    var label = document.createElement('div');
    label.style.fontSize = size.label + 'px';
    label.style.fontWeight = '700';
    label.style.color = '#111111';
    label.style.whiteSpace = 'nowrap';
    label.style.padding = '1px 6px';
    label.style.borderRadius = '8px';
    cell.appendChild(label);

    return { cell: cell, circle: circle, icon: icon, label: label };
  }

  // Same chip-row idiom as Current Conditions — single line now that the
  // band description lives on the icon's pill/label in the image and the
  // overall reading has its own spot below the icons, instead of every
  // row carrying a second detail line (which is what was overflowing the
  // pane before).
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.height = '20px';
    row.style.boxSizing = 'border-box';
    row.style.overflow = 'hidden';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    labelEl.textContent = label;
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var pollutantPills = [];
  var currentLayoutKey = undefined;

  function ensureGrid(available){
    var layoutKey = available.map(function(p){ return p.key; }).join(',');
    if (layoutKey === currentLayoutKey) return;
    currentLayoutKey = layoutKey;

    grid.innerHTML = '';
    rightPane.innerHTML = '';
    pollutantPills = [];

    if (available.length === 0){
      grid.style.gridTemplateColumns = '1fr';
      grid.style.gridTemplateRows = '1fr';
      var placeholder = document.createElement('div');
      placeholder.style.display = 'flex';
      placeholder.style.alignItems = 'center';
      placeholder.style.justifyContent = 'center';
      placeholder.style.textAlign = 'center';
      placeholder.style.fontSize = '10px';
      placeholder.style.color = overlayTextColor;
      placeholder.style.opacity = '0.7';
      // ensureGrid only actually rebuilds the DOM when the set of
      // available pollutants changes (see the layoutKey guard above) --
      // if there's genuinely no particle sensor, that never changes, so
      // this text is effectively set once and never revisited, same
      // class of bug as everything else fixed this session.
      DivumWXI18N.applyLabel(placeholder, 'No particle sensor data available');
      grid.appendChild(placeholder);
      return;
    }

    var tmpl = gridTemplateFor(available.length);
    grid.style.gridTemplateColumns = tmpl.columns;
    grid.style.gridTemplateRows = tmpl.rows;
    var size = sizeFor(available.length);

    pollutantPills = available.map(function(p){
      var built = buildIconCell(size);
      built.label.textContent = p.label;
      grid.appendChild(built.cell);
      var chipValue = addChipRow(p.label);
      return { key: p.key, els: built, chipValue: chipValue };
    });
    rightPane.lastElementChild.style.borderBottom = 'none'; // last row — no divider under it
  }

  function renderCard(v){
    var available = POLLUTANTS.filter(function(p){
      return Object.prototype.hasOwnProperty.call(v, p.key);
    });
    ensureGrid(available);

    if (available.length === 0){
      overallLabel.style.color = overlayTextColor;
      overallValue.textContent = DivumWXI18N.t('No Data');
      overallValue.style.background = 'transparent';
      overallValue.style.color = overlayTextColor;
      return;
    }

    var highestBand = 1, overallDesc = 'Low';
    pollutantPills.forEach(function(p){
      var value = v[p.key];
      var daqi = calculateDAQI(value, p.key);
      var color = DAQI_COLORS[daqi.band];

      p.els.circle.style.background = color;
      p.els.icon.src = iconUrl(daqi.band);
      p.els.label.style.background = color;
      p.chipValue.textContent = value.toFixed(1) + ' \u00B5g/m\u00B3';
      if (daqi.band > highestBand){ highestBand = daqi.band; overallDesc = DivumWXI18N.t(daqi.desc); }
    });

    var overallColor = DAQI_COLORS[highestBand];
    overallLabel.style.color = overlayTextColor;
    overallValue.textContent = overallDesc;
    overallValue.style.background = overallColor;
  }

  var lastData = null;
  function refresh(){
    fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(loop){
        var o = loop.observations || {};
        var raw = {};
        POLLUTANTS.forEach(function(p){
          var val = o[p.key];
          if (typeof val === 'number' && !isNaN(val)) raw[p.key] = val;
        });

        lastData = raw;
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardAirquality: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);




  window.addEventListener('themechange', function(){
    if (lastData) renderCard(lastData);
  });
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
})();
} catch (e) {
  console.error("cardsBundle: cardAirquality.js failed:", e);
}

/* ===== cardVapourPressureDeficit.js ===== */
try {
/*
##############################################################################################
# cardVapourPressureDeficit.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardVapourPressureDeficit.js =====================
(function(){
  var VPD_TREE_MARKUP = '<symbol viewBox="0 0 117.74 106.411" id="vpdTreeArt"><path style="fill:#9d7b6c;stroke:none" d="m108.656 178.201.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m104.687 178.201.176.089zm2.998-.088v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m109.802 177.849.265.264zm-5.556.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.188 177.584.264.265zm.529.265.264.264zm5.732.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m110.42 177.496.088.176zm-7.496.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m102.394 177.32.264.264zm7.143 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.539 177.143.176.088zm-4.498.265.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m100.983 177.143.176.088zm5.027 0 .176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m105.304 176.79.265.265zm-4.85.353.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.748 176.79.264.265zm5.292 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m104.51 176.526.265.264zm-5.027.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.954 176.526.265.264zm5.292 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M110.596 176.261v.265h1.058zm-11.906.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M107.685 176.261v.265h2.91z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m103.981 176.261.265.265zm2.91 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.425 176.261.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M113.242 175.997v.264h.793zm-15.082.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m106.01 176.085.176.088zm6.438-.088v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m105.569 175.997.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.896 175.997.264.264zm5.556 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.631 175.997.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.04 175.732.264.265zm9.525 0v.265h1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m103.188 175.732.264.265zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.367 175.732.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M112.448 175.467v.265l4.762.265v-.53zm5.027 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.837 175.467.265.265zm5.821 0 .265.265zm1.588 0 .264.265zm7.408 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m110.86 175.203.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.308 175.203.265.264zm6.086 0 .264.264zm1.323 0 .264.264zm6.79.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m109.802 174.938.265.265zm-13.758.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.78 174.938.264.265zm6.35 0 .265.265zm1.058 0 .265.265zm6.35 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m109.273 174.674.265.264zm-13.758.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.008 174.674.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.658 174.674.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m102.394 174.409.264.265zm6.085 0 .265.265zm-13.494.265.265.264zm6.88 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m101.6 174.409.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.456 174.409.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m86.695 174.497.177.088zm7.408 0 .177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.52 174.497.177.088zm2.646 0 .176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m108.215 174.144.264.265zm-25.136.265.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.398 174.144.264.265zm7.673 0 .264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m87.753 174.233.177.088zm5.38-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.55 174.144.265.265zm4.762 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M113.506 173.88v.264h.794Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M109.802 173.88v.264h3.704z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m100.806 173.88.265.264zm.794 0 .265.264zm7.938 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M91.81 173.88v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m89.076 173.968.177.088zm2.382 0 .176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 173.88.265.264zm6.526.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m116.064 173.703.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.008 173.615.265.265zm6.086 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m108.744 173.615.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m101.335 173.615.265.265zm6.086 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m100.277 173.615.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.492 173.615.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m117.475 173.35.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m108.48 173.35.264.265zm8.643.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m108.215 173.35.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m101.07 173.35.265.265zm6.085 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m99.748 173.35.264.265zm1.058 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.483 173.35.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m118.974 173.174.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m107.95 173.086.265.265zm10.495.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m99.219 173.086.264.265zm1.323 0 .264.265zm6.35 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.954 173.086.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M120.915 172.822v.264h.793zm-40.217.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M115.358 172.822v.264h1.323zm4.763 0v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M111.654 172.822v.264h3.704z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m100.277 172.822.265.264zm6.086 0 .264.264zm1.322 0 .265.264zm3.175 0v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m98.425 172.822.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.072 172.91.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M124.619 172.557v.265h.794zm-44.186.265.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M118.533 172.557v.265h.794zm4.498 0v.265h1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m109.978 172.645.177.088zm7.497-.088v.265h1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.538 172.557.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.098 172.557.265.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.748 172.557.264.265zm6.085 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.367 172.557.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.014 172.645.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m125.765 172.469.088.176zm-45.596.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M121.18 172.292v.265h1.057zm4.322.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m109.008 172.292.265.265zm11.377 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m106.892 172.292.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m105.569 172.292.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.219 172.292.264.265zm6.085 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m96.22 172.38.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.691 172.38.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m108.48 172.028.264.264zm14.817 0v.264h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m108.215 172.028.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.363 172.028.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.69 172.028.264.264zm6.085 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m94.633 172.116.176.088zm3.792-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.904 172.028.265.264zm13.758 0v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m107.95 171.763.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.808 171.851.176.089zm6.438-.088.264.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M92.34 171.763v.265h.793zm5.027 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M91.281 171.763v.265h1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m107.42 171.499.265.264zm-27.781.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.569 171.499.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m103.717 171.499.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M96.308 171.499v.264h.794zm7.144 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M89.694 171.499v.264h1.058zm6.262.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.04 171.234.264.265zm1.852 0 .264.265zm-17.551.353.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.923 171.234.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.633 171.322.176.088zm8.025-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m88.283 171.322.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m87.753 171.322.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.627 170.97.265.264zm-27.252.265.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m104.51 170.97.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.041 171.058.176.088zm2.205-.089.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M101.07 170.97v.264h.795z" transform="translate(-13.494 -71.967)"/><path style="fill:#4c2725;stroke:none" d="M94.985 171.234v.265c4.128.034 6.188 2.912 9.79 4.21 3.152 1.135 6.55.314 9.79.288-1.466-.616-3.227-.294-4.763-.922-1.978-.808-3.54-2.379-5.556-3.192-3.016-1.217-6.116-.897-9.26-.649" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M95.25 170.97v.264h1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m87.048 170.97.264.264zm7.85.089.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.783 170.97.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.363 170.705.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.717 170.705.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M90.487 170.705v.264h1.323zm12.965 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M89.694 170.705v.264h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m86.254 170.705.265.264zm2.646 0v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.99 170.705.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.11 170.705.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m102.835 170.528.176.089zm2.998-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M93.662 170.44v.265h.794zm8.732 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m87.842 170.44.264.265zm5.027 0v.265h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m87.489 170.528.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M101.335 170.176v.264h.794zm4.234 0 .264.264zm-20.109.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M96.044 170.176v.264h.793zm4.939.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.199 170.352.088.176zm5.732-.176.265.264zm1.852 0 .265.264zm8.202 0v.264h1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m105.304 169.911.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.254 169.911.265.265zm12.171 0v.265h1.058zm6.615 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m85.99 169.911.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m84.667 169.911.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.402 169.911.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m67.733 169.911.265.265zm4.498 0 .265.265zm32.544-.264.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.725 169.647.265.264zm18.785 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.137 169.647.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m72.76 169.647-.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m104.246 169.382.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.196 169.382.264.265zm18.785 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.873 169.382.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m106.892 169.117.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m103.628 169.206.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.188 169.117.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.667 169.117.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.608 169.117.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.29 169.117.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m67.998 169.117.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m106.363 168.853.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m102.394 168.853.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.041 168.941.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M98.16 168.853v.264h3.705z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M95.78 168.853v.264h2.38z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M94.985 168.853v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M94.192 168.853v.264h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.402 168.853.265.264zm9.437.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m63.5 168.853.265.264zm3.704 0 .265.264zm1.058 0 .265.264zm15.082 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M93.662 168.588v.265h7.144zm7.409 0v.265l3.704.264zm4.762 0 .265.265zm1.059 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m92.869 168.588.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m92.604 168.588.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.137 168.588.265.265zm8.114.088.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.08 168.588.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M73.025 168.588v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M92.34 168.324v.264h1.058zm-28.84.264.265.265zm4.498 0-.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m91.546 168.324.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m91.281 168.324.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.017 168.324.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.873 168.324.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.815 168.324.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m67.469 168.324.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m67.204 168.324.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m90.487 168.059.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m90.223 168.059.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.056 168.059.265.265zm9.79 0v1.588h.264zm3.969 0 .264.265zm.793 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m68.262 168.059.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.03 168.059.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M90.223 167.794v.265l1.852.265v-.265zm-26.458.265.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m89.694 167.794.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.608 167.794.265.265zm5.821 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.55 167.794.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m88.9 167.53.265.264zm-19.58.264.265.265zm2.91 0 .265.265zm1.059 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m88.635 167.53.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.585 167.53.265.264zm13.759 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m90.752 167.265.265.265zm-26.723.265.265.264zm4.763 0-.265 1.058h-.265l-.264-.264v-.265zm.529 0-.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m88.106 167.265.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.344 167.265.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.285 167.265.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m73.025 167.265-.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m64.294 167.265.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m87.842 167 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.344 167 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m82.285 167 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M79.11 167v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m73.554 167-.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.496 167 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.85 167-.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.823 167 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.056 166.736-.264.53zm18.521 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m89.958 166.472.265.264zm-25.4.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m87.312 166.472.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.76 166.472.265.264zm1.059 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m70.644 166.472-.53.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.32 166.472.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m87.048 166.207.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m87.048 165.942.264.265zm-21.96.265-.265.53zm4.498 0 .265.265zm8.82.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m73.554 165.942-.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.173 165.942.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m87.842 165.678.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m86.783 165.678.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.437 165.678.265.264zm1.588 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m65.617 165.678-.265.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M82.02 165.413v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M79.11 165.413v1.588h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.29 165.149.264.264zm9.79 0v1.587h.264zm3.44 0 .264.264zm-16.404.264.264.265zm4.057.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m71.702 165.149-.53.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m70.38 165.149.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m66.94 165.149.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.08 164.884.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.199 165.06.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m66.675 164.884-.265.53zm5.292 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m87.577 164.62-.794.529c.332.791 2.904 3.924 3.175 1.852h-.264l.264.529h-.264c-1.582-.73-1.79-1.328-2.117-2.91" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M78.052 164.62v.793h.265zm4.322.177.088.176zm3.88-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m71.967 164.62-.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m70.644 164.62.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m67.469 164.62.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.167 164.531.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.285 164.355.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m74.083 164.355-.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.231 164.355.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m67.204 164.355-.264.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m85.99 164.09.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.285 164.09.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m78.934 164.267.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m72.231 164.09-.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m85.99 163.826.264.264zm-15.082.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.725 163.561.265.265zm-6.88.265.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.374 163.737.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m67.998 163.561.264.265zm3.175 0 .264.265zm6.703.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m85.99 163.297.529 1.587h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m85.725 163.297.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M83.08 163.297v1.058h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m78.581 163.297.265.264zm3.704 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.581 163.032.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m68.262 163.032.265.265zm9.26 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 162.767.265.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m78.317 162.767.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m74.083 162.767-.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.496 162.767.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m85.813 162.68.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m85.46 162.503.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M83.08 162.503v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.02 162.503.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.317 162.503.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.436 162.415.088.176zm2.91 0 .089.176zm.705-.177.265.265zm3.969 0 .264.265zm-8.467.265.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.052 161.974.265.264zm7.144 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.967 161.974.264.264zm5.027 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m85.196 161.709.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m82.903 161.885.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.756 161.709.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.819 161.709.264.265zm3.968 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.554 161.709.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.025 161.709.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m85.549 161.62.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m85.196 161.444.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.756 161.444.265.265zm1.059 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.787 161.444.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.231 161.444.265.265zm2.381 0 .265.265zm2.117 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.815 161.18.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m81.756 161.18.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.523 161.18.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.083 161.18.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.819 161.18.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m73.29 161.18-.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.523 160.915.264.265zm3.969 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.877 160.915.265.265zm1.588 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M66.94 173.88c2.98-.683 4.854-1.797 6.879 1.058l-1.55-5.82 1.285-7.409.794-.53c-1.734-.038-1.667 2.566-2.268 3.705-.908 1.721-2.75 2.645-3.757 4.257-.804 1.286-.991 3.271-1.383 4.739" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.496 160.915.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m68.35 161.092.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.02 160.827.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m82.55 160.65.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.492 160.65.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m77.258 160.65.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.083 160.65.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.819 160.65-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.55 160.386.265.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m81.492 160.386.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.258 160.386.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.436 160.562.088.177zm.706-.176.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m74.083 160.386.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M85.196 160.122v.793h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m84.931 160.122.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.227 160.122.265.264zm1.323 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m75.935 160.122.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.67 160.122.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m74.083 160.122-.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m81.227 159.857.265.265zm-12.965.265v.793h.265zm4.763 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M75.67 159.857c-1.416 2.439-3.944 10.84-.923 12.957 1 .701 3.89.63 5.157.801-.38-.8-1.25-1.2-1.506-2.116-1.185-4.223.602-7.872-2.727-11.642" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m77.082 159.769.088.176zm5.203-.177.265.265zm-8.995.265.264.265zm2.116 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.29 159.328.264.264zm7.672 0 .265.264zm1.323 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.962 159.063.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M84.667 158.799v.793h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.02 158.799.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.819 158.799.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.667 158.534.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.698 158.534.264.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M76.994 158.534v1.058h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m81.756 158.27.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M84.931 158.005v1.323h.265zm.53 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m77.082 158.181.088.177zm3.704 0 .088.177zm3.969 0 .088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.844 157.917.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m81.756 157.476.265.264zm-6.085.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.258 157.476.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M68.262 157.476v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.522 157.387.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.258 157.211.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.024 157.387.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m68.262 157.211.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 156.947.265.264zm2.47.176.087.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.2 156.947.265.264zm1.058 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m84.755 156.858.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.522 156.858.088.177zm1.499-.176.264.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.523 156.682.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.523 156.417.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m68.615 156.33.088.176zm8.908-.176.264.264zm2.999.176.088.177zm1.587 0 .088.177zm2.381 0 .088.177zm-8.025.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m68.527 155.888.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.522 155.8.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.226 155.535.088.177zm-6.439.089.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m68.792 155.359.264.265zm8.025.176.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m84.402 155.094.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.137 155.094.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.522 155.27.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.876 155.27.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M75.406 155.094v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m68.792 155.094.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m82.109 155.006.088.177zm2.028-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.02 154.565.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.056 154.565.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.554 154.3.265.265zm8.467 0 .264.264zm1.852 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.873 154.036.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.32 154.036.265.265zm4.498 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m84.137 153.772.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m81.756 153.772.265.264zm2.117 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M80.433 153.772v.793h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m75.23 153.948.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.32 153.772.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.756 153.507.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.433 153.507.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.608 153.242.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.585 153.242.265.265zm8.202 0v1.852h.265zm2.646 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.787 152.978.265.264zm3.705 0 .264.264zm2.116 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.554 152.978.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.585 152.978.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.608 152.713.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.169 152.713.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.965 152.89.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.344 152.449.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.227 152.449.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.169 152.449.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.876 152.625.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.344 152.184.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.962 152.184.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.904 152.184.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.344 151.92.264.264zm-13.406.441.088.177zm4.674-.176.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.962 151.92.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.904 151.92.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.698 151.655.264.264zm-7.32.44.088.177zm3.351-.177v1.059h.265zm.794 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.523 151.655.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.938 151.831.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.523 151.39.264.265zm2.117 0 .264.265zm1.058 0 .264.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.348 151.39.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.08 151.126.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.08 150.861.264.265zm-10.054.265.265.264zm6.615 0 .264.264zm.793 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.375 150.861.265.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.083 150.861.265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m83.08 150.597.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m79.375 150.597.265.264zm.794 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.258 150.597.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.76 150.597.265.264zm3.793.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.11 150.332.265.265zm1.059 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m79.904 150.067.265.265zm-6.085.265.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.11 150.067.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.994 150.067.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.904 149.803.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.994 149.803.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.496 149.803.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.994 149.538.264.265zm1.852 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.29 149.538.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.938 149.715.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M82.815 149.274v1.058h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.846 149.274.264.264zm.794 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.231 149.274.265.264zm3.969 0v.793h.265zm.53 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.64 149.009.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.73 149.009.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m78.934 148.92.088.177zm-5.909.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.73 148.744.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.76 148.48.265.264zm3.705 0 .264.264zm2.91 0 .265.264zm3.792.176.089.177zm-13.317.088v.794h.265zm2.117 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.375 148.215.265.265zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.67 148.392.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.465 148.215.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.024 148.392.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.465 147.95.264.265zm2.91 0 .265.264zm3.704 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.702 147.95.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.585 147.95.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m83.344 147.686.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.67 147.862.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.344 147.422.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.584 147.598.088.176zm3.616-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m69.85 147.422.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.674 147.598.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m83.608 147.157.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.2 147.157.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.437 147.157.265.265zm4.322.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.608 146.892.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M78.581 146.892v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.2 146.892.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.32 146.892.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m83.873 146.363.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.409 146.54.088.176zm6.085 0 .089.176zm.441-.177.265.265zm3.175 0v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M91.546 146.099v.264h1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.873 146.099.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.935 146.099.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m90.4 145.922.176.088zm3.263-.088.265.265zm-58.208.265v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m84.137 145.834.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.935 145.834.265.265zm2.382 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m89.694 145.57.264.264zm4.762 0 .265.265zm-25.4.265.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.137 145.57.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M79.11 145.57v.793h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.144 145.481.089.177zm6.527-.176.264.264zm8.731 0 .265.264zm-12.17.264v1.059h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.402 145.04.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.67 145.04.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m54.769 145.04.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M92.604 144.776v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m87.048 144.776-.265.529zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m84.667 144.776.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m37.306 144.776.265.264zm18.257 0 .264.264zm13.229 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.667 144.511.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M78.317 144.511v1.323h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.759 144.687.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m68.792 144.511.264.265zm6.35 0v1.058h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.486 144.423.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m87.842 144.247.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m84.931 144.247.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m56.62 144.247.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.075 143.982.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.931 143.982.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.81 143.717.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m86.166 143.806.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m85.196 143.717.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m97.896 143.453.264.264zm-60.06.264.264.265zm14.023 0 .265.265zm16.757.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.076 143.541.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.196 143.453.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M71.437 143.453v.794h.265zm.794 0v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m34.131 143.453.265.264zm15.346 0 .265.264zm8.467 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.694 143.188.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m68.615 143.365.088.176zm9.702-.177v1.323h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m98.954 142.924-.264.793zm-60.06.264-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.958 142.924.265.264zm1.676.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M75.67 142.924v1.323h.265zm9.79 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m48.683 142.924.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.223 142.659.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.67 142.659.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.398 142.394.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m85.725 142.394.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m75.67 142.394.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m32.808 142.394.53.53zm8.996 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.576 142.306.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M78.317 142.13v1.058h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m60.06 142.13.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.99 141.865.264.265zm2.645 0v1.323h.265zm4.498 0 .265.265zm1.852 0v.265h.794zm1.235.088.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.024 142.042.088.176zm3.086-.177v3.704h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.79 142.042.088.176zm.794 0 .088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m48.154 141.865.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.869 141.6.264.265zm1.323 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.99 141.6.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m75.935 141.6.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m60.59 141.6.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.84 141.512.088.177zm.441-.176v1.058h.265zm1.323 0 .265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M78.317 141.336v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M75.142 141.336v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m100.013 141.072-.794 1.058zm4.145.088.176.088zm2.645 0 .177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.254 141.072.265.264zm2.646 0 .265.264zm3.44 0 .264.264zm1.058 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M79.11 141.072v.793h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.2 141.072.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m71.967 141.072.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m102.923 140.807.265.265zm-41.54.265.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.075 140.807.265.265zm3.969 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M91.546 140.807c.004 1.621.064 3.417 1.852 3.969.429-1.824-.114-3.151-1.852-3.97" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.254 140.807.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.2 140.807.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.76 140.807.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m26.194 140.807.264.265zm2.381 0 .265.265zm1.852 0 .265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.165 140.542.264.265zm6.614 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M68.527 140.542v.794h.265zm3.528.177.088.176zm4.145-.177.265.265zm2.47.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m107.95 140.278-.265.529zm-78.581.265.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.25 140.278.265.264zm3.175 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M93.133 140.278c.711 1.386 1.949 1.749 3.44 1.587-.736-1.492-1.904-1.583-3.44-1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.519 140.278.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M79.11 140.278v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.465 140.278.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m75.494 140.454.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m32.808 140.278-.264 1.852h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.84 140.19.088.176zm2.822-.177v.265h1.323zm5.292 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.519 140.013.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.465 140.013.264.265zm2.204.177.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.025 140.013.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m68.615 140.19.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m36.512 140.013-1.058 2.646c1.115-.55 1.88-1.51 1.058-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m24.606 140.013.265.265zm4.234 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.783 139.749.265.264zm10.584 0 .264.264zm1.852 0 .264.264zm7.055.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.581 139.749.265.264zm.794 0 .265.264zm2.117 0v.793l.529-.264v-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.465 139.749.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m31.133 139.837.176.088zm32.102-.088-.264.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.574 139.572.177.088zm4.057-.088.265.265zm7.938 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M88.9 143.453c1.944-.56 2.059-2.219 1.852-3.969-1.663.818-1.847 2.262-1.852 3.969" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 139.484.264.265zm2.91 0-.264.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.375 139.484.265.265zm2.646 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m68.88 139.66.088.177zm3.44 0 .088.177zm3.352-.176.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m30.162 139.484-.264.53h.53zm34.396 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.577 139.22.265.264zm6.791.089.176.088zm3.528-.089.264.265zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.73 139.22.264.264zm10.583 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.29 139.22.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m108.48 138.955.264.264zm1.588 0 .264.264zm-85.46.264.264.265zm44.186 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.775 138.955.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M97.367 140.542c1.189-.132 1.805-.537 2.38-1.587-1.152.183-1.85.526-2.38 1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.106 138.955.265.264zm6.615 0 .264.264zm3.88.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m87.842 138.955.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.285 138.955.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m78.934 139.131.088.177zm.53 0 .087.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.73 138.955.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m31.485 138.955-.529.264v.265zm2.116 0-.53.265v.265zm33.602 0v.795h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.428 138.867.088.176zm2.822-.177.265.265zm6.88 0 .264.265zm.53 0 .264.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.55 138.69.265.265zm6.085 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.844 138.867.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.846 138.69.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.056 138.69.265.265zm6.88 0 .264.265zm.794 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m112.977 138.426.265.264zm.53 0 .264.264zm.53 0 .264.264zm-93.662.264.264.265zm2.293.088.176.089zm22.842-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.515 138.426.264.264zm2.91 0v.264h1.058zm3.44 0 .264.264zm4.762 0v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m89.165 138.426.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 138.426.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.756 138.426.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.846 138.426.264.264zm.882.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.584 138.602.088.176zm1.058 0 .089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m111.39 138.161-.265.53zm3.175 0 .264.265zm-92.781.441.088.176zm1.5-.176.264.264zm9.26 0 .53.529v-.53zm36.513 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.815 138.161.264.265zm6.879 0 .264.265zm2.91 0 .265.265zm3.175 0 .265.265zm2.117 0 .264.265zm5.027 0 .264.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.082 138.337.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.32 138.161.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m64.823 138.161-.53 1.058.794-.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.836 138.073.088.176zm1.764-.176.265.264zm4.763 0 .264.264zm-68.528.264c-.35 1.48-.175 2.624.53 3.969h.529c.533-1.698.4-2.823-1.059-3.969m14.023 2.646c1.975.541 3.483-.654 3.705-2.646-1.837.1-2.922 1.015-3.705 2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.844 138.073.089.176zm1.235-.176.265.264zm6.88 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.819 137.897.264.264zm2.47.176.087.176zm2.91 0 .089.176zm.529 0 .088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m48.683 137.897.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 137.632.264.265zm7.408 0 .265.265zm6.88 0 .264.265zm2.116 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.904 137.632.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.11 137.632.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.585 137.632.265.265zm7.497.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m32.897 137.808.088.177zm32.19-.176.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.954 137.367.265.265zm2.47.177.088.176zm4.674-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M97.367 137.367c.611.942 1.296 1.032 2.38 1.059-.564-.967-1.309-1.033-2.38-1.059" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.344 137.367.264.265zm7.408 0 .265.265zm2.381 0 .265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m79.904 137.367.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.258 137.367.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m72.849 137.544.088.176zm1.234-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M34.396 140.013c1.72-.094 2.716-.9 2.91-2.646-1.557.283-2.538 1.087-2.91 2.646m22.49-2.646.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m114.565 137.103.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.398 137.103.264.264zm3.969 0v.264h1.323zm8.466 0 .265.264zm6.615 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.608 137.103.265.264zm7.409 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.169 137.103.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m79.375 137.103.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.258 137.103.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.85 137.103.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m37.747 137.191.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M33.867 137.103v1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.662 136.838.265.265zm2.91 0 .265.265zm7.232.177.088.176zm1.764-.177.264.265zm6.614 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.281 136.838.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.169 136.838.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.375 136.838.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m77.258 136.838.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M67.204 136.838v.53h.53v-.53zm1.058 0v.53h.53v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m57.238 137.015.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.417 136.574.264.264zm2.38 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.81 136.574.265.264zm2.382 0 .264.264zm8.995 0v1.323h.265zm2.117 0 .265.264zm3.704 0 .265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.546 136.574.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.873 136.574.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.433 136.574.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.15 136.574.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M33.073 136.574v.793h.264l.265-.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.775 136.309.265.265zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M103.717 136.309c.37 1.72 1.08 3.07 2.91 3.44-.046-1.92-.918-3.218-2.91-3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.573 136.309.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M92.34 139.749c2.138-.188 3.629-1.201 4.497-3.175-2.339-.642-3.976 1.005-4.497 3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M82.02 136.309v1.058h.265zm2.116 0 .265.265zm7.938 0 .265.265zm2.822.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m70.203 136.485.088.177zm2.91 0 .088.177zm1.323 0 .088.177zm5.204-.176.264.265zm.793 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M65.088 136.574v.264l.529 1.059h.529v-1.059h.529v1.059c.377-1.155-.606-1.46-1.587-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.604 136.044.265.265zm10.319 0 .265.265zm.97.089.176.088zm-66.234.352.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m92.34 136.044.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.402 136.044.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.698 136.044.264.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.611 136.22.088.177zm2.029-.177.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M76.465 136.044v1.323h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m49.742 136.044-.265.53zm19.314 0 .53 2.117.264-.53-.265-.793.53.265v-.265h-.794l.264-.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.133 135.78.265.264zm7.938 0v1.058h.264zm4.41.088.176.088zm1.146-.088.265.264zm4.498 0 .265.264zm7.408 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m92.869 135.78.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.667 135.78.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m70.38 135.78.264.264zm9.525 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.238 135.956.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m37.835 135.78.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.662 135.515.265.265zm8.996 0 .265.265zm2.117 0 .265.265zm6.085 0 .265.265zm2.117 0v1.588h.265zm5.292 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.398 135.515.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.931 135.515.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.611 135.692.088.176zm2.293-.177.265.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.29 135.515.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.15 135.427.089.176zm-79.199.088.265.265zm11.906.265c.792 2.206 2.644 1.747 4.498 1.323-.936-1.646-2.83-1.632-4.498-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m114.3 135.25.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M94.985 135.25v.265h1.588zm9.26 0 .265.264zm2.381 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m94.72 135.25.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M82.02 135.25v.794h.265zm3.44 0 .264.264zm8.996 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.962 135.25.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.904 135.25.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m74.7 135.427.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m38.894 135.25.264.265zm18.256 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m38.63 135.25.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m117.74 134.986.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m115.623 134.986.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.896 134.986.264.265zm3.263.176.088.177zm1.235-.176.264.265zm4.762 0 .265.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.631 134.986.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.278 135.074.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.725 134.986.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.227 134.986.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m70.732 135.162.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m47.096 134.986.264.265zm22.93.176-.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.866 134.81.176.088zm3.263-.088.265.264zm1.323 0 .265.264zm2.91 0 .265.264zm6.35 0 .264.264zm-81.756 3.968-.264-3.704c-1.59.876-1.225 2.923.264 3.704m7.938-3.704.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.695 134.81.177.088zm11.73-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.254 134.722.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.257 134.898.088.176zm.97-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m73.554 134.722.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m69.32 134.722-.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M41.54 134.722c.42 1.765 1.66 2.727 3.44 2.91-.314-1.97-1.5-2.82-3.44-2.91m15.61 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m40.746 134.722.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m115.094 134.457.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.273 134.457.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M101.6 134.457c-.623 1.643-.287 3.34 1.058 4.498.838-1.742.465-3.302-1.058-4.498" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.748 134.457.264.265zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m88.283 134.545.176.088zm11.2-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m87.842 134.457.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.489 134.545.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.492 134.457.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.554 134.457.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m58.208 134.457.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.68 134.457.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.277 134.192.265.265zm5.82 0 .266.265zm.882.177.088.176zm5.468-.177.265.265zm5.115.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M89.958 134.192v.265h.794zm10.054 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m89.694 134.192.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.341 134.28.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.492 134.192.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.257 134.369.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m70.997 134.369.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.68 134.192.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m50.8 134.192.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m39.687 134.192.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m30.427 134.192.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m28.84 134.192.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m122.238 133.928.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M119.327 133.928v.264h1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m117.475 133.928 1.588 2.116c-.09-1.178-.43-1.78-1.588-2.116" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m114.3 133.928.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.806 133.928.265.264zm2.117 0 .265.264zm2.91 0 .265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.81 133.928.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m91.546 133.928.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.193 134.016.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m59.531 133.928.265.264zm17.992 0v1.587h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.944 133.928.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m30.956 133.928.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m30.427 133.928.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.569 133.663.264.265zm2.91 0 .265.265zm2.117 0v1.323h.264zm1.587 0 .265.265zm6.615 0 .265.265zm-89.253.353.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.133 133.663.265.265zm7.938 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m92.869 133.663.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.604 133.663.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.756 133.663.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m80.522 133.84.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m66.94 134.192.793.53v.264l-.793.794 2.91.529h.265l.529-.53-.265-.793.53-.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m60.325 133.663.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.04 133.399.264.264zm2.91 0 .265.264zm3.969 0 .264.264zm8.731 0 .265.264zm-90.488.264.265.265zm1.059 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M102.923 133.399c.582 1.695 2.18 2.753 3.969 2.38-.844-1.91-1.963-2.375-3.97-2.38" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.6 133.399.265.264zm1.058 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.456 133.399.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m94.192 133.399.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M88.37 133.399v.264h1.588zm5.556 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.611 133.575.088.176zm4.145-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M76.465 133.399v1.323h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.854 133.399.265.264zm1.323 0c-.333 1.792-.415 3.475 1.058 4.762.995-1.9.692-3.46-1.058-4.762m9.084.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m59.972 133.487.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m31.485 133.399.265.264zm27.517 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m30.427 133.399.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.42 133.134.265.265zm4.233 0 .265.265zm8.731 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M106.892 133.134c.17 1.926 1.028 3.145 2.91 3.704-.048-2.048-.941-3.124-2.91-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.865 133.134.264.265zm1.323 0v.265h1.058zm3.527.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.25 133.134.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.756 133.134.265.265zm5.821 0 .265.265zm2.381 0 .265.265zm5.027 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.522 133.31.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.523 133.134.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.907 133.31.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.06 133.134.265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m51.33 133.134-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.856 132.87.265.264zm-89.164.265.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M118.533 133.134v.53l2.382.264c-.731-.826-1.3-.993-2.382-.794" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m111.39 132.87.264.264zm7.143 0-1.058.794v.265l1.588.794v-1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M111.125 132.87c-.56 1.895-.054 3.478 1.852 4.233-.005-1.83-.325-3.099-1.852-4.234" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.13 132.87.264.264zm6.88 0v.265h.793zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.044 132.87.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 132.87.264.264zm8.731 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 132.87.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M80.698 132.87c-.252 3.08-1.877 5.862-2.093 8.995-.301 4.37 1.027 7.542 1.953 11.642.608 2.69.533 5.216 1.347 7.937.928 3.103-.532 7.853 3.82 8.732v-.265c-4.071-3.11-2.991-10.253-3.613-14.817-.3-2.21-1.797-3.985-2.465-6.085-.729-2.293-.591-4.761-.533-7.144.07-2.812 3.077-6.503 1.584-8.996" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.648 132.87.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m60.59 132.87.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.126 132.693.177.088zm1.676-.088.265.264zm-70.026.44.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.837 132.605.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.02 132.605.265.264zm4.762 0 .265.264zm9.79 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m77.258 132.605.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.42 132.34.265.265zm-46.567.265.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m102.394 132.34.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.43 132.34.264.265zm7.673 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.258 132.34.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M76.2 132.34v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.913 132.34.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m39.423 132.34.264.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m111.566 132.164.176.088zm.794 0 .176.088zm-80.345.176.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.658 132.076.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.631 132.076.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.165 132.076.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.258 132.076.265.264zm3.175 0v1.058h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.119 132.076.264.264zm10.407.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m32.28 132.076.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.77 131.811.265.265zm-82.55.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m113.506 131.811.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m113.242 131.811.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m110.86 131.811.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.9 131.811.265.265zm8.996 0 .264.265zm8.996 0 .264.265zm2.645 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m47.625 131.811.265.265zm4.498 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m40.481 131.811.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m34.837 131.9.176.087z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M33.602 131.811v.265h1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m121.708 131.547.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m117.299 131.723.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.565 131.547.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.3 131.547.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.596 131.547.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.923 131.547.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.342 131.723.089.176zm2.293-.176.265.264zm9.525 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.024 131.723.088.176zm.97-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m52.652 131.547.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m35.719 131.547.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m31.75 131.547.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m31.485 131.547.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M23.283 131.547c-.263 2.037-2.681 6.026-.264 6.879v-.265l-1.059-.794c1.63-1.097 2.056-4.074 1.323-5.82" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m117.21 131.282.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m116.417 131.282.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M115.623 131.282v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m115.358 131.282.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m110.596 131.282.264.265zm4.498 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.106 131.282.265.265zm10.319 0 .265.265zm10.848 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.994 131.282.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.577 131.017.265.265zm11.113 0 .264.265zm4.498 0 .264.265zm2.91 0 .265.265zm2.91 0 .265.265zm1.323 0 .265.265zm-78.581.265.265.265zm7.144 0 .264.265zm36.777 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M86.254 131.017c.528 2.016 1.782 2.376 3.704 2.382-.736-1.566-2.003-2.293-3.704-2.382" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.994 131.017.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m62.177 131.017.265.265zm13.494 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.383 131.017.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m53.975 131.017.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m39.952 131.017.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m36.512 131.017.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m32.015 131.017.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.946 130.753.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m110.331 130.753.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.744 130.753.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.188 130.753.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.43 130.841.177.088zm12.523-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.73 130.753.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m34.131 130.753.265.264zm5.556 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m33.867 130.753.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m123.56 130.488.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m116.681 130.488.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.452 130.488.265.265zm4.763 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m99.219 130.488.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.402 130.488.265.265zm.53 0 .264.265zm2.646 0v.265h.794zm1.5.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M80.433 130.488v1.059h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.73 130.488.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.265 130.665.088.176zm13.141-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m34.66 130.488.265.265zm26.812.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m113.683 130.312.176.088zm2.734-.088.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m113.153 130.312.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M112.183 130.224v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m111.919 130.224.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m110.067 130.224.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.597 130.312.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M105.833 130.488c.952 1.594 2.117 2.297 3.97 2.381-.393-1.936-2.084-2.898-3.97-2.38" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.833 130.224.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.483 130.224.265.264zm3.97 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.608 130.224.265.264zm3.44 0 .264.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.433 130.224.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.73 130.224.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m35.19 130.224.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.565 129.959.264.265zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.717 129.959.264.265zm10.583 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.748 129.959.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 129.959.264.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M74.083 129.959v2.117h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.522 129.87.088.177zm2.293-.177.264.265zm2.204.177.089.176zm1.235-.177.265.265zm4.498 0 .265.265zm19.05 0 .265.265zm1.94.177.089.176zm3.352-.177v.265h.794zm-89.43.265c.6.972 1.257 1.247 2.382 1.323-.59-1.053-1.198-1.284-2.381-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.553 129.87.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m74.877 129.694-.238 6.35-2.287 9.26 3.319 12.436h.264c2.339-4.626-.575-8.777-.783-13.493-.21-4.778 3.55-10.218-.275-14.553" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m35.454 129.694.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m124.354 129.43-.529.794zm-100.278.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.802 129.43.265.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m103.981 129.43.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 129.43.265.264zm8.467 0 .264.264zm8.996 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.465 129.43.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.285 129.165.265.265zm2.382 0 .264.265zm2.116 0 .265.265zm4.498 0 .265.265zm8.996 0 .265.265zm3.969 0 .264.265zm14.023 0 .264.265zm-43.392.265.53.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.169 129.165.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M76.2 129.165c-.05 2.5.754 4.917.483 7.409-.265 2.443-1.66 4.607-1.517 7.143.293 5.167 2.872 9.16.77 14.288h-.265c-.013-4.766-3.432-8.408-3.432-12.965 0-5.106 3.913-9.61 2.373-14.816h-.264l-2.9 15.08 2.4 8.468-.03 6.35c-.944.49-1.32 1.048-1.322 2.116-.886.513-1.115 1.107-1.059 2.117h-.529l.265.529h-.53l.265.53-.793 1.058-1.059 1.058c-1.353.244-1.68 1.392-1.852 2.646l.794-.265-.53-.53v-.264l3.705-2.91-.53.265v-.265l3.97-5.82c-.703 1.712-1.343 3.17-.794 5.026-1.077.735-1.223 1.949-1.059 3.175h-.264v-.529h-.265c0 .756.663 3.16 1.059 1.323l.264-2.91.265-1.059c.903-.789.517-2.376.264-3.44l.53.265-.265-.794.53-1.058.528-.53c1.9 1.276 2.964 4.375 2.97 6.616.002 1.088-.605 3.152.734 3.44-.398-1.831.247-3.697-.18-5.557-.417-1.818-1.871-3.384-1.888-5.292-.018-2.166.942-4.095.7-6.35-.32-2.97-2.067-5.692-2.063-8.731.007-5.337 3.448-9.431.521-14.817" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.51 128.9.265.265zm9.437.088.177.088zm3.528-.088.265.264zm-88.635.264.793 3.704h.53c.462-1.567.221-2.942-1.323-3.704m42.686.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m100.542 128.9.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 128.9.264.265zm4.498 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m80.169 128.9.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m76.2 128.9.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m39.687 128.9.265.265zm26.723 0c.185 1.794 1.191 3.119 2.91 3.704-.018-2.053-.796-3.321-2.91-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m35.719 128.9.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m115.094 128.636.264.265zm2.116 0 .265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m104.775 128.636.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.169 128.636.264.265zm4.233 0 .265.265zm9.437.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.2 128.636.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m62.442 128.636.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m54.24 128.636.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.742 128.548.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.304 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.04 128.372.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m100.806 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.458 128.46.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M86.519 129.43v.529c2.109 1.057 3.905.598 5.291-1.323-1.993-.474-3.528-.213-5.291.794" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.137 128.372.265.264zm3.97 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.2 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.442 128.372.264.264zm8.73 0 .265.264zm2.91 0v1.058h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m61.383 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m39.952 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m35.983 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m25.135 128.372.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m111.654 128.107.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.569 128.107.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m101.07 128.107.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.109 128.283.088.177zm1.764-.176.264.265zm5.027 0v.265h1.852zm5.82 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.904 128.107.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m53.181 128.107.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m36.248 128.107.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m126.47 127.842-.793.53v.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.681 127.842.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M112.977 128.372v.264h2.381v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.363 127.842.264.265zm3.174 0v1.059h.265zm3.44 1.059h.265l.529-.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m101.335 127.842.265.265zm4.763 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.935 127.842.265.265zm3.97 0 .264.265zm3.704 0 .265.265zm1.588 0v.265h1.058zm9.79 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m47.625 127.842-.265 1.059h.265zm4.233 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m40.217 127.842.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m36.777 127.842.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.333 127.666.176.088zm1.852 0 .176.088zm9.613-.088v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m106.892 127.578.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m101.6 127.578.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.137 127.578.265.264zm2.91 0 .265.264zm5.027 0 .265.264zm1.058 0v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.935 127.578.265.264zm3.705 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m36.777 127.578.265.264zm24.606 0v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m26.458 127.578.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.242 127.313.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.39 127.313.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m101.865 127.313.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.726 127.401.177.089zm.882-.088.265.265zm6.527.088.176.089zm5.38-.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.285 127.313c-.299 1.902.988 2.86 2.646 3.44l.265-.265c-.547-1.565-1.347-2.56-2.91-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.02 127.313.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.64 127.313.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.819 127.313.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m61.383 127.313.265.265zm1.411.177.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m37.042 127.313.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m26.987 127.313.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.77 127.049.265.264zm2.646 0 .264.264zm7.32.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m111.39 127.049.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.67 127.049.265.264zm7.673 0 .264.264zm4.321.176.088.176zm1.5-.176.264.264zm3.44 0 .264.264zm.794 0 .264.264zm2.381 0 .265.264zm6.35 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.819 127.049.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M64.03 127.049c-.412 1.726-.147 3.001.793 4.498h.53c.84-1.749.505-3.599-1.324-4.498" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m37.306 127.049.265.264zm3.44 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m112.8 126.96.089.177zm1.234-.176.265.265zm8.996 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m102.658 126.784.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 126.784.264.265zm4.233 0 .265.265zm4.234 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.375 126.784.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.67 126.784.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m70.38 126.784.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m50.8 126.784.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m122.502 126.52.265.264zm-89.43 3.97c1.816-.54 1.615-2.138 1.324-3.705-1.464.767-1.572 2.196-1.323 3.704m4.762-3.704.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.188 126.52.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.375 126.52.265.264zm3.44 0 .264.265zm4.233 0 .264.265zm1.323 0 .264.265zm2.47.177.087.176zm2.029-.177.264.265zm3.175 0 .264.265zm6.879 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.67 126.52.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m62.97 126.52.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m41.01 126.52.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m38.1 126.52.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m112.977 126.255.265.264zm1.323 0 .265.264zm7.938 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.717 126.255.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.783 126.255.265.264zm16.67 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.642 126.431.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m70.115 126.255.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m47.89 126.255.264.264zm2.645 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.798 125.99.265.265zm.97.088.176.089zm2.205-.088.265.265zm-83.608.265.264.264zm2.91 0 .265.264zm1.058 0c.265 1.758 1.094 3.073 2.91 3.44-.088-1.903-.925-3.22-2.91-3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m104.246 125.99.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 125.99.265.265zm3.969 0 .264.265zm3.968 0 .265.265zm13.494 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.11 125.99.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m63.235 125.99.265.265zm6.615 0 .265.265zm5.556 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m38.894 125.99.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m128.323 125.726-.265.793zm-101.336.264.265.265zm1.853 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m121.708 125.726.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m112.713 127.578 1.852-1.852c-1.133.241-1.473.79-1.852 1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M111.39 125.726v1.323h.264zm2.116 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.304 125.726.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m105.04 125.726.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.11 125.726.265.264zm6.88 0 .264.264zm4.233 0 .264.264zm1.146.176.089.176zm13.406-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.406 125.726.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.29 125.726.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m69.585 125.726.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m61.648 125.726.264.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M114.035 125.461v.265l.794.264zm5.028 0 .264.265zm1.146.176.088.177zm-81.315.089-.265.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m111.39 125.461.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m106.803 125.55.177.087z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M96.308 125.461v.794h.265zm9.966.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M93.398 128.9c1.648-.634 2.714-1.635 2.91-3.439-1.954.354-2.863 1.487-2.91 3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.372 125.55.177.087zm2.558.088.088.177zm5.732 1.147 1.853-1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.55 125.726c1.098 2.098 2.862 2.367 5.027 1.852-.936-1.959-3.05-2.436-5.027-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 125.461.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m63.765 125.461.264.265zm5.556 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M55.033 125.461c-.414 1.51.09 2.65 1.588 3.175-.03-1.424-.24-2.501-1.588-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m41.54 125.461.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M116.152 125.197v1.058h.265zm5.292 0 .264.264zm2.47.176.087.176zm-84.755.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.39 125.197.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m107.95 125.197.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.958 125.197.265.264zm17.64.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.846 125.197.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m73.025 125.197.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m69.056 125.197.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m61.913 125.197.264.264zm2.116 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m31.75 125.197.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.269 124.932.264.265zm.529 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m108.48 124.932.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.278 125.02.177.088zm1.588 0 .176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.846 124.932.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m42.333 124.932.265.265zm19.844 0 .265.265zm2.117 0 .264.265zm10.848 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m128.852 124.667.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.004 124.667.265.265zm3.263.177.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.654 124.667.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 124.667.264.265zm10.319 0 .264.265zm2.116 0 .265.265zm8.467 0 .264.265zm8.996 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m75.142 124.667.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.823 124.667.265.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m39.687 124.667.265.265zm22.755 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.533 124.403.265.264zm4.763 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.919 124.403.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.9 124.403.265.264zm4.233 0v1.587h.265zm6.88 0 .264.264zm8.996 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M87.842 124.403c.184 2.032 1.269 2.775 3.175 3.175-.434-1.717-1.354-2.932-3.175-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.142 124.403.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m72.496 124.403.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m65.528 124.491.177.088zm1.94-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m63.5 124.403.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m123.031 124.138.265.265zm3.704 0 .265.265zm-86.783.265.265.264zm23.019 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m112.183 124.138.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M82.55 124.138v.265h.794zm1.323 0v.265h.794zm3.969 0v.265h.793zm9.26 0 .265.265zm3.175 0 .265.265zm1.588 0 .264.265zm2.028.088.176.089zm5.38-.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.581 124.138.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m112.713 123.874.264.264zm3.792.176.088.176zm9.701-.176.265.264zm-85.99.264.265.265zm23.283 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.538 123.874.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.03 123.874.264.264zm17.727 0 .265.264zm3.352.088.176.088zm15.434-.088.264.264zm3.968 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m25.4 123.874.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.946 123.609.264.265zm3.44 0v1.588h.264zm1.853 0 .264.265zm3.704 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M116.681 123.609c-.426 2.296-.133 3.821 1.059 5.82h.529c1.142-2.276.772-4.54-1.588-5.82" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.802 123.609.265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M59.002 123.609c-.47 1.978-.099 3.58 1.852 4.498.505-1.759-.195-3.65-1.852-4.498m15.963.176.088.177zm3.704 0 .089.177zm2.734-.088.177.088zm4.057-.088.265.265zm7.409 0 .264.265zm1.323 0 .264.265zm1.764.088.176.088zm4.85-.088.265.265zm1.059 0 .264.265zm3.175 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m129.205 123.52.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m121.62 123.433.177.088zm4.057-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M121.18 123.344c.096 1.869 1.004 3.32 2.91 3.705-.094-2.018-1.031-3.048-2.91-3.705" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.506 123.344.265.265zm7.409 0v1.059h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m110.067 123.344.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.662 123.344.265.265zm2.91 0 .265.265zm1.058 0 .265.265zm4.498 0 .265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M92.075 127.578h.53l.264-4.234c-1.835.917-1.605 2.618-.794 4.234" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.962 123.344.265.265zm7.938 0 .265.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m74.877 123.344.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m71.702 123.344.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.647 123.52.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m64.294 123.344.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m63.235 123.344.265.265zm.706.089.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m62.883 123.433.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.442 123.344.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m62.177 123.344.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M123.031 123.08v.264h1.059zm2.381 0 .265.264zm2.117 0v1.058h.265zm-83.344.264.265.265zm1.588 0 .264 4.234c2.085-.676 1.04-2.876.265-4.234z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m113.77 123.08.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m110.331 123.08.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.37 123.08.265.264zm2.381 0 .265.264zm.794 1.058 1.323-.794zm5.556-1.058.265.264zm.794 0 .264.264zm3.175 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.877 123.08.265.264zm3.44 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.854 123.08.265.264zm10.583 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m60.59 123.08.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m41.01 123.08.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M23.812 123.08v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.3 122.815.265.265zm5.909.177.088.176zm2.028-.177.265.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m110.596 122.815.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.43 122.815.264.265zm.53 0 .264.265zm12.7 0 .265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.317 122.815.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m59.796 122.815.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m44.98 122.815.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M118.269 122.55v1.059h.264zm1.587 0 .265.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.125 122.55.265.265zm3.44 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m115.094 122.286.264.265zm4.498 0 .264.265zm1.587 0 .265.265zm3.704 0 .265.265zm2.382 0 .264.265zm-82.021.265.264.264zm13.758 0 .265.264zm11.642 0 .264.264zm9.79 0 .264.264zm3.97 0 .264.264zm3.44 0 .264.264zm10.848 0 .264.264zm2.645 0 .265.264zm1.588 0 .265.264zm7.937 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m111.654 122.286.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.569 122.286.264.265zm5.82 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M101.865 124.138c1.666.479 3.319-.09 4.233-1.587-1.844-.438-3.193-.054-4.233 1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.137 122.286.265.265zm5.028 0 .264.265zm4.762 0 .265.265zm5.204.088.176.089zm4.321-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m58.473 122.286.265.265zm11.906 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M55.298 122.286c-.367 1.604.069 2.714 1.587 3.44l-1.058-3.44z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m45.508 122.286.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m120.915 122.022.264.264zm2.998.176.088.176zm-92.957.088c.032.977.083 1.529 1.059 1.852z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m112.448 122.022.265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m112.183 122.022.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M104.246 122.022v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M97.102 124.932c2.263.492 3.856-.75 4.498-2.91-2.031.047-3.812.897-4.498 2.91" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.102 122.022.265.264zm2.646 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M93.398 122.815v.53c1.634.594 3.33.668 4.233-1.059-1.559-.37-2.813-.215-4.233.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.7 122.198.089.176zm8.907-.176.265.264zm5.292 0 .265.264zm5.733.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m69.85 122.022.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m46.038 122.022.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.54 122.022.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m120.65 121.757.265.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M118.798 121.757c-.464 1.57-.07 2.764.53 4.233h.793c.398-1.673.14-2.799-.794-4.233z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m115.888 121.757.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m113.242 121.757.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.635 121.757.265.265zm2.382 0v1.058h.264zm4.233 0v.265h1.587zm2.646 0v.265h1.852zm2.91 0v.265h.794zm1.852 0v.265h1.059zm6.615 0v.265h.794zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M87.842 121.757c.24 1.126.769 1.522 1.852 1.852-.355-1.033-.794-1.548-1.852-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.726 121.845.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M80.433 122.022c.805 2.315 2.957 2.428 5.027 1.852-.983-1.923-3.079-2.209-5.027-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M80.169 122.022v.264l1.058-.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m74.612 121.757.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.417 121.492.264.265zm2.38 0-.264.53zm1.587 0 .265.265zm4.322.177.088.176zm2.028-.177.265.265zm-100.012 1.852 1.323 1.588c1.746-.195 2.396-1.252 2.646-2.91-1.634-.376-2.917-.01-3.97 1.322m14.553-1.587.265.265zm5.027 0 .265.265zm10.848 0 .265.265zm11.906 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.035 121.492.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M31.485 121.757c.84.952 1.68 1.047 2.91 1.058-.665-1.17-1.651-1.328-2.91-1.058m9.26-.265.265.265zm6.085 0 .265.265zm2.646 2.382c1.773-.042 3.1-.623 3.704-2.382-1.865.005-3.292.344-3.704 2.382m7.408-2.382.265.265zm11.025.089.176.088zm10.407-.089v1.323h.264zm5.203.089.177.088zm1.588 0 .176.088zm2.91 0 .176.088zm1.94-.089.265.265zm7.409 0 .264.265zm2.646 0 .264.265zm4.233 0 .264.265zm2.557.089.177.088zm1.323 0 .177.088zm2.205-.089.265.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m25.93 121.492.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.946 121.228.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m116.681 121.228.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m114.565 121.228.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 121.228.265.264zm3.175 0 .265.264zm11.112 0 .265.264zm3.705 0 .264.264zm1.94.176.088.177zm2.293-.176.265.264zm1.587 0 .265.264zm4.497 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.317 121.228.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.7 121.404.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m65.793 121.316.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M64.823 121.228v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m64.558 121.228.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M38.63 121.228c-.698 1.245-.938 2.32-.53 3.704 1.555-.809 1.693-2.19 1.058-3.704zm1.588 0 .264.264zm6.879 0 .264.264zm9.26 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m24.342 121.228.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m117.475 120.963.265.265zm2.646 0 .264.265zm5.82 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m117.21 120.963.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m115.094 120.963.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.752 120.963.265.265zm10.054 0 .265.265zm1.852 0 .265.265zm2.382 0 .264.265zm3.527.177.089.176zm2.558-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M90.487 120.963v2.646c.606-.886.653-1.784 0-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.317 120.963.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m63.235 120.963.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m55.827 120.963.265.265zm7.056.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m47.625 120.963.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.324 120.787.177.088zm-85.637.176.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M124.619 120.699c.269 1.787 1.161 3.109 2.91 3.704-.019-2.118-.817-3.201-2.91-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.004 120.699.265.264zm4.41.088.176.088zm2.028.088.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m117.74 120.699.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.862 120.787.176.088zm1.5.088.087.176zm2.029-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M106.627 120.963c.57.798 1.281.798 1.852 0z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m55.033 120.699.265.264zm6.88 0 .264.264zm20.108 0 .264.264zm4.498 0 .264.264zm10.318 0 .265.264zm4.234 0 .264.264zm1.852 0 .264.264zm4.145.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m48.154 120.699.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m43.039 120.787.176.088zm4.85-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.598 120.699.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M121.444 120.434v.265h.793zm-86.519 2.91c1.846.379 2.735-.944 2.91-2.645-1.524.307-2.301 1.262-2.91 2.645m4.233-2.645.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M120.12 120.699c.87 1.66 2.128 2.338 3.97 2.38-.568-1.903-2.014-2.756-3.97-2.38" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.533 120.434.265.265zm1.764.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m118.269 120.434.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m43.656 120.434.265.265zm4.763 0 .264.265zm5.997.088.176.088zm6.703-.088.264.265zm17.462 0 .265.265zm3.704 0 .265.265zm4.498 0 .265.265zm9.173.088.176.088zm1.41-.088.265.265zm5.82 0 .265.265zm2.205.176.089.177zm1.147-.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m41.54 120.434.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m29.104 120.434.265.265zm2.293.088.177.088zm7.232-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M26.987 120.434v.53l.53-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.063 120.17.264.264zm7.055.089.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M115.358 120.17v.793h.265zm3.44 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.951 120.258.177.088zm2.205-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M102.658 121.757c1.226-.03 2.05-.188 2.646-1.323-1.3-.238-2.059.133-2.646 1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.717 120.17.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M96.837 120.963v.265c1.663.8 3.313.701 4.498-.794-1.593-.47-3.056-.279-4.498.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 120.17.265.264zm4.498 0 .264.265zm9.26 0 .265.265zm1.588 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.581 120.17.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m53.181 120.17.265.264zm21.255.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m52.828 120.258.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m43.92 120.17.265.264zm5.292 0v.265h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.54 120.17.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.413 119.905.264.264zm2.205.176.089.177zm-94.897.177.177.088zm5.38-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m119.592 119.905.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 119.905.264.264zm8.466 0 .265.264zm7.673 0 .265.264zm1.588 0 .264.264zm1.852 0v.264h2.117zm8.996 0 .264.264zm2.117 0 .264.264zm2.204.176.089.177zm3.352-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m74.348 119.905.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m124.883 119.64.265.265zm-100.806 1.323c.668 2.012 3.249.745 3.704-.794zm10.23-.97.177.088zm6.702-.088.265.264zm3.44 0 .265.264zm15.346 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m120.12 119.64.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.344 119.64.264.265zm11.906 0 .265.265zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m59.267 119.64.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.738 119.64.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m40.746 119.64.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m40.217 119.64.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m37.042 119.64.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m35.454 119.64.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m120.65 119.376.265.264zm3.969 0 .264.264zm-89.43.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m116.946 119.376.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.681 119.376.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.83 119.376.264.264zm1.323 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.224 119.464.177.088zm8.29-.088.265.264zm8.907.088.177.088zm3.263-.088.265.264zm2.117 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.285 121.228c2.251.632 3.85.41 5.292-1.588-1.967-.468-4.292-.44-5.292 1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.05 119.464.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.436 119.552.088.176zm4.674-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m28.046 119.376.264.264zm12.17 0 .265.264zm5.027 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m21.255 119.552.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m121.18 119.111.264.265zm3.175 0 .265.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m120.915 119.111.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m117.475 119.111.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m117.21 119.111.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m116.152 119.111.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.565 119.111.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.067 119.111.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.363 120.434 1.587-1.323Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M84.931 119.111v.265h1.588zm10.848 0 .265.265zm9.26 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.375 119.111.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m46.302 119.111.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m39.952 119.111.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m124.09 118.847.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m121.444 118.847.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m118.533 118.847.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m118.269 118.847.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.483 118.847.265.264zm3.793.176.088.176zm2.293-.176.264.264zm.793 1.322h.265l1.323-1.058c-.87-.178-1.213.312-1.587 1.058m3.968-1.322.265.264zm3.704 0 .265.264zm1.853 0 .264.264zm2.116 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m96.837 118.847-1.852 1.852c1.007-.204 2.09-.68 1.852-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.37 118.847.265.264zm.529 0 .265.264zm3.704 0 .265.264zm.53 0 .264.264zm3.352.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.944 118.847.264.264zm21.696 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m39.423 118.847.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m18.874 119.023.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m123.825 118.582.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m119.327 118.582.265.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.063 118.582.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m115.623 118.582.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.165 118.582.264.265zm9.26 0 .265.265zm1.588 0 .264.265zm5.82 0 .265.265zm4.763 0 .264.265zm3.175 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.904 118.582.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M71.702 118.582c-.713 1.483-.452 2.542.265 3.969h.529c.62-1.472.47-2.57-.265-3.97zm2.47.177.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m39.158 118.582.265.265zm7.409 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m126.47 118.317.265.265zm-96.309.265.265.265zm2.558.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m119.856 118.317.265.265zm2.117 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.592 118.317.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m115.358 118.317.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.506 118.317.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M109.273 121.757c2.02-.225 2.605-1.56 2.646-3.44-1.712.482-2.303 1.805-2.646 3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.433 118.317.265.265zm14.817 0 .265.265zm1.587 0 .265.265zm3.44 0 .265.265zm8.731 0v.265h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m74.083 118.317.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M69.85 118.317c-.564 1.18-.438 1.97 0 3.175h.53c.346-1.185.299-2.222-.53-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m55.21 118.406.176.088zm2.734-.089.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m33.337 118.317.265.265zm5.292 0 .265.265zm8.467 0 .264.265zm7.673 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m29.633 118.317.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m126.206 118.053.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m120.385 118.053.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m120.12 118.053.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m115.094 118.053.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.962 118.053.265.264zm8.732 0 .264.264zm2.116 0 .265.264zm3.175 0 .265.264zm5.557 0 .264.264zm5.82 0 .265.264zm2.116 0 .265.264zm2.117 0 .264.264zm.794 0v.264l.529 1.059h.264zm1.852 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.698 118.053.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.589 117.876.176.089zm-91.458.177.265.264zm13.494 0 .265.264zm5.292 0v.264h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M123.825 117.788c.498 1.769 1.964 2.493 3.704 2.646-.232-2.065-1.822-2.606-3.704-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m123.649 117.965.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m122.502 117.788.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m120.65 117.788.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.492 117.788.264.265zm10.054 0 .264.265zm1.852 0 .264.265zm3.792.177.088.176zm3.616-.177.265.265zm5.821 0 .265.265zm6.35 0 .265.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.172 117.965.088.176zm7.055-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.68 117.788.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m52.388 117.788.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m48.595 117.876.177.089zm3.528-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m34.66 117.788.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m122.767 117.524.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.565 117.524.264.264zm6.35 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.3 117.524.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.75 117.612.176.088zm14.111-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M94.72 117.524c.601.951 1.328 1.074 2.382.793v-.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.285 117.524.265.264zm8.996 0 .265.264zm3.175 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 117.524.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m51.065 117.524.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.18 117.347.177.088zm2.998-.088.265.265zm1.852 0 .265.265zm-87.577.265v.264h.794zm1.235.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m114.035 117.259.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.331 117.259.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M108.215 117.524v.529l2.91-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.52 117.347.177.088zm6.527.088.088.177zm4.938-.176v.265h1.323zm6.086 0 .264.265zm1.852 0v1.323h.265zm3.969 0 .264.265zm1.323 0-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.08 117.259.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m28.575 117.259.265.265zm27.252 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m128.852 116.994.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.77 116.994.265.265zm2.998.177.089.176zm2.029-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m112.8 117.17.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m28.31 116.994.265.265zm1.588.53v.529c1.336.475 2.183.393 3.44-.265-1.059-.991-2.177-.797-3.44-.264m27.605-.353.088.176zm16.404 0 .088.176zm10.23-.177.265.265zm3.97 0v.794h.264zm8.996 0v.265l1.852.265zm11.906 0v.265h1.059zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m130.704 116.73.53.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.853 116.818.177.088zm1.323 0 .177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m123.296 116.73.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m113.77 116.73.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.105 116.906.088.177zm2.117 0 .088.177zm12.082-.176v.264h.794zm5.027 0 .265.264zm1.411.176.089.177zm.97-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m84.667 116.73.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.819 116.73.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.035 116.465.265.265zm7.938 0 .265.265zm3.44 0 .264.265zm-69.498.441.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m112.977 116.465.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.196 116.465.264.265zm11.112 0 .265.265zm5.116.177.088.176zm1.763-.177.265.265zm6.614 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m24.87 116.465.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.677 116.2.265.265zm1.94.176.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m122.238 116.2.264.265zm1.322 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.946 116.2.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.035 116.2.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.311 116.377.088.176zm22.666-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.46 116.2.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m127.176 116.024.177.088zm-66.851.177c-.495 1.805.438 3.051 2.117 3.704-.04-1.675-.546-2.954-2.117-3.704m2.646 0c-.324 1.36-.498 2.702 1.058 3.175L63.5 116.2Z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m125.677 116.465 1.852.265v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.856 115.936.265.265zm6.35 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m113.242 115.936.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.589 115.76.176.088zm-97.808.176.265.265zm15.081 0c.044 1.449.43 2.403 1.853 2.91-.078-1.41-.558-2.292-1.853-2.91m30.692 0 .265.265zm12.436 0 .264.265zm2.469.176.088.177zm4.145-.176.265.265zm3.969 0 .264.265zm6.88 0 .264.265zm4.498 0 .265.265zm1.588 0 .264.265zm1.94.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m122.502 115.672.265.264zm1.323 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.34 115.672.264.264zm1.058 0v.264h1.852zm3.44 0 .264.264zm6.88 0 .264.264zm4.498 0 .264.264zm5.027 0 .264.264zm3.968 0 .265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m86.254 115.672.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m73.554 115.672.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.361 115.583.088.177zm1.764-.176.265.265zm11.377 0 .265.265zm1.588 0 .264.265zm2.116 0 .265.265zm-68.968.44.088.177zm11.818-.176c-.094.985.185 1.393 1.059 1.852zm1.852 0-.264 1.852.793-1.852z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M106.363 115.672v.264c.874.234 1.344.253 1.852-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M106.363 115.407v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M103.452 119.64c1.826-.274 3.125-1.111 3.704-2.91-1.23.057-1.902.008-2.116-1.323-2.042.711-1.93 2.393-1.588 4.233" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.783 115.407.265.265zm1.852 0 .265.265zm3.44 0 .265.265zm.794 0 .264.265zm4.233 0 .265.265zm7.144 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.554 115.407.265.265zm12.965 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m25.93 115.407.264.265zm1.588 0 .264.265zm28.134.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m128.411 115.319.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.413 115.142.264.265zm1.059 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m122.767 115.142.264.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m117.475 115.142.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m114.035 115.142.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m113.506 115.142.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.546 115.142.264.265zm.794 0 .264.265zm2.91 0 .265.265zm2.117 0 .264.265zm9.525 0v.265h1.058zm3.968 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M91.017 115.142c.037 1.738.552 3.085 2.116 3.97.534-1.857-.292-3.347-2.116-3.97" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.075 114.878.265.264zm5.556 0 .265.264zm8.996 0 .265.264zm3.969 0 .264.264zm3.44 0 .264.264zm3.705 0 .264.264zm2.734.176.088.177zm2.293-.176.264.264zm1.587 0 .265.264zm2.381 0 .265.264zm-39.687.264.264.265zm1.852 0 .265.265zm1.147.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M89.165 114.878c-.668 1.43-.986 2.687-.53 4.233 1.606-1.041 1.647-2.482 1.323-4.233z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m87.312 114.878.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m56.885 114.878.265.264zm6.88 0 1.322 1.587zm9.525 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m55.298 114.878.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m25.665 114.878.264.264zm1.587 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m123.031 114.613.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.004 114.613.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m113.77 114.613.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.842 114.613.264.265zm1.587 0 .265.265zm8.731 0 .265.265zm4.498 0v.265h1.588zm7.673 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.787 114.613.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.523 114.613.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.465 114.613.264.265zm.705.088.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.2 114.613.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m73.29 114.613.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m49.477 114.613 1.323 3.175c1.518-1.108-.013-2.719-1.323-3.175m16.404 0-.529 1.852h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m20.285 114.701.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m125.413 115.672 1.587-1.323Z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m124.619 114.349.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.802 114.349.265.264zm2.822.088.177.088zm5.645-.088.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M109.538 114.349c-.309 1.599.654 2.472 2.116 2.91-.225-1.417-.848-2.245-2.117-2.91" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.899 114.525.088.176zm2.557-.176.265.264zm4.234 0 .264.264zm2.645 0v.793h.265zm3.44 0 .265.264zm2.381 0 .265.264zm2.117 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m88.106 114.349.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.846 114.349.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.935 114.349.265.264zm2.558.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.29 114.349.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m55.033 114.349.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.069 114.349.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M130.969 114.084v1.588h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.983 114.172.176.088zm6.438-.088.264.265zm4.762 0 .265.265zm12.7 0 .265.265zm.794.794 1.323-.53Z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M96.573 116.994h2.381l-.264 2.117c1.543-.312 4.172-2.92 2.418-4.542-1.536-1.422-4.151 1.061-4.535 2.425" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.927 114.084.265.265zm5.556 0v.265h1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M92.075 114.349c.74 1.41 1.996 1.322 3.44 1.323-.818-1.414-1.913-1.593-3.44-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.516 114.172.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.08 114.172.177.088zm8.29-.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.64 114.084.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.67 114.084.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m42.333 114.084.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m19.315 114.084.264.265zm7.408 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m123.825 113.82.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.9 113.82.265.264zm2.117 0v.265h.793zm6.085 0 .265.265zm8.467 0 .264.265zm6.085 0 .265.265zm7.408 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.403 113.908.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m40.746 113.82-.53 2.38c1.014-.558 1.046-1.28 1.059-2.38zm14.024 0 .264.264zm1.94.177.088.176zm16.316-.177.265.265zm3.969 0v.265h.793zm3.88.089.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m25.135 113.82.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#4c2725;stroke:none" d="M114.035 114.878h-.264c-.3.885-.933 1.974-.51 2.906.4.884 2.492 1.936 1.864 3.075-.566 1.028-2.5 1.198-3.47 1.67-1.334.647-2.156 1.932-3.44 2.63-1.21.658-2.694.575-3.97 1.076-3.194 1.256-4.297 4.62-7.143 6.31-3.44 2.044-8.011.895-11.377 2.78-1.838 1.028-3.157 3.337-3.969 5.217h-.264l.793-7.408h-.264c-.427 1.795-1.497 3.287-2.06 5.027-.908 2.81-.927 6.607-.507 9.525.406 2.817 2.747 5.092 2.817 7.938.068 2.768.856 5.182 1.05 7.937.146 2.09-.28 4.202 1.622 5.652 4.04 3.08 9.712.69 14.276.855 2.117.077 4.173.424 6.085 1.38 1.486.743 2.815 2.112 4.498 2.375 5.162.806 10.444-1.252 15.61-1.266v-.265c-4.763 0-9.823 1.517-14.552.63-3.136-.587-5.281-3.519-8.466-3.78-4.42-.363-11.294 1.219-14.76-2.434-2.295-2.419-2.62-6.119-3.08-9.232-.377-2.547-1.734-5.375-1.578-7.938.136-2.225 1.62-4.38 2.542-6.35.56-1.196.794-2.693 1.83-3.593 1.615-1.403 4.783-3.534 6.844-4.099 2.221-.609 4.506-.312 6.613-1.475 2.912-1.608 2.849-5.72 6.616-6.356 3.334-.563 1.74 3.27 4.025 4.25 1.863.801 3.773-.93 5.764-.104-.949-3.34-4.089.047-5.219-1.936-3.6-6.316 6.303-8.402 9.693-11.07 1.66-1.307 2.635-3.477 3.729-5.25-2.963.538-7.845 10.265-11.119 3.66-.324-.656-.12-1.614-.259-2.337" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m124.354 113.555.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M116.946 117.524c2.373-.21 3.595-1.612 3.704-3.97-2.07.376-3.488 1.875-3.704 3.97" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m111.39 113.555.264.264zm8.202 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.363 115.142 1.322-1.587c-.923.28-1.172.64-1.323 1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.34 113.555.264.264zm5.027 0 .264.264zm8.466 0 .265.264zm.794 0-.53 1.587h.266z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m89.165 113.555.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.815 113.555.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m82.55 113.555.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.73 113.555.264.264zm2.293.088.177.088zm3.263-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.406 113.555.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.025 113.555.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m42.862 113.555.265.264zm11.642 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m127.53 113.29.264.794h.264zm-111.39.265c.777 2.397 3.566 2.285 3.44 5.556h1.323v-.264h-.794c.729-2.49-.996-3.324-2.38-5.028z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m125.413 113.29.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m125.148 113.29.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.044 113.29.264.265zm1.587 0 .265.265zm2.91 0v.265h.794zm6.79.088.177.089zm10.318 0 .177.089zm1.059 0 .176.089zm1.675-.088.265 1.059h.265zm4.498 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.608 113.29.265.265zm5.821 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.465 113.29.264.265zm1.852 0 .264.265zm1.675.177.089.176zm3.352-.177.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="M41.54 113.29v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m26.194 113.29.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m24.077 113.29.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.745 113.114.177.088zm5.468.088.088.176zm5.733-.176.264.264zm2.646 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M102.658 114.613c1.479-.004 2.48-.112 3.44-1.323-1.532-.36-2.702-.173-3.44 1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.133 113.026.265.264zm4.763 0 .264.264zm1.852 0 .265.264zm2.117 0 .264.264zm1.852 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.402 113.026.265.264zm5.292 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.052 113.026.265.264zm3.175 0 .265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m75.142 113.026.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m56.356 113.026.265.264zm1.059 0c.278 1.377 1.004 2.102 2.38 2.38-.167-1.493-.929-2.117-2.38-2.38m15.345 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m43.127 113.026.265.264zm10.23.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m19.58 113.555.793 1.587 1.587-2.116zm3.968-.53.264.265zm2.381 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m14.552 113.026.794.529v-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.398 112.761.264.265zm5.82 0 .265.265zm3.175 0 .264.265zm2.116 0v.265h.794zm6.35 0 .265.265zm2.117 0v1.588h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m84.931 112.761.265.265zm5.027 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.935 112.761.265.265zm1.852 0 .265.265zm1.853 0 .264.265zm2.116 0 .265.265zm.794 0 .265.265zm2.117 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.142 112.761.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m72.76 112.761.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.658 112.497.265.264zm13.759 0 .264.264zm3.175 0 .264.264zm2.645 0 .265.264zm-99.22.264.265.265zm2.646 0 .264.265zm15.963.176.088.177zm4.674-.176v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M96.044 114.084c1.1-.17 1.634-.587 2.116-1.587-1.153.13-1.719.482-2.116 1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.142 112.497.264.264zm4.233 0 .265.264zm2.117 0 .264.264zm3.704 0 .264.264zm5.027 0 .264.264zm.794 0 .264.264zm5.82 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m25.4 112.497.265.264zm19.844 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.927 112.232.265.265zm3.44 0v.265h.793zm1.852 0 .264.265zm16.933 0 .265.265zm3.175 0 .265.265zm.97.088.177.088zm1.411-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.725 112.232.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.281 111.967.265.265zm2.91 0 .265.265zm8.82.177.087.176zm9.702-.177.264.265zm6.085 0 .265.265zm.794 0 .264.265zm2.91 0 .265.265zm-100.277.265.265.265zm50.27 0 .265.265zm3.175 0 .264.265zm3.44 0 .264.265zm3.705 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m85.99 111.967.264.265zm4.497 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m77.258 111.967.265.265zm.53 0 .264.265zm.706.089.176.088zm2.734-.089.265.265zm.794 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m72.496 111.967.264.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M43.127 111.967v1.059h.265zm12.7 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.804 111.967.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m126.47 111.703.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M117.74 111.703v.264h.793zm1.587 0 .265.264zm2.117 0 .264.264zm1.323 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M116.152 111.967c.94 1.45 2.133 1.634 3.704 1.059-1.02-1.16-2.231-1.302-3.704-1.059" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m116.593 111.791.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M99.219 112.761c1.477.71 3.17.743 3.704-1.058-1.436 0-2.675-.056-3.704 1.058" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.752 111.703.265.264zm5.556 0 .265.264zm1.588 0 .264.264zm2.381 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.254 111.703.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m82.285 111.703-.264 1.587.794-1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.962 111.703.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m80.698 111.703 1.058 1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.546 111.438.264.265zm2.91 0 .265.265zm1.588 0 .264.265zm5.027 0v.265h1.323zm5.556 0v.265h1.058zm5.82 0 .266.265zm8.202 0v.794h.265zm-99.219.265.265.264zm2.293.088.177.088zm19.932-.088.265.264zm11.907 0 .264.264zm2.91.264v.53l3.44.264c-.987-1.117-2.05-1.124-3.44-.794m6.615 1.323c1.515.431 2.638-.084 3.174-1.587-1.412.065-2.312.446-3.174 1.587m7.143-1.587.265.264zm2.646 0 .265.264zm2.117 0 .264.264zm3.44 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.519 111.438.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.73 111.438.264.265zm1.058 0v.265h.794zm1.059 0 .264.265zm.705.088.177.089zm4.322-.088v.265h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m72.231 111.438.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.069 111.438.264.265zm13.229 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m22.49 111.438.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m21.167 111.438.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.81 111.174.265.264zm6.439.176.088.176zm9.701-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m86.783 111.174.265.264zm4.234 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.2 111.174.265.264zm1.058 0 .265.264zm2.646 0 .265.264zm3.44 0 .264.264zm2.293.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M75.67 111.174c.149 1.43.683 2.296 2.117 2.645v-1.058l2.117 1.058c-.302-1.043-1.016-1.909-2.117-1.322z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m42.333 111.174.265.264zm12.436 0 .264.264zm17.198 0 .264.264zm3.44 0v.793h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m21.696 111.174.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.631 110.909.265.265zm3.175 0v.265h.794zm9.79 0v1.588h.264zm7.937 0 .265.265zm-101.6.794v.529c1.374.661 2.768.613 4.234.265-.867-1.652-2.726-1.52-4.234-.794m3.97-.53.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M95.78 110.909c.6.952 1.327 1.074 2.38.794v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.99 110.909.264.265zm5.291 0 .265.265zm.794 0 .265.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.7 111.085.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m42.598 110.909.264.265zm10.583 0v.265h.794Z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m21.167 110.909.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.34 110.644.264.265zm2.469.177.088.176zm1.235-.177v.265h1.323zm3.969 0 .264.265zm2.293.089.176.088zm15.963-.089.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.281 110.644.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.612 110.644.265.265zm5.028 0 .264.265zm7.408 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m71.702 110.644.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M48.419 110.644v.265h1.058zm13.229.794 2.117-.264c-.878-.403-1.512-.58-2.117.264" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m34.925 110.644.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m120.474 110.556.088.177zm.793 0 .089.177zm-100.63.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M110.86 110.38c.001 1.835.19 3.3 2.117 3.969-.005-1.753-.21-3.421-2.117-3.97" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.42 110.38.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M105.833 110.644v.53h2.382c-.766-.718-1.378-.802-2.382-.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.465 110.38.264.264zm6.35 0 .264.264zm4.497 0 .265.264zm4.234 0 .264.264zm1.235.088.176.088zm6.702-.088.265.264zm3.44 0 .265.264zm2.91 0v1.058h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m71.437 110.38.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m46.478 110.468.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m45.508 110.38.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m94.633 110.203.176.089zm8.82-.088.264.265zm7.408 0v.265l1.323.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M91.017 113.82c2.165-.119 3.291-1.488 3.968-3.44-2.234-.416-3.75 1.325-3.968 3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.398 110.115.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m87.577 110.115.265.265zm3.969 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.372 110.203.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.815 110.644v.265c1.233.593 2.206.593 3.44 0-1.071-.931-2.206-.858-3.44-.265" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 110.115.264.265zm4.498 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M76.73 110.38v.529l3.174.53c-.705-1.344-1.801-1.382-3.175-1.06" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.994 110.115.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m71.173 110.115.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m45.508 110.115.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.333 110.115.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m30.692 110.115.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m121.444 109.85.793 2.911c.656-1.08.857-1.764.265-2.91z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.67 109.85.265.265zm7.144 0 .264.264zm8.995 0 .265.264zm7.144 0 .265.264zm4.763 0 .264.264zm16.492.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.436 110.027.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m70.644 109.85.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m44.98 109.85.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m36.777 109.85.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.981 109.586.265.265zm2.646 0 .265.265zm13.23 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.81 109.586.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.406 109.586.265.265zm.53 0 .264.265zm1.5.088.176.088zm10.407-.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m70.115 109.586.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m119.592 109.322.264.264zm-94.457 2.38c1.482.17 2.358-.702 2.646-2.116-1.426.159-2.123.798-2.646 2.117m40.217-1.852c.905 1.141 1.773 1.38 3.175 1.058-.69-1.314-1.826-1.453-3.175-1.058m4.498-.265.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m111.919 109.322.264.264zm.529 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.78 109.322.264.264zm3.704 0 .265.264zm4.763 0 .264.264zm1.323 0 .793.793v-.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m88.106 109.322.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.436 109.498.088.176zm.706-.176.264.264zm1.41.176.089.176zm1.5-.176.264.264zm5.292 0 .264.264zm1.587 0 .265.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m69.32 109.322.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.515 109.057.264.265zm-51.33.265.265.264zm24.871 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m88.37 109.057.265.265zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 109.057.264.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M80.962 109.057c.585 1 1.3 1.025 2.382.794v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.2 109.057.265.265zm2.117 0 .264.265zm2.38 0 1.06 1.058v-.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m68.527 109.057.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.648 110.38 1.587-1.323zm6.614-1.323.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m43.92 109.057.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M38.1 110.115v.53l1.852.264-1.323 3.175c1.527-.083 4.457-3.203 2.117-3.704l.529-1.058c-1.281-.348-2.096.091-3.175.793" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m13.494 109.057.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M121.444 109.85h1.058l.53 1.588h.264l-.265-2.646z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m121.356 108.88.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.893 108.88.176.089zm14.111-.089v1.588h.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M99.219 109.586v.794c2.092.762 3.9.546 5.291-1.323-1.973-.366-3.438-.226-5.291.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.877 108.792.265.265zm3.704 0 .265.265zm2.646 0v.265h1.588zm2.91 0 .265.265zm2.646 0 .265.265zm1.588 0 .264.265zm6.879 0 .265.265zm1.058 0 .265.265zm3.705.53.793-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.083 108.792.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m118.798 108.528.265.264zm-75.142.264.265.265zm24.077 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M118.269 108.528c.006 1.853.408 3.23 2.381 3.704-.088-1.69-.767-3.049-2.381-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M101.335 108.528v.264h2.117z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m88.635 108.528.265.264zm3.705 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 108.528.264.264zm7.673 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m74.083 108.528.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m115.8 108.351.176.089zm-72.407.177.264.264zm23.812 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m113.506 108.263.265.265zm1.94.177.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M105.833 109.85c1.043-.212 1.546-.555 1.852-1.587-1.042.213-1.545.556-1.852 1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.083 108.263.265.265zm12.171 0 .265.265zm8.731 0 .265.265zm10.848.794 1.059-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m66.94 108.263.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m13.758 108.263.265.265zm23.284 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M98.954 107.999v.264h1.058zm8.467 0 .264.793h.265zm3.881.088.177.088zm2.205-.088.265.264zm3.175 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m92.604 107.999.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 107.999.264.264zm10.054 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m76.73 109.85 2.38-1.587c-1.29-.238-2.025.35-2.38 1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.994 108.792 1.058-.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m66.675 107.999.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.275 107.999.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.425 107.734.265.265zm12.17 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m89.165 107.734.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.725 108.263.794-.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m85.46 107.734.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M83.873 107.999c.673 1.364 1.739 1.734 3.175 1.323-.863-1.09-1.795-1.465-3.175-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M74.612 107.734v.794h.265zm9.437.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m66.41 107.734.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m41.275 107.734.265.265zm1.587 0 .265.265zm1.323.53c1.044 1.129 2.273 1.133 3.705.793v-.794c-1.335-.565-2.385-.635-3.705 0m12.436-.529c-.724 1.513-.33 2.6.529 3.969h.53c.444-1.507.467-3.168-1.06-3.969" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m113.506 107.47.265.264zm7.144.265 2.646 1.588h1.323c-.24-1.12-1.646-1.042-2.646-1.059l.53-.794zm-106.627 0 .264.265zm14.287 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m94.809 107.646.088.176zm3.087-.177.264.265zm12.17 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m92.869 107.47.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M76.465 107.47v.793h.264zm4.233 0 .264.265zm4.498 0-.53.265V108zm2.734.177.088.176zm1.235-.177.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.819 107.47.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M53.71 110.115c1.494-.305 1.994-1.244 2.382-2.646-1.666.225-1.988 1.15-2.382 2.646m12.436-2.646.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m120.915 107.205.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.802 107.205.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m89.43 107.205.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m77.7 107.293.176.088zm2.47-.088.264.264zm2.116 0 .265.264zm2.382 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m73.819 107.205.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m66.146 107.205.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M19.58 107.205c.366 1.749 1.043 3.063 2.91 3.44-.047-1.92-.919-3.218-2.91-3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m119.856 106.94.794.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.397 107.117.088.176zm.705-.177.265.265zm2.734.177.088.176zm7.585-.177.264.265zm2.117 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m93.133 106.94.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m65.881 106.94.265.265zm7.938 0 .264.265zm.793 0 .265.265zm5.292 0 .265.265zm2.117 0 .264.265zm.882.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.681 107.734 1.323-.794zm2.382-1.058.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.837 106.676.265.264zm4.234 0v.264h1.587zm2.646 0 .264.264zm.793 0 .265.264zm3.175 0 .265.264zm1.588 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m89.694 106.676.264.264zm3.44 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.312 106.676.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M76.994 106.676v.529h1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.2 106.676.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.881 106.676.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m26.987 106.676.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m69.32 106.411.265.265zm1.058 0 .265.265zm10.848 0 .265.265zm.53 0 .264.265zm2.381 0 .265.265zm2.91 0 .265.265zm2.646 0 .264.265zm3.704 0 .264.265zm3.175 0 .264.265zm3.969 0 .264.265zm6.085 0 .265.265zm4.763 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m69.056 106.411.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M16.669 106.411c.483 1.229 1.12 1.951 2.381 2.381-.116-1.529-.873-2.213-2.381-2.38m25.929 0v.793h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m113.594 106.323.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.78 106.147.264.264zm6.615 0 .264.264zm6.614 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m93.398 106.147.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m89.958 106.147.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.11 106.147.265.264zm7.673 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.554 106.147.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.13 105.882.264.265zm1.411.176.088.177zm1.323 0 .088.177zm1.588 0 .088.177zm4.674-.176.265.265zm-45.508.265.264.264zm3.175 0 .264.264zm1.852 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m99.483 105.882.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.958 105.882.265.265zm5.557 0 .264.265zm.793 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M75.935 109.85c.66-1.37.586-2.58 0-3.968-1.703.918-1.284 2.839 0 3.969" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m73.554 105.882.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m68.527 105.882.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m65.617 105.882.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.25 105.617.265.265zm.794 0 .264.265zm5.82 0 .265.265zm6.173.177.088.176zm.794 0 .088.176zm-73.113.617v.53c1.319.63 2.45.586 3.175-.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M94.985 105.617c-.425 1.483-.104 2.958 1.323 3.705.37-1.607.003-2.703-1.323-3.705" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m94.72 105.617.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.486 105.794.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.554 105.617.265.265zm1.323.794 1.058-.53zm3.969-.794.264.265zm2.116 0 .265.265zm.618.177.088.176zm4.41-.177.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m70.997 105.794.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m68.262 105.617.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.617 105.617.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.335 105.353.265.264zm4.851.176.088.177zm4.674-.176.265.264zm-69.85.264v1.588h.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M99.748 105.617c.84.952 1.68 1.048 2.91 1.059-.822-1.038-1.614-1.41-2.91-1.059" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.748 105.353-.53.794 1.06.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m90.223 105.353.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.196 105.353.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M84.137 105.353c.235 2.097 1.805 2.563 3.705 2.646-.498-1.769-1.964-2.493-3.705-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m70.908 105.353.265.264zm12.965 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m67.998 105.353.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m43.392 107.205 1.587-1.852c-1.032.306-1.375.81-1.587 1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.01 105.353.265.264zm2.117 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m112.977 105.088.53.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.698 105.088.264.265zm2.381 0v1.588h.265zm7.144 0 .264.265zm8.202 0 .265.265zm12.17 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m76.994 106.147 1.058-1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.789 105.176.176.089zm2.47-.088-.53 1.852h1.852v-.264l-1.587-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m70.908 105.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.896 104.824.264.264zm10.671.176.089.176zm1.5-.176.264.264zm-44.715.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m90.487 104.824.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.083 104.824.265.264zm3.704 0-.264 1.323zm10.055 0v.264h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.378 105 .088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m67.733 104.824.265.264zm3.175 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m65.352 104.824.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.714 104.647.176.088zm-60.237.177c.09 1.178.43 1.78 1.588 2.116z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M108.48 104.559c.573 1.681 1.32 3.065 3.174 3.44-.105-2.021-1.133-3.23-3.175-3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M96.573 104.559v.265h.794zm2.293.088.176.088zm9.172.088.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M96.044 104.559c.283 2.119 1.899 3.332 3.969 3.44-.507-2.354-1.718-3.002-3.97-3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.867 104.735.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m90.487 104.559.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.433 104.559.265.265zm1.059 0v.794h.264zm5.556 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m70.908 104.559.265.265zm2.382 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.352 104.559.265.265zm2.117 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m43.656 104.559.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.275 104.559.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.466 104.383.176.088zm1.411-.089.265.265zm3.792.177.089.176zm1.5-.177.264.265zm.793 0 .265.265zm1.94.177.089.176zm3.88-.177.265.265zm2.117 0 .265.265zm1.587 0 .265.265zm7.673 0 .265.265zm5.38.177.088.176zm1.323 0 .088.176zm3.616-.177v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m70.908 104.294.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m67.469 104.294.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m21.431 104.294.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.612 104.03.265.264zm5.292 0 .265.264zm.794 0 .264.264zm.53 0 .264.264zm.53 0 .264.264zm3.087.088.176.088zm1.676-.088.264.264zm2.116 0 .265.264zm9.26 0 .265.264zm9.79 0 .264.264zm2.646 0v.264h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m73.554 104.03 1.852 1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m67.204 104.03.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.54 104.03.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m35.807 104.206.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.538 103.765.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M103.981 103.765v3.44h.53v-3.44z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M93.662 103.765v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m90.84 103.942.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 103.765.265.265zm.794 0 .264.265zm1.852 0 .264.265zm3.175 0 .264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.02 103.765c-.409 1.503-.442 2.623.795 3.704l-.265-3.704z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.64 103.765.264.265zm.793 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M79.11 103.765c-.567 1.98.5 3.366 2.382 3.969-.15-1.71-.777-3.202-2.382-3.969" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.612 103.5.265.265zm6.88 0 .264.264zm1.587 0 .265.264zm5.027 0 .265.264zm12.171 0 .265.264zm3.704 0v.264l.794.265zm1.852 0v1.059h.265zm1.588 0 .264.264zm1.587 0 .265.264zm-67.204.264.265.265zm23.372.177.088.176zm2.028-.177.265.265zm3.44 0 .264.265zm2.91 0v.265h.794zm1.764.088.176.089zm1.147-.088.264.265zm2.38 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.113 103.677.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m70.644 103.5.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.088 103.5.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.577 103.236.265.265zm1.588 0 .264.265zm1.675.176.088.177zm9.173-.176.264.265zm7.143 0 .265.265zm1.588 0 .264.265zm2.646 0 .264.265zm-74.348.265c.333 1.637 1.331 2.078 2.91 2.116-.666-1.269-1.494-1.891-2.91-2.116m5.027 0 .264.264zm2.381 0 .265.264zm15.081 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M86.519 103.236c.404 1.395 1.297 1.572 2.646 1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.465 103.236.264.265zm3.792.176.088.177zm5.997-.176.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m70.644 103.236.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m60.325 103.236.265.265zm6.615 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m60.06 103.236.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.395 103.06.177.088zm7.497-.088.264.264zm1.587 0 .265.264zm-49.477.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M97.102 103.236c1.007 1.206 2.167 1.555 3.704 1.588-.517-1.789-2.079-1.97-3.704-1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.278 103.06.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="M93.662 102.972v.793h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.854 102.972.265.264zm3.969 0 .264.264zm2.117 0 .264.264zm6.173.176.088.176zm9.79 0 .088.176zm.97-.176.264.264zm.53 0v.793h.264zm4.498 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.738 102.972.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.627 102.707.265.265zm1.588 0 .264.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M106.363 102.707c-.498 1.684-.067 3.017.793 4.498 1.57-1.497.754-3.346-.794-4.498" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.2 102.707.265.265zm11.906 0 .265.265zm5.556 0 .265.265zm12.436 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m61.119 102.707.264.265zm3.704 0 .265.265zm5.556 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.473 102.707.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m44.98 102.707.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.628 102.53.177.089zm4.322-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m103.1 102.53.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m102.658 102.442.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m102.394 102.442.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.13 102.442.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m93.662 102.442.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.554 102.442.265.265zm2.381 0 .265.265zm1.059 0v.265h1.587zm6.614 0 .265.265zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m70.38 102.442.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M68.527 102.442c-1.453 2.458 1.221 3.244 1.588.53l-1.059.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.208 102.442.265.265zm3.175 0 .265.265zm3.44 0 .265.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.775 102.178.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m104.51 102.178.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m101.6 102.178.265.264zm2.646 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m101.335 102.178.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.783 102.178.265.264zm3.44 0v1.587h.264zm10.76.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m84.931 102.178-.264 1.852.793-1.852z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.344 102.178.264.264zm1.323 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m82.815 102.178 1.322 1.852z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.406 102.178.265.264zm.794 0 .265.264zm2.646 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M73.554 102.178c.699 1.274 1.726 1.888 3.175 1.587-.747-1.362-1.692-1.574-3.175-1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m72.849 102.354.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m66.675 102.178.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m59.531 102.178.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.304 101.913.265.265zm2.381 0 .265.265zm2.646 0 .265.265zm-52.387.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m100.542 101.913.264.265zm4.498 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m100.277 101.913.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.013 101.913.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m93.75 102.09.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M73.554 101.913v.265h1.323zm5.556 0 .265.265zm.794 0v.794h.265zm5.292 0 .264 1.323h.265zm1.324 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m72.76 101.913.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m59.796 101.913.264.265zm2.381 0 .265.265zm2.381 0 .265.265zm5.557 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.944 101.913.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m45.244 101.913.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M112.713 101.649v.793h.264zm-76.994.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.802 101.649.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M107.685 101.649c.898 1.725 2.065 2.364 3.97 2.38-.614-2.045-2.005-2.375-3.97-2.38" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.483 101.649.265.264zm6.35 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m72.76 101.649.265.264zm2.91 0 .265.264zm5.909.176.088.176zm4.674-.176.265.264zm1.852 0 .265.264zm11.025.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.558 101.649.265.264zm1.852 0 .265.264zm3.705 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.06 101.649.265.264zm2.382 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.738 101.649.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.68 101.649.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m35.19 101.649.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M107.95 101.384v.265h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.363 101.384.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m98.425 101.384.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.706 101.384.265.265zm13.23 0 .264.265zm2.646 0 .265.265zm11.377 0 .265.265zm.794 0 .265.265zm7.408 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m60.325 101.384.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m58.738 101.384.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.892 101.12.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.631 101.12.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.367 101.12.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M93.662 101.12v.793h.265zm3.44 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m90.752 101.12.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.317 101.12.264.264zm1.852 0 .264.265zm1.058 0 .265.265zm6.615 0 .264.265zm1.852 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M75.935 101.649v.529h3.175v-.53c-1.193-.504-1.996-.566-3.175 0" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.524 101.208.177.088zm1.94-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m72.849 101.296.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m67.204 102.178 1.852-1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.294 101.12.264.264zm1.852 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m62.97 101.12.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.767 101.296.089.176zm.706-.177.264.265zm2.117 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.42 100.855.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.573 100.855.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.308 100.855.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m90.752 100.855.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M89.165 100.855c-.39 1.333-.217 2.468 1.058 3.175l-.53-3.175z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m66.146 100.855.264.264zm9.525 0 .264.264zm10.407.176.088.177zm1.5-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m64.03 100.855.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m63.5 100.855.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m63.235 100.855.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m60.59 100.855.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M36.777 101.384c1.033 1.055 2.169.803 3.44.265v-.53zm21.696-.53.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m112.536 100.767.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.95 100.59.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m104.51 100.59.265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m104.246 100.59.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.13 100.59.264.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m101.865 100.59.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.044 100.59.264.265zm5.556 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.962 100.59.265.265zm6.35 0 .265.265zm1.323 1.852h.265l.794-1.587c-.92-.022-.999.793-1.059 1.587" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M80.698 100.59c-.671 1.395-.677 2.64.53 3.704.65-1.361.538-2.599-.53-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.554 100.59.265.265zm2.646 0 .265.265zm4.233 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m72.849 100.767.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.854 100.59.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.208 100.59.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.415 100.59.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m45.244 100.59.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m43.392 100.59.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.304 100.326.265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.335 100.326.265.264zm3.705 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m101.07 100.326.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.515 100.326.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.486 100.502.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.819 100.326.264.264zm2.646 0 .264.264zm10.583 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.881 100.326.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m58.208 100.326.265.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.415 100.326.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m105.833 100.061.265.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.25 100.061.265.265zm5.292 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.783 100.061.265.265zm8.202 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M85.99 100.061c.235 1.478.883 2.343 2.38 2.646-.276-1.374-1.021-2.263-2.38-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.667 100.061.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M69.85 100.061v1.323h.265zm3.175 0 .265.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m65.881 100.061.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.383 100.061.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.944 100.061.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m57.15 100.061.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m43.127 100.061.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m106.363 99.797.264.264zm2.91 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.013 99.797.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.72 99.797.265.264zm5.027 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.348 99.797.264.264zm2.646 0 .264.264zm6.879 0 .264.264zm2.293.088.176.088zm7.32.088.088.176zm.97-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.025 99.797.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m61.913 99.797.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.067 99.532.264.265zm-65.088.265.265.264zm11.906 0 .265.264zm1.059 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.802 99.532.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.483 99.532.265.265zm7.409 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.192 99.532.264.265zm5.027 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.927 99.532.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.398 99.532.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.755 99.708.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.608 99.532.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.641 99.62.176.088zm6.703-.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m73.819 101.12 3.175-.794v-.53c-1.46-.334-2.486-.074-3.175 1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.29 99.532.264.265zm1.587 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m62.706 99.532.265.265zm2.91 0 .265.265zm4.498 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.442 99.532.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m56.62 99.532.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m44.715 99.532.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.862 99.532.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m112.713 99.267-.53 1.059h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m111.125 99.267.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m107.685 99.267.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.69 99.267.264.265zm8.73 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.425 99.267.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M90.487 99.267v1.059h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.08 99.267.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.67 99.267.265.265zm7.144 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.29 99.267.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M66.41 101.649c1.143-.467 1.733-1.122 1.588-2.382zm3.705-2.382.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m65.617 99.267.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m63.235 99.267.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m56.356 99.267.265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M52.123 102.442c1.502-.66 2.384-1.524 2.646-3.175-1.74.194-2.906 1.38-2.646 3.175m3.969-3.175.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m108.215 99.003.264.264zm2.91 0-.53.264v.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.896 99.003.264.264zm10.054 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.631 99.003.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.367 99.003.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 99.003.264.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m84.402 99.003.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.55 99.003.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m76.994 99.003.264.264zm5.291 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m70.38 99.003.264.264zm3.175 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.68 99.003.264.264zm7.938 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m55.827 99.003.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.86 98.738.265.265zm-55.297.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M109.273 98.738v.265h.794zm1.235.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m109.008 98.738.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.837 98.738.265.265zm11.907 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m90.752 98.738.265.265zm5.82 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.519 98.738.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 98.738.265.265zm2.116 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.819 98.738.264.265zm7.937 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m55.298 98.738.264.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m55.033 98.738.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m44.185 98.738.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.542 98.474.264.264zm1.058 0 .265.264zm2.117 0 .264.264zm-61.384.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.308 98.474.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.873 98.474.264.264zm2.381 0 .265.264zm4.498 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m83.608 98.474.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.492 98.474.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m70.644 98.474.264.264zm3.44 0 .264.264zm4.763 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m54.504 98.474.265.264zm2.91 0 .265.264zm7.937 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m54.24 98.474.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m43.92 98.474.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.277 98.209.265.265zm.882.176.088.177zm2.293-.176.265.265zm1.323 0-.53.53v.264c.596-.125.826-.228.53-.794m6.88 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m96.044 98.209.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 98.209.264.265zm6.086 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.815 98.209.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m81.227 98.209.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M58.473 99.532c1.405-.001 2.483.05 3.175-1.323-1.348 0-2.705-.167-3.175 1.323m6.88-1.323.264.265zm5.556 0 .265.265zm3.44 0 .264.265zm3.969 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m53.975 98.209.265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m53.71 98.209.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m43.656 98.209.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.013 97.944.264.265zm3.174 0 .265.265zm5.027 0 .264.265zm3.704 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.78 97.944.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m91.017 97.944.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M86.254 98.209c1.014 1.15 2.102 1.172 3.44.53-1.07-.852-2.15-.836-3.44-.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m86.519 97.944.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.285 97.944.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m80.962 97.944.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.612 97.944.265.265zm2.205.177.089.176zm.706-.177v1.059h.264zm1.676.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m53.181 97.944.265.265zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m52.917 97.944.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m43.392 97.944.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.635 98.209 1.323-.265zm12.171-.53.265.265zm.618.176.088.177zm3.086-.176.265.264zm3.175 0 .265.264zm2.558.088.176.088zm1.94-.088.265.264zm-70.379.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m88.37 97.68.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m82.02 97.68.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m77.787 97.68.265.264zm1.059 0 .264.264zm1.852 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m76.994 97.68.264 1.587h.265V97.68z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m74.877 97.68.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.088 97.68.264.264zm6.614 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m56.62 97.68.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m52.388 97.68.264.264zm3.968 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.542 97.415.264.265zm6.879 0 .264.265zm2.381 0 .265.265zm2.646 0 .265.265zm-73.819.794v.53c1.08.159 1.762.035 2.117-1.06zm4.498-.53.265.265zm8.996 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m100.013 97.415 1.058 1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.515 97.415.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.106 97.415-.794.265v.264zm1.852 0 .265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.756 97.415.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.581 97.415.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m77.787 97.415 1.323 1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m68.792 98.738 1.852-.529v-.53l-.265-.264zm3.175-1.323.264.265zm3.44 0 .264.265zm1.588 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m65.088 97.415.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M63.5 97.415v1.852h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m56.003 97.503.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m51.594 97.415.264.265zm3.968 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m51.33 97.415.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.54 97.415.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.246 97.15.264.265zm2.91 0 .265.264zm1.588 0v1.058h.264zm.794 0 .264.264zm1.146.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m101.6 97.15.265 1.588h.264v-1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m91.546 97.15.264.265zm3.969 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.756 97.15.265.265zm2.117 0v.264h1.058zm3.969 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.433 97.15.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.052 97.15.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m72.496 97.15.264.265zm3.175 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m55.033 97.15.265.265zm10.054 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m54.769 97.15.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m50.8 97.15.265.265zm3.704 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m42.598 97.15.264.265zm5.027 1.058 2.381-.53c-.935-.696-1.802-.461-2.381.53m2.91-1.058.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m42.333 97.15.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.865 96.886.264 1.588h.265zm5.028 0 .264.265zm2.38 0 .265.265zm1.852 0v.794h.265zm1.588 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m91.81 96.886.265.265zm3.705 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 96.886.264.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.492 96.886.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.169 96.886.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.2 96.886.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.025 96.886.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m53.887 96.974.176.088zm18.873-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m53.446 96.886.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m50.27 96.886.265.265zm2.822.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.48 96.622.264.264zm-58.473.264.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M103.188 96.622c.089 1.178.429 1.78 1.587 2.116-.2-1.092-.529-1.715-1.587-2.116" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m92.075 96.622.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.492 96.622.264.264zm4.498 0 .264.264zm1.587 0 .265.264zm1.588 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.169 96.622.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.73 96.622.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m73.554 96.622.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.29 96.622.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.823 96.622.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m52.3 96.71.176.088zm11.025.088.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m51.858 96.622.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.188 96.357-.265 1.323h.265v-1.058l.793.264zm5.82 0 .265.266zm-59.266.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m92.34 96.357.264.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.9 96.357.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M87.577 96.357c.59 1.052 1.198 1.284 2.381 1.323-.553-.998-1.267-1.248-2.38-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m77.523 96.357.264.265zm2.381 0 .265.265zm2.734.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m74.083 96.357.265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.819 96.357.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m64.823 96.357.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m63.235 96.357.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m51.33 96.357.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m51.065 96.357.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m49.477 96.357.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.333 96.357.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m115.888 96.092-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.215 96.092.264.265zm3.175 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m92.604 96.092.265.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.815 96.092.264.265zm5.027 0 .264.265zm1.764.089.176.088zm1.322 0 .177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m81.227 96.092.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m79.64 96.092.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.052 96.092.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.787 96.092.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m50.8 96.092.265.265zm14.023 0 .264.265zm9.79 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m50.535 96.092.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.46 95.828.265.264zm5.821 0 .265.264zm1.588 0 .264.264zm3.44 0 .264.264zm11.642 0 .265.264zm.794 0 .264.264zm2.91 0 .265.264zm-70.026.44.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.815 96.357v.53l3.44-.265c-1.051-.985-2.2-.862-3.44-.265" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.227 95.828.265.264zm2.117 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.375 95.828.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.022 95.916.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.581 95.828.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m75.142 95.828.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m65.881 95.828.265.264zm1.059 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.97 95.828.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m49.213 95.828.264.264zm1.058 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m34.396 95.828.264 5.82.794-.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.573 95.563.264.265zm11.112 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.398 95.563.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M83.873 95.563v.265h1.058zm4.145.088.176.089zm3.792-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m80.962 95.563.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.348 95.563.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.558 95.563.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m62.97 95.563.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M54.24 96.092v.53c1.07.29 1.801.198 2.38-.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m50.006 95.563.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.417 95.299-.265.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.42 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M106.892 95.299c.047 1.461.554 2.229 1.852 2.91-.077-1.41-.558-2.292-1.852-2.91" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m93.927 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.962 95.299.265.264zm1.588 0 .265.264zm5.82 0 .265.264zm5.291 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m75.67 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.612 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m65.617 95.299.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m64.558 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m62.97 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m51.858 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m50.006 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m41.804 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m34.925 95.299.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M112.977 95.034v1.588h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M112.713 95.034c-1.047.948-1.308 1.784-1.323 3.175 1.27-.626 1.89-1.811 1.323-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.892 95.034-.265 1.588h.265zm5.556 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.456 95.034.265.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.958 95.034.265.265zm4.234 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M87.048 95.034c.47.628.852.628 1.323 0z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.08 95.034.264.265zm3.704 0 .794.794v-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.7 95.122.176.088zm2.999-.088.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.558 95.034.265.265zm8.996 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m49.213 95.034.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.681 94.77.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M84.402 94.77v.264h1.323zm3.969 0 .264.265zm.529 0 .265.265zm1.323 0 .264.265zm4.498 0 .264.265zm2.381 0 .265.265zm10.319 0 .264.265zm3.263.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.581 94.77.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m78.317 94.77.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.877 94.77.265.264zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m62.706 94.77.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m49.742 94.77.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m49.477 94.77.53.529Z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m42.069 94.77-.256 2.91 1.672 3.704-2.15 3.969 1.095 5.291-.626 3.97c1.265-.823 1.319-1.748 1.323-3.176 4.897 3.2 12.795-1.067 12.965 7.144l-7.409-.614-7.143 2.73-1.323-.793c-2.484-4.54-6.485.009-9.79-1.255-2.234-.854-2.771-3.729-4.282-5.348-1.44-1.543-3.868-1.876-5.772-2.658v.265c1.786 1.323 4.072 2.406 5.388 4.234 1.124 1.562 1.576 4.27 3.368 5.244 2.034 1.104 4.974-.748 7.119-.695 1.9.047 3.835 1.613 5.556 2.33l-4.762 6.085-.53-.265c-1.34 2.619-2.278 2.104-4.357 3.555-1.251.872-1.47 2.253-3.05 2.795v.265l1.322-.53c1.105-.193 1.299-1.016 2.185-1.59.814-.527 4.054-1.594 4.958-1.686 1.4-.142 2.56 2.408 2.122 3.536-.445 1.15-1.644 1.732-2.121 2.916 1.934-.803 3.998-2.1 3.267-4.498-.372-1.22-1.363-2.07-.978-3.44.99-3.513 5.423-6.938 8.559-8.467 5.3-2.585 10.884 3.107 16.404 2.646l-1.058 1.323c-3.062.137-1.851 4.338-2.241 6.35-.194 1.003-.75 1.758-1.199 2.646-1.135-.167-1.825.026-2.381 1.059l-.53-.265-.529 2.91h.265v-2.116c1.365-.563 3.964-1.335 4.715-2.674 1.192-2.125.144-4.855 1.959-6.824 2.987-3.24 6.753.184 7.67 3.412 1.488 5.245-3.828 9.565-2.698 14.817.237 1.101.422 2.716 1.319 3.44l-.087 4.762-1.68 6.88.064 3.703-4.118 5.557c-1.3.984-.764 3.039-2.393 3.884-2.855 1.482-7.23-.2-10.307.13-2.884.31-4.382 2.56-6.88 3.659v.264c2.367-.108 4.314-1.782 6.615-1.837 2.434-.06 4.711 1.348 7.144 1.524 4.503.325 9.255-2.83 8.731-7.624 4.958-2.312 7.497-10.138 6.473-15.092-.524-2.535-2.172-4.779-2.236-7.398-.168-6.853 7.052-18.254-.798-23.248-3.494-2.223-8.297-.33-11.63-3.27-2.081-1.836-1.48-4.643-2.883-6.808-1.43-2.208-8.248-2.265-10.622-1.864-.789-1.305-2.816-2.182-2.67-3.968.286-3.5 4.466-4.507 1.082-8.187-.496-.539-.904-.854-1.587-1.074z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.951 94.593.177.088zm2.205-.088.265.264zm1.147.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.25 94.505.265.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.428 94.681.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M89.43 95.563v.53c1.405.261 2.487-.162 2.91-1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.137 94.505.265.264zm1.853 0 .264.264zm4.938.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m79.375 94.505.265.264zm.794 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m79.11 94.505.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 94.505.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.347 94.681.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.465 94.505.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m67.292 94.681.089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m62.706 94.505.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m42.069 94.505.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.946 94.24.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.109 94.417.088.176zm1.764-.177.264.265zm2.646 0 .264.265zm2.116 0 .265.265zm6.88 0 .264.265zm1.852 0 .264.265zm4.233 0 .265.265zm2.646 0 .264.265zm2.646 0 .264.265zm3.44 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.294 94.24.264.265zm12.964 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.806 93.976.265.264zm-38.364.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.367 93.976.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m95.78 93.976.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.402 93.976.265.264zm2.381 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m84.226 94.152.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m76.73 93.976.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.23 94.152.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m62.442 93.976.264.264zm1.852 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.542 93.711.264.265zm2.91 0 .265.265zm3.175 0 .265.265zm1.323 0 .265.265zm.882.176.088.177zm1.235-.176.264.265zm-60.06.265c.499 1.456 1.396 2.033 2.91 2.116-.498-1.448-1.421-1.981-2.91-2.116" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.044 93.711.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.106 93.711.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m87.842 93.711 1.058 1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M60.325 93.711c.089 1.602.802 2.218 2.381 2.381-.397-1.308-1.083-1.963-2.381-2.38m3.969 0 .264.264zm13.229 0 .264.265zm1.5.088.176.088zm.618-.088.264.265zm2.645 0 .265.265zm2.382 0 .264.265zm2.38 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m117.299 93.623.088.176zm-68.704.176.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.188 93.447.264.264zm2.38 0 .265.264zm3.44 0 .264.264zm.794 0 .265.264zm4.939.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.631 93.447.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.581 93.447.265.264zm1.588 0 .264.264zm2.381 0 .265.264zm2.381 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.523 93.447.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m76.994 93.447.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.177 93.447.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M36.777 93.447v1.058h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.538 93.182.264.265zm4.498 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M109.273 93.182c-.665 1.825-.604 3.55 1.323 4.498.424-1.785.164-3.323-1.323-4.498" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.923 93.182.265.265zm4.762 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.308 93.182.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.11 93.182.53.53v-.53zm3.705 0 .264.265zm4.586.176.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.523 93.182.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.642 93.358.089.177zm3.352-.176.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M67.204 93.182v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m62.177 93.182.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.42 92.917.265.265zm-60.854 3.175v-.529L44.45 95.3l.265-2.117c-3.326 1.165-1.213 4.385 1.852 2.91" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.627 92.917 1.588 2.117c-.129-1.147-.454-1.78-1.588-2.117" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 92.917.264.265zm10.848 0 .264.265zm2.116 0 .265.265zm5.292 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M84.402 94.77c1.34-.033 2.67-.293 2.646-1.853-1.247.338-2.064.645-2.646 1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m84.49 93.094.088.176zm1.5-.177v.265h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.02 95.563c1.418-.424 2.182-1.156 2.382-2.646-1.498.304-2.146 1.168-2.381 2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.913 92.917.264.265zm18.52 0 .265.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m117.563 92.83.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m77.787 92.653.265.264zm1.323 0 .265.264zm9.79 0 .265.264zm1.235.088.176.088zm1.675-.088.265.264zm1.5.088.176.088zm3.087.088.088.177zm6.261-.176.265.264zm4.145.088.177.088zm6.703-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.258 92.653.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m37.57 92.653.265.264zm11.642 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.04 92.388.264.265zm6.085 0v.265h1.058zm2.117 0 .264.265zm1.852 0 .264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.984 92.565.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.752 92.388.265.265zm.794 0 .264.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m79.64 93.711 1.058-1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M78.317 92.388v.794h.264zm1.587 0-.264.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.787 92.388.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m77.258 92.388.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M65.617 92.388v1.059h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.846 92.124.264.264zm10.054 0 .265.264zm2.117 0 .264.264zm3.175 0 .264.264zm5.644.176.088.176zm2.029-.176v1.587h.264zm.529 0 .264.264zm2.381 0 .265.264zm7.408 0 .265.264zm3.44 0 .264.264zm-53.975.264.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M73.819 95.828c1.427-.747 1.748-2.222 1.323-3.704-1.58.79-1.613 2.118-1.323 3.704m4.762-3.704.794 1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.348 92.124-.53.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m62.97 92.124.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.877 91.859.53.53zm2.47.176.088.177zm.705-.176.265.265zm11.113 0 .264.265zm2.116 0 .265.265zm.53 0 .264.265zm6.439.176.088.177zm5.997-.176.264.265zm5.82 0 .265.265zm-48.684.265.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m61.383 91.859.265.265zm1.323 0 .265.265zm1.323 0v1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.893 91.683.176.088zm8.026-.089.264.265zm2.646 0 .529.53v-.53zm2.382 0v1.059h.264zm-77.523.265-.265.265c.452 1.43 1.123 2.177 2.646 2.38-.365-1.393-1.078-2.07-2.381-2.645" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M102.394 91.594c.532 1.791 1.643 2.743 3.44 3.175-.211-2.042-1.42-3.07-3.44-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.6 91.594.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.16 91.594.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.139 91.683.176.088zm4.586-.089.265.265zm.53 0 .264.265zm3.175 0 .265.265zm2.646 0 .265.265zm2.646 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m61.119 91.594.264.265zm2.116 0 .265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M117.74 91.33v1.058h.264zm-79.905.264.265.265zm9.26 0v.265h.795zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.433 91.33.265.264zm1.94.176.089.177zm4.41-.176.264.264zm2.91 0 .265.264zm2.646 0 .264.264zm10.054 0v.264h1.058zm7.408 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m78.14 91.506.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.294 91.33.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m63.235 91.33.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m62.442 91.33.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m60.854 91.33.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m46.038 91.33.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.77 91.065.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M113.242 91.065c.109 1.619.84 2.273 2.38 2.646-.323-1.36-1.055-2.192-2.38-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m111.125 91.065.265.265zm1.852 0v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M109.802 91.065c.59 1.053 1.198 1.285 2.381 1.323-.545-1.04-1.239-1.26-2.38-1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.604 91.065.265.265zm2.381 0 .265.265zm6.35 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M88.9 92.653c1.35.276 2.261-.258 2.646-1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.312 91.065.265.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m65.97 91.242.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.294 91.065.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m63.235 91.065.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m62.177 91.065.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m60.59 91.065.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.425 90.8.265.265zm2.646 0 .264.264zm2.646 0v.264h1.323zm6.35 0v.264h.793zm6.702.176.089.176zm-59.09.088.265 3.175h.529c.61-1.292.543-2.455-.794-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.661 90.977.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.02 90.8.265.265zm5.556 0 .265.264zm3.704 0 .265.793h.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M77.523 90.8v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.558 90.8.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m63.5 90.8.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m61.913 90.8.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.06 90.8.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m39.952 90.8.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M99.483 90.536v.794h.265zm1.323 0 .265.265zm2.029.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m98.425 90.536.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M91.81 92.653c1.725.43 2.94-.472 3.44-2.117-1.55.21-2.736.645-3.44 2.117" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.819 90.536.264.265zm1.587 0-.794.265v.264zm16.14 0v.265h.794zm2.116 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m64.823 90.536.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m63.5 90.536.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m59.796 90.536.264.265zm1.852 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M38.63 90.536v.265h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.542 90.272.264.264zm1.587 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M99.748 90.272c.015 1.796.36 3.122 1.852 4.233.541-1.831.154-3.665-1.852-4.233" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.425 90.272.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M82.55 90.272v.264h.794zm1.058 0v.264h.794zm2.117 0v.793h.265zm4.762 0 .265.264zm2.382 0 .264.264zm3.792.176.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m61.383 90.272.265.264zm2.382 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m59.267 90.272.264.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m59.002 90.272.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m45.597 90.448.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.227 90.007.265.265zm.794 0 .264.265zm2.646 0 .264.265zm3.704 0 .264.265zm1.587 0 .265.265zm3.44 0 .264.265zm6.526.088.177.088zm1.94-.088.265.265zm3.175 0 .264.265zm1.587 0v.265h1.058zm5.733.088.176.088zm3.792-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M79.375 90.007c.612 1.242 1.579 1.652 2.91 1.852-.338-1.638-1.387-1.838-2.91-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m65.352 90.007.265.265zm9.79 0 .264.265zm1.058 0 .265.265zm1.323 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m64.03 90.007.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m61.119 90.007.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m58.65 90.095.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m58.208 90.007.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m40.305 90.183.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.935 89.742.265.265zm.794 0 .265.265zm2.381.265v.265l1.588-.265zm2.382-.265.264.265zm3.44 0 .264.265zm1.059 0 .264.265zm7.937 0 .265.265zm14.023 0 .265.265zm2.91 0 .265.265zm1.852 0 .265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m66.94 89.742-.265.53zm1.852 0 .529.53zm1.059 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m64.294 89.742.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.854 89.742.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.326 89.83.177.089zm3.264-.089.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M56.356 89.742v.265h.794Z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m49.565 89.919.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.877 89.478.265.264zm2.646 0 .264.264zm11.906 0 .265.264zm4.763 0 .264.264zm4.498 0 .264.264zm9.79 0 .264.264zm2.117 0 .264.264zm.529 0 .265.264zm1.852 0 .265.264zm1.058 0 .265.264zm1.588 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m67.91 89.566.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m65.617 89.478.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m64.558 89.478.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m55.474 89.566.177.088zm4.851-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M50.535 90.272c.37 1.107 1.081.974 2.117.793v1.852c1.24-.372 2.396-2.477.744-3.23-.967-.44-2.016.161-2.86.585m4.41-.706.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M118.004 89.213v1.059h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.744 89.213.264.265zm1.587 0 .265.265zm5.027 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.69 89.213.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m72.849 89.39.088.176zm1.763-.177.265.265zm1.059 0 .264.265zm2.646 0v1.852h.264zm3.44 0 .264.265zm2.646 0 .265.265zm1.852 0 .265.265zm8.202 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m66.234 89.39.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m60.06 89.213.265.265zm4.763 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m54.24 89.213.264.265zm5.556 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m53.975 89.213.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m40.481 89.213.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.83 88.949.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M114.3 88.949c.018 1.976.687 3.122 2.646 3.704-.085-1.827-.871-3.1-2.646-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.48 88.949.264.264zm2.999.176.088.176zm1.764-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.098 90.007 2.646-.53c-1.002-.757-2.038-.572-2.646.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.873 88.949.264.264zm1.5.088.176.088zm1.147-.088.264.264zm2.47.176.087.176zm.97-.176.265.264zm4.763 0 .264.264zm9.26 0 .265.264zm2.382 0-.53.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M81.492 89.742c1.204.601 2.164.716 3.44.265v-.53c-1.303-.473-2.3-.64-3.44.265" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m65.088 88.949.264.264zm.793 0 .265.264zm14.023 0v.264h1.058zm2.381 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m59.267 88.949.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.452 88.684.265.265zm3.44 0v.265h1.323zm3.175 0 .264.265zm-56.621.265.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M101.07 88.949c1.036 1.63 2.41 1.847 4.234 1.852-.661-1.9-2.407-2.391-4.233-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.07 88.684.53 1.058Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.954 88.684.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.73 88.772.177.088zm4.321-.088.265.265zm3.44 0 .264.265zm5.291 0 .265.265zm3.616.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M72.76 88.684c.39 1.48 1.13 2.034 2.646 2.117-.461-1.342-1.263-1.897-2.646-2.117" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m70.38 88.684.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m58.738 88.684.264.265zm6.615 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m53.181 88.684.265.265zm5.292 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.463 88.596.088.176zm2.558-.177.264.265zm5.027 0 .264.265zm3.704 0 .265.265zm3.969 0 .264.265zm4.498 0 .264.265zm2.381 0v.265h1.588zm11.994.177.089.176zm-70.732.088c-1.191 1.229-1.174 2.44-.529 3.969 1.157-1.02 1.418-2.668.53-3.969" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m77.787 88.42.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m72.937 88.508.176.088zm1.41-.089.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.617 88.42.529.529v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m57.856 88.508.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m57.326 88.508.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m52.917 88.42.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M45.773 88.42v1.058h.264zm3.704 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.802 88.155.265.264zm2.117 0v1.587h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M96.837 88.155v1.323h.265zm2.382 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.083 88.155.265.264zm2.91 0v1.058h.265zm2.646 0 .264.264zm5.644.176.088.177zm2.293-.176.265.264zm1.676.176.088.177zm2.47-.088.176.088zm2.47-.088.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#4c2725;stroke:none" d="m65.881 88.155.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M55.827 88.155v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m55.563 88.155.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m55.21 88.243.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.95 87.89.265.265zm7.85.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m99.483 87.89.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.9 87.89.265.265zm4.233 0 .265.265zm2.029.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M85.99 91.859c1.709-.738 3.332-1.915 2.91-3.969-1.844.645-3.245 1.88-2.91 3.969" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M75.406 87.89v1.059h.265zm4.498 0 .265.265zm8.379.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m66.675 87.89-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m53.975 87.89.265.265zm11.73.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m53.71 87.89.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m41.275 87.89.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.169 87.626.264.264zm2.47.176.087.176zm10.23-.176.265.264zm2.646 0 .264.264zm4.233 0 .264.264zm9.084.176.088.176zm7.585-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m65.97 87.802.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m53.446 87.626.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m50.006 87.626.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M86.519 87.361v.794h.264zm6.085 0 .265.265zm3.969 0 .264.265zm3.44 0 .264.265zm4.233 0v.265h1.587zm4.233 0 .265.265zm8.202 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M85.196 88.949c.924-.28 1.172-.64 1.323-1.588z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.197 87.45.177.087zm.882-.088.265.265zm2.646 0-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m79.64 88.949 2.91-.53v-.793c-1.356-.26-2.28.06-2.91 1.323" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.29 87.361.264.265zm3.44 0 .264.265zm1.323 0 .265.265zm1.058 0 .265.265zm1.764.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m66.94 87.361.264.265zm4.762 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m65.881 87.361.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m53.181 87.361.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.215 87.097.264.264zm5.644.176.088.176zm.706-.176.264.264zm1.146.176.088.176zm-63.059.088v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m107.42 87.097 1.324 1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.098 87.097.265.264zm1.058 0 .53.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m103.452 87.097.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m103.188 87.097.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.542 87.097.264.264zm2.38 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.573 87.097.264.264zm3.704 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.965 87.273.088.176zm.706-.176.264.264zm3.969 0 .264.264zm2.91 0 .265.264zm6.615 0v.264h1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m42.069 87.097.264.264zm2.646 0 .264.264zm5.556 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.363 86.832.264.265zm3.174 0v.794h.265zm7.409 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M101.07 86.832v.265h1.324z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.904 86.832.265.265zm2.381 0 .265.265zm2.646 0 .265.265zm3.352.088.176.088zm2.734-.088.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M75.935 86.832c-.33 1.39-.323 2.453.794 3.44l-.264-3.44z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.025 86.832.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m53.27 87.008.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m42.598 86.832.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M112.183 90.007c1.404-.73 1.575-1.962 1.588-3.44-1.54.743-1.586 1.872-1.588 3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M107.95 86.567v.265h.794zm5.027 0-.794 1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.308 86.567.265.265zm1.323 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M62.97 86.567c-.593 1.234-.593 2.207 0 3.44 1-.98.837-2.15.53-3.44zm11.642 0 .265.265zm1.588 0 .53.53zm8.467 0 .264.265zm3.175 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m53.181 86.567-.264.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m50.535 86.567.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m115.623 87.89 1.587-1.587z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.008 86.303.265.264zm2.47.176.088.177zm4.674-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.16 86.303.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.896 86.303.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.044 86.303.264.264zm1.323 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m79.287 86.391.176.088zm2.822.088.088.177zm2.293-.176.265.264zm2.91 0 .265.264zm4.057.176.089.177zm2.823-.176.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M77.523 86.567c.661 1.128 1.72 1.128 2.381 0z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.348 86.303.264.264zm3.175 1.058h.264l.53-.794c-.706-.165-.815.123-.794.794" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M67.204 86.303v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.802 86.038.265.265zm-56.62.265-.265.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m107.685 86.832 1.588-.53c-.762-.29-1.151-.146-1.588.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m107.685 86.038.265.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m98.69 86.038.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.425 86.038.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.78 86.038.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m93.662 86.038.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m92.075 86.038 1.323 2.117-3.704 1.058v.794c2.126.775 3.878.58 5.291-1.323l-.793-.265v-.264l1.323-.265c-.711-1.445-1.901-1.816-3.44-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.873 86.038.264.265zm7.144 0 .264.265zm.882.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.02 86.038c.748 1.362 1.693 1.574 3.176 1.588-.685-1.388-1.718-1.575-3.175-1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M108.215 85.774v.264h1.058zm2.91 0 .265.264zm3.175 0v1.058h.265zm.794 0 .264.264zm-33.955.352.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m104.775 85.774.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m104.51 85.774.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M103.188 85.774v.264h1.322z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m99.219 85.774.264.264zm3.616.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.954 85.774.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m97.102 85.774.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.025 85.774.265.264zm.794 0 .264.264zm2.646 0v.264h.793zm5.556 0v.264h1.323zm5.556 0 .265.264zm3.175 0 .265.264zm2.117 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m65.881 85.774.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m114.565 85.509.793 2.117z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.041 85.597.176.088zm2.999-.088.264.265zm1.587 0v.794h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="M100.542 85.509v.265h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m100.277 85.509.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.924 85.597.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.837 85.509.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m90.223 85.509.264.265zm4.762 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M87.312 86.303c1.315.736 2.511.96 3.97.529-.84-1.599-2.645-1.583-3.97-.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.554 85.509.265.265zm1.764.088.176.088zm3.792-.088.265.265zm1.323 0 .265.265zm1.059 0 .264.265zm5.556 0 .264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M73.29 85.509c-.296 1.586.373 2.588 1.852 3.175-.13-1.445-.618-2.39-1.852-3.175" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m118.092 85.42.089.177zm-51.682.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.86 85.244.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M110.596 85.244c-1.054 1.597-.957 3.22.529 4.498.738-1.532.69-3.235-.53-4.498" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.331 85.244-.264.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.627 85.244.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m74.612 85.244.265.265zm3.97 0 .264.265zm5.733.089.176.088zm2.47-.089.264.265zm.53 0 .264.265zm1.588 0v.265h.794zm5.82 0 .265.265zm2.116 0 .265.265zm8.467 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M71.967 85.244v1.059h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m96.573 84.98.264.264zm8.996 0 .264.264zm1.058 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m80.169 84.98.264.264zm2.646 0 .264.264zm4.762 0 .265.264zm3.44 0v.264h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m51.33 84.98.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m105.569 84.715.264.265zm1.323 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.456 84.715.265.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m112.624 84.539.177.088zm-35.63.176.264.265zm.793 0 .265.265zm4.763 0 .265.265zm2.117 0 .264.265zm5.82 0 .265.265zm1.853 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m106.892 84.45.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.819 84.45.264.265zm2.91 0 .265.264zm2.91 0v1.323h.265zm2.645 0 .265.264zm7.673 0 .265.264zm2.91 0 .265.264zm1.323 0 .264.264zm7.408 0v.264h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m117.828 84.362.088.177zm-59.62.089.265.264zm5.821 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m111.654 84.186.265.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m107.156 84.186.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m105.922 84.362.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.806 84.186.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m96.044 84.186.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.432 84.362.088.177zm4.498 0 .088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m51.858 84.186-.264.53zm10.32 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.035 83.922.265.264Z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m107.42 83.922.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m95.78 83.922.264.264zm4.498 0 .265.264zm2.999.176.088.176zm2.557-.176.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m94.28 84.098.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m73.554 83.922.265.264zm2.646 0 .265.264zm1.058 0 .265.264zm5.821 0 .265.264zm.794 0v1.322h.264zm2.646 0v1.058h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m57.944 83.922.264.264zm.529 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M56.62 83.922c-.588 1.223-.793 2.445.53 3.175v-3.175z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m117.475 83.657.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.215 83.657.264.265zm6.085 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m107.95 83.657.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.013 83.657.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.515 83.657.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m94.192 83.657.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.869 83.657.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M89.958 83.922c.81 1.385 2.133 1.005 3.44.529v-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m114.035 83.392.265.265zm-38.1.265.265.265zm3.44 0 .265.265zm11.024.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m109.008 83.392.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m108.744 83.392.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m106.098 83.392.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m85.725 83.392.265.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m84.402 83.392-.265 1.852.794-1.852z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.55 83.392.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m82.285 83.392 1.323 1.852z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m75.318 83.48.176.089zm6.174-.089.264.265zm.529 0v.794h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M73.554 83.392c.603 2.011 2.028 2.365 3.969 2.382-.767-1.773-2.112-2.342-3.969-2.382" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m57.68 83.392.264.265zm14.552 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M53.975 83.392c-.417 1.756-.14 3.144 1.588 3.97.407-1.616-.197-3.073-1.588-3.97" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m52.652 83.392-.264.53Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m113.506 83.128.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M111.125 83.392v.53c1.166.56 2.01.56 3.175 0-.94-.882-1.975-.883-3.175-.53" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.273 83.128.265.264zm1.852 0v1.058h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m106.892 83.128.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.394 83.128.264.264zm4.233 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M100.013 83.392c.905 1.027 1.863 1.055 3.174 1.059-.468-1.482-1.863-1.446-3.174-1.059" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.013 83.128.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.78 83.128.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M73.554 83.128v.264h1.323zm5.556 0 .265.264zm1.059 0 .264.264zm3.351.088.177.088zm.617.176v.265l.794-.265v1.059h.265l-.265-1.323zm1.323-.264.265.264zm.794 0 .265.264zm3.44 0v.264h1.058zm1.323.264v.265l1.587-.265zm1.852-.264v.264h.793zm1.587 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.946 82.863.529.53zm-63.764.265.265.264zm7.938 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M111.919 82.863v.265h1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#c3a073;stroke:none" d="m109.538 82.863.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m107.95 82.863.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.308 82.863.265.265zm4.498 0v.265h1.323zm4.234 0v.265h1.058zm2.557.088.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m94.985 82.863.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.9 82.863.265.265zm2.646 0 .264.265zm2.116 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m87.312 82.863-.264 2.646c.852-.788 1.074-1.52.794-2.646z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m81.227 82.863.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.008 82.599.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m108.744 82.599.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.219 82.599.264.264zm.794 0v.264h.793zm4.497 0 .265.264zm1.852 0 .265.264zm2.116 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m96.573 82.599.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.815 82.599.264.264zm2.38 0 .265.264zm2.381 0 .53.529v-.53zm1.058 0 .265.265zm2.117 0 .265.264zm.53 0 .264.264zm2.117 0 .264.264zm1.852 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M80.698 82.599c-.605 1.258-.469 2.153 0 3.44h.794l-.265-3.44z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m57.415 82.599.264.264zm1.323 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m109.802 82.334.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m109.626 82.51.088.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.335 82.334.265.265zm7.938 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m95.78 82.334.264.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M76.994 82.334v1.058h.264zm1.587 0 .265.265zm2.117 0-.265.53zm3.263.176.088.177zm4.41-.176.264.265zm2.116 0 .265.265zm.53 0 .264.265zm2.116 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m116.152 82.07.53.529zm-60.854.265-1.323.53v.264c.657-.032 1.452-.011 1.323-.794m17.727 0-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.425 82.07.265.264zm1.147.177.088.176zm.44-.177.265.265zm1.587 0 .265.265zm5.027 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#815d4d;stroke:none" d="m97.102 82.07.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m78.317 82.07.264.264zm11.906 0 .264.265zm2.646 0 .264.265zm3.175 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m115.623 81.805.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.01 81.893.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M104.246 82.07v.529l2.646-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.865 81.805.264.264zm2.645 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#9d7b6c;stroke:none" d="m96.308 81.805.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.075 81.805.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M91.017 82.07c.769.943 1.442 1.172 2.645 1.058-.769-.943-1.442-1.173-2.645-1.059" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m89.694 81.805.264.264zm1.323 0v.264h.793z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M88.37 81.805c.596 1.18 1.39 1.308 2.647 1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.638 81.981.088.177zm2.381 0 .089.177z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M77.258 81.805c.006 2.006.47 3.125 2.382 3.969-.017-1.811-.472-3.428-2.382-3.97" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m56.885 81.805.265.264zm3.44 0 .265.264zm14.287 0-.264.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.367 81.54.264.265zm.882.177.088.176zm3.88-.177.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m96.573 81.54.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m77.523 81.54.264.265zm15.61 0v.265h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M114.3 81.276v.264l1.058.265v-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#4c2725;stroke:none" d="M96.837 81.276c-.61.827-2.11 1.624-2.268 2.67-.219 1.448 1.565 2.257 2.096 3.419.68 1.49.13 3.475-.11 5.023-.453 2.907-4.178 3.288-5.387 5.821-.842 1.764.084 3.702-.238 5.556-.907 5.209-4.957 9.317-9.968 10.319-1.29.258-3.53 1.233-4.705.369-1.53-1.124-1.776-4.2-2.12-5.925-.894-4.465-2.756-8.602 2.063-11.41.7-.408 1.564-.937 2.381-1.042 1.377-.178 4.666 3.395 6.086 4.25-.244-1.542-1.294-1.555-2.322-2.492-2.17-1.98-1.318-3.675-4.822-2.535l.53-6.615h-.266c0 2.59-.216 5.288-2.385 7.04-1.427 1.154-3.446 1.515-4.586 3.039-1.624 2.172 1.608 6.01-.257 7.483-1.867 1.476-3.39-2.648-3.803-3.804-1.029-2.88-2.918-6.93-2.59-10.054.145-1.392 1.436-2.318 1.715-3.704l-2.91 3.704c-1.019-1.962-2.883-3.228-5.027-3.7-1.727-.38-3.546-.126-4.763-1.591h-.264c.23 3.036 3.658 2.324 5.82 3.071 1.945.672 3.136 2.239 3.908 4.072 1.33 3.16 1.236 4.147-1.217 6.196-.508.425-1.202 1.67-1.982 1.535-1.277-.221-1.52-2.851-2.37-3.651-2.106-1.983-5.34-.811-7.334-3.286h-.265c-.29 3.062 5.893 3.13 7.6 5.055.872.984 1.094 3.154 2.72 3.237 1.556.08 2.776-2.537 4.173-2.115 1.64.496 1.613 4.932 2.442 6.254 1.537 2.453 4.885 1.885 6.211 4.767 1.894 4.115 1.83 8.883 2.846 13.23.742 3.181 2.442 6.475 2.308 9.789-.129 3.167-2.032 6.057-1.914 9.26.116 3.19 2.05 6.08 2.178 9.26.085 2.082-1.04 4.013-.726 6.086.308 2.046 1.796 3.695 2.014 5.82.278 2.708-.871 5.133 1.418 7.353 2.547 2.47 5.417 1.217 8.418.896 2.183-.234 4.33.193 6.35 1.016 5.191 2.115 9.17 3.624 14.816 2.907v-.265c-1.853-.344-3.803-.054-5.556-.843-2.797-1.259-4.543-3.294-7.673-4.012-4.193-.962-9.89-.19-13.218-3.375-2.36-2.26-1.223-4.84-2.054-7.645-.858-2.897-.81-5.637-1.487-8.467-.91-3.806-2.283-6.773-2.003-10.848.246-3.562 2.067-6.759 2.2-10.318.136-3.603-3.166-7.644-1.997-11.113 1.155-3.432 6.828-4.362 9.563-6.39 4.957-3.677 5.74-9.365 5.292-15.041l10.054 2.885 7.673-3.15v-.264c-2.955-.315-5.095 1.854-7.938 1.844-1.664-.006-6.723-1.434-7.3-3.172-.322-.969.68-1.876 1.123-2.641 1.254-2.166 1.033-5.293 2.794-7.108 1.8-1.855 3.668-.089 5.75-.65 1.231-.331.93-1.663 1.534-2.532.666-.96 1.86-1.126 2.45-2.145l-3.027 1.02-2.005 2.259-6.875.954-1.656-3.242 1.392-2.314z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M86.519 81.276v1.323h.264zm3.968 0v.264h.794zm3.97 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m76.465 81.276.264.264zm2.645 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M100.013 82.599c1.212-.092 1.85-.487 2.38-1.588-1.22.092-1.804.513-2.38 1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.806 81.011-.529.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m97.102 81.011.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.873 81.011.264.265zm4.41.088.176.088zm3.263-.088.264.265zm3.44 0 .264.265zm1.852 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m77.17 81.1.177.088zm.617-.088v.265h1.059zm2.382 0-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m92.869 80.747.264.264zm2.381 0 .265.264zm6.085 0v.264l1.323.265zm7.938 0v.265h.794zm1.323 0v.264h.794z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M90.223 80.747v.529l1.587-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m83.608 80.482.265.265zm1.323 0v.794h.265zm1.411.176.089.177zm4.41-.176v.265h.794zm2.381 0 .265.265zm4.851.176.088.177zm6.438-.088.177.088zm4.322-.088.264.265zm-20.902.265.264.264zm1.41.176.089.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M114.035 80.217v.794h.265Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 79.953.264.264zm1.852 0 .265.264zm3.263.176.088.177zm1.5-.176.264.264zm6.086 0v1.852h.264zm.705.088.177.088zm1.147-.088.265.264zm1.588 0 .264.264zm1.675.176.088.177zm.706-.176.264.264zm2.381 0 .265.264zm2.91 0 .265.264zm-27.516.264.264.265zm1.852 0 .264.265zm2.116 0 .265.265zm4.234 0 .264.265zm1.852 0 .264.265zm10.319 0 .264.265zm2.293.089.176.088zm2.205-.089.264.265zm2.91 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M82.815 79.953c-.288 1.254-.333 2.69 1.058 3.175l-.53-3.175zm2.645 0c-.465 1.506-.451 2.616.794 3.704l-.264-3.704z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m114.3 79.688.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.865 79.688.264.265zm1.058 0 .265.265zm2.381 0 .265.265zm2.381 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M92.869 81.54c1.452-.035 2.482-.363 2.91-1.852-1.41.077-2.292.558-2.91 1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m82.815 79.688-.265.794h.265zm11.377 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m80.698 79.688-.265 1.059h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m110.596 79.424.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m100.277 79.688 1.588.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m91.281 79.159.265.265zm8.202 0 .265.265zm5.557 0 .264.265zm1.41.176.089.177zm.97-.176.264.265zm2.91 0 .265.265zm-15.346.265v.264l1.059.265v-.265zm3.175 0 .265.264zm2.117 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m91.017 79.159 1.058 1.323z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.37 79.159.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m84.314 79.247.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m88.106 78.894.265.265zm11.907 0 .264.265zm.793 0 .265.265zm1.588 0 .264.265zm7.408 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m114.388 78.806.088.177zm-33.337.265.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.219 78.63.264.264zm1.852 0 .264.264zm8.467 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M98.954 78.63c-.983 1.49-.855 2.707 0 4.233h.265c.665-1.382.841-3.053-.265-4.233" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.577 78.63.265.264zm11.113 0-.265.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M87.048 78.63c.1 1.481.697 2.215 2.117 2.646-.157-1.405-.777-2.187-2.117-2.646" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m84.667 78.63.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.92 78.453.177.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M107.42 78.365c.415 2.048 2.045 2.655 3.97 2.382-.934-1.58-2.142-2.297-3.97-2.382" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.277 78.365.265.265zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m89.165 78.365.264.265zm4.145.088.176.089z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m87.048 78.365-.265 1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m83.608 78.365.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m104.246 78.1.264.265zm2.91.264v.265l1.059-.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m113.683 77.924.176.088zm-32.191.177-.265.529zm1.587 0 .265.265zm2.381 0 .265.264zm2.91 0 .265.264zm5.732.088.177.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.335 77.836.265.265zm2.646 0 .265.265zm.794 0-.265 1.323h.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m100.277 79.159 1.323-1.058Z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.631 77.836.265.265zm3.175 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m82.462 77.924.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M106.363 77.572v.793h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m112.713 77.307.264.265zm-20.373.265.529.529zm3.264.177.088.176z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.717 77.307.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m112.095 77.13.177.089zm-21.343.176-.794 1.323 1.323-1.058zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m96.837 77.042.265.265zm5.292 0v1.588h.265zm1.323 0 .265.265zm2.646 0 .264.265zm2.117 0 .264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m110.067 76.778.264.264zm.97.088.176.088z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m106.892 76.778.264.264zm.529 0 .264.264zm.529 0 .265.264zm.53 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.363 80.217-.265-3.44c-1.545.852-1.176 2.684.265 3.44" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m103.981 76.778.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M102.394 76.778c.005 1.865.343 3.292 2.381 3.704-.078-1.68-.653-3.209-2.381-3.704" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m97.631 76.778.265.264zm1.323 0v.264h1.058z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M97.102 76.778 98.69 78.1z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.16 76.513.265.265zm4.41.088.177.089zm1.147-.088.264.265zm1.146.177.088.176zm.706-.177-.53.794zm1.058 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m110.067 76.249.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m100.983 75.543.176.088zm4.145.088.088.177zm2.822-.176.265.264zm-8.467.264.265.265zm6.703.177.088.176zm.97-.177.265.265zm-7.408.265.264.265zm3.704 0 .265.265zm-5.82.265.264.264zm8.731 0 .265.264zm1.058 0 .264.264zm1.323 0 .264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m106.098 75.19 1.323 1.852c-.001-1.034-.328-1.54-1.323-1.852" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.954 75.19.265.265zm.97.088.177.089zm2.205-.088.265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m95.515 75.19.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m108.744 74.926.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="m108.48 74.926-.265 2.116h.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m98.425 74.926.265.264zm.794 0 .264.264zm7.055.088.177.088zm1.94-.088.265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M97.102 75.19c.632 1.281 1.539 1.555 2.91 1.588-.546-1.337-1.481-1.936-2.91-1.588" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m102.394 74.661.264.265zm-5.557.265v.529l.53-.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m110.067 74.397.264.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m101.865 74.132.264.265zm1.675.176.088.177zm-1.41.089.264.264zm2.646 0 .265.264z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M99.219 74.397c.767 1.253 2.147 1.1 3.44.793-.992-1.19-2.015-1.058-3.44-.793" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="m99.748 74.132.264.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m96.308 74.132-.264.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#80a168;stroke:none" d="M100.277 73.867v.265h1.058zm4.233 0 .265.265z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="m109.538 73.603.529.529z" transform="translate(-13.494 -71.967)"/><path style="fill:#578d34;stroke:none" d="M103.981 73.603c-.6 1.426-.646 2.82.794 3.704l-.265-3.704z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M106.363 72.28v.264h.793zm-6.967.353.177.088zm8.467 0 .176.088zm-9.525.264.176.088zm10.142-.088.265.265zm-10.848.265.265.264zm-.529.264-.265.53z" transform="translate(-13.494 -71.967)"/><path style="fill:#a7d6ad;stroke:none" d="m95.78 78.1-2.647.53c-1.238-2.015-2.116-.28-3.708-.22-1.803.069-3.674-1.053-4.758 1.014h-.53c-1.996-3.805-3.051 1.783-4.62 1.992-2.135.285-5.246-.424-6.825 1.75-1.347 1.856-.788 4.25-2.564 6.258-.896 1.014-2.732-.3-3.283 1.151-.49 1.293.481 3.966.36 5.517-3.519-.574-.435-6.957-.126-8.99.163-1.072-.64-1.378-1.198-.535-.825-.452-.928-1.319-1.67-1.805-.678-.444-1.569-.27-2.238-.772-.6-.45-.704-1.26-1.267-1.713-1.51-1.214-2.056 1.53-2.233 2.438h-.265c-1.603-5.858-7.946.526-8.597 4.234-.337 1.921.327 3.396-1.192 5.027v-2.382c-3.708.383-1.503-2.452-3.26-4.06-2.604-2.385-4.522 1.5-4.942 3.531-1.39-.395-2.331.04-2.117 1.588-1.073.658-1.054 1.458-1.058 2.646-1.26.011-2.158.046-2.646 1.323 1.244 1.498.966 3.22.794 5.027 1.058 1.12.708 2.286.529 3.704-3.382.527-1.255 3.585 1.587 2.91-.187 2.465-3.221 3.062-5.288 2.455-1.485-.437-2.508-2.086-3.708-3.014-2.827-2.187-8.428-5.02-12.155-3.514-1.219.492-.897 1.758-1.334 2.75-1.156 2.62-1.53 7.392 2.906 6.88 1.05 1.946 2.973 2.443 2.381 5.027l1.588-.265c-.258 1.18-.95 3.757.284 4.45 1.002.563 2.147.052 3.128.779.996.739 1.312 2.181 2.677 2.373 1.67.235 3.584-.853 5.288-.987-1.41 2.305-6.277 1.57-8.345 3.79-2.233 2.398-.326 5.966-2.768 8.38 1.455 1.697 6.112 5.836 7.409 1.852 2.168-.006 3.493-.245 3.704-2.645 2.158 1.242-.2 3.021-.221 4.762-.012.962 1.298 3.326 1.856 4.15 1.388 2.049 2.865-1.082 3.726-1.74 2.373-1.811 5.164-.872 5.487-4.791 2.044-.067 1.676-1.08 2.275-2.642.384-1.001 1.333-1.557 1.565-2.654.338-1.598-.55-3.162-.328-4.758.11-.787.521-3.914 1.553-3.914 1.377 0 2.01 2.385 3.144 2.994 1.504.807 2.49.893 3.433 2.507-2.306.946-3.627 1.642-4.69 3.969-.783 1.714-2.342 3.1-2.66 5.027-.555 3.371 1.242 3.374 3.57 4.48 1.055.502 1.693 1.394 2.986 1.192 2.485-.388 4.685-2.504 6.727-3.82 1.623-1.047 4-2.576 3.592-4.762l1.852-.265v-2.117h4.233c.673-3.468-.581-12.561-6.35-9.236-3.014 1.738-1.504 5.132-2.954 7.621-.838 1.44-2.706 1.282-3.845 2.316-.768.698-.726 1.789-.874 2.739h-.265c.011-3.78 2.749-2.819 4.187-5.32 1.906-3.313-1.085-6.522 3.486-8.438v-.265c-5.31-.015-10.023-4.69-15.345-3.147-1.99.576-3.99 2.184-5.557 3.498a14 14 0 0 0-3.055 3.618c-1.676 2.835.956 3.983.324 6.613-.346 1.44-2.267 2.306-3.354 3.176.302-1.468 1.734-1.887 2.16-3.199.364-1.12-.726-3.847-2.14-3.705-.706.072-1.273.852-1.883 1.166-.972.502-2.187.177-3.096.719-1.532.913-1.742 2.06-3.772 2.109v-.265c1.792-.468 1.92-1.752 3.234-2.75.81-.616 1.947-.4 2.792-.977 1.363-.933 2.782-3.23 4.004-4.485.733-.753 3.137-2.888 2.23-4.054-3.33-4.291-7.74-.013-11.466-1.387-2.193-.81-2.79-4.216-4.13-5.914-1.307-1.656-3.587-2.507-5.131-3.98l5.735 2.673 4.058 5.261 6.082-.957 6.614 3.006 7.938-2.84-1.323-3.175c1.322.702 2.059 1.646 1.852 3.175l4.763.794c-.126-1.595-.294-3.798-1.644-4.878-1.595-1.276-4.655-.635-6.558-.682-1.805-.045-3.2-.762-4.763-1.584.238 1.475-.121 2.28-1.323 3.175-.252-1.534.086-2.499 1.058-3.704v-.265c-1.146-1.61-2.379-3.835-1.436-5.82.56-1.18 2.073-2.05 2.113-3.44.045-1.51-1.413-2.599-1.803-3.969-.27-.946.195-1.971.333-2.91h.264c.025 2.913 2.76 4 3.06 6.614.392 3.424-5.262 5.772-.414 8.73 3.032 1.85 7.884-.345 10.527 1.967 2.309 2.02 1.125 6.024 3.772 7.832 3.337 2.28 7.754.786 11.1 2.8 3.127 1.883 3.294 4.946 4.234 8.04h.265v-1.058h.265l1.058 1.587c0-2.703-.838-5.037-1.3-7.673-.666-3.784-.6-9.271-3.102-12.406-1.422-1.782-4.1-1.475-5.466-3.473-1.136-1.661-.64-4.539-2-5.968-1.216-1.278-3.236 2.165-4.688 1.93-1.655-.266-1.801-2.56-2.819-3.527-1.94-1.846-7.788-1.657-7.348-5.13h.53c.95 2.653 4.275 1.782 6.345 2.947 1.572.885 2.053 2.985 2.915 4.46.985-.48 3.626-2.256 4.073-3.247 1.018-2.257-1.398-6.92-3.283-8.07-2.329-1.42-8.11-.345-6.876-4.557h.265c.189 2.446 2.934 1.795 4.762 2.323 2.042.59 3.495 1.835 4.763 3.498h.264l2.646-4.763h.265c-.046 1.965-1.506 2.965-1.792 4.763-.447 2.818 1.208 6.606 2.056 9.26h.265l1.323-2.117c-.136 2.283-1.607 2.842-.311 5.292.447.845 2.287 3.05 3.061 1.283.958-2.186-1.636-4.367-.308-6.564 1.255-2.077 3.754-2.36 5.408-3.896 1.75-1.624 1.832-4.723 1.94-6.963h.264c.861 2.367-.335 4.24-.53 6.615 3.434-.58 2.702.768 4.868 2.776 1.036.96 2.035 1.043 2.541 2.516-1.184-.493-2.46-1.03-3.415-1.909-.708-.652-1.21-1.829-2.166-2.154-.84-.285-1.876.241-2.62.594-3.523 1.667-3.712 4-3.44 7.437h.264c.858-.41 1.273.124 1.852.794l-1.852-.529c.19 2.374.676 7.985 2.495 9.664.968.894 2.74.204 3.855-.046 3.59-.807 6-1.38 8.195-4.59 1.326-1.941 2.55-4.243 2.834-6.615.203-1.692-.685-3.132.194-4.763 1.26-2.335 4.741-2.938 5.318-5.556.345-1.564.784-4.065.044-5.553-.499-1.002-2.013-1.703-2.047-2.904-.033-1.18 1.891-2.126 2.396-3.185h.529l-1.588 2.381 1.852 3.175 3.44-1.037 3.675.084 1.727-2.036 3.594-1.244c-.772.945-1.998 1.28-2.715 2.19-.687.872-.463 2.19-1.554 2.786-1.398.765-3.208-.512-4.726-.098-2.471.672-2.333 3.763-3.028 5.705-.597 1.671-3.45 4.426-1.191 5.784 1.804 1.085 5.301 2.413 7.393 1.977 2.166-.452 5.203-3.128 7.144-1.146-2.842.25-4.887 2.59-7.673 3.047-3.354.55-7.11-1.82-10.054-3.047.138 6.713-1.04 11.86-6.88 16.026-2.287 1.632-6.485 1.985-7.816 4.613-1.363 2.687.593 6.159 1.35 8.73.538 1.826.633 3.933.91 5.82h.265l.529-2.116h.264l-.529 7.408h.265c2.829-6.555 8.395-5.025 14.023-7.064 3.129-1.134 4.233-4.395 6.879-6.136 1.658-1.092 3.567-.923 5.292-1.731 1.33-.624 2.122-1.985 3.44-2.648.954-.48 2.548-.55 3.267-1.392 1.041-1.217-1.489-2.516-1.841-3.52-.346-.984.483-2.1.954-2.909v2.646l2.382 2.381 4.977-2.296 4.283-4.054c-1.58 2.072-2.5 4.618-4.762 6.095-2.564 1.673-7.44 2.722-8.989 5.572-.79 1.453-.02 3.695-.007 5.267 2.354-.002 4.592-.976 5.556 1.852-2.072-1.106-4.085.786-6.056-.11-1.795-.816-1.026-4.304-3.21-4.313-4.08-.019-4.268 4.106-6.884 6.066-2.269 1.7-4.864 1.236-7.398 1.965-3.805 1.096-7.403 4.315-8.466 8.033h1.058v1.588l6.615.745 5.82-2.597c1.111-5.5 8.711.762 9.526-4.498.926.254 5.831-.251 6.169-1.098.427-1.073-.3-2.795-.349-3.93 2.263 1.888 2.395 3.046 5.557 2.647.041-2.496 3.91-1.904 1.852-5.027l1.587.264c1.018-2.498 3.354-3.174 4.772-5.295 2.162-3.236 1.488-7.915.298-11.374-.678-1.972-1.457-3.843-3.747-3.968l-.264-2.117-1.059.265-2.646-1.588 1.059.265c-3.844-4.404-6.855 4.824-9.79 1.323 3.876-.31-.042-7.007.673-9.237.441-1.378 2.433-2.522 3.284-3.728 2.213-3.132 3.378-8.813 1.724-12.432-.692-1.513-2.267-1.53-3.076-2.73-.466-.69.002-1.524-.219-2.276-.47-1.59-2.837-2.102-4.238-1.877 1.356-7.327-16.086-6.5-14.287 1.059z" transform="translate(-13.494 -71.967)"/><path style="fill:#dbd3a1;stroke:none" d="M100.806 72.28v.264h1.059z" transform="translate(-13.494 -71.967)"/></symbol>';

  var VPD_TREE_SYMBOL_ID = 'vpdTreeArt';

  function ensureVpdTreeSprite(){
    if (document.getElementById(VPD_TREE_SYMBOL_ID)) return;
    var sprite = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    sprite.setAttribute('aria-hidden', 'true');
    sprite.style.position = 'absolute';
    sprite.style.width = '0';
    sprite.style.height = '0';
    sprite.style.overflow = 'hidden';
    var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    defs.innerHTML = VPD_TREE_MARKUP;
    sprite.appendChild(defs);
    document.body.appendChild(sprite);
  }
  ensureVpdTreeSprite();

  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }

  var mount = document.getElementById('vpdCard21');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';
  var units = 'kPa';

  // -- Title bar ------------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Vapour Pressure Deficit (kPa)');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // -- 60:40 content split (left: tree + hero value, right: readouts) ------
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions — padding and gap trimmed
  // slightly from the standard 3px/2px, since six rows (Day/Month/Year
  // Min/Max) in this card's pane add up to a couple pixels more than the
  // 175px available, reading as a tight fit at the very top and bottom.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '2px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var dayMinText   = addChipRow('Day Min');
  var dayMaxText    = addChipRow('Day Max');
  var monthMinText   = addChipRow('Month Min');
  var monthMaxText    = addChipRow('Month Max');
  var yearMinText       = addChipRow('Year Min');
  var yearMaxText        = addChipRow('Year Max');
  yearMaxText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the records page — an absolutely-
  // positioned transparent overlay anchor, appended last so it paints on
  // top of everything else and actually receives the click. top/bottom
  // match the title band (-20px) and this card's own border-bottom
  // override (0, set above). Class name lets the shared hover-tooltip
  // script (indexNew.html) find it and read data-modal. VPD has no
  // dedicated chart page (charts-d3.html doesn't cover it) — records.html
  // does list it, so that's the link target instead.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'records.html';
  cardLink.setAttribute('data-modal', 'Vapour Pressure Deficit');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'records.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175;

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    var treeViewBox = { w: 117.74, h: 106.411 };
    var treeH = 130, treeW = treeH * (treeViewBox.w / treeViewBox.h);
    // treeY nudged down slightly (5→12) and the hero value pulled up
    // close beneath the tree (was a fixed H-8, independent of the tree's
    // own position, leaving a ~32px gap between them with the whole
    // composition nearly filling the pane edge-to-edge) — heroY is now
    // derived from the tree's own bottom edge instead, so the two sit
    // close together as one group, vertically centered as a group within
    // the pane rather than each pinned to an opposite edge.
    var treeY = 12;
    svg.append('use')
      .attr('href', '#vpdTreeArt').attr('xlink:href', '#vpdTreeArt')
      .attr('height', treeH).attr('width', treeW)
      .attr('x', (W - treeW) / 2).attr('y', treeY);

    // Hero value — same accent colour + mono font as Current Conditions.
    var heroY = treeY + treeH + 15;
    var currentEl = svg.append('text').attr('x', W / 2).attr('y', heroY).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(v.current.toFixed(2) + ' ' + units);

    // ---- Right pane: 6 readouts as label/value chip rows ----
    dayMinText.textContent = v.dayMin.toFixed(2) + ' ' + units;
    dayMaxText.textContent = v.dayMax.toFixed(2) + ' ' + units;
    monthMinText.textContent = v.monthMin.toFixed(2) + ' ' + units;
    monthMaxText.textContent = v.monthMax.toFixed(2) + ' ' + units;
    yearMinText.textContent = v.yearMin.toFixed(2) + ' ' + units;
    yearMaxText.textContent = v.yearMax.toFixed(2) + ' ' + units;
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(arch){
        var vpd = arch.vpd || {};
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

        lastData = {
          current: num(vpd.current, 0),
          dayMin: num(vpd.day_min, 0),
          dayMax: num(vpd.day_max, 0),
          monthMin: num(vpd.month_min, 0),
          monthMax: num(vpd.month_max, 0),
          yearMin: num(vpd.year_min, 0),
          yearMax: num(vpd.year_max, 0)
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardVapourPressureDeficit: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardVapourPressureDeficit.js failed:", e);
}

/* ===== cardEvapoTranspiration.js ===== */
try {
/*
##############################################################################################
# cardEvapoTranspiration.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardEvapoTranspiration.js =====================
(function(){
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23', month: 'long',
      year: 'numeric', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    return new Date(Date.UTC(+p.year, months.indexOf(p.month), +p.day, +p.hour, +p.minute, +p.second));
  }

  var mount = document.getElementById('etCard22');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar --------------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Evapotranspiration (mm)');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // -- 60:40 content split (left: leaf + hero value, right: readouts) ------
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Same chip-row idiom as Current Conditions.
  function addChipRow(label){
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.gap = '1px';
    row.style.padding = '3px 0';
    row.style.borderBottom = '1px solid var(--bs-border-color)';

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    valueEl.style.whiteSpace = 'nowrap'; valueEl.style.overflow = 'hidden'; valueEl.style.textOverflow = 'ellipsis';
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var hourText  = addChipRow('Last Hour');
  var last24hText = addChipRow('Last 24 Hours');
  var monthText     = addChipRow('This Month');
  var yearText          = addChipRow('This Year');
  yearText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the records page — an absolutely-
  // positioned transparent overlay anchor, appended last so it paints on
  // top of everything else and actually receives the click. top/bottom
  // match the title band (-20px) and this card's own border-bottom
  // override (0, set above). Class name lets the shared hover-tooltip
  // script (indexNew.html) find it and read data-modal. Evapotranspiration
  // has no dedicated chart page (charts-d3.html doesn't cover it) —
  // records.html does list it, so that's the link target instead.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'records.html';
  cardLink.setAttribute('data-modal', 'Evapotranspiration');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '1400px');
  cardLink.setAttribute('data-modal-height', '700px');
  cardLink.setAttribute('data-url', 'records.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175;

  function drawLeaf(svg, x, y, height){
    var width = height * (37.891 / 50.412);
    var g = svg.append('g').attr('transform', 'translate(' + x + ',' + y + ') scale(' + (width / 37.891) + ')');
    var inner = g.append('g').attr('transform', 'translate(-6.26)scale(.1)');
    inner.append('path').style('fill', '#3a7f0d').attr('d',
      'M339.772 0s44.536 108.954-146.337 182.138C89.719 221.893 10.059 323.789 105.173 481.193c7.877-70.357 41.653-225.485 186.888-260.884 0 0-135.176 50.546-147.117 279.347 69.459 9.752 232.361 16.305 280.726-125.062C489.536 187.817 339.772 0 339.772 0');
    inner.append('path').style('fill', '#49a010').attr('d',
      'M145.007 498.704c147.456-58.849 254.748-196.71 269.556-361.283C384.418 56.107 339.772 0 339.772 0s44.536 108.954-146.337 182.138C89.719 221.893 10.059 323.789 105.173 481.193c7.877-70.357 41.653-225.485 186.888-260.884-.008.001-134.782 50.421-147.054 278.395');
    [[90.459,171.985,13.785],[133.782,158.2,9.846],[124.921,64.662,24.615],[200.736,120.785,7.877],[266.713,76.477,22.646]].forEach(function(c){
      inner.append('circle').style('fill', '#5f8dd3').attr('cx', c[0]).attr('cy', c[1]).attr('r', c[2]);
    });
    return { width: width };
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    var leafHeight = 90, leafX = W / 2, leafY = 20;
    drawLeaf(svg, leafX - (leafHeight * 37.891 / 50.412) / 2, leafY, leafHeight);

    // Hero value — same accent colour + mono font as Current Conditions.
    // heroY derived from the leaf's own bottom edge (was a fixed H-16,
    // independent of the leaf's position, leaving a ~57px dead gap
    // between the leaf and the value beneath it) so the two sit close
    // together as one group, vertically centered as a group within the
    // pane rather than each pinned toward an opposite edge.
    var heroY = leafY + leafHeight + 18;
    svg.append('text').attr('x', leafX).attr('y', heroY).style('text-anchor', 'middle')
      .style('font-family', '"IBM Plex Mono", ui-monospace, monospace').style('font-size', '13px').style('fill', 'var(--bw-accent)')
      .text(v.current.toFixed(2) + ' ' + v.units);
    svg.append('text').attr('x', leafX).attr('y', heroY + 14).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', '8px').style('fill', overlayTextColor)
      .text(DivumWXI18N.t('Current'));

    // ---- Right pane: 4 readouts as label/value chip rows ----
    hourText.textContent = v.hour.toFixed(2) + ' ' + v.units;
    last24hText.textContent = v.last24h.toFixed(2) + ' ' + v.units;
    monthText.textContent = v.month.toFixed(2) + ' ' + v.units;
    yearText.textContent = v.yearTotal.toFixed(2) + ' ' + v.units;
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(arch){
        var et = arch.et || {};
        var meta = arch.meta || {};
        function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

        var now = stationNow();
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        lastData = {
          units: meta.rain_units || 'mm',
          current: num(et.current, 0),
          hour: num(et.hour, 0),
          last24h: num(et.last24h, 0),
          month: num(et.month, 0),
          yearTotal: num(et.year, 0),
          monthLabel: months[now.getUTCMonth()],
          year: now.getUTCFullYear()
        };
        renderCard(lastData);
        setStatus(true);
      }).catch(function(e){
        console.warn('cardEvapoTranspiration: refresh failed --', e.message);
        setStatus(false);
      });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardEvapoTranspiration.js failed:", e);
}

/* ===== cardEarthquake.js ===== */
try {
/*
##############################################################################################
# cardEarthquake.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardEarthquake.js =====================
(function(){
  var EQ_JSON_URL = './jsondata/eq.txt';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: '2-digit', month: 'short', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(new Date()).forEach(function(x){ p[x.type] = x.value; });
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function eventTimeLabel(isoString){
    if (!isoString) return '\u2014';
    var d = new Date(isoString);
    if (isNaN(d.getTime())) return '\u2014';
    var p = stationParts(d);
    return p.hour + ':' + p.minute + ':' + p.second + ' ' + (+p.day) + ' ' + p.month + ' ' + p.year;
  }

  function haversineKm(lat1, lon1, lat2, lon2){
    var R = 6371;
    var toRad = function(d){ return d * Math.PI / 180; };
    var dLat = toRad(lat2 - lat1), dLon = toRad(lon2 - lon1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  var MAG_CATEGORIES = [
    { max: 4.0, label: 'Minor', color: '#2e8b57' },
    { max: 5.0, label: 'Light', color: '#fde396' },
    { max: 6.0, label: 'Moderate', color: '#ff964f' },
    { max: 7.0, label: 'Strong', color: '#ff6181' },
    { max: 8.0, label: 'Great', color: '#be688b' },
    { max: Infinity, label: 'Major', color: '#007FFF' }
  ];
  function categoryFor(mag){
    for (var i = 0; i < MAG_CATEGORIES.length; i++){
      if (mag < MAG_CATEGORIES[i].max) return MAG_CATEGORIES[i];
    }
    return MAG_CATEGORIES[MAG_CATEGORIES.length - 1];
  }

  var mount = document.getElementById('earthquakeCard23');
  if (!mount || !window.d3) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content (was 157px).
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar --------------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Earthquake');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // -- 60:40 content split (left: pulse rings + magnitude, right: readouts) --
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // Fixed-height/nowrap/ellipsis by default (Time, Depth, Category are
  // short, predictable values that don't need more). Location and
  // Epicenter pass wrap:true instead — USGS region names and station
  // names can be long, and there's plenty of vertical room in this pane
  // (5 short rows only need ~100px of the 175px available) to just let
  // those wrap onto a second line rather than truncating a real place
  // name with an ellipsis.
  function addChipRow(label, opts){
    var wrap = opts && opts.wrap;
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.boxSizing = 'border-box';
    row.style.borderBottom = '1px solid var(--bs-border-color)';
    if (wrap) {
      row.style.minHeight = '20px';
      row.style.padding = '3px 0';
    } else {
      row.style.height = '20px';
      row.style.overflow = 'hidden';
    }

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = 'var(--bs-body-color)';
    labelEl.style.opacity = '0.85';
    labelEl.style.whiteSpace = 'nowrap';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    if (wrap) {
      valueEl.style.whiteSpace = 'normal';
      valueEl.style.wordBreak = 'break-word';
      valueEl.style.lineHeight = '1.25';
    } else {
      valueEl.style.whiteSpace = 'nowrap';
      valueEl.style.overflow = 'hidden';
      valueEl.style.textOverflow = 'ellipsis';
    }
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var locationText = addChipRow('Location', { wrap: true });
  var timeText       = addChipRow('Time');
  var depthText         = addChipRow('Depth');
  var epicenterText        = addChipRow('Epicenter', { wrap: true });
  var categoryText            = addChipRow('Category');
  categoryText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the earthquake map — an absolutely-
  // positioned transparent overlay anchor, appended last so it paints on
  // top of everything else and actually receives the click. top/bottom
  // match the title band (-20px) and this card's own border-bottom
  // override (0, set above). Class name lets the shared hover-tooltip
  // script (indexNew.html) find it and read data-modal. No dedicated
  // chart exists for earthquakes — this links to the same worldwide map
  // modal the card's old toolbar used to.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'modalEarthquakeMap.html';
  cardLink.setAttribute('data-modal', 'Earthquakes');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Earthquake Map');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-modal-width', '900px');
  cardLink.setAttribute('data-modal-height', '620px');
  cardLink.setAttribute('data-url', 'modalEarthquakeMap.html');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  var W = 180, H = 175;

  function addCenter(svg, x, y, text, fill, size){
    svg.append('text').attr('x', x).attr('y', y).style('text-anchor', 'middle')
      .style('font-family', 'inherit').style('font-size', (size || 11) + 'px').style('fill', fill || overlayTextColor)
      .text(text);
  }

  function renderCard(v){
    var svgSel = d3.select(leftPane);
    var svg = svgSel.select('svg');
    svg.remove();
    svg = svgSel.append('svg').attr('viewBox', '0 0 ' + W + ' ' + H).attr('width', '100%').attr('height', '100%');

    var cx = W / 2, cy = 95;
    // "Magnitude" label used to sit at cy-48 (=37 with the old cy=85),
    // which put it inside the outer ring's own radius (top edge at
    // cy-53=32) — the label and the ring's top arc were overlapping.
    // Given a fixed position clear of the rings instead of one anchored
    // to cy, and cy itself nudged down so the ring + hero number sit
    // centered as their own group beneath it.
    addCenter(svg, cx, 24, DivumWXI18N.t('Magnitude'));
    addCenter(svg, cx, cy + 6, v.magnitude.toFixed(1), 'var(--bw-accent)', 22);

    var rings = [ { r: 20, sw: 2.5 }, { r: 31, sw: 2.0 }, { r: 42, sw: 1.0 }, { r: 53, sw: 0.5 } ];
    rings.forEach(function(ring){
      svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', ring.r)
        .attr('stroke', v.cat.color).attr('fill', 'none').attr('stroke-width', ring.sw);
    });

    var pulse = svg.append('circle').attr('cx', cx).attr('cy', cy).attr('r', 20)
      .attr('stroke', v.cat.color).attr('fill', 'none').attr('stroke-width', 2.5);
    (function loop(){
      pulse.transition().attr('stroke-width', 2.5).attr('r', 20)
        .transition().duration(3000).attr('stroke-width', 0).attr('r', 53)
        .ease(d3.easeSin).on('end', loop);
    })();

    // ---- Right pane: 5 readouts as label/value chip rows ----
    locationText.textContent = v.location;
    timeText.textContent = v.time;
    depthText.textContent = v.depth.toFixed(1) + ' km';
    epicenterText.textContent = v.distanceKm.toFixed(1) + ' km' + (v.station ? ' (from ' + v.station + ')' : '');
    categoryText.textContent = DivumWXI18N.t(v.cat.label);
  }

  var lastData = null;
  window.addEventListener('i18nready', function(){
    if (lastData) renderCard(lastData);
  });
  function refresh(){
    Promise.allSettled([
      fetch(EQ_JSON_URL + ((EQ_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var eqResult = results[0], archResult = results[1];
      if (eqResult.status === 'rejected') console.warn('cardEarthquake: eq.txt fetch failed --', eqResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardEarthquake: archive.json fetch failed --', archResult.reason.message);

      var eq = eqResult.status === 'fulfilled' ? eqResult.value : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var props = (eq.features && eq.features[0] && eq.features[0].properties) || {};
      var meta = arch.meta || {};
      function num(x, fallback){ return (typeof x === 'number' && !isNaN(x)) ? x : (fallback || 0); }

      var stationLat = num(meta.latitude, 51.94), stationLon = num(meta.longitude, -0.987);
      var quakeLat = num(props.lat, 0), quakeLon = num(props.lon, 0);
      var magnitude = num(props.mag, 0);

      lastData = {
        location: props.flynn_region || DivumWXI18N.t('Unknown location'),
        time: eventTimeLabel(props.time),
        depth: num(props.depth, 0),
        distanceKm: haversineKm(stationLat, stationLon, quakeLat, quakeLon),
        station: meta.station_location || '',
        magnitude: magnitude,
        cat: categoryFor(magnitude)
      };
      renderCard(lastData);
      setStatus(eqResult.status === 'fulfilled' && archResult.status === 'fulfilled');
    }).catch(function(e){
      console.warn('cardEarthquake: refresh failed --', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
})();
} catch (e) {
  console.error("cardsBundle: cardEarthquake.js failed:", e);
}

/* ===== cardWebcam.js ===== */
try {
/*
##############################################################################################
# cardWebcam.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardWebcam.js =====================
(function(){
  var LOOP_JSON_URL = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var ASTRO_JSON_URL = './jsondata/almanac.json';
  var POLL_MS = 30 * 1000;

  // Fallback defaults — used until the one-off config fetch below resolves,
  // and permanently if [DivumWXCards][[webcam_title]]/[[webcam_image]]
  // were never set (e.g. cardWebcam wasn't selected during install, or
  // an install predating this option).
  var MODAL_TITLE = 'Looking Towards North West of Steeple Claydon, UK';
  var WEBCAM_IMAGE_PATH = 'img/picam.jpg';

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }
  function cacheBustToken(){
    var p = stationParts(new Date());
    return p.year + p.month + p.day + p.hour + p.minute + p.second;
  }

  var mount = document.getElementById('webcamCard24');
  if (!mount) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (the standalone "Webcam"
  // modal link is removed below — the image itself is still a click-
  // through to the Timelapse modal, that's unrelated and stays) —
  // override the shared .card CSS's 18px border-bottom just for this
  // mount so the body can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px body (was 157px).
  // This card is just the camera image, no numeric readouts, so unlike
  // most other cards there's no 60:40 split to apply here — just the
  // shared "no toolbar / no border-bottom band" treatment.
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar (label swaps Webcam/Timelapse based on day/night) -------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = DivumWXI18N.t('Webcam');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // -- Body: the webcam image itself, wrapped in the timelapse link ------------
  // No padding — the image fills the entire content area edge-to-edge
  // (up to the title band above, and flush with the card's own left/
  // right/bottom edges), rather than sitting in a 2px/6px inset margin.
  var body = document.createElement('div');
  body.style.height = '175px';
  body.style.width = '100%';
  body.style.boxSizing = 'border-box';
  body.style.display = 'flex';
  body.style.alignItems = 'center';
  body.style.justifyContent = 'center';
  mount.appendChild(body);

  var imgLink = document.createElement('a');
  // href (not just data-url) is what chartModalOpen() actually reads —
  // this was previously left as the placeholder '#', which meant clicking
  // the webcam image did nothing at all. Now points at the real Timelapse
  // modal (data-url kept in sync purely for convention, as on every other
  // card link). class="card-whole-link" also wires this into index.html's
  // shared hover tooltip (see the "Timelapse" special-case there), which
  // this link never had before.
  imgLink.className = 'card-whole-link';
  imgLink.href = 'modalTimelapse.html';
  imgLink.setAttribute('data-modal', 'Timelapse');
  imgLink.setAttribute('data-title', DivumWXI18N.t('Timelapse') + ' - ' + MODAL_TITLE);
  imgLink.setAttribute('data-type', 'iframe');
  imgLink.setAttribute('data-modal-width', '760px');
  imgLink.setAttribute('data-modal-height', '460px');
  imgLink.setAttribute('data-url', 'modalTimelapse.html');
  imgLink.style.display = 'block';
  imgLink.style.width = '100%';
  imgLink.style.height = '100%';
  body.appendChild(imgLink);

  var img = document.createElement('img');
  img.alt = 'weathercam';
  img.style.display = 'block';
  img.style.width = '100%';
  img.style.height = '100%';
  img.style.objectFit = 'cover';
  img.style.borderRadius = '5px';
  imgLink.appendChild(img);

  // The status badge reflects the *timelapse pipeline's* health, not just
  // whether the raw source image loaded — those are two different things.
  // picam.jpg is written by a separate capture process on its own cadence;
  // day.mp4 only updates when TimelapseService actually captured and
  // appended a new frame. Checking the image's own mtime was checking the
  // wrong signal — it could easily be fresh while the timelapse itself had
  // stalled, or read stale for reasons that have nothing to do with
  // whether the timelapse is actually working.
  var DAY_TIMELAPSE_PATH = './webcam-timelapse/day.mp4';

  function checkStaleness(url){
    return fetch(url, { method: 'HEAD', cache: 'no-store' }).then(function(r){
      var lastModifiedHeader = r.headers.get('Last-Modified');
      if (!lastModifiedHeader) return true;
      var ageSeconds = (Date.now() - new Date(lastModifiedHeader).getTime()) / 1000;
      // An unparseable Last-Modified value makes ageSeconds NaN, and every
      // comparison against NaN (including "<= 300") is false in JS — that
      // silently forced the badge to permanently read stale/red regardless
      // of how recently the image actually changed. Fail open here the
      // same way the missing-header branch above already does.
      if (isNaN(ageSeconds)) return true;
      return ageSeconds <= 300;
    }).catch(function(){ return true; });
  }

  var lastIsDay = null;
  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
        var loopResult = results[0], astroResult = results[1];
        if (loopResult.status === 'rejected') { console.warn('cardWebcam: loop.json fetch failed --', loopResult.reason.message); setStatus(false); return; }
        if (astroResult.status === 'rejected') console.warn('cardWebcam: almanac.json fetch failed --', astroResult.reason.message);

        var o = loopResult.value.observations || {};
        var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};
        // Primary source: almanac.json's actual sun altitude -- "day" is
        // precisely "between sunrise and sunset" by definition (sun
        // above the horizon), astronomically exact regardless of what a
        // hardware-derived isDay flag happens to mean. Falls back to
        // loop.json's own observations.isDay only if almanac.json's
        // fetch failed.
        var sunAltRaw = alm['almanac.sun.alt'];
        var sunAlt = (typeof sunAltRaw === 'number' && !isNaN(sunAltRaw)) ? sunAltRaw : null;
        var isDay = (sunAlt !== null) ? (sunAlt > 0) : (o.isDay === 1);
        lastIsDay = isDay;

        if (!isDay){
          titleLabel.textContent = DivumWXI18N.t('Timelapse');
          img.src = 'img/nightTime.svg';
          setStatus(false);
          return;
        }

        titleLabel.textContent = DivumWXI18N.t('Webcam');
        var url = WEBCAM_IMAGE_PATH + '?v=' + cacheBustToken();

        // Both checks run concurrently and independently; the final status
        // is only "fresh" once both have resolved and both agree — an
        // image load failure shouldn't get silently overwritten by a
        // later-resolving "day.mp4 is fresh" result, or vice versa.
        var imageOk = null, timelapseFresh = null;
        function maybeSetStatus(){
          if (imageOk === null || timelapseFresh === null) return;
          setStatus(imageOk && timelapseFresh);
        }
        img.onload = function(){ imageOk = true; maybeSetStatus(); };
        img.onerror = function(){ imageOk = false; maybeSetStatus(); };
        img.src = url;

        checkStaleness(DAY_TIMELAPSE_PATH + '?v=' + cacheBustToken()).then(function(fresh){
          timelapseFresh = fresh;
          maybeSetStatus();
        });
      }).catch(function(e){
        console.warn('cardWebcam: refresh failed --', e.message);
        setStatus(false);
      });
  }
  function fetchWebcamConfig(){
    return fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(data){
        var m = data && data.meta;
        if (m && m.webcam_title) MODAL_TITLE = m.webcam_title;
        if (m && m.webcam_image) WEBCAM_IMAGE_PATH = m.webcam_image;
        // The image-link modal was already built with the fallback title
        // above (before this fetch could resolve) — update it in place.
        imgLink.setAttribute('data-title', DivumWXI18N.t('Timelapse') + ' - ' + MODAL_TITLE);
      })
      .catch(function(e){
        console.warn('cardWebcam: config fetch failed --', e.message);
      });
  }

  fetchWebcamConfig().then(function(){
    refresh();
    setInterval(refresh, POLL_MS);
  });
  window.addEventListener('i18nready', function(){
    // refresh() already re-derives titleLabel.textContent from lastIsDay
    // every poll cycle -- this just avoids waiting up to POLL_MS for the
    // very first translated paint.
    if (lastIsDay !== null) refresh();
    // The Timelapse tooltip is set at two points that can each run
    // before OR after strings.json loads (card boot, and whenever
    // fetchWebcamConfig's own separate fetch resolves) -- neither is
    // guaranteed to run after i18n is ready, so re-apply here too,
    // using whatever MODAL_TITLE currently holds (already correct by
    // now if fetchWebcamConfig finished first, or still the fallback
    // otherwise -- that fetch's own .then() re-applies this same line
    // again once it does finish, so between the two this always ends
    // up correct regardless of which finishes first).
    imgLink.setAttribute('data-title', DivumWXI18N.t('Timelapse') + ' - ' + MODAL_TITLE);
  });
})();
} catch (e) {
  console.error("cardsBundle: cardWebcam.js failed:", e);
}

/* ===== cardStationImage.js ===== */
try {
/*
##############################################################################################
# cardStationImage.js version 0.2.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardStationImage.js =====================
// Same structure and mechanics as cardWebcam.js — full-bleed image, no
// toolbar, status dot in the title bar — just a different, static image
// (a photo of the station itself rather than a live outdoor view) and a
// fixed "Station Image" title bar label instead of the Webcam/Timelapse
// day-night swap. STATION_IMAGE_PATH/STATION_IMAGE_TITLE below are
// fallback defaults, used until the one-off config fetch resolves, and
// permanently if [DivumWXCards] station_image_path/station_image_title
// were never set (e.g. cardStationImage wasn't selected during install,
// or an install predating this option) -- same pattern as
// cardWebcam.js's MODAL_TITLE/WEBCAM_IMAGE_PATH fallback+fetch.
(function(){
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var STATION_IMAGE_TITLE = DivumWXI18N.t('Station Image');
  var STATION_IMAGE_PATH = 'img/stationImage.jpg';
  var POLL_MS = 30 * 1000;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationParts(date){
    var parts = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(date).forEach(function(p){ parts[p.type] = p.value; });
    return parts;
  }
  function stationNow(){
    var p = stationParts(new Date());
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }

  var mount = document.getElementById('stationImageCard25');
  if (!mount) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card — override the shared
  // .card CSS's 18px border-bottom just for this mount so the body can
  // reclaim that space. Card height stays 195px: 20px title band
  // (border-top, unchanged) + 175px body (was 157px). This card is just
  // an image, no numeric readouts, so unlike most other cards there's no
  // 60:40 split to apply here — just the shared "no toolbar / no
  // border-bottom band" treatment, same as cardWebcam.js.
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar ----------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  titleLabel.textContent = 'Station Image';
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // -- Body: the station image, full-bleed --------------------------------
  // No padding — the image fills the entire content area edge-to-edge
  // (up to the title band above, and flush with the card's own left/
  // right/bottom edges), same as cardWebcam.js.
  var body = document.createElement('div');
  body.style.height = '175px';
  body.style.width = '100%';
  body.style.boxSizing = 'border-box';
  body.style.display = 'flex';
  body.style.alignItems = 'center';
  body.style.justifyContent = 'center';
  mount.appendChild(body);

  var img = document.createElement('img');
  img.alt = STATION_IMAGE_TITLE;
  img.style.display = 'block';
  img.style.width = '100%';
  img.style.height = '100%';
  img.style.objectFit = 'cover';
  img.style.borderRadius = '5px';
  body.appendChild(img);

  var titleOverriddenByConfig = false;
  window.addEventListener('i18nready', function(){
    // Only re-apply the translated fallback if the station owner hasn't
    // set their own custom title -- that's arbitrary user-typed text
    // (e.g. "Backyard Cam"), never something DivumWX should translate.
    if (!titleOverriddenByConfig) {
      STATION_IMAGE_TITLE = DivumWXI18N.t('Station Image');
      img.alt = STATION_IMAGE_TITLE;
    }
  });

  function refresh(){
    img.onload = function(){ setStatus(true); };
    img.onerror = function(){ setStatus(false); };
    img.src = STATION_IMAGE_PATH;
  }
  function fetchStationImageConfig(){
    return fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(data){
        var m = data && data.meta;
        if (m && m.station_image_title) { STATION_IMAGE_TITLE = m.station_image_title; img.alt = STATION_IMAGE_TITLE; titleOverriddenByConfig = true; }
        if (m && m.station_image_path) STATION_IMAGE_PATH = m.station_image_path;
      })
      .catch(function(e){
        console.warn('cardStationImage: config fetch failed --', e.message);
      });
  }

  fetchStationImageConfig().then(function(){
    refresh();
    setInterval(refresh, POLL_MS);
  });
})();
} catch (e) {
  console.error("cardsBundle: cardStationImage.js failed:", e);
}
/* ===== cardSolarEnergy.js ===== */
try {
/*
##############################################################################################
# cardSolarEnergy.js version 0.0.2
#  Ported from the legacy dvmSolarEnergyModule.php (PHP/D3v4 standalone module) to this
#  card architecture -- same underlying data (solar/battery/grid/load), same weather-
#  conditioned PV icon selection, redone as a 60:40 hero/readouts card matching every
#  other card added since. Data source is jsondata/solar_data.json -- an MQTT-topic-keyed
#  capture from a Solar Assistant integration (topics like
#  "solar_assistant/inverter_1/pv_power/state", each {value, timestamp, raw}), NOT
#  loop.json/archive.json as an earlier version of this file assumed -- see topicValue()
#  below for the lookup helper, and refresh() for the exact topic names used, confirmed
#  against a real capture. inverter_1-scoped topics are used for instantaneous power
#  (pv_power, grid_power, load_power) since that's the only place they're exposed;
#  total-scoped topics are used for battery power/SOC and all daily cumulative energy
#  figures, since inverter_1 doesn't expose those at all -- only "total" aggregates them.
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardSolarEnergy.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var SOLAR_JSON_URL   = './jsondata/solar_data.json';
  var ASTRO_JSON_URL   = './jsondata/almanac.json';
  var CLOUD_JSON_URL   = './jsondata/cloud_coverage.json';
  var POLL_MS = 10 * 1000; // power/SOC/grid readings are live values, not slow-changing -- same interval class as the other live-gauge cards (wind, barometer, etc.), not the 30s used by webcam/earthquake's much slower-changing sources.

  // Legacy PHP hardcoded 4050 (W) as the array's rated capacity to turn
  // instantaneous power into a percentage. No per-installation array-size
  // config exists anywhere in the current install.py/[DivumWXCards]
  // pipeline, so this stays a hardcoded constant here too -- ported
  // as-is, not improved, since getting this wrong per-station would be
  // worse than an admitted limitation. Worth making configurable
  // (a new [DivumWXCards] array_rated_watts key + install.py prompt) if
  // this card gets adopted for real.
  var ARRAY_RATED_WATTS = 4050;

  function pad2(n){ return n < 10 ? '0' + n : String(n); }
  function stationNow(){
    var p = {};
    new Intl.DateTimeFormat('en-GB', {
      timeZone: StationTime.getTZ(), hourCycle: 'h23',
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    }).formatToParts(new Date()).forEach(function(x){ p[x.type] = x.value; });
    return new Date(Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour, +p.minute, +p.second));
  }

  // Ported directly from the PHP module's cloud-icon if/else-if chain --
  // same five cloud-cover bands, same night override to a single dark
  // icon regardless of cover. cloudCover source changed: the PHP read a
  // dedicated jsondata/awc.txt (Aviation Weather Center) fetch that's
  // specific to the old PHP subsystem; this uses archive.json's own
  // sky.cloud_cover instead (already present, already used the same way
  // by cardCurrent.js's icon picker) so this card doesn't need a data
  // source nothing else in the current architecture depends on.
  function pickPvIcon(cloudCoverPct, isDay){
    if (!isDay) return 'img/pvNight.svg';
    if (cloudCoverPct > 0 && cloudCoverPct < 7)   return 'img/pvClearDay.svg';
    if (cloudCoverPct < 32)  return 'img/pvMostlyClearDay.svg';
    if (cloudCoverPct < 70)  return 'img/pvPartlyCloudyDay.svg';
    if (cloudCoverPct < 95)  return 'img/pvMostlyCloudyDay.svg';
    return 'img/pvOvercastDay.svg';
  }

  var mount = document.getElementById('solarEnergyCard26');
  if (!mount) return;
  mount.innerHTML = '';
  mount.style.position = 'relative';
  mount.style.display = 'flex';
  mount.style.flexDirection = 'column';
  // No bottom-border band or toolbar on this card (links removed below) —
  // override the shared .card CSS's 18px border-bottom just for this mount
  // so the content pane can reclaim that space. Card height stays 195px:
  // 20px title band (border-top, unchanged) + 175px content. This exact
  // line is what VPD/Earthquake both have and this card was missing --
  // every "content overflows past the visible border" symptom traced
  // back to using their 175px content height without this override, so
  // the true available space was only 157px (195 - 20 - 18), not 175px.
  mount.style.borderBottom = '0';

  var overlayTextColor = 'var(--bs-body-color)';

  // -- Title bar --------------------------------------------------------------
  var titleBar = document.createElement('div');
  titleBar.style.position = 'absolute';
  titleBar.style.top = '-20px';
  titleBar.style.left = '0';
  titleBar.style.right = '0';
  titleBar.style.height = '20px';
  titleBar.style.boxSizing = 'border-box';
  titleBar.style.display = 'flex';
  titleBar.style.alignItems = 'center';
  titleBar.style.justifyContent = 'space-between';
  titleBar.style.gap = '8px';
  titleBar.style.padding = '0 14px';
  titleBar.style.fontSize = '9px';
  titleBar.style.color = overlayTextColor;
  titleBar.style.background = 'transparent';

  var titleLabel = document.createElement('span');
  DivumWXI18N.applyLabel(titleLabel, 'Solar Energy');
  titleLabel.style.fontWeight = '600';
  titleLabel.style.whiteSpace = 'nowrap';
  titleLabel.style.overflow = 'hidden';
  titleLabel.style.textOverflow = 'ellipsis';

  var statusWrap = document.createElement('span');
  statusWrap.style.display = 'flex';
  statusWrap.style.alignItems = 'center';
  statusWrap.style.gap = '4px';
  statusWrap.style.flexShrink = '0';
  statusWrap.style.opacity = '0.85';

  var statusDot = document.createElement('span');
  statusDot.style.width = '6px';
  statusDot.style.height = '6px';
  statusDot.style.borderRadius = '50%';
  statusDot.style.background = '#999';
  statusDot.style.flexShrink = '0';

  var statusTime = document.createElement('span');

  statusWrap.appendChild(statusDot);
  statusWrap.appendChild(statusTime);
  titleBar.appendChild(titleLabel);
  titleBar.appendChild(statusWrap);
  mount.appendChild(titleBar);

  function setStatus(ok){
    statusDot.style.background = ok ? '#2ecc71' : '#e74c3c';
    var t = stationNow();
    statusTime.textContent = pad2(t.getUTCHours()) + ':' + pad2(t.getUTCMinutes()) + ':' + pad2(t.getUTCSeconds());
  }

  // ---- 60:40 content split (left: PV icon + hero power value, right: readouts) ----
  var contentWrap = document.createElement('div');
  contentWrap.style.height = '175px';
  contentWrap.style.width = '100%';
  contentWrap.style.boxSizing = 'border-box';
  contentWrap.style.overflow = 'hidden';
  contentWrap.style.display = 'flex';
  contentWrap.style.alignItems = 'stretch';
  mount.appendChild(contentWrap);

  var divider = document.createElement('div');
  divider.style.position = 'absolute';
  divider.style.left = '60%';
  divider.style.top = '6px';
  divider.style.bottom = '6px';
  divider.style.width = '1px';
  divider.style.background = 'var(--bs-border-color)';
  divider.style.pointerEvents = 'none';
  mount.appendChild(divider);

  var leftPane = document.createElement('div');
  leftPane.style.flex = '0 0 60%';
  leftPane.style.width = '60%';
  leftPane.style.height = '175px';
  leftPane.style.boxSizing = 'border-box';
  leftPane.style.overflow = 'hidden';
  leftPane.style.display = 'flex';
  leftPane.style.flexDirection = 'column';
  leftPane.style.alignItems = 'center';
  leftPane.style.justifyContent = 'center';
  contentWrap.appendChild(leftPane);

  var pvIcon = document.createElement('img');
  pvIcon.style.width = '96px';
  pvIcon.style.height = '96px';
  pvIcon.style.objectFit = 'contain';
  leftPane.appendChild(pvIcon);

  var pvHeroLabel = document.createElement('div');
  DivumWXI18N.applyLabel(pvHeroLabel, 'PV Array Generating');
  pvHeroLabel.style.fontSize = '9px';
  pvHeroLabel.style.fontVariantCaps = 'small-caps';
  pvHeroLabel.style.letterSpacing = '.06em';
  pvHeroLabel.style.color = overlayTextColor;
  pvHeroLabel.style.opacity = '0.85';
  pvHeroLabel.style.marginTop = '4px';
  leftPane.appendChild(pvHeroLabel);

  var pvHeroValue = document.createElement('div');
  pvHeroValue.style.fontSize = '20px';
  pvHeroValue.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
  pvHeroValue.style.color = 'var(--bw-accent)';
  leftPane.appendChild(pvHeroValue);

  var rightPane = document.createElement('div');
  rightPane.style.flex = '0 0 40%';
  rightPane.style.width = '40%';
  rightPane.style.boxSizing = 'border-box';
  rightPane.style.overflow = 'hidden';
  rightPane.style.display = 'flex';
  rightPane.style.flexDirection = 'column';
  rightPane.style.justifyContent = 'center';
  rightPane.style.padding = '0 10px 0 14px';
  contentWrap.appendChild(rightPane);

  // wrap:true (matching cardEarthquake.js's identical addChipRow) lets a
  // long value ("Importing from Grid", "Discharging — (SOC 87%)") wrap
  // onto a second line at its natural width instead of being clipped
  // with an ellipsis. With 6 rows total (2 of them wrapping to 2 lines)
  // packed into the fixed 175px content budget every card gets, spacing
  // is tightened here (row padding, line-height, fixed-row height) vs.
  // Earthquake's more spacious 5-row version -- text size/wrapping
  // itself is unchanged, only the vertical space around it.
  function addChipRow(label, opts){
    var wrap = opts && opts.wrap;
    var row = document.createElement('div');
    row.style.display = 'flex';
    row.style.flexDirection = 'column';
    row.style.justifyContent = 'center';
    row.style.boxSizing = 'border-box';
    row.style.borderBottom = '1px solid var(--bs-border-color)';
    if (wrap) {
      row.style.minHeight = '20px';
      row.style.padding = '3px 0';
    } else {
      row.style.height = '20px';
      row.style.overflow = 'hidden';
    }

    var labelEl = document.createElement('span');
    DivumWXI18N.applyLabel(labelEl, label);
    labelEl.style.fontSize = '7px';
    labelEl.style.fontVariantCaps = 'small-caps';
    labelEl.style.letterSpacing = '.06em';
    labelEl.style.color = overlayTextColor;
    labelEl.style.opacity = '0.85';
    labelEl.style.whiteSpace = 'nowrap';
    row.appendChild(labelEl);

    var valueEl = document.createElement('span');
    valueEl.style.fontSize = '9.5px';
    valueEl.style.fontFamily = '"IBM Plex Mono", ui-monospace, monospace';
    valueEl.style.color = 'var(--bw-accent)';
    if (wrap) {
      valueEl.style.whiteSpace = 'normal';
      valueEl.style.wordBreak = 'break-word';
      valueEl.style.lineHeight = '1.25';
    } else {
      valueEl.style.whiteSpace = 'nowrap';
      valueEl.style.overflow = 'hidden';
      valueEl.style.textOverflow = 'ellipsis';
    }
    row.appendChild(valueEl);

    rightPane.appendChild(row);
    return valueEl;
  }

  var gridText      = addChipRow('Grid', { wrap: true });
  var batteryText    = addChipRow('Battery', { wrap: true });
  var loadText          = addChipRow('House Load');
  var dailyEnergyText       = addChipRow('Solar Daily Energy');
  var dailyExportText          = addChipRow('Grid Daily Export');
  var efficiencyText              = addChipRow('PV Efficiency');
  efficiencyText.parentElement.style.borderBottom = 'none'; // last row — no divider under it

  // Whole card is a click-through to the solar/energy chart -- same
  // pattern every other card's whole-card link uses. Assumes a 'solar'
  // chart type exists in charts-d3.html (the legacy module linked to a
  // dedicated dvmhighcharts/dvmSolarEnergyChart.php that has no
  // equivalent here yet) -- confirm/add that chart type before relying
  // on this link actually going anywhere useful.
  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=solar&embed=1';
  cardLink.setAttribute('data-modal', 'Solar Energy');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Solar Energy Chart & Records');
  cardLink.setAttribute('data-type', 'iframe');
  cardLink.setAttribute('data-url', 'charts-d3.html?type=solar&embed=1');
  cardLink.style.position = 'absolute';
  cardLink.style.top = '-20px';
  cardLink.style.left = '0';
  cardLink.style.right = '0';
  cardLink.style.bottom = '0';
  cardLink.style.display = 'block';
  mount.appendChild(cardLink);

  function fmtPower(w){
    return (typeof w === 'number' && !isNaN(w)) ? Math.round(Math.abs(w)) + ' W' : '\u2014';
  }
  function fmtEnergy(kwh){
    return (typeof kwh === 'number' && !isNaN(kwh)) ? kwh.toFixed(2) + ' kWh' : '\u2014';
  }

  function renderCard(v){
    pvIcon.src = v.icon;
    pvHeroValue.textContent = fmtPower(v.pvPower);

    gridText.textContent = v.gridState + ' ' + fmtPower(v.gridPower);
    batteryText.textContent = v.batteryState + ' ' + fmtPower(v.batteryPower) + ', ' +
      (typeof v.batterySOC === 'number' ? v.batterySOC.toFixed(0) : '\u2014') + '%';
    loadText.textContent = fmtPower(v.loadPower);
    dailyEnergyText.textContent = fmtEnergy(v.solarDailyEnergy);
    dailyExportText.textContent = fmtEnergy(v.gridDailyExport);
    efficiencyText.textContent = (typeof v.pvEfficiency === 'number' && !isNaN(v.pvEfficiency))
      ? v.pvEfficiency.toFixed(1) + '%' : '\u2014';
  }

  function num(x){ return (typeof x === 'number' && !isNaN(x)) ? x : null; }

  // solar_data.json's actual shape (confirmed against a real capture):
  //   { last_updated, message_count, packet_count, connected, unique_topics,
  //     data: { "<mqtt/topic/path>/state": { value, timestamp, raw }, ... } }
  // Every reading lives at solarData.data[topic].value -- this just does that
  // lookup safely, returning null (not throwing) if the topic is absent (e.g.
  // a station with a different inverter model/topic naming) or the payload
  // isn't shaped as expected.
  function topicValue(solarData, topic){
    var entry = solarData && solarData.data && solarData.data[topic];
    return entry ? entry.value : null;
  }

  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(SOLAR_JSON_URL + ((SOLAR_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(CLOUD_JSON_URL + ((CLOUD_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1], solarResult = results[2], astroResult = results[3], cloudResult = results[4];
      if (loopResult.status === 'rejected') console.warn('cardSolarEnergy: loop.json fetch failed --', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardSolarEnergy: archive.json fetch failed --', archResult.reason.message);
      if (solarResult.status === 'rejected') console.warn('cardSolarEnergy: solar_data.json fetch failed --', solarResult.reason.message);
      if (astroResult.status === 'rejected') console.warn('cardSolarEnergy: almanac.json fetch failed --', astroResult.reason.message);
      // cloud_coverage.json is optional -- logs at info (not warn) since
      // absent-and-rejected is a normal state on installs without it,
      // but still logged (not silent) so "is it actually being used?"
      // is answerable from the console: covers both fetch failure and
      // fetch-succeeded-but-malformed (cloudPercent missing/not a
      // number), two different failure modes that otherwise look
      // identical from the outside.
      if(cloudResult.status === 'rejected'){
        console.info('cardSolarEnergy: cloud_coverage.json fetch failed (falling back to loop.json/archive.json) --', cloudResult.reason.message);
      } else if(typeof cloudResult.value.cloudPercent !== 'number' || isNaN(cloudResult.value.cloudPercent)){
        console.info('cardSolarEnergy: cloud_coverage.json fetched but cloudPercent is missing/invalid (falling back) --', JSON.stringify(cloudResult.value));
      }

      // BUGFIX: this used to read loopResult.value directly (the whole
      // loop.json object) rather than its .observations sub-object, the
      // way every other card in this file does. isDay (and cloudcover)
      // only actually exist under .observations, so o.isDay was always
      // undefined -- "undefined === 1" is always false -- meaning this
      // card thought it was permanently night, regardless of actual
      // time. That alone fully explained the reported symptom (night
      // icon shown well past sunrise) before the almanac-based fix
      // below was even added.
      var o = loopResult.status === 'fulfilled' ? (loopResult.value.observations || {}) : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var cloudCoverage = cloudResult.status === 'fulfilled' ? cloudResult.value : null;
      var sky = arch.sky || {};
      var solarData = solarResult.status === 'fulfilled' ? solarResult.value : {};
      var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};

      // inverter_1-scoped: instantaneous power. total-scoped: battery
      // power/SOC and all daily cumulative energy figures -- inverter_1
      // doesn't expose those at all, only "total" aggregates them.
      var pvPower       = num(topicValue(solarData, 'solar_assistant/inverter_1/pv_power/state'));
      var gridPowerRaw   = num(topicValue(solarData, 'solar_assistant/inverter_1/grid_power/state'));
      var loadPower       = num(topicValue(solarData, 'solar_assistant/inverter_1/load_power/state'));
      var batteryPowerRaw   = num(topicValue(solarData, 'solar_assistant/total/battery_power/state'));
      var batterySOC             = num(topicValue(solarData, 'solar_assistant/total/battery_state_of_charge/state'));
      var pvEnergyToday               = num(topicValue(solarData, 'solar_assistant/total/pv_energy/state'));
      var gridEnergyOutToday               = num(topicValue(solarData, 'solar_assistant/total/grid_energy_out/state'));

      // Primary source: almanac.json's actual sun altitude (matches
      // cardSolarDial.js's own isDay logic) -- "day" is precisely
      // "between sunrise and sunset" by definition (sun above the
      // horizon), astronomically exact regardless of what any hardware-
      // derived isDay flag happens to mean. Falls back to loop.json's
      // observations.isDay only if almanac.json's own fetch failed.
      var sunAlt = num(alm['almanac.sun.alt']);
      var isDay = (sunAlt !== null) ? (sunAlt > 0) : (o.isDay === 1);
      // Same fix as cardCurrent.js's identical bug -- see its comment
      // for the full explanation. loop.json's o.cloudcover is the
      // live/correct value; archive.json's sky.cloud_cover has been
      // observed stuck at 0 in every sample seen this whole
      // conversation, and the old priority order never actually fell
      // back away from it since 0 is still a valid number.
      //
      // Between sunrise and sunset, cloud_coverage.json (a sky-camera-
      // derived reading, when available) takes priority over
      // loop.json/archive.json -- but only during the day. "Available"
      // means the fetch succeeded AND cloudPercent is actually a valid
      // number. Falls back to loop.json/archive.json at night, or any
      // time cloud_coverage.json's fetch failed or its data was invalid.
      var cloudPercentFromCamera = (cloudCoverage && typeof cloudCoverage.cloudPercent === 'number' && !isNaN(cloudCoverage.cloudPercent))
        ? cloudCoverage.cloudPercent : null;
      var cloudCoverPct = (isDay && cloudPercentFromCamera !== null)
        ? cloudPercentFromCamera
        : ((typeof o.cloudcover === 'number') ? o.cloudcover : (sky.cloud_cover || 0));
      console.info('cardSolarEnergy: cloud cover source —', {
        isDay: isDay, sunAlt: sunAlt, cameraAvailable: cloudPercentFromCamera !== null,
        cameraValue: cloudPercentFromCamera, usedValue: cloudCoverPct,
        source: (isDay && cloudPercentFromCamera !== null) ? 'cloud_coverage.json' : 'loop.json/archive.json'
      });

      // Ported directly from the PHP module: <0 means charging/exporting,
      // >=0 means discharging/importing. Display value is the magnitude
      // (abs), same as the PHP's abs() calls -- the state label already
      // carries the direction. Sign convention for grid_power specifically
      // is assumed to match the old module's (not separately confirmed
      // against this integration's own docs) -- worth double-checking
      // against a real export event if the label ever looks backwards.
      var batteryState = (batteryPowerRaw !== null && batteryPowerRaw < 0) ? DivumWXI18N.t('Charging') : DivumWXI18N.t('Discharging');
      // "to Grid"/"from Grid" dropped -- this row's own label already says
      // GRID, so the full phrase was redundant and was the direct cause of
      // this row wrapping to 2 lines, which left too little vertical room
      // for the 6 rows to fit within the card's fixed height without the
      // last row (PV Efficiency) crowding the bottom border.
      var gridState = (gridPowerRaw !== null && gridPowerRaw < 0) ? DivumWXI18N.t('Exporting') : DivumWXI18N.t('Importing');

      var pvEfficiency = (pvPower !== null) ? (pvPower / ARRAY_RATED_WATTS * 100) : null;

      renderCard({
        icon: pickPvIcon(cloudCoverPct, isDay),
        pvPower: pvPower,
        pvEfficiency: pvEfficiency,
        batteryState: batteryState,
        batteryPower: batteryPowerRaw,
        batterySOC: batterySOC,
        gridState: gridState,
        gridPower: gridPowerRaw,
        loadPower: loadPower,
        solarDailyEnergy: pvEnergyToday,
        gridDailyExport: gridEnergyOutToday
      });
      // "connected" (solar_data.json's own top-level flag) reflects
      // whether the Solar Assistant MQTT feed itself is live -- a more
      // meaningful health signal than just "did the file fetch", since
      // the file can still exist and fetch fine with stale contents if
      // the underlying MQTT connection has dropped.
      var solarConnected = solarResult.status === 'fulfilled' && solarData.connected === true;
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled' && solarConnected);
    }).catch(function(e){
      console.warn('cardSolarEnergy: refresh failed --', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
  // No lastData cache in this card (unlike most others) -- refresh() both
  // fetches and renders in one step, so re-running it is the correct way
  // to pick up translations once strings.json has loaded.
  window.addEventListener('i18nready', refresh);
})();
} catch (e) {
  console.error("cardsBundle: cardSolarEnergy.js failed:", e);
}
