/*
##############################################################################################
# sharedData.js version 1.0.0
#  One shared copy of loop.json, archive.json and almanac.json per page.
#  Copyright (C) 2026 Ian Millard
#  GPLv3
##############################################################################################
#
#  Dozens of cards and page scripts read the same three files, each on its own
#  timer: one dashboard tab made ~285 requests for them in under three minutes
#  (reported by Johannes), though archive.json only changes once per archive
#  interval. Loaded first on each page, this wraps window.fetch for plain GET
#  reads of those three files only -- every other request is untouched:
#
#   - reads within MAX_AGE of the last download get that copy, without a request;
#   - reads made while a download is in flight share it (one request);
#   - each caller gets its own Response, so its own parsed object -- one card
#     changing the data can't affect another;
#   - a download that fails, or isn't valid JSON (e.g. half uploaded over FTP),
#     falls back to the last good copy while it's younger than MAX_STALE;
#     with no good copy yet, the caller sees the real result as before.
#
#  MAX_AGE is kept below the fastest reader: the live gauges read loop.json
#  every 3 seconds, so its copies last 2.
*/
(function () {
  'use strict';
  if (!window.fetch || window.DivumWXData) return;

  var MAX_AGE   = { 'loop.json': 2000,   'archive.json': 60000,  'almanac.json': 30000 };
  var MAX_STALE = { 'loop.json': 120000, 'archive.json': 900000, 'almanac.json': 900000 };
  var KEEP_HEADERS = ['content-type', 'date', 'last-modified'];

  var realFetch = window.fetch.bind(window);
  var entries = {};      // file -> { text, headers, at }  (last good copy)
  var inflight = {};     // file -> Promise of { ok, text, headers, status, statusText, error }
  var stats = { network: 0, shared: 0, fallback: 0 };

  function sharedFile(input, init) {
    var method = (init && init.method) || (input && typeof input === 'object' && input.method) || 'GET';
    if (String(method).toUpperCase() !== 'GET') return null;
    var url;
    try { url = new URL(typeof input === 'string' ? input : input.url, window.location.href); } catch (e) { return null; }
    if (url.origin !== window.location.origin) return null;
    var m = /\/jsondata\/(loop|archive|almanac)\.json$/.exec(url.pathname);
    return m ? { file: m[1] + '.json', url: url.origin + url.pathname } : null;
  }

  function keepHeaders(h) {
    var out = {};
    KEEP_HEADERS.forEach(function (k) { var v = h.get(k); if (v) out[k] = v; });
    return out;
  }

  function respond(text, headers, status, statusText) {
    return new Response(text, { status: status || 200, statusText: statusText || 'OK', headers: headers || {} });
  }

  function download(f) {
    stats.network++;
    return realFetch(f.url + '?_=' + Date.now(), { cache: 'no-store' })
      .then(function (r) {
        return r.text().then(function (text) {
          return { ok: r.ok, text: text, headers: keepHeaders(r.headers), status: r.status, statusText: r.statusText };
        });
      }, function (err) { return { ok: false, error: err }; })
      .then(function (res) {
        if (res.ok) {
          try {
            JSON.parse(res.text);
            entries[f.file] = { text: res.text, headers: res.headers, at: Date.now() };
            res.good = true;
          } catch (e) { res.good = false; }
        }
        return res;
      });
  }

  function sharedFetch(f) {
    var now = Date.now();
    var e = entries[f.file];
    if (e && now - e.at < MAX_AGE[f.file]) {
      stats.shared++;
      return Promise.resolve(respond(e.text, e.headers));
    }
    var p = inflight[f.file];
    if (p) stats.shared++;
    else {
      p = inflight[f.file] = download(f);
      p.then(function () { delete inflight[f.file]; }, function () { delete inflight[f.file]; });
    }
    return p.then(function (res) {
      if (res.good) return respond(res.text, res.headers);
      var last = entries[f.file];
      if (last && Date.now() - last.at < MAX_STALE[f.file]) {
        stats.fallback++;
        return respond(last.text, last.headers);
      }
      if (res.error) throw res.error;
      // No good copy: pass the real answer on (an HTTP error, or text that
      // isn't valid JSON, which some callers can still make use of).
      return respond(res.text, res.headers, res.status, res.statusText);
    });
  }

  window.fetch = function (input, init) {
    var f = sharedFile(input, init);
    return f ? sharedFetch(f) : realFetch(input, init);
  };

  window.DivumWXData = {
    stats: function () { return { network: stats.network, shared: stats.shared, fallback: stats.fallback }; },
    MAX_AGE: MAX_AGE,
    MAX_STALE: MAX_STALE
  };
})();
