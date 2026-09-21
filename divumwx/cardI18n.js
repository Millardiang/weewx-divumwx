try {
/*
##############################################################################################
# cardI18n.js version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardI18n.js =====================

(function(){
  var STRINGS_JSON_URL = './jsondata/strings.json';
  var LANG_STORAGE_KEY = 'dashboardLanguage';
  var payload = null;
  var activeLang = null;
  var loaded = false;
  var registeredLabels = [];
  var registeredAttrs = [];

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

  // HatScripts/circle-flags (MIT licensed), the values below are that

  var LANGUAGE_FLAG_COUNTRY = {
    ar: 'sa',
    br: 'fr-bre',
    ca: 'es-ct',
    cn: 'cn',
    cy: 'gb-wls',
    cz: 'cz',
    da: 'dk',
    de: 'de',
    en: 'gb',
    en_US: 'us',
    es: 'es',
    eu: 'es-pv',
    fr: 'fr',
    gr: 'gr',
    hi: 'in',
    it: 'it',
    nl: 'nl',
    no: 'no',
    pl: 'pl',
    pt: 'pt',
    sv: 'se',
    fi: 'fi',
    hu: 'hu',
    is: 'is',
    ta: 'in',
    th: 'th',
    tr: 'tr',
    uk: 'ua',
    ur: 'pk'
  };

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

      activeLang = (saved && payload[saved]) ? saved : (payload['_default'] || 'en');
      loaded = true;

      reapplyAll();
      resolveReady();
    })
    .catch(function(e){
      console.warn('cardI18n: strings.json fetch failed \u2014 staying in English:', e.message);
      payload = {};
      activeLang = 'en';
      loaded = true;
      resolveReady();
    })
    .then(function(){

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
