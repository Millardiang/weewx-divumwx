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
  console.error("cardI18n.js failed:", e);
}
