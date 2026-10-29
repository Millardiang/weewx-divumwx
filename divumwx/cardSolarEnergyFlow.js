/* ===== cardSolarEnergyFlow.js ===== */
try {
/*
##############################################################################################
# cardSolarEnergyFlow.js version 1.0.4
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

  function pickPvIcon(cloudCoverPct, isDay){
    if (!isDay) return 'meteocons/fill/svg/clear-night.svg';
    if (cloudCoverPct > 0 && cloudCoverPct < 7)   return 'meteocons/fill/svg/clear-day.svg';
    if (cloudCoverPct < 32)  return 'meteocons/fill/svg/mostly-clear-day.svg';
    if (cloudCoverPct < 70)  return 'meteocons/fill/svg/partly-cloudy-day.svg';
    if (cloudCoverPct < 95)  return 'meteocons/fill/svg/cloudy.svg';
    return 'meteocons/fill/svg/overcast-day.svg';
  }

  function pickPvPowerColor(watts){
    if (watts <= 0) return 'silver';
    if (watts <= 1500) return '#FFD700';
    if (watts <= 3000) return '#ffa242';
    return '#ff7400';
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
  DivumWXI18N.applyAttr(titleLabel, 'title', 'Solar Power Flow');
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

  var graphicWrap = document.createElement('div');
  graphicWrap.style.position = 'relative';
  graphicWrap.style.width = '100%';
  graphicWrap.style.height = '100%';
  graphicWrap.style.flex = '1 1 auto';
  graphicWrap.style.boxSizing = 'border-box';
  leftPane.appendChild(graphicWrap);

  // ---- Compact Solar Power Flow diagram (same design as the charts modal's
  //      Flow Diagram, re-laid out for the 189 x 175 px left pane) ----
  // PV feeds the inverter from the left, Grid from the top, Battery from the
  // bottom; the inverter feeds the Essential and Non-Essential circuits on the
  // right. Each tracer takes the colour of the box the power flows FROM.
  var FLOW_C = { pv: '#F28C1B', grid: '#E53935', batt: '#2EA043', inv: '#2B8CEB', ess: '#43A047', non: '#F28C1B' };
  var FLOW_MIN_W = 1;

  if (!document.getElementById('solarEnergyFlowMiniStyle')) {
    var flowStyle = document.createElement('style');
    flowStyle.id = 'solarEnergyFlowMiniStyle';
    flowStyle.textContent =
      ':root{--seh-chip-bg:#EFECE2;}body.dark{--seh-chip-bg:#0A0F22;}' +
      '.sfm svg{display:block;width:100%;height:100%;}' +
      '.sfm text{font-family:Lexend,"Helvetica Neue",Helvetica,Arial,sans-serif;text-anchor:middle;}' +
      '.sfm-box rect.sfm-bg{fill:color-mix(in srgb, var(--c) 16%, var(--seh-chip-bg));stroke:var(--c);stroke-width:.7;}' +
      '.sfm-box.idle{--c:#9AA0A8 !important;}' +
      '.sfm-box .sfm-icon{color:var(--c);fill:none;stroke:currentColor;stroke-width:3.4;stroke-linecap:round;stroke-linejoin:round;}' +
      '.sfm-box .sfm-icon .sfm-fill{fill:currentColor;stroke:none;opacity:.85;}' +
      '.sfm-name{font-size:6px;font-weight:600;fill:var(--bs-body-color,#1D2C4E);font-variant-caps:small-caps;letter-spacing:.03em;}' +
      '.sfm-box.idle .sfm-name{opacity:.7;}' +
      '.sfm-val{font-size:8.5px;font-weight:700;fill:var(--c);}' +
      '.sfm-sub{font-size:5.5px;font-weight:600;}' +
      '.sfm-wire{fill:none;stroke:var(--bs-border-color,#C9CFD8);stroke-width:1;stroke-linecap:round;}' +
      '.sfm-tr{fill:none;stroke-width:1.2;stroke-linecap:round;stroke-dasharray:4 5;animation:sfmFlow var(--dur,1.6s) linear infinite;}' +
      '@keyframes sfmFlow{from{stroke-dashoffset:9;}to{stroke-dashoffset:0;}}' +
      '@media (prefers-reduced-motion: reduce){.sfm-tr{animation:none;stroke-dasharray:none;}}';
    document.head.appendChild(flowStyle);
  }

  // Wires in their "forward" direction (source end first).
  var FLOW_WIRE = {
    pv:      'M48 87 H67',
    grid:    'M95 39 V63',   gridRev: 'M95 63 V39',
    batt:    'M95 136 V111', battRev: 'M95 111 V136',
    load:    'M123 87 H134',
    ess:     'M134 87 V51 H141',
    non:     'M134 87 V123 H141'
  };

  function flowDur(w){
    var a = Math.min(Math.abs(w || 0), 5000);
    return (2.4 - 1.8 * Math.sqrt(a / 5000)).toFixed(2) + 's';
  }
  function flowTracer(on, d, color, w){
    return on ? '<path class="sfm-tr" d="'+d+'" stroke="'+color+'" style="--dur:'+flowDur(w)+'"/>' : '';
  }
  function flowBox(id, c, idle, x, y, w, h, inner, title){
    return '<g class="sfm-box'+(idle ? ' idle' : '')+'" style="--c:'+c+'"><title>'+title+'</title>' +
      '<rect class="sfm-bg" x="'+x+'" y="'+y+'" width="'+w+'" height="'+h+'" rx="5"/>'+inner+'</g>';
  }
  // Outline icons -- the same drawings as the Flow Diagram in the charts
  // modal and solarFlowKiosk.html, scaled down from their +-24 unit grid.
  var GLYPH_SCALE = 0.31;
  function glyph(inner){ return '<g class="sfm-icon" transform="scale(' + GLYPH_SCALE + ')">' + inner + '</g>'; }
  var GLYPH = {
    sun: glyph('<circle r="9"/><path d="M0 -15 V-21 M0 15 V21 M-15 0 H-21 M15 0 H21 M-10.6 -10.6 L-14.8 -14.8 ' +
               'M10.6 10.6 L14.8 14.8 M-10.6 10.6 L-14.8 14.8 M10.6 -10.6 L14.8 -14.8"/>'),
    pylon: glyph('<path d="M-9 22 L-3 -22 H3 L9 22"/><path d="M-18 -13 H18 M-13 -3 H13"/>' +
                 '<path d="M-18 -13 V-9 M18 -13 V-9 M-13 -3 V1 M13 -3 V1"/>' +
                 '<path d="M-5.6 -3 L7.4 12 M5.6 -3 L-7.4 12 M-7.4 12 L9 22 M7.4 12 L-9 22"/>'),
    inv: glyph('<rect x="-16" y="-22" width="32" height="44" rx="5"/><rect x="-10" y="-16" width="20" height="13" rx="2"/>' +
               '<path d="M-7 -9.5 C-5 -14 -3 -14 -1 -9.5 S3 -5 5 -9.5" style="stroke-width:2.6"/>' +
               '<circle class="sfm-fill" cx="-6" cy="3" r="1.9"/><circle class="sfm-fill" cx="0" cy="3" r="1.9"/>' +
               '<circle class="sfm-fill" cx="6" cy="3" r="1.9"/><path d="M-9 10 H9 M-9 15 H9"/>'),
    shield: glyph('<path d="M0 -22 L17 -15.5 V-2 C17 10 9.5 18.5 0 22 C-9.5 18.5 -17 10 -17 -2 V-15.5 Z"/><path d="M-7 0 L-2 5.5 L8 -5"/>'),
    house: glyph('<path d="M-20 -1 L0 -20 L20 -1"/><path d="M-14 -7 V20 H14 V-7"/><path d="M-4.5 20 V9 H4.5 V20"/>')
  };
  function glyphBattery(soc){
    var lvl = Math.max(0, Math.min(100, typeof soc === 'number' ? soc : 0)) / 100;
    var h = (36 * lvl).toFixed(1), y = (19 - 36 * lvl).toFixed(1);
    return glyph('<rect x="-13" y="-22" width="26" height="46" rx="5"/><path d="M-5 -26 H5"/>' +
      '<rect class="sfm-fill" x="-8" y="' + y + '" width="16" height="' + h + '" rx="2"/>');
  }
  function flowText(cls, x, y, s, extra){
    return '<text class="'+cls+'" x="'+x+'" y="'+y+'"'+(extra || '')+'>'+s+'</text>';
  }

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
  var loadText          = addChipRow('Total Load');
  var dailyEnergyText       = addChipRow('Solar Daily Energy');
  var dailyExportText          = addChipRow('Grid Daily Export');
  var efficiencyText              = addChipRow('PV Efficiency');
  efficiencyText.parentElement.style.borderBottom = 'none';

  var cardLink = document.createElement('a');
  cardLink.className = 'card-whole-link';
  cardLink.href = 'charts-d3.html?type=solar&embed=1';
  DivumWXI18N.applyAttr(cardLink, 'data-modal', 'Solar Power Flow');
  DivumWXI18N.applyAttr(cardLink, 'data-title', 'Solar Power Flow Chart & Records');
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

  function renderDiagram(v){
    var T = function(k){ return DivumWXI18N.t(k); };
    var pvOn   = v.pvPower !== null && v.pvPower > FLOW_MIN_W;
    // Grid: + import (grid -> inverter), - export (inverter -> grid)
    var gIn    = v.gridPower !== null && v.gridPower >  FLOW_MIN_W;
    var gOut   = v.gridPower !== null && v.gridPower < -FLOW_MIN_W;
    // Battery: - discharging (battery -> inverter), + charging (inverter -> battery)
    var bDis   = v.batteryPower !== null && v.batteryPower < -FLOW_MIN_W;
    var bChg   = v.batteryPower !== null && v.batteryPower >  FLOW_MIN_W;
    var loadOn = v.totalLoadPower !== null && v.totalLoadPower > FLOW_MIN_W;
    var essOn  = v.upsLoadPower !== null && v.upsLoadPower > FLOW_MIN_W;
    var nonOn  = v.houseLoadPower !== null && v.houseLoadPower > FLOW_MIN_W;

    var gridMode = v.gridPower === null ? '' : gIn ? T('Import') : gOut ? T('Export') : T('Idle');
    var battMode = v.batteryPower === null ? '' : bDis ? T('Discharging') : bChg ? T('Charging') : T('Idle');
    var soc = (typeof v.batterySOC === 'number' && !isNaN(v.batterySOC)) ? Math.round(v.batterySOC) + '%' : '—';

    var svg = '<svg viewBox="0 0 190 175" preserveAspectRatio="xMidYMid meet" role="img">';
    svg += '<path class="sfm-wire" d="'+FLOW_WIRE.pv+'"/><path class="sfm-wire" d="'+FLOW_WIRE.grid+'"/>' +
      '<path class="sfm-wire" d="'+FLOW_WIRE.batt+'"/><path class="sfm-wire" d="'+FLOW_WIRE.load+'"/>' +
      '<path class="sfm-wire" d="'+FLOW_WIRE.ess+'"/><path class="sfm-wire" d="'+FLOW_WIRE.non+'"/>';
    svg += flowTracer(pvOn, FLOW_WIRE.pv, FLOW_C.pv, v.pvPower) +
      flowTracer(gIn || gOut, gIn ? FLOW_WIRE.grid : FLOW_WIRE.gridRev, gIn ? FLOW_C.grid : FLOW_C.inv, v.gridPower) +
      flowTracer(bDis || bChg, bDis ? FLOW_WIRE.batt : FLOW_WIRE.battRev, bDis ? FLOW_C.batt : FLOW_C.inv, v.batteryPower) +
      flowTracer(loadOn, FLOW_WIRE.load, FLOW_C.inv, v.totalLoadPower) +
      flowTracer(essOn, FLOW_WIRE.ess, FLOW_C.inv, v.upsLoadPower) +
      flowTracer(nonOn, FLOW_WIRE.non, FLOW_C.inv, v.houseLoadPower);

    // PV Solar (left)
    svg += flowBox('pv', FLOW_C.pv, !pvOn, 3, 63, 45, 48,
      '<g transform="translate(25.5 76)">'+GLYPH.sun+'</g>' +
      flowText('sfm-name', 25.5, 95, T('PV Solar')) + flowText('sfm-val', 25.5, 105, fmtPower(v.pvPower)), T('PV Solar'));
    // Grid (top)
    svg += flowBox('grid', FLOW_C.grid, !gIn && !gOut, 67, 2, 56, 37,
      '<g transform="translate(76 20.5)">'+GLYPH.pylon+'</g>' +
      flowText('sfm-name', 101, 11, T('Grid')) +
      flowText('sfm-val', 101, 22, fmtPower(v.gridPower)) +
      flowText('sfm-sub', 101, 32, gridMode, ' fill="'+(gOut ? FLOW_C.batt : 'var(--c)')+'"'), T('Grid'));
    // Inverter (centre)
    svg += flowBox('inv', FLOW_C.inv, v.online === false, 67, 63, 56, 48,
      '<g transform="translate(77 87)">'+GLYPH.inv+'</g>' +
      flowText('sfm-name', 102, 80, T('Inverter')) +
      flowText('sfm-val', 102, 92, fmtPower(v.totalLoadPower)) +
      flowText('sfm-sub', 102, 102, v.online === false ? T('Offline') : T('Online'), ' fill="'+(v.online === false ? FLOW_C.grid : FLOW_C.batt)+'"'), T('Inverter'));
    // Battery (bottom)
    svg += flowBox('batt', FLOW_C.batt, v.batterySOC === null && !bDis && !bChg, 63, 136, 64, 37,
      '<g transform="translate(72 154.5)">'+glyphBattery(v.batterySOC)+'</g>' +
      flowText('sfm-name', 101, 144.5, T('Battery')) +
      flowText('sfm-val', 101, 155.5, soc + '<tspan class="sfm-sub" dx="2.5" style="fill:var(--bs-body-color);opacity:.75">' + fmtPower(v.batteryPower) + '</tspan>') +
      flowText('sfm-sub', 101, 166, battMode, ' fill="'+(bDis ? '#E58A00' : bChg ? FLOW_C.inv : 'var(--bs-body-color)')+'"'), T('Battery'));
    // Essential and Non-Essential (right)
    svg += flowBox('ess', FLOW_C.ess, !essOn, 141, 30, 47, 42,
      '<g transform="translate(164.5 41)">'+GLYPH.shield+'</g>' +
      flowText('sfm-name', 164.5, 57, T('Essential')) + flowText('sfm-val', 164.5, 67, fmtPower(v.upsLoadPower)), T('Essential'));
    svg += flowBox('non', FLOW_C.non, !nonOn, 141, 102, 47, 42,
      '<g transform="translate(164.5 113)">'+GLYPH.house+'</g>' +
      flowText('sfm-name', 164.5, 129, T('Non-Essential')) + flowText('sfm-val', 164.5, 139, fmtPower(v.houseLoadPower)), T('Non-Essential'));
    svg += '</svg>';

    graphicWrap.className = 'sfm';
    graphicWrap.innerHTML = svg;
  }

  function renderCard(v){
    renderDiagram(v);

    gridText.textContent = v.gridState + ' ' + fmtPower(v.gridPower);
    batteryText.textContent = v.batteryState + ' ' + fmtPower(v.batteryPower) + ', ' +
      (typeof v.batterySOC === 'number' ? v.batterySOC.toFixed(0) : '\u2014') + '%';
    loadText.textContent = fmtPower(v.totalLoadPower);
    dailyEnergyText.textContent = fmtEnergy(v.solarDailyEnergy);
    dailyExportText.textContent = fmtEnergy(v.gridDailyExport);
    efficiencyText.textContent = (typeof v.pvEfficiency === 'number' && !isNaN(v.pvEfficiency))
      ? v.pvEfficiency.toFixed(1) + '%' : '\u2014';
  }

  function num(x){ return (typeof x === 'number' && !isNaN(x)) ? x : null; }

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
      if (loopResult.status === 'rejected') console.warn('cardSolarEnergyFlow: loop.json fetch failed --', loopResult.reason.message);
      if (archResult.status === 'rejected') console.warn('cardSolarEnergyFlow: archive.json fetch failed --', archResult.reason.message);
      if (solarResult.status === 'rejected') console.warn('cardSolarEnergyFlow: solar_data.json fetch failed --', solarResult.reason.message);
      if (astroResult.status === 'rejected') console.warn('cardSolarEnergyFlow: almanac.json fetch failed --', astroResult.reason.message);

      if(cloudResult.status === 'rejected'){
        console.info('cardSolarEnergyFlow: cloud_coverage.json fetch failed (falling back to loop.json/archive.json) --', cloudResult.reason.message);
      } else if(typeof cloudResult.value.cloudPercent !== 'number' || isNaN(cloudResult.value.cloudPercent)){
        console.info('cardSolarEnergyFlow: cloud_coverage.json fetched but cloudPercent is missing/invalid (falling back) --', JSON.stringify(cloudResult.value));
      }

      var o = loopResult.status === 'fulfilled' ? (loopResult.value.observations || {}) : {};
      var arch = archResult.status === 'fulfilled' ? archResult.value : {};
      var cloudCoverage = cloudResult.status === 'fulfilled' ? cloudResult.value : null;
      var sky = arch.sky || {};
      var solarData = solarResult.status === 'fulfilled' ? solarResult.value : {};
      var alm = astroResult.status === 'fulfilled' ? astroResult.value : {};

      var pvPower       = num(topicValue(solarData, 'solar_assistant/inverter_1/pv_power/state'));
      var gridPowerRaw   = num(topicValue(solarData, 'solar_assistant/inverter_1/grid_power/state'));
      // Solar Assistant's load_power_non-essential reading is the TOTAL (house + UPS) on this
      // inverter; load_power is the house load without the UPS (non-essential = load_power + essential).
      var totalLoadPower  = num(topicValue(solarData, 'solar_assistant/inverter_1/load_power_non-essential/state'));
      var batteryPowerRaw   = num(topicValue(solarData, 'solar_assistant/total/battery_power/state'));
      var batterySOC             = num(topicValue(solarData, 'solar_assistant/total/battery_state_of_charge/state'));
      var pvEnergyToday               = num(topicValue(solarData, 'solar_assistant/total/pv_energy/state'));
      var gridEnergyOutToday               = num(topicValue(solarData, 'solar_assistant/total/grid_energy_out/state'));

      var houseLoadPower  = num(topicValue(solarData, 'solar_assistant/inverter_1/load_power/state'));
      var upsLoadPower       = num(topicValue(solarData, 'solar_assistant/inverter_1/load_power_essential/state'));

      var sunAlt = num(alm['almanac.sun.alt']);
      var isDay = (sunAlt !== null) ? (sunAlt > 0) : (o.isDay === 1);

      var cloudPercentFromCamera = (cloudCoverage && typeof cloudCoverage.cloudPercent === 'number' && !isNaN(cloudCoverage.cloudPercent))
        ? cloudCoverage.cloudPercent : null;
      var cloudCoverPct = (isDay && cloudPercentFromCamera !== null)
        ? cloudPercentFromCamera
        : ((typeof o.cloudcover === 'number') ? o.cloudcover : (sky.cloud_cover || 0));
      console.info('cardSolarEnergyFlow: cloud cover source —', {
        isDay: isDay, sunAlt: sunAlt, cameraAvailable: cloudPercentFromCamera !== null,
        cameraValue: cloudPercentFromCamera, usedValue: cloudCoverPct,
        source: (isDay && cloudPercentFromCamera !== null) ? 'cloud_coverage.json' : 'loop.json/archive.json'
      });

      var batteryState = (batteryPowerRaw !== null && batteryPowerRaw < 0) ? DivumWXI18N.t('Discharging') : DivumWXI18N.t('Charging');

      var gridState = (gridPowerRaw !== null && gridPowerRaw < 0) ? DivumWXI18N.t('Exporting') : DivumWXI18N.t('Importing');

      // The array's rated watts, from the installer (archive.json meta.solar_array_watts).
      // Not set: no efficiency figure.
      var arrayWatts = (arch.meta && typeof arch.meta.solar_array_watts === 'number' && arch.meta.solar_array_watts > 0)
        ? arch.meta.solar_array_watts : null;
      var pvEfficiency = (pvPower !== null && arrayWatts !== null) ? (pvPower / arrayWatts * 100) : null;

      renderCard({
        icon: pickPvIcon(cloudCoverPct, isDay),
        iconColor: pickPvPowerColor(pvPower),
        pvPower: pvPower,
        pvEfficiency: pvEfficiency,
        batteryState: batteryState,
        batteryPower: batteryPowerRaw,
        batterySOC: batterySOC,
        gridState: gridState,
        gridPower: gridPowerRaw,
        totalLoadPower: totalLoadPower,
        houseLoadPower: houseLoadPower,
        upsLoadPower: upsLoadPower,
        solarDailyEnergy: pvEnergyToday,
        gridDailyExport: gridEnergyOutToday,
        online: solarResult.status === 'fulfilled' && solarData.connected === true
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
