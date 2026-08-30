/*
##############################################################################################
# iopctrl.js version 0.0.1
#  Copyright (C) 2026 Ian Millard, Sean Balfour
#  GPLv3
##############################################################################################
*/

/* ===================================================================================================
   iopctrl Function Library for Gauges (d3-7.9.0.min.js) - Fully Scoped. Sean Balfour April 27th 2026
   =================================================================================================== */
var iopctrl = (function() {
    var iopctrl = { version: "7.6.1" };

    const i_extent = (d) => (d && d.length > 0) ? (d[0] < d[d.length - 1] ? [d[0], d[d.length - 1]] : [d[d.length - 1], d[0]]) : [0, 0];
    const i_rng = (s) => {
        if (!s || !s.range) return [0, 0];
        var r = s.range();

        return [r[0], r[r.length - 1]];
    };

    iopctrl.arcaxis = function() {
        var scale = d3.scaleLinear(), outerRadius = 100, tickPadding = 20,
            tickArguments_ = [10], tickSubdivide = 9, tickFormat_ = null, orient = "out";

        function arcaxis(g) {
        g.each(function() {
            var s = d3.select(this);
            var ticks = scale.ticks ? scale.ticks.apply(scale, tickArguments_) : scale.domain();

            // --- UPDATED FORMAT LOGIC ---

            var fmt = (typeof tickFormat_ === "function") ? tickFormat_ :
                      (scale.tickFormat ? scale.tickFormat.apply(scale, tickArguments_) : d3.format("d"));

            var subticks = [];
            if (tickSubdivide > 0 && ticks.length > 1) {
                var step = (ticks[1] - ticks[0]) / (tickSubdivide + 1);
                for (var i = 0; i < ticks.length - 1; i++) {
                    for (var j = 1; j <= tickSubdivide; j++) { subticks.push(ticks[i] + j * step); }
                }
            }

                s.selectAll(".tick.minor").data(subticks).join("line").attr("class", "tick minor")
                    .each(function(d) {
                        const a = scale(d);
                        if (isNaN(a)) return;
                        d3.select(this).attr("x1", outerRadius * Math.sin(a)).attr("y1", -outerRadius * Math.cos(a))
                            .attr("x2", (outerRadius + 7) * Math.sin(a)).attr("y2", -(outerRadius + 7) * Math.cos(a));
                    });

                var major = s.selectAll(".tick.major").data(ticks).join("g").attr("class", "tick major");
                major.selectAll("line").data(d => [d]).join("line")
                    .each(function(d) {
                        const a = scale(d);
                        if (isNaN(a)) return;
                        d3.select(this).attr("x1", outerRadius * Math.sin(a)).attr("y1", -outerRadius * Math.cos(a))
                            .attr("x2", (outerRadius + 15) * Math.sin(a)).attr("y2", -(outerRadius + 15) * Math.cos(a));
                    });

                major.selectAll("text").data(d => [d]).join("text").attr("class", "major unselectable")
                    .each(function(d) {
                        const a = scale(d);
                        if (isNaN(a)) return;
                        const r = (orient === "in") ? (outerRadius - tickPadding) : (outerRadius + tickPadding);

                        d3.select(this).attr("transform", `translate(${r * Math.sin(a)}, ${-r * Math.cos(a)})`)
                            .attr("text-anchor", "middle").attr("dy", ".35em").text(fmt(d));
                    });

                s.selectAll("path.domain").data([0]).join("path").attr("class", "domain")
                    .attr("d", d3.arc().startAngle(scale.range()[0]).endAngle(scale.range()[scale.range().length-1]).innerRadius(outerRadius).outerRadius(outerRadius));
            });
        }
        arcaxis.tickFormat = function(x) { if (!arguments.length) return tickFormat_; tickFormat_ = x; return arcaxis; };
        arcaxis.scale = function(x) { if (!arguments.length) return scale; scale = x; return arcaxis; };
        arcaxis.outerRadius = function(x) { if (!arguments.length) return outerRadius; outerRadius = x; return arcaxis; };
        arcaxis.ticks = function(x) { tickArguments_ = [x]; return arcaxis; };
        arcaxis.tickSubdivide = function(x) { if (!arguments.length) return tickSubdivide; tickSubdivide = x; return arcaxis; };
        arcaxis.tickPadding = function(x) { if (!arguments.length) return tickPadding; tickPadding = x; return arcaxis; };
        arcaxis.orient = function(x) { if (!arguments.length) return orient; orient = x; return arcaxis; };
        return arcaxis;
    };

    iopctrl.arcslider = function() {
        var radius = 100, transitionDuration = 500, axis = iopctrl.arcaxis();
        var _indicator, _currentValue, _currentRad, _cursorArc, _pointerUpdate, _cursorUpdate;

        function arcslider(g) {
            var range = i_rng(axis.scale());
            g.each(function() {
                var s = d3.select(this).classed("gauge", true);
                s.selectAll(".arc-lane").data([0]).join("path").attr("class", "arc-lane").style("fill", "none");

                _cursorArc = d3.arc().startAngle(range[0]).innerRadius(0.5 * radius).outerRadius(radius);
                _cursorUpdate = s.selectAll(".arc-cursor").data([0]).join("path").attr("class", "arc-cursor").style("fill", "none");

                s.selectAll(".gauge-axis-group").data([0]).join("g").attr("class", "gauge-axis-group").call(axis);

                var group = s.selectAll(".needle-group").data([0]).join("g").attr("class", "needle-group");
                _pointerUpdate = group.selectAll(".needle-pointer").data([0]).join("g").attr("class", "needle-pointer");

                if (_indicator) _pointerUpdate.call(_indicator, radius);

                _currentRad = range[0];
                redraw(axis.scale().invert(range[0]), 0);
            });
        }

        function redraw(value, td) {
            const s = axis.scale();
            if (!s || isNaN(s(value))) return;
            const dur = (typeof td !== "undefined") ? td : transitionDuration;
            const targetRad = s(value);
            const range = i_rng(s);
            const startRad = (typeof _currentRad === "undefined") ? range[0] : _currentRad;

            _cursorUpdate.transition().duration(dur).attrTween("d", function() {
                return function(t) {
                    _currentRad = startRad + (targetRad - startRad) * t;
                    const rot = 180 * _currentRad / Math.PI;
                    if (!isNaN(rot)) _pointerUpdate.attr("transform", `rotate(${rot})`);
                    return _cursorArc.endAngle(_currentRad)();
                };
            });
        }
        arcslider.radius = function(x) { if (!arguments.length) return radius; radius = x; axis.outerRadius(x); return arcslider; };
        arcslider.axis = function(x) { if (!arguments.length) return axis; axis = x; return arcslider; };
        arcslider.indicator = function(x) { if (!arguments.length) return _indicator; _indicator = x; return arcslider; };
        arcslider.value = function(x) { if (!arguments.length) return _currentValue; redraw(x); return arcslider; };
        arcslider.transitionDuration = function(x) { if (!arguments.length) return transitionDuration; transitionDuration = x; return arcslider; };
        return arcslider;
    };
    return iopctrl;
})();