/*
##############################################################################################
# gaugeDial.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
#
#  Shared dial renderer used by cardAnemometer.js, cardBarometer.js, cardSolarRadiation.js,
#  cardUvIndex.js and cardHumidity.js, so all five gauges share exactly the same geometry,
#  tick styling, and pointer style. Keeping this in one place is deliberate: five copies of
#  the same trig would drift out of sync the first time any one of them got tweaked.
# ============================================================================================
*/

(function (global) {
  'use strict';

  var MONO_FONT = "'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace";

  var GEOM = { W: 310, H: 155, cx: 155, cy: 88, R: 62, BAND: 13 };

  function renderTrack(svg, cfg) {

    var arcGen = d3.arc().innerRadius(cfg.R - cfg.BAND).outerRadius(cfg.R)
      .startAngle(-135 * Math.PI / 180).endAngle(135 * Math.PI / 180);
    svg.append('path')
      .attr('transform', 'translate(' + cfg.cx + ',' + cfg.cy + ')')
      .attr('d', arcGen())
      .style('fill', cfg.trackColor || 'var(--bs-secondary-bg, #d9dde1)');
  }

  function renderBands(svg, cfg, bands) {

    var arcScale = d3.scaleLinear().domain(cfg.domain).range([-135, 135]).clamp(true);
    var arcGen = d3.arc().innerRadius(cfg.R - cfg.BAND).outerRadius(cfg.R);
    svg.selectAll(null)
      .data(bands).enter().append('path')
      .attr('transform', 'translate(' + cfg.cx + ',' + cfg.cy + ')')
      .attr('d', function (d) {
        return arcGen.startAngle(arcScale(d[0]) * Math.PI / 180).endAngle(arcScale(d[1]) * Math.PI / 180)();
      })
      .style('fill', function (d) { return d[2]; });
  }

  function renderTicks(svg, cfg) {

    var arcScale = d3.scaleLinear().domain(cfg.domain).range([-135, 135]).clamp(true);
    var g = svg.append('g');
    cfg.ticks.forEach(function (tv) {
      var angleDeg = arcScale(tv);
      var flip = angleDeg > 90 || angleDeg < -90;
      var tg = g.append('g').attr('transform', 'translate(' + cfg.cx + ',' + cfg.cy + ') rotate(' + angleDeg + ')');
      var lg = tg.append('g')
        .attr('transform', 'translate(0,' + (-(cfg.R + 16)) + ')' + (flip ? ' rotate(180)' : ''));
      lg.append('text')
        .attr('text-anchor', 'middle').attr('dy', '0.32em')
        .style('font-family', MONO_FONT)
        .style('font-size', '7px')
        .style('fill', cfg.textColor || 'var(--bs-secondary-color)')
        .text(cfg.format ? cfg.format(tv) : tv);
    });
  }

  function renderPointer(svg, cfg) {

    var arcScale = d3.scaleLinear().domain(cfg.domain).range([-135, 135]).clamp(true);
    var angleDeg = arcScale(cfg.value);
    var innerR = cfg.R - cfg.BAND;
    var g = svg.append('g').attr('transform', 'translate(' + cfg.cx + ',' + cfg.cy + ') rotate(' + angleDeg + ')');
    g.append('line')
      .attr('x1', 0).attr('y1', -(cfg.R + 3)).attr('x2', 0).attr('y2', -(innerR - 3))
      .style('stroke', cfg.color || '#1c1c1c').style('stroke-width', 2).style('stroke-linecap', 'round');
    var triTip = innerR - 6, triSize = 4.5;
    g.append('path')
      .attr('d', 'M0,' + (-(triTip - triSize * 1.7)) + ' L' + (-triSize) + ',' + (-triTip) + ' L' + triSize + ',' + (-triTip) + ' Z')
      .style('fill', cfg.color || '#1c1c1c');
  }

  function renderValue(svg, cfg) {

    svg.append('text').attr('x', cfg.cx).attr('y', cfg.y).style('text-anchor', 'middle')
      .style('font-family', MONO_FONT).style('font-weight', '700')
      .style('font-size', (cfg.fontSize || 19) + 'px').style('fill', cfg.color || 'var(--bs-body-color)')
      .text(cfg.text);
  }

  function renderMinMax(svg, cfg) {

    var g = svg.append('g').style('font-family', MONO_FONT).style('font-size', '8px')
      .style('fill', cfg.textColor || 'var(--bs-secondary-color)');
    var gap = 10;
    var minW = String(cfg.minText).length * 4.3 + 8;
    var maxW = String(cfg.maxText).length * 4.3 + 8;
    var totalW = minW + maxW + gap;
    var minX = cfg.cx - totalW / 2 + minW / 2;
    var maxX = cfg.cx + totalW / 2 - maxW / 2;
    g.append('text').attr('x', minX).attr('y', cfg.y).style('text-anchor', 'middle').text('\u25BE ' + cfg.minText);
    g.append('text').attr('x', maxX).attr('y', cfg.y).style('text-anchor', 'middle').text('\u25B4 ' + cfg.maxText);
  }

  var CORNER_X = { left: 44, right: 266 };

  function renderCornerStat(svg, cfg) {

    svg.append('text').attr('x', cfg.x).attr('y', cfg.y).style('text-anchor', 'middle')
      .style('font-family', MONO_FONT).style('font-size', '6.5px')
      .style('fill', 'var(--bs-secondary-color)').text(cfg.label);
    svg.append('text').attr('x', cfg.x).attr('y', cfg.y + 10).style('text-anchor', 'middle')
      .style('font-family', MONO_FONT).style('font-weight', '600').style('font-size', '8px')
      .style('fill', cfg.color || 'var(--bs-body-color)').text(cfg.value);
  }

  function renderCornerStats(svg, cfg) {

    var slots = [
      { x: CORNER_X.left,  y: cfg.topY },
      { x: CORNER_X.right, y: cfg.topY },
      { x: CORNER_X.left,  y: cfg.bottomY },
      { x: CORNER_X.right, y: cfg.bottomY }
    ];
    cfg.items.slice(0, 4).forEach(function (it, i) {
      renderCornerStat(svg, { x: slots[i].x, y: slots[i].y, label: it.label, value: it.value, color: it.color });
    });
  }

  global.GaugeDial = {
    FONT: MONO_FONT,
    GEOM: GEOM,
    renderTrack: renderTrack,
    renderBands: renderBands,
    renderTicks: renderTicks,
    renderPointer: renderPointer,
    renderValue: renderValue,
    renderMinMax: renderMinMax,
    renderCornerStat: renderCornerStat,
    renderCornerStats: renderCornerStats
  };
})(window);
