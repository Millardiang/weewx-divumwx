/* ===== cardSolarEnergyFlow.js ===== */
try {
/*
##############################################################################################
# cardSolarEnergyFlow.js version 1.0.0
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

// ===================== cardSolarEnergyFlow.js =====================
(function(){
  var LOOP_JSON_URL    = './jsondata/loop.json';
  var ARCHIVE_JSON_URL = './jsondata/archive.json';
  var SOLAR_JSON_URL   = './jsondata/solar_data.json';
  var ASTRO_JSON_URL   = './jsondata/almanac.json';
  var CLOUD_JSON_URL   = './jsondata/cloud_coverage.json';
  var POLL_MS = 10 * 1000;

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
  function num(x){ return (typeof x === 'number' && !isNaN(x)) ? x : null; }

  function pickPvIcon(cloudCoverPct, isDay){
    if (!isDay) return 'meteocons/fill/svg/clear-night.svg';
    if (cloudCoverPct > 0 && cloudCoverPct < 7)   return 'meteocons/fill/svg/clear-day.svg';
    if (cloudCoverPct < 32)  return 'meteocons/fill/svg/mostly-clear-day.svg';
    if (cloudCoverPct < 70)  return 'meteocons/fill/svg/partly-cloudy-day.svg';
    if (cloudCoverPct < 95)  return 'meteocons/fill/svg/cloudy.svg';
    return 'meteocons/fill/svg/overcast-day.svg';
  }

  var UPS_LOAD_TOPIC = 'solar_assistant/inverter_1/ups_load_power/state';

  function topicValue(solarData, topic){
    var entry = solarData && solarData.data && solarData.data[topic];
    return entry ? entry.value : null;
  }

  var mount = document.getElementById('solarEnergyFlowCard27');
  if (!mount) return;
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
  DivumWXI18N.applyLabel(titleLabel, 'Solar Power Flow');
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

  var stage = document.createElement('div');
  stage.style.position = 'relative';
  stage.style.width = '100%';
  stage.style.height = '175px';
  stage.style.boxSizing = 'border-box';
  stage.style.overflow = 'hidden';
  stage.style.padding = '3px 4px 2px';
  mount.appendChild(stage);

  var inner = document.createElement('div');
  inner.style.position = 'absolute';
  inner.style.top = '3px';
  inner.style.left = '4px';
  inner.style.right = '4px';
  inner.style.bottom = '2px';
  stage.appendChild(inner);

  var NODES = {
    solar:   { l: 30, t: 1,  w: 40, h: 17 },
    grid:    { l: 2,  t: 41, w: 26, h: 17 },
    inv:     { l: 38, t: 33, w: 24, h: 34 },
    load:    { l: 70, t: 28, w: 28, h: 15 },
    ups:     { l: 70, t: 56, w: 28, h: 15 },
    battery: { l: 18, t: 79, w: 64, h: 20 }
  };
  function edges(n){ return { l: n.l, r: n.l + n.w, t: n.t, b: n.t + n.h, cx: n.l + n.w/2, cy: n.t + n.h/2 }; }
  var E = {}; for (var k in NODES) E[k] = edges(NODES[k]);

  var COLORS = { green: '#33cc55', red: '#e6483f', amber: '#f2a93b', grey: '#4a4a4e' };

  if (!document.getElementById('solarEnergyFlowDashStyle')) {
    var dashStyle = document.createElement('style');
    dashStyle.id = 'solarEnergyFlowDashStyle';
    dashStyle.textContent =
      '@keyframes solarEnergyFlowDash { to { stroke-dashoffset: -40; } }' +
      '.flow-dash { animation: solarEnergyFlowDash 1.1s linear infinite; }';
    document.head.appendChild(dashStyle);
  }

  function buildLine(from, to, state){
    var a = E[from], b = E[to];
    var vertical = Math.abs(a.cx - b.cx) < Math.abs(a.cy - b.cy);
    var x1, y1, x2, y2;
    if (vertical) {
      x1 = x2 = (a.cx + b.cx) / 2;
      y1 = (a.cy < b.cy) ? a.b : a.t;
      y2 = (a.cy < b.cy) ? b.t : b.b;
    } else {
      y1 = y2 = (a.cy + b.cy) / 2;
      x1 = (a.cx < b.cx) ? a.r : a.l;
      x2 = (a.cx < b.cx) ? b.l : b.r;
    }
    var c = COLORS[state] || COLORS.grey;
    var s = '<line x1="'+x1+'%" y1="'+y1+'%" x2="'+x2+'%" y2="'+y2+'%" stroke="'+c+'" stroke-width="2.5" stroke-linecap="round" vector-effect="non-scaling-stroke"/>';
    if (state !== 'grey') {
      s += '<line class="flow-dash" x1="'+x1+'%" y1="'+y1+'%" x2="'+x2+'%" y2="'+y2+'%" stroke="'+c+'" stroke-width="2.5" stroke-linecap="round" ' +
           'stroke-dasharray="5 9" opacity="0.9" vector-effect="non-scaling-stroke"/>';
    }
    return s;
  }

  function iconPylon(color){
    return '<svg viewBox="0 0 24 24" fill="none" stroke="'+color+'" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">' +
      '<path d="M12 2 L19 22 M12 2 L5 22"/><path d="M8.2 12.5 H15.8 M7 16.5 H17 M9.3 8.7 H14.7"/>' +
      '<path d="M4 22 H20"/><line x1="12" y1="2" x2="12" y2="6"/></svg>';
  }
  function iconHouse(color){
    return '<svg viewBox="0 0 24 24" fill="'+color+'"><path d="M12 2.7 L22 11 H19V21 H14V15 H10V21 H5V11 H2 Z"/></svg>';
  }
  function iconUPS(color){
    return '<svg viewBox="0 0 24 24" fill="none" stroke="'+color+'" stroke-width="2" stroke-linejoin="round" stroke-linecap="round">' +
      '<rect x="3" y="5" width="18" height="14" rx="2"/>' +
      '<path d="M13 8 L9 13 H12 L11 16 L15 11 H12 Z" fill="'+color+'" stroke="none"/></svg>';
  }
  function iconBattery(color, pct){
    var innerW = 16 * Math.max(0, Math.min(100, (typeof pct === 'number' ? pct : 0))) / 100;
    return '<svg viewBox="0 0 24 24" fill="none">' +
      '<rect x="2" y="7" width="19" height="10" rx="2" stroke="'+color+'" stroke-width="2"/>' +
      '<rect x="22" y="10" width="2" height="4" rx="1" fill="'+color+'"/>' +
      '<rect x="4" y="9" width="'+innerW+'" height="6" fill="'+color+'"/></svg>';
  }
  function iconInverter(){
    return '<svg viewBox="0 0 24 30" fill="none">' +
      '<rect x="1" y="1" width="22" height="28" rx="4" fill="#e9e9ec"/>' +
      '<rect x="6" y="6" width="12" height="6" rx="1.5" fill="#2b2b2e"/>' +
      '<path d="M2 19 H22" stroke="#c8c8cc" stroke-width="1"/></svg>';
  }

  function ring(pct, color){
    var r = 9, c = 2 * Math.PI * r;
    var p = (typeof pct === 'number' && !isNaN(pct)) ? Math.max(0, Math.min(100, pct)) : 0;
    var off = c * (1 - p / 100);
    return '<svg viewBox="0 0 24 24">' +
      '<circle cx="12" cy="12" r="'+r+'" fill="none" stroke="#333" stroke-width="3"/>' +
      '<circle cx="12" cy="12" r="'+r+'" fill="none" stroke="'+color+'" stroke-width="3" stroke-linecap="round" ' +
      'stroke-dasharray="'+c+'" stroke-dashoffset="'+off+'" transform="rotate(-90 12 12)"/></svg>';
  }

  function nodeStyle(n){
    return 'position:absolute;left:'+n.l+'%;top:'+n.t+'%;width:'+n.w+'%;height:'+n.h+'%;' +
      'background:#1c1c1e;border-radius:8px;box-sizing:border-box;display:flex;align-items:center;gap:5px;padding:0 6px;overflow:hidden;';
  }
  var INVERTER_STYLE = 'flex-direction:column;justify-content:center;gap:2px;padding:4px;';
  var BATTERY_TEXT_STYLE = 'flex:1 1 auto;min-width:0;';

  var ICON_WRAP = 'flex:0 0 auto;width:16px;height:16px;display:flex;align-items:center;justify-content:center;';
  var TEXT_WRAP = 'display:flex;flex-direction:column;line-height:1.05;overflow:hidden;min-width:0;';
  var VALUE_STYLE = 'color:#fff;font-weight:700;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:\'IBM Plex Mono\',ui-monospace,monospace;';
  var LABEL_STYLE = 'color:'+overlayTextColor+';opacity:.85;font-size:7.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';

  function fmtPower(w){
    return (typeof w === 'number' && !isNaN(w)) ? Math.round(Math.abs(w)) + ' W' : '\u2014';
  }

  function renderCard(v){
    var solarState  = (v.pvPower !== null && v.pvPower > 0) ? 'green' : 'grey';
    var gridState   = (v.gridPower === null) ? 'grey' : (v.gridPower < 0 ? 'green' : (v.gridPower > 0 ? 'red' : 'grey'));
    var loadState   = (v.loadPower !== null && v.loadPower > 0) ? 'green' : 'grey';
    var upsState    = (v.upsLoadPower !== null && v.upsLoadPower > 0) ? 'green' : 'grey';
    var battState   = (v.batteryPower === null) ? 'grey' : (v.batteryPower < 0 ? 'green' : (v.batteryPower > 0 ? 'amber' : 'grey'));
    var battColor   = COLORS[battState === 'grey' ? 'grey' : battState];
    var gridColor   = COLORS[gridState === 'grey' ? 'grey' : gridState];

    var svg = '<svg viewBox="0 0 100 100" preserveAspectRatio="none" style="position:absolute;top:0;left:0;width:100%;height:100%;">' +
      buildLine('solar', 'inv', solarState) +
      buildLine('grid', 'inv', gridState) +
      buildLine('inv', 'load', loadState) +
      buildLine('inv', 'ups', upsState) +
      buildLine('inv', 'battery', battState) +
      '</svg>';

    var html = svg;

    html += '<div style="'+nodeStyle(NODES.solar)+'">' +
      '<span style="'+ICON_WRAP+'"><img src="'+v.icon+'" style="width:100%;height:100%;object-fit:contain;"/></span>' +
      '<span style="'+TEXT_WRAP+'"><span style="'+VALUE_STYLE+'">'+fmtPower(v.pvPower)+'</span>' +
      '<span style="'+LABEL_STYLE+'" data-i18n-label="Solar PV"></span></span></div>';

    html += '<div style="'+nodeStyle(NODES.grid)+'">' +
      '<span style="'+ICON_WRAP+'">'+iconPylon(gridColor)+'</span>' +
      '<span style="'+TEXT_WRAP+'"><span style="'+VALUE_STYLE+'">'+fmtPower(v.gridPower)+'</span>' +
      '<span style="'+LABEL_STYLE+'" data-i18n-label="Grid"></span></span></div>';

    html += '<div style="'+nodeStyle(NODES.inv)+INVERTER_STYLE+'">' +
      '<span style="width:22px;height:28px;flex:0 0 auto;">'+iconInverter()+'</span>' +
      '<span style="'+LABEL_STYLE+'color:var(--bs-body-color);" data-i18n-label="Inverter"></span></div>';

    html += '<div style="'+nodeStyle(NODES.load)+'">' +
      '<span style="'+ICON_WRAP+'">'+iconHouse(loadState === 'grey' ? COLORS.grey : '#3ecf6a')+'</span>' +
      '<span style="'+TEXT_WRAP+'"><span style="'+VALUE_STYLE+'">'+fmtPower(v.loadPower)+'</span>' +
      '<span style="'+LABEL_STYLE+'" data-i18n-label="Load"></span></span></div>';

    html += '<div style="'+nodeStyle(NODES.ups)+'">' +
      '<span style="'+ICON_WRAP+'">'+iconUPS(upsState === 'grey' ? COLORS.grey : '#a970ff')+'</span>' +
      '<span style="'+TEXT_WRAP+'"><span style="'+VALUE_STYLE+'">'+fmtPower(v.upsLoadPower)+'</span>' +
      '<span style="'+LABEL_STYLE+'" data-i18n-label="UPS Load"></span></span></div>';

    var battStateLabel = (v.batteryPower === null) ? '' :
      (v.batteryPower < 0 ? DivumWXI18N.t('Charging') : (v.batteryPower > 0 ? DivumWXI18N.t('Discharging') : DivumWXI18N.t('Idle')));
    html += '<div style="'+nodeStyle(NODES.battery)+'">' +
      '<span style="'+ICON_WRAP+'">'+iconBattery(battColor, v.batterySOC)+'</span>' +
      '<span style="'+TEXT_WRAP+BATTERY_TEXT_STYLE+'">' +
      '<span style="'+VALUE_STYLE+'">'+fmtPower(v.batteryPower)+' <span style="color:#9a9a9e;font-weight:600;">'+
      (typeof v.batterySOC === 'number' ? v.batterySOC.toFixed(0) : '\u2014')+'%</span></span>' +
      '<span style="color:'+battColor+';font-size:7px;font-weight:600;white-space:nowrap;">'+battStateLabel+'</span></span>' +
      '<span style="flex:0 0 auto;width:16px;height:16px;margin-left:4px;">'+ring(v.batterySOC, battColor)+'</span></div>';

    inner.innerHTML = html;

    inner.querySelectorAll('[data-i18n-label]').forEach(function(el){
      el.textContent = DivumWXI18N.t(el.getAttribute('data-i18n-label'));
    });
  }

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

  function refresh(){
    Promise.allSettled([
      fetch(LOOP_JSON_URL + ((LOOP_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ARCHIVE_JSON_URL + ((ARCHIVE_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(SOLAR_JSON_URL + ((SOLAR_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(ASTRO_JSON_URL + ((ASTRO_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); }),
      fetch(CLOUD_JSON_URL + ((CLOUD_JSON_URL).indexOf('?')>-1?'&':'?') + '_=' + Date.now(), {cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    ]).then(function(results){
      var loopResult = results[0], archResult = results[1], solarResult = results[2], astroResult = results[3], cloudResult = results[4];
      if (loopResult.status === 'rejected') console.warn('cardSolarEnergyFlow: loop.json fetch failed --', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardSolarEnergyFlow: archive.json fetch failed --', archResult.reason.message);
      if (solarResult.status === 'rejected') console.warn('cardSolarEnergyFlow: solar_data.json fetch failed --', solarResult.reason.message);
      if (astroResult.status === 'rejected') console.warn('cardSolarEnergyFlow: almanac.json fetch failed --', astroResult.reason.message);
      if (cloudResult.status === 'rejected'){
        console.info('cardSolarEnergyFlow: cloud_coverage.json fetch failed (falling back to loop.json/archive.json) --', cloudResult.reason.message);
      } else if (typeof cloudResult.value.cloudPercent !== 'number' || isNaN(cloudResult.value.cloudPercent)){
        console.info('cardSolarEnergyFlow: cloud_coverage.json fetched but cloudPercent is missing/invalid (falling back) --', JSON.stringify(cloudResult.value));
      }

      var o = loopResult.status === 'fulfilled' ? (loopResult.value.observations || {}) : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var cloudCoverage = cloudResult.status === 'fulfilled' ? cloudResult.value : null;
      var sky = arch.sky || {};
      var solarData = solarResult.status === 'fulfilled' ? solarResult.value : {};
      var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};

      var pvPower          = num(topicValue(solarData, 'solar_assistant/inverter_1/pv_power/state'));
      var gridPowerRaw      = num(topicValue(solarData, 'solar_assistant/inverter_1/grid_power/state'));
      var loadPower           = num(topicValue(solarData, 'solar_assistant/inverter_1/load_power/state'));
      var upsLoadPower           = num(topicValue(solarData, UPS_LOAD_TOPIC));
      var batteryPowerRaw          = num(topicValue(solarData, 'solar_assistant/total/battery_power/state'));
      var batterySOC                  = num(topicValue(solarData, 'solar_assistant/total/battery_state_of_charge/state'));

      var sunAlt = num(alm['almanac.sun.alt']);
      var isDay = (sunAlt !== null) ? (sunAlt > 0) : (o.isDay === 1);
      var cloudPercentFromCamera = (cloudCoverage && typeof cloudCoverage.cloudPercent === 'number' && !isNaN(cloudCoverage.cloudPercent))
        ? cloudCoverage.cloudPercent : null;
      var cloudCoverPct = (isDay && cloudPercentFromCamera !== null)
        ? cloudPercentFromCamera
        : ((typeof o.cloudcover === 'number') ? o.cloudcover : (sky.cloud_cover || 0));

      renderCard({
        icon: pickPvIcon(cloudCoverPct, isDay),
        pvPower: pvPower,
        gridPower: gridPowerRaw,
        loadPower: loadPower,
        upsLoadPower: upsLoadPower,
        batteryPower: batteryPowerRaw,
        batterySOC: batterySOC
      });

      var solarConnected = solarResult.status === 'fulfilled' && solarData.connected === true;
      setStatus(loopResult.status === 'fulfilled' && archResult.status === 'fulfilled' && solarConnected);
    }).catch(function(e){
      console.warn('cardSolarEnergyFlow: refresh failed --', e.message);
      setStatus(false);
    });
  }
  refresh();
  setInterval(refresh, POLL_MS);
  window.addEventListener('i18nready', refresh);
})();
} catch (e) {
  console.error("cardsBundle: cardSolarEnergyFlow.js failed:", e);
}