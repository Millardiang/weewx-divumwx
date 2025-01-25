<?php
include('dvmCombinedData.php');

if($theme==="light"){ echo "<body style='background-color:e0eafb'>";}
else if($theme==="dark"){ echo "<body style='background-color:#292E35'>";}
if($theme==="light"){ $textColor = "#1c4263";}
else if($theme==="dark"){ $textColor = "silver";}
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2023 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.       #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################
?>
<!DOCTYPE html> 
<html lang="en">
<head> 
<meta charset="utf-8">
<title>Solar Light Map</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div style="position:relative; top:10px;display:flex;justify-content:center;align-items:center;font-family:Helvetica;">
<?php
if($theme==="light"){echo "<font color='#1c4263'>"."Solar Light Map"."</font>";}
else if($theme==="dark"){echo "<font color='silver'>"."Solar Light Map"."</font>";}
?>
</div>

<style>
.solarmap{display:flex;justify-content:center;align-items:center;margin-top:20px;margin-left:0px;
text{fill:<?php if($theme==="light"){echo "#1c4263";} else if($theme==="dark"){echo "silver";}?>;font-family:Helvetica;font-size:7.5px;}
.axis path{stroke:<?php if($theme==="light"){echo "#1c4263";} else if($theme==="dark"){echo "#555";}?>;stroke-width:1;fill:none;} 
.axis line{stroke:<?php if($theme==="light"){echo "#1c4263";} else if($theme==="dark"){echo "#555";}?>;stroke-width:1;fill:none;shape-rendering:crispEdges;}}
</style> 
<script src="js/d3.7.9.0.min.js"></script>
<div class="solarmap"></div>

