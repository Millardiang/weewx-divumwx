/*
##############################################################################################
# mapShading.js version 1.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
  Shading for the Equal Earth maps in the Earth Daylight card's colours:
  ocean and land graded from light, under the Sun, to dark on the far side
  of the Earth, and night shaded in four deepening steps.

  Used by js/equalEarthMap.js (the Equal Earth page and the World Daylight
  Map), modalEarthquakeMap.html, modalBoltekLightning.html and
  js/lightningLive.js. The colours are css/equalEarthMap.css's --map-*
  tokens; this file only does the geometry. Needs d3 (v7) loaded first.

  The gradient is laid out on the globe, not on the screen: a colour for
  each ring of equal distance from the point under the Sun (every 4
  degrees), so it is the same in every projection and stays right where a
  flat map wraps round.

      DivumMapShading.rings(sun)                 // [{ geo, t }], far to near
      DivumMapShading.colour(near, far, t)       // colour t of the way to far
      DivumMapShading.night(anti)                // the four night circles
      DivumMapShading.fillCanvas(ctx, path, geo, near, far, sun)
      DivumMapShading.fillSvg(parent, path, geo, near, far, sun, clipId)
      DivumMapShading.tokens(el)                 // --map-* colours at el
*/
(function(){
  'use strict';
  if (window.DivumMapShading) return;

  var STEP = 4;                         // degrees between gradient rings
  var NIGHT_RADII = [90, 84, 78, 72];   // Sun 0, 6, 12 and 18 degrees below the horizon
  var SPHERE = { type: 'Sphere' };

  var cacheKey = null, cache = null;
  function rings(sun){
    var key = sun[0].toFixed(2) + ',' + sun[1].toFixed(2);
    if (key === cacheKey) return cache;
    var list = [];
    for (var r = 180 - STEP; r >= STEP; r -= STEP) {
      list.push({ geo: d3.geoCircle().center(sun).radius(r).precision(2)(), t: r / 180 });
    }
    cacheKey = key; cache = list;
    return list;
  }

  var mixers = {};
  function colour(near, far, t){
    var k = near + '|' + far;
    if (!mixers[k]) mixers[k] = d3.interpolateRgb(near, far);
    return mixers[k](t);
  }

  function night(anti){
    return NIGHT_RADII.map(function(r){ return d3.geoCircle().center(anti).radius(r)(); });
  }

  // Fill `geo` (the Sphere, or land) on a canvas. `path` is a d3.geoPath
  // drawing into ctx.
  function fillCanvas(ctx, path, geo, near, far, sun){
    ctx.save();
    if (geo.type !== 'Sphere') { ctx.beginPath(); path(geo); ctx.clip(); }
    ctx.beginPath(); path(SPHERE); ctx.fillStyle = far; ctx.fill();
    var list = rings(sun);
    for (var i = 0; i < list.length; i++) {
      ctx.beginPath(); path(list[i].geo); ctx.fillStyle = colour(near, far, list[i].t); ctx.fill();
    }
    ctx.restore();
  }

  // Fill `geo` in an SVG: appends a clipped group to `parent` (a d3
  // selection) and returns it. clipId must be unique in the page.
  function fillSvg(parent, path, geo, near, far, sun, clipId){
    parent.append('clipPath').attr('id', clipId).append('path').attr('d', path(geo));
    var g = parent.append('g').attr('clip-path', 'url(#' + clipId + ')');
    g.append('path').attr('d', path(SPHERE)).style('fill', far);
    rings(sun).forEach(function(r){
      var d = path(r.geo);
      if (d) g.append('path').attr('d', d).style('fill', colour(near, far, r.t));
    });
    return g;
  }

  function tokens(el){
    var cs = getComputedStyle(el || document.body), v = function(k){ return cs.getPropertyValue(k).trim(); };
    return {
      ocean: v('--map-ocean') || '#D4E2FF', oceanFar: v('--map-ocean-far') || '#5B7FC7',
      land: v('--map-land') || '#5FD18F', landFar: v('--map-land-far') || '#1F5C3D',
      night: v('--map-night') || '14, 17, 22',
      nightAlphas: [1, 2, 3, 4].map(function(i, k){
        var a = parseFloat(v('--map-night-a' + i));
        return isFinite(a) ? a : [0.35, 0.2308, 0.20, 0.25][k];
      })
    };
  }

  window.DivumMapShading = {
    rings: rings, colour: colour, night: night,
    fillCanvas: fillCanvas, fillSvg: fillSvg, tokens: tokens,
    NIGHT_RADII: NIGHT_RADII
  };
})();
