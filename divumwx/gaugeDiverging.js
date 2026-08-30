/*
##############################################################################################
# gaugeDiverging.js version 0.0.2
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
# ============================================================================================
*/

(function (global) {
  'use strict';

  var FONT = "'Helvetica Neue', Helvetica, Arial, sans-serif";

  // ---- Shared geometry ----
  var VIEW_W = 340, VIEW_H = 330, CX = 170, CY = 196;
  var OUTER_GEOM = { R: 126, BAND: 20 };
  var INSET_GEOM = { R: 62, BAND: 12 };
  var ARC_START = -135, ARC_END = 135;

  var TRACK_COLOR = '#6b9b6f';
  var BAND_COLD = '#1c3fa0';
  var BAND_HOT = '#a5241c';
  var BAND_MID = '#eef1f5';

  function deg2rad(d) { return d * Math.PI / 180; }

  function chromeColors() {
    var cs = getComputedStyle(document.documentElement);
    return {
      text: (cs.getPropertyValue('--bs-body-color') || '#222').trim(),
      tick: (cs.getPropertyValue('--bs-secondary-color') || '#5b6472').trim()
    };
  }

  function ensureSvg(containerId) {
    var wrap = document.getElementById(containerId);
    if (!wrap || typeof d3 === 'undefined') return null;
    d3.select(wrap).selectAll('svg').remove();
    var svg = d3.select(wrap).append('svg')
      .attr('viewBox', '0 0 ' + VIEW_W + ' ' + VIEW_H)
      .style('width', '100%').style('height', '100%').style('display', 'block');
    var root = svg.append('g').attr('transform', 'translate(' + CX + ',' + CY + ')');
    return { svg: svg, root: root };
  }

  // ---- Colour math shared by the linear arc and the compass ring ----

  function bandColorAt(vm, value, lo, hi) {
    var coldInterp = d3.interpolateRgb(BAND_COLD, BAND_MID);
    var hotInterp = d3.interpolateRgb(BAND_MID, BAND_HOT);
    if (vm <= value) {
      var span = value - lo;
      var fc = span > 0 ? Math.max(0, Math.min(1, (value - vm) / span)) : 0;
      return coldInterp(1 - fc);
    }
    var span2 = hi - value;
    var fh = span2 > 0 ? Math.max(0, Math.min(1, (vm - value) / span2)) : 0;
    return hotInterp(fh);
  }

  function bandSegments(value, lo, hi, angleOf, N) {
    var segs = [];
    for (var i = 0; i < N; i++) {
      var v0 = lo + (hi - lo) * (i / N);
      var v1 = lo + (hi - lo) * ((i + 1) / N);
      var vm = (v0 + v1) / 2;
      segs.push({ a0: deg2rad(angleOf(v0)), a1: deg2rad(angleOf(v1)), color: bandColorAt(vm, value, lo, hi) });
    }
    return segs;
  }

  function renderTrack(root, geom, startDeg, endDeg) {
    var arcGen = d3.arc().innerRadius(geom.R - geom.BAND).outerRadius(geom.R)
      .startAngle(deg2rad(startDeg)).endAngle(deg2rad(endDeg));
    root.append('path').attr('d', arcGen()).style('fill', TRACK_COLOR);
  }

  function renderBandSegs(root, geom, segs) {
    var arcGen = d3.arc().innerRadius(geom.R - geom.BAND).outerRadius(geom.R);
    root.selectAll(null).data(segs).enter().append('path')
      .attr('d', function (d) { return arcGen({ startAngle: d.a0, endAngle: d.a1 }); })
      .attr('fill', function (d) { return d.color; });
  }

  function renderMarker(root, geom, angleDeg, markerColor) {
    var g = root.append('g').attr('transform', 'rotate(' + angleDeg + ')');
    g.append('line')
      .attr('x1', 0).attr('y1', -geom.R).attr('x2', 0).attr('y2', -(geom.R - geom.BAND))
      .style('stroke', markerColor).style('stroke-width', 3.5).style('stroke-linecap', 'round');
    var half = 5, baseR = geom.R - geom.BAND - 12, tipR = geom.R - geom.BAND - 2;
    g.append('path')
      .attr('d', 'M' + (-half) + ',' + (-baseR) + ' L' + half + ',' + (-baseR) + ' L0,' + (-tipR) + ' Z')
      .style('fill', markerColor);
  }

  function renderTickLabel(root, geom, angleDeg, text, tickColor, fontSize) {
    var norm = ((angleDeg % 360) + 540) % 360 - 180;
    var flip = norm > 90 || norm < -90;
    var tg = root.append('g').attr('class', 'gdiv-tick').attr('transform', 'rotate(' + angleDeg + ')');
    tg.append('line').attr('class', 'gdiv-tick-line')
      .attr('x1', 0).attr('y1', -(geom.R + 2)).attr('x2', 0).attr('y2', -(geom.R + 10))
      .style('stroke', tickColor).style('stroke-width', 2);
    var lg = tg.append('g')
      .attr('transform', 'translate(0,' + (-(geom.R + 24)) + ')' + (flip ? ' rotate(180)' : ''));
    lg.append('text').attr('class', 'gdiv-tick-text')
      .attr('text-anchor', 'middle').attr('dy', '0.32em')
      .style('font-family', FONT).style('font-size', (fontSize || 13) + 'px')
      .style('fill', tickColor)
      .text(text);
  }

  function renderMinorTicks(root, geom, angles) {
    root.selectAll(null).data(angles).enter().append('line')
      .attr('transform', function (v) { return 'rotate(' + v + ')'; })
      .attr('x1', 0).attr('y1', -(geom.R - geom.BAND)).attr('x2', 0).attr('y2', -geom.R)
      .style('stroke', 'rgba(255,255,255,0.55)').style('stroke-width', 1);
  }

  function renderCenterText(root, cfg) {
    var s = cfg.scale || 1;
    var cc = chromeColors();
    if (!cfg.skipTitle) {
      root.append('text').attr('x', 0).attr('y', -6 * s)
        .style('text-anchor', 'middle').style('font-family', FONT)
        .style('font-size', (15 * s) + 'px').style('fill', cc.text)
        .text(cfg.title || '');
    }
    var valueY = (cfg.skipTitle ? 10 : 34) * s;
    root.append('text').attr('x', 0).attr('y', valueY)
      .style('text-anchor', 'middle').style('font-family', FONT).style('font-weight', '700')
      .style('font-size', (34 * s) + 'px').style('fill', cc.text)
      .text(cfg.valueText == null ? '--' : cfg.valueText);

    if (!cfg.skipMinMax && (cfg.minText != null || cfg.maxText != null)) {
      var g = root.append('g').style('font-family', FONT).style('font-size', (15 * s) + 'px').style('fill', cc.text);
      var minStr = '\u25BE ' + (cfg.minText == null ? '--' : cfg.minText);
      var maxStr = '\u25B4 ' + (cfg.maxText == null ? '--' : cfg.maxText);
      var minW = minStr.length * 8 * s + 6, maxW = maxStr.length * 8 * s + 6, gap = 16 * s;
      var totalW = minW + maxW + gap;
      var minX = -totalW / 2 + minW / 2, maxX = totalW / 2 - maxW / 2;
      var y = (cfg.skipTitle ? 42 : 62) * s;
      g.append('text').attr('x', minX).attr('y', y).style('text-anchor', 'middle').text(minStr);
      g.append('text').attr('x', maxX).attr('y', y).style('text-anchor', 'middle').text(maxStr);
    }
  }

  function renderTopLabel(root, geom, title, valueText) {
    var cc = chromeColors();
    root.append('text').attr('x', 0).attr('y', -(geom.R + 58))
      .style('text-anchor', 'middle').style('font-family', FONT).style('font-weight', '700')
      .style('font-size', '14px').style('fill', cc.text).text(title || '');
    if (valueText != null) {
      root.append('text').attr('x', 0).attr('y', -(geom.R + 40))
        .style('text-anchor', 'middle').style('font-family', FONT)
        .style('font-size', '13px').style('fill', cc.text).text(valueText);
    }
  }

  // ===================== Linear (270deg) gauge =====================

  function scaleOf(domain) {
    return d3.scaleLinear().domain(domain).range([ARC_START, ARC_END]).clamp(true);
  }

  function renderLinearRing(root, cfg, geom) {
    var cc = chromeColors();
    var scale = scaleOf(cfg.domain);
    var value = cfg.value == null ? cfg.domain[0] : cfg.value;

    renderTrack(root, geom, ARC_START, ARC_END);

    if (cfg.value != null && cfg.bandHalfWidth > 0) {
      var lo = Math.max(cfg.domain[0], value - cfg.bandHalfWidth);
      var hi = Math.min(cfg.domain[1], value + cfg.bandHalfWidth);
      if (hi > lo) renderBandSegs(root, geom, bandSegments(value, lo, hi, scale, 60));
    }

    if (cfg.showTicks !== false) {
      (cfg.majorTicks || []).forEach(function (tv) {
        renderTickLabel(root, geom, scale(tv), cfg.tickFormat ? cfg.tickFormat(tv) : String(tv), cc.tick, cfg.tickFontSize);
      });
      if (cfg.minorPerInterval && cfg.majorTicks && cfg.majorTicks.length > 1) {
        var minors = [];
        for (var i = 0; i < cfg.majorTicks.length - 1; i++) {
          var a = cfg.majorTicks[i], b = cfg.majorTicks[i + 1];
          for (var k = 1; k <= cfg.minorPerInterval; k++) minors.push(scale(a + (b - a) * k / (cfg.minorPerInterval + 1)));
        }
        renderMinorTicks(root, geom, minors);
      }
    }

    if (cfg.value != null) renderMarker(root, geom, scale(value), cc.text);
  }

  function render(containerId, cfg) {
    var ctx = ensureSvg(containerId);
    if (!ctx) return;
    renderLinearRing(ctx.root, cfg, OUTER_GEOM);
    renderTopLabel(ctx.root, OUTER_GEOM, cfg.title, null);
    renderCenterText(ctx.root, Object.assign({ skipTitle: true }, cfg));
  }

  function renderDual(containerId, outerCfg, innerCfg) {
    var ctx = ensureSvg(containerId);
    if (!ctx) return;
    renderLinearRing(ctx.root, outerCfg, OUTER_GEOM);
    renderTopLabel(ctx.root, OUTER_GEOM, outerCfg.title, outerCfg.valueText);

    var innerRenderCfg = Object.assign({}, innerCfg, { showTicks: false });
    renderLinearRing(ctx.root, innerRenderCfg, INSET_GEOM);
    renderCenterText(ctx.root, Object.assign({ scale: 0.5, skipMinMax: true }, innerCfg));
  }

  // ===================== Compass (360deg) gauge =====================

  var COMPASS_LABELS = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];

  function ensurePersistentSvg(containerId) {
    var wrap = document.getElementById(containerId);
    if (!wrap || typeof d3 === 'undefined') return null;
    var svg = d3.select(wrap).select('svg');
    var isNew = svg.empty();
    if (isNew) {
      svg = d3.select(wrap).append('svg')
        .attr('viewBox', '0 0 ' + VIEW_W + ' ' + VIEW_H)
        .style('width', '100%').style('height', '100%').style('display', 'block');
    }
    var root = svg.select('g.gdiv-root');
    if (root.empty()) root = svg.append('g').attr('class', 'gdiv-root').attr('transform', 'translate(' + CX + ',' + CY + ')');
    return { root: root };
  }

  function ensureNamedGroup(root, className) {
    var g = root.select('g.' + className);
    var isNew = g.empty();
    if (isNew) g = root.append('g').attr('class', className);
    return { g: g, isNew: isNew };
  }

  function currentRotationDeg(node) {
    var t = d3.select(node).attr('transform');
    if (!t || t.indexOf('rotate') === -1) return 0;
    var m = /rotate\(([^,)]+)/.exec(t);
    return m ? parseFloat(m[1]) : 0;
  }

  function tweenMarkerRotation(g, targetDeg, immediate) {
    g.interrupt();
    if (immediate) { g.attr('transform', 'rotate(' + targetDeg + ')'); return; }
    var current = currentRotationDeg(g.node());
    var target = targetDeg;
    if (target - current > 180) target -= 360;
    else if (target - current < -180) target += 360;
    g.transition().duration(700).ease(d3.easeCubicOut)
      .attrTween('transform', function () {
        var interp = d3.interpolate(current, target);
        return function (t) { return 'rotate(' + interp(t) + ')'; };
      });
  }

  function ensureMarkerShape(g, geom, markerColor) {
    var line = g.select('line.gdiv-marker-line');
    if (line.empty()) {
      line = g.append('line').attr('class', 'gdiv-marker-line')
        .attr('x1', 0).attr('y1', -geom.R).attr('x2', 0).attr('y2', -(geom.R - geom.BAND))
        .style('stroke-width', 3.5).style('stroke-linecap', 'round');
    }
    line.style('stroke', markerColor);

    var tri = g.select('path.gdiv-marker-tri');
    if (tri.empty()) {
      var half = 5, baseR = geom.R - geom.BAND - 12, tipR = geom.R - geom.BAND - 2;
      tri = g.append('path').attr('class', 'gdiv-marker-tri')
        .attr('d', 'M' + (-half) + ',' + (-baseR) + ' L' + half + ',' + (-baseR) + ' L0,' + (-tipR) + ' Z');
    }
    tri.style('fill', markerColor);
  }

  function renderCompassRing(parentG, cfg, geom, showTicks, isNew) {
    var cc = chromeColors();

    if (isNew) {
      renderTrack(parentG, geom, 0, 360);
      if (showTicks) {
        COMPASS_LABELS.forEach(function (label, i) {
          var angleDeg = i * 45;
          parentG.append('line').attr('class', 'gdiv-cardinal-sep')
            .attr('transform', 'rotate(' + angleDeg + ')')
            .attr('x1', 0).attr('y1', -(geom.R - geom.BAND)).attr('x2', 0).attr('y2', -geom.R)
            .style('stroke', '#fff').style('stroke-width', 1.5);
          renderTickLabel(parentG, geom, angleDeg, label, cc.tick, 12);
        });
      }
    }
    if (showTicks) {
      parentG.selectAll('line.gdiv-tick-line').style('stroke', cc.tick);
      parentG.selectAll('text.gdiv-tick-text').style('fill', cc.tick);
    }

    var band = ensureNamedGroup(parentG, 'gdiv-band-group');
    band.g.selectAll('*').remove();
    if (cfg.value != null) {
      var half = cfg.bandHalfDeg == null ? 130 : cfg.bandHalfDeg;
      var lo = cfg.value - half, hi = cfg.value + half;
      var identity = function (v) { return v; };
      renderBandSegs(band.g, geom, bandSegments(cfg.value, lo, hi, identity, 90));
    }

    if (cfg.value != null) {
      var marker = ensureNamedGroup(parentG, 'gdiv-marker-group');
      ensureMarkerShape(marker.g, geom, cc.text);
      tweenMarkerRotation(marker.g, cfg.value, isNew);
    }
  }

  function renderCompass(containerId, cfg) {
    var ctx = ensurePersistentSvg(containerId);
    if (!ctx) return;

    var ring = ensureNamedGroup(ctx.root, 'gdiv-outer-ring');
    renderCompassRing(ring.g, cfg, OUTER_GEOM, true, ring.isNew);

    var top = ensureNamedGroup(ctx.root, 'gdiv-toplabel');
    top.g.selectAll('*').remove();
    renderTopLabel(top.g, OUTER_GEOM, cfg.title, null);

    var center = ensureNamedGroup(ctx.root, 'gdiv-centertext');
    center.g.selectAll('*').remove();
    renderCenterText(center.g, Object.assign({ skipTitle: true, skipMinMax: true }, cfg));
  }

  function renderCompassDual(containerId, outerCfg, innerCfg) {
    var ctx = ensurePersistentSvg(containerId);
    if (!ctx) return;

    var outerRing = ensureNamedGroup(ctx.root, 'gdiv-outer-ring');
    renderCompassRing(outerRing.g, outerCfg, OUTER_GEOM, true, outerRing.isNew);

    var top = ensureNamedGroup(ctx.root, 'gdiv-toplabel');
    top.g.selectAll('*').remove();
    renderTopLabel(top.g, OUTER_GEOM, outerCfg.title, outerCfg.valueText);

    var innerRing = ensureNamedGroup(ctx.root, 'gdiv-inner-ring');
    renderCompassRing(innerRing.g, innerCfg, INSET_GEOM, false, innerRing.isNew);

    var center = ensureNamedGroup(ctx.root, 'gdiv-centertext');
    center.g.selectAll('*').remove();
    renderCenterText(center.g, Object.assign({ scale: 0.5, skipMinMax: true }, innerCfg));
  }

  global.GaugeDiverging = {
    OUTER_GEOM: OUTER_GEOM, INSET_GEOM: INSET_GEOM,
    render: render, renderDual: renderDual,
    renderCompass: renderCompass, renderCompassDual: renderCompassDual
  };
})(window);