<script>
(function () {

var lat = <?php echo $lat;?>;
var lng = <?php echo $lon;?>;

// Math functions
function toDegrees(x) {
  return x * (180.0 / Math.PI);
}

function toRadians(x) {
  return x * (Math.PI / 180.0);
}

var dayMs = 1000 * 60 * 60 * 24,
    J1970 = 2440588,
    J2000 = 2451545;

function toJulian(date) { return date.valueOf() / dayMs - 0.5 + J1970;}
function fromJulian(j) { return new Date((j + 0.5 - J1970) * dayMs);}
function toDays(date) { return toJulian(date) - J2000;}

var e = toRadians(23.4397); // obliquity of the Earth

function rightAscension(l, b) { return Math.atan2(Math.sin(l) * Math.cos(e) - Math.tan(b) * Math.sin(e), Math.cos(l));}
function declination(l, b) { return Math.asin(Math.sin(b) * Math.cos(e) + Math.cos(b) * Math.sin(e) * Math.sin(l));}

function azimuth(H, phi, dec) { return Math.atan2(Math.sin(H), Math.cos(H) * Math.sin(phi) - Math.tan(dec) * Math.cos(phi));}
function altitude(H, phi, dec) { return Math.asin(Math.sin(phi) * Math.sin(dec) + Math.cos(phi) * Math.cos(dec) * Math.cos(H));}

function siderealTime(d, lw) { return toRadians(280.16 + 360.9856235 * d) - lw;}

function astroRefraction(h) {
    if (h < 0) // the following formula works for positive altitudes only.
        h = 0; // if h = -0.08901179 a div/0 would occur.

    // 1.02 / Math.tan(h + 10.26 / (h + 5.10)) h in degrees, result in arc minutes -> converted to rad:
    return 0.0002967 / Math.tan(h + 0.00312536 / (h + 0.08901179));
}

function solarMeanAnomaly(d) { return toRadians(357.5291 + 0.98560028 * d);}

function eclipticLongitude(M) {

    var C = toRadians(1.9148 * Math.sin(M) + 0.02 * Math.sin(2 * M) + 0.0003 * Math.sin(3 * M)), // equation of center
        P = toRadians(102.9372); // perihelion of the Earth

    return M + C + P + Math.PI;
}

function sunCoords(d) {

    var M = solarMeanAnomaly(d),
        L = eclipticLongitude(M);

    return {
        dec: declination(L, 0),
        ra: rightAscension(L, 0)
    };
}


var SunCalc = {};


// calculates sun position for a given date and latitude/longitude

SunCalc.getPosition = function(date, lat, lng) {

    var lw  = toRadians(-lng),
        phi = toRadians(lat),
        d   = toDays(date),

        c  = sunCoords(d),
        H  = siderealTime(d, lw) - c.ra;

    return {
        azimuth: azimuth(H, phi, c.dec),
        altitude: altitude(H, phi, c.dec)
    };
};


// sun times configuration (angle, morning name, evening name)

var times = SunCalc.times = [
    [-0.833, 'sunrise',       'sunset'      ],
    [  -0.3, 'sunriseEnd',    'sunsetStart' ],
    [    -6, 'dawn',          'dusk'        ],
    [   -12, 'nauticalDawn',  'nauticalDusk'],
    [   -18, 'nightEnd',      'night'       ],
    [     6, 'goldenHourEnd', 'goldenHour'  ]
];

// adds a custom time to the times config

SunCalc.addTime = function(angle, riseName, setName) {
    times.push([angle, riseName, setName]);
};


// calculations for sun times

var J0 = 0.0009;

function julianCycle(d, lw) { return Math.round(d - J0 - lw / (2 * Math.PI));}

function approxTransit(Ht, lw, n) { return J0 + (Ht + lw) / (2 * Math.PI) + n;}
function solarTransitJ(ds, M, L) { return J2000 + ds + 0.0053 * Math.sin(M) - 0.0069 * Math.sin(2 * L);}

function hourAngle(h, phi, d) { return Math.acos((Math.sin(h) - Math.sin(phi) * Math.sin(d)) / (Math.cos(phi) * Math.cos(d)));}

// returns set time for the given sun altitude
function getSetJ(h, lw, phi, dec, n, M, L) {

    var w = hourAngle(h, phi, dec),
        a = approxTransit(w, lw, n);
    return solarTransitJ(a, M, L);
}


// calculates sun times for a given date and latitude/longitude

SunCalc.getTimes = function(date, lat, lng) {

    var lw = toRadians(-lng),
        phi = toRadians(lat),

        d = toDays(date),
        n = julianCycle(d, lw),
        ds = approxTransit(0, lw, n),

        M = solarMeanAnomaly(ds),
        L = eclipticLongitude(M),
        dec = declination(L, 0),

        Jnoon = solarTransitJ(ds, M, L),

        i, len, time, Jset, Jrise;


    var result = {
        solarNoon: fromJulian(Jnoon),
        nadir: fromJulian(Jnoon - 0.5)
    };

    for (i = 0, len = times.length; i < len; i += 1) {
        time = times[i];

        Jset = getSetJ(time[0] * Math.PI/180.0, lw, phi, dec, n, M, L);
        Jrise = Jnoon - (Jset - Jnoon);

        result[time[1]] = fromJulian(Jrise);
        result[time[2]] = fromJulian(Jset);
    }

    return result;
};


// moon calculations, based on http://aa.quae.nl/en/reken/hemelpositie.html formulas

function moonCoords(d) { // geocentric ecliptic coordinates of the moon

    var L = toRadians(218.316 + 13.176396 * d), // ecliptic longitude
        M = toRadians(134.963 + 13.064993 * d), // mean anomaly
        F = toRadians(93.272 + 13.229350 * d),  // mean distance

        l  = L + toRadians(6.289 * Math.sin(M)), // longitude
        b  = toRadians(5.128 * Math.sin(F)),     // latitude
        dt = 385001 - 20905 * Math.cos(M);  // distance to the moon in km

    return {
        ra: rightAscension(l, b),
        dec: declination(l, b),
        dist: dt
    };
}

SunCalc.getMoonPosition = function(date, lat, lng) {

    var lw  = toRadians(-lng),
        phi = toRadians(lat),
        d   = toDays(date),

        c = moonCoords(d),
        H = siderealTime(d, lw) - c.ra,
        h = altitude(H, phi, c.dec),
        pa = Math.atan2(Math.sin(H), Math.tan(phi) * Math.cos(c.dec) - Math.sin(c.dec) * Math.cos(H));

    h = h + astroRefraction(h); // altitude correction for refraction

    return {
        azimuth: azimuth(H, phi, c.dec),
        altitude: h,
        distance: c.dist,
        parallacticAngle: pa
    };
};

SunCalc.getMoonIllumination = function(date) {

    var d = toDays(date),
        s = sunCoords(d),
        m = moonCoords(d),

        sdist = 149598000, // distance from Earth to Sun in km

        phi = Math.acos(Math.sin(s.dec) * Math.sin(m.dec) + Math.cos(s.dec) * Math.cos(m.dec) * Math.cos(s.ra - m.ra)),
        inc = Math.atan2(sdist * Math.sin(phi), m.dist - sdist * Math.cos(phi)),
        angle = Math.atan2(Math.cos(s.dec) * Math.sin(s.ra - m.ra), Math.sin(s.dec) * Math.cos(m.dec) -
                Math.cos(s.dec) * Math.sin(m.dec) * Math.cos(s.ra - m.ra));

    return {
        fraction: (1 + Math.cos(inc)) / 2,
        phase: 0.5 + 0.5 * inc * (angle < 0 ? -1 : 1) / Math.PI,
        angle: angle
    };   
};


function hoursLater(date, h) {
    return new Date(date.valueOf() + h * dayMs / 24);
}

// calculations for moon rise/set times are based on http://www.stargazing.net/kepler/moonrise.html article

SunCalc.getMoonTimes = function(date, lat, lng, inUTC) {
    var t = new Date(date);
    if (inUTC) t.setUTCHours(0, 0, 0, 0);
    else t.setHours(0, 0, 0, 0);

    var hc = 0.133 * Math.PI/180.0,
        h0 = SunCalc.getMoonPosition(t, lat, lng).altitude - hc,
        h1, h2, rise, set, a, b, xe, ye, d, roots, x1, x2, dx;

    // go in 2-hour chunks, each time seeing if a 3-point quadratic curve crosses zero (which means rise or set)
    for (var i = 1; i <= 24; i += 2) {
        h1 = SunCalc.getMoonPosition(hoursLater(t, i), lat, lng).altitude - hc;
        h2 = SunCalc.getMoonPosition(hoursLater(t, i + 1), lat, lng).altitude - hc;

        a = (h0 + h2) / 2 - h1;
        b = (h2 - h0) / 2;
        xe = -b / (2 * a);
        ye = (a * xe + b) * xe + h1;
        d = b * b - 4 * a * h1;
        roots = 0;

        if (d >= 0) {
            dx = Math.sqrt(d) / (Math.abs(a) * 2);
            x1 = xe - dx;
            x2 = xe + dx;
            if (Math.abs(x1) <= 1) roots++;
            if (Math.abs(x2) <= 1) roots++;
            if (x1 < -1) x1 = x2;
        }

        if (roots === 1) {
            if (h0 < 0) rise = i + x1;
            else set = i + x1;

        } else if (roots === 2) {
            rise = i + (ye < 0 ? x2 : x1);
            set = i + (ye < 0 ? x1 : x2);
        }

        if (rise && set) break;

        h0 = h2;
    }

    var result = {};

    if (rise) result.rise = hoursLater(t, rise);
    if (set) result.set = hoursLater(t, set);

    if (!rise && !set) result[ye > 0 ? 'alwaysUp' : 'alwaysDown'] = true;

    return result;
};


// export as AMD module / Node module / browser variable
if (typeof define === 'function' && define.amd) define(SunCalc);
else if (typeof module !== 'undefined') module.exports = SunCalc;
else window.SunCalc = SunCalc;

}());

