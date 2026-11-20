/*
##############################################################################################
# visitorReport.js version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== visitorReport.js =====================
// The site visitor report, from jsondata/visitors.json (written by
// user.divumwx_visitors from Cloudflare Web Analytics). Loaded by
// siteHeader.js only when the footer's "Visits" line is clicked:
// DivumWXVisitorReport.open(doc, base).

(function(){
  'use strict';
  if (window.DivumWXVisitorReport) return;

  function T(s){ try { return (window.DivumWXI18N && DivumWXI18N.t) ? DivumWXI18N.t(s) : s; } catch (e) { return s; } }
  function esc(s){ return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){ return '&#' + c.charCodeAt(0) + ';'; }); }
  function lang(){ try { var l = window.DivumWXI18N && DivumWXI18N.getLanguage && DivumWXI18N.getLanguage(); return l ? [String(l).replace('_', '-')] : undefined; } catch (e) { return undefined; } }
  function num(n){ try { return Number(n || 0).toLocaleString(lang()); } catch (e) { return String(n || 0); } }
  function day(iso, opts){ try { return new Date(iso + 'T12:00:00Z').toLocaleDateString(lang(), opts); } catch (e) { return iso; } }

  var CSS = '' +
    '.vr-back{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:5000;display:flex;align-items:flex-start;justify-content:center;overflow:auto;padding:24px 12px}' +
    '.vr{background:var(--card-bg,#fff);color:var(--bs-body-color,#1D2C4E);border:1px solid var(--bs-border-color,#C9CFD8);border-radius:8px;max-width:860px;width:100%;padding:18px 20px 16px;box-sizing:border-box;font-size:13px;position:relative}' +
    '.vr h2{font-size:17px;margin:0 0 2px;font-weight:600}' +
    '.vr h3{font-size:13px;margin:0 0 6px;font-weight:600}' +
    '.vr-sub,.vr-note{color:var(--bs-secondary-color,#5C6672);font-size:12px}' +
    '.vr-x{position:absolute;top:10px;right:12px;background:none;border:0;color:inherit;font-size:22px;line-height:1;cursor:pointer;padding:4px 8px}' +
    '.vr-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:14px 0}' +
    '.vr-tile{border:1px solid var(--bs-border-color,#C9CFD8);border-radius:6px;padding:8px 10px}' +
    '.vr-tile b{display:block;font-size:22px;font-weight:600;font-variant-numeric:tabular-nums}' +
    '.vr-tile span{color:var(--bs-secondary-color,#5C6672);font-size:12px}' +
    '.vr-chart{position:relative;margin:0 0 14px}' +
    '.vr-chart svg{display:block;width:100%;height:150px;overflow:visible}' +
    '.vr-chart rect.hit{fill:transparent}' +
    '.vr-chart g:hover path{opacity:.75}' +
    '.vr-axis{fill:var(--bs-secondary-color,#5C6672);font-size:10px}' +
    '.vr-grid{stroke:var(--bs-border-color,#C9CFD8);stroke-width:1}' +
    '.vr-tip{position:absolute;pointer-events:none;background:var(--card-bg,#fff);border:1px solid var(--bs-border-color,#C9CFD8);border-radius:4px;padding:4px 7px;font-size:12px;white-space:nowrap;display:none;box-shadow:0 2px 6px rgba(0,0,0,.2)}' +
    '.vr-lists{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px 18px}' +
    '.vr-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:2px 8px;margin:0 0 5px}' +
    '.vr-row .n{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' +
    '.vr-row .v{font-variant-numeric:tabular-nums;color:var(--bs-secondary-color,#5C6672)}' +
    '.vr-row i{grid-column:1/-1;display:block;height:4px;border-radius:2px;background:var(--bw-sky,#4a90d9)}' +
    '.vr-row img{height:.85em;width:auto;margin-right:5px;vertical-align:-.05em;border-radius:2px}' +
    '.vr-err{border:1px solid var(--bw-accent,#B45309);border-radius:6px;padding:8px 10px;margin:10px 0}' +
    '.vr-foot{margin-top:14px}' +
    '@media (max-width:560px){.vr-tiles{grid-template-columns:repeat(2,1fr)}}';

  function addCss(){
    if (document.getElementById('vr-css')) return;
    var s = document.createElement('style'); s.id = 'vr-css'; s.textContent = CSS;
    document.head.appendChild(s);
  }

  function countryName(code){
    if (!/^[A-Z]{2}$/.test(code || '')) return code || T('Unknown');
    try { return new Intl.DisplayNames(lang(), { type: 'region' }).of(code) || code; } catch (e) { return code; }
  }

  // ---- 30-day bar chart of visits (one series: no legend; the heading names it)
  function chart(daily){
    var W = 800, H = 150, L = 34, B = 18, top = 6;
    var max = 0;
    daily.forEach(function(d){ if (d.visits > max) max = d.visits; });
    var step = max <= 5 ? 1 : Math.pow(10, Math.floor(Math.log10(max / 2)));
    [1, 2, 5, 10].some(function(m){ if (max / (step * m) <= 4) { step *= m; return true; } return false; });
    var ymax = Math.max(1, Math.ceil(max / step) * step);
    var bw = (W - L) / daily.length, gap = Math.max(2, bw * 0.25);
    var y = function(v){ return top + (H - B - top) * (1 - v / ymax); };
    var out = '<svg viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none" role="img" aria-label="' + esc(T('Visits per day, last 30 days')) + '">';
    for (var t = 0; t <= ymax; t += step) {
      out += '<line class="vr-grid" x1="' + L + '" x2="' + W + '" y1="' + y(t) + '" y2="' + y(t) + '"/>' +
             '<text class="vr-axis" x="' + (L - 6) + '" y="' + (y(t) + 3) + '" text-anchor="end">' + num(t) + '</text>';
    }
    daily.forEach(function(d, i){
      var x = L + i * bw + gap / 2, w = bw - gap, h = (H - B - top) * d.visits / ymax;
      var r = Math.min(4, w / 2, h);
      // flat at the baseline, rounded at the data end
      var p = h > 0 ? 'M' + x + ',' + (H - B) + 'V' + (H - B - h + r) + 'q0,-' + r + ' ' + r + ',-' + r +
                      'H' + (x + w - r) + 'q' + r + ',0 ' + r + ',' + r + 'V' + (H - B) + 'Z' : '';
      out += '<g data-i="' + i + '"><rect class="hit" x="' + (L + i * bw) + '" y="0" width="' + bw + '" height="' + H + '"/>' +
             (p ? '<path d="' + p + '" style="fill:var(--bw-sky,#4a90d9)"/>' : '') + '</g>';
      if (i === 0 || i === daily.length - 1 || i % 7 === (daily.length - 1) % 7) {
        out += '<text class="vr-axis" x="' + (L + i * bw + bw / 2) + '" y="' + (H - 4) + '" text-anchor="middle">' +
               esc(day(d.date, { day: 'numeric', month: 'short' })) + '</text>';
      }
    });
    return out + '</svg><div class="vr-tip"></div>';
  }

  function hookChart(box, daily){
    var tip = box.querySelector('.vr-tip');
    box.querySelectorAll('g[data-i]').forEach(function(g){
      g.addEventListener('mousemove', function(e){
        var d = daily[+g.getAttribute('data-i')], r = box.getBoundingClientRect();
        tip.innerHTML = '<b>' + esc(day(d.date, { weekday: 'short', day: 'numeric', month: 'short' })) + '</b><br>' +
          esc(T('Visits')) + ': ' + num(d.visits) + '<br>' + esc(T('Page views')) + ': ' + num(d.views);
        tip.style.display = 'block';
        var x = e.clientX - r.left + 12;
        if (x + tip.offsetWidth > r.width) x = e.clientX - r.left - tip.offsetWidth - 12;
        tip.style.left = x + 'px';
        tip.style.top = Math.max(0, e.clientY - r.top - tip.offsetHeight - 8) + 'px';
      });
      g.addEventListener('mouseleave', function(){ tip.style.display = 'none'; });
    });
  }

  function list(title, rows, label, base){
    if (!rows || !rows.length) return '';
    var max = 0;
    rows.forEach(function(r){ if (r.views > max) max = r.views; });
    var h = '<div><h3>' + esc(T(title)) + '</h3>';
    rows.slice(0, 10).forEach(function(r){
      var name = label ? label(r.name) : (r.name || T('Unknown'));
      var flag = (title === 'Countries' && /^[A-Z]{2}$/.test(r.name || ''))
        ? '<img src="' + base + 'img/flags/' + r.name.toLowerCase() + '.svg" alt="" onerror="this.remove()">' : '';
      h += '<div class="vr-row"><span class="n" title="' + esc(name) + '">' + flag + esc(name) + '</span>' +
           '<span class="v">' + num(r.views) + '</span>' +
           '<i style="width:' + Math.max(1, Math.round(100 * r.views / (max || 1))) + '%"></i></div>';
    });
    return h + '</div>';
  }

  function pageName(p){
    if (!p) return T('Unknown');
    var s = p.replace(/[?#].*$/, '').replace(/\/divumwx(\/|$)/, '/');
    return (s === '/' || s === '' || /\/index\.html$/.test(s)) && s.split('/').length <= 2 ? T('Dashboard') : s;
  }

  function open(doc, base){
    base = base || './';
    addCss();
    var old = document.querySelector('.vr-back'); if (old) old.remove();
    var back = document.createElement('div');
    back.className = 'vr-back';
    var h = '<div class="vr" role="dialog" aria-modal="true" aria-labelledby="vr-title">' +
      '<button class="vr-x" type="button" aria-label="' + esc(T('Close')) + '">&times;</button>' +
      '<h2 id="vr-title">' + esc(T('Site visitors')) + '</h2>' +
      '<div class="vr-sub">' + esc(doc.host || '') + '</div>';
    if (doc.error) h += '<div class="vr-err">' + esc(T('Visitor figures unavailable:')) + ' ' + esc(doc.error) + '</div>';
    if (doc.today) {
      var tiles = [[T('Today'), doc.today], [T('7 days'), doc.week], [T('30 days'), doc.month]];
      if (doc.total && doc.total.since) tiles.push([T('Since') + ' ' + day(doc.total.since, { day: 'numeric', month: 'short', year: 'numeric' }), doc.total]);
      h += '<div class="vr-tiles">' + tiles.map(function(t){
        return '<div class="vr-tile"><span>' + esc(t[0]) + '</span><b>' + num(t[1] && t[1].visits) + '</b>' +
               '<span>' + esc(T('Visits')) + ' &middot; ' + num(t[1] && t[1].views) + ' ' + esc(T('Page views')) + '</span></div>';
      }).join('') + '</div>';
      if (doc.daily && doc.daily.length) h += '<h3>' + esc(T('Visits per day, last 30 days')) + '</h3><div class="vr-chart">' + chart(doc.daily) + '</div>';
      h += '<div class="vr-lists">' +
        list('Top pages', doc.pages, pageName, base) +
        list('Countries', doc.countries, countryName, base) +
        list('Referring sites', doc.referrers, function(n){ return n || T('Direct'); }, base) +
        list('Browsers', doc.browsers, null, base) +
        list('Operating systems', doc.os, null, base) +
        list('Devices', doc.devices, function(n){ return n ? n.charAt(0).toUpperCase() + n.slice(1) : T('Unknown'); }, base) +
        '</div>';
    }
    var foot = [T('Lists show page views in the last 30 days.'), T('Figures from Cloudflare Web Analytics, without bots.')];
    if (doc.sampled) foot.push(T('Cloudflare sampled the page loads, so the figures are estimates.'));
    (doc.notes || []).forEach(function(n){ foot.push(n); });
    if (doc.generated) foot.push(T('Updated') + ' ' + new Date(doc.generated * 1000).toLocaleString(lang(), { dateStyle: 'medium', timeStyle: 'short' }));
    h += '<div class="vr-foot vr-note">' + foot.map(esc).join(' ') + '</div></div>';
    back.innerHTML = h;
    document.body.appendChild(back);
    var chartBox = back.querySelector('.vr-chart');
    if (chartBox) hookChart(chartBox, doc.daily);
    var opener = document.activeElement;
    function close(){ back.remove(); document.removeEventListener('keydown', key); if (opener && opener.focus) opener.focus(); }
    function key(e){ if (e.key === 'Escape') close(); }
    back.addEventListener('click', function(e){ if (e.target === back) close(); });
    back.querySelector('.vr-x').addEventListener('click', close);
    document.addEventListener('keydown', key);
    back.querySelector('.vr-x').focus();
  }

  window.DivumWXVisitorReport = { open: open };
})();