var w = 620;
var h = 320;
var padding = 25;
var padding_up = 8;

var now = new Date(),
    start = d3.timeYear.floor(now),
    end = d3.timeYear.ceil(now);

var x = d3.scaleTime()
  .range([padding, w - padding + 16])
  .domain([start, end]);

var y = d3.scaleLinear()
  .range([h - padding, padding_up])
  .domain([-80, 80]);

var color = d3.scaleLinear()
  .domain([90, 60, 30, 0])
  .range(['#d7191c', '#fdae61', '#abd9e9', '#2c7bb6']);

var xAxis = d3.axisBottom(x)
  .ticks(12)
  .tickSize(4)
  .tickPadding(2);

var yAxis = d3.axisLeft(y)
  .ticks(9)
  .tickSize(4)
  .tickPadding(2)
  .tickFormat(function(d) { return d + "°";})
  .tickValues([-80, -60, -40, -20, 0, 20, 40, 60, 80]); 

var svg = d3.select('.solarmap')
  .append('svg')
  .attr('width', w)
  .attr('height', h);

var data = [],
    latitudes = y.ticks(90),
    days = d3.range(0, 365, 2).map(function (i) { return d3.timeDay.offset(start, i); });

for (var i = 0, len = latitudes.length - 1; i < len; i++) {
  for (var j = 0, len2 = days.length - 1; j < len2; j++) {

    var day1 = days[j],
        day2 = days[j + 1],
        lat1 = latitudes[i],
        lat2 = latitudes[i + 1],
        day = new Date((day1.valueOf() + day2.valueOf()) / 2),
        lat = (lat1 + lat2) / 2;

    var solarNoon = SunCalc.getTimes(day, lat, 0).solarNoon;
    var altitude = SunCalc.getPosition(solarNoon, lat, 0).altitude * 180 / Math.PI;

    data.push({
      day1: day1,
      day2: day2,
      lat1: lat1,
      lat2: lat2,
      altitude: altitude
    });
  }
}

svg.selectAll('.cell')
  .data(data)
  .enter().append('rect')
  .attr('x', function (d) { return x(d.day1); })
  .attr('y', function (d) { return y(d.lat2); })
  .attr('width', function (d) { return x(d.day2) - x(d.day1); })
  .attr('height', function (d) { return y(d.lat1) - y(d.lat2); })
  .attr('fill', function (d) { return color(d.altitude); });

svg
    .append('g')
    .attr("class", "xScale axis")
    .attr('transform', 'translate(0,' + (h - padding) + ')')
    .call(xAxis);

  svg
    .append('g')
    .attr("class", "yScale axis")
    .attr('transform', 'translate(' + padding + ',0)')
    .call(yAxis);

</script>
</body>
</html>