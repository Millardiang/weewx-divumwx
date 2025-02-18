<?php
include('dvmCombinedData.php');
if($theme==="light"){ echo "<body style='background-color:e0eafb'>"; }
else if($theme==="dark"){ echo "<body style='background-color:#292E35'>"; }
##############################################################################################
#        ________   __  ___      ___  ____  ____  ___      ___    __   __  ___  ___  ___     #
#       |"      "\ |" \|"  \    /"  |("  _||_ " ||"  \    /"  |  |"  |/  \|  "||"  \/"  |    #
#       (.  ___  :)||  |\   \  //  / |   (  ) : | \   \  //   |  |'  /    \:  | \   \  /     #
#       |: \   ) |||:  | \\  \/. ./  (:  |  | . ) /\\  \/.    |  |: /'        |  \\  \/      #
#       (| (___\ |||.  |  \.    //    \\ \__/ // |: \.        |   \//  /\'    |  /\.  \      #
#       |:       :)/\  |\  \\   /     /\\ __ //\ |.  \    /:  |   /   /  \\   | /  \   \     #
#       (________/(__\_|_)  \__/     (__________)|___|\__/|___|  |___/    \___||___/\___|    #
#                                                                                            #
#     Copyright (C) 2025 Ian Millard, Steven Sheeley, Sean Balfour. All rights reserved      #
#      Distributed under terms of the GPLv3. See the file LICENSE.txt for your rights.       #
#    Issues for weewx-divumwx skin template are only addressed via the issues register at    #
#                    https://github.com/Millardiang/weewx-divumwx/issues                     #
##############################################################################################
// Sean Jan 25th 2025
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">

<style>
.solarTerminator { display:flex; justify-content:center; align-items:center; margin-top:33px; margin-left:0px; }
.ocean { fill: url(#oceanGradient); }
.graticule { fill: none; stroke: #444; stroke-width: 0.2px; stroke-opacity: 1.0; }
.land { fill: url(#landGradient); }
.borders { fill: none; stroke: #292E35; stroke-width: 0.2px; }
.night { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.2; } 
.civiltwilight { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.35; }
.nauticaltwilight { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.5; }
.astronomicaltwilight { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.6; }

.dot { fill: red; }
.ring { fill: none; stroke: red; stroke-width: 1px; }

.meridian180 { stroke: red; stroke-width: 0.5px; fill: none; }
.gmt_meridian { stroke: red; stroke-width: 0.5px; fill: none; }
.equator { stroke: red; stroke-width: 0.5px; fill: none; }
.capricorn { stroke: blue; stroke-width: 0.5px; fill: none; stroke-dasharray: 3, 3; }
.cancer { stroke: blue; stroke-width: 0.5px; fill: none; stroke-dasharray: 3, 3; }
.arctic { stroke: magenta; stroke-width: 0.5px; fill: none; stroke-dasharray: 3, 3; }
.antarctic { stroke: magenta; stroke-width: 0.5px; fill: none; stroke-dasharray: 3, 3; }
.ecliptic { stroke: yellow; stroke-width: 0.5px; fill: none; }

.sun { mask: url(#globeMaskSun); }
.sun { fill: url(#sunGradient); }
.moon { mask: url(#globeMaskMoon); }
.moon { fill: url(#moonGradient); }

#globeMaskSun rect { fill: #fff; }
#globeMaskMoon rect { fill: #fff; }
#globeMaskCircleSun { fill: #000; opacity: 0.0; }
#globeMaskCircleMoon { fill: #000; opacity: 0.0; }
#globeMaskCircleSun.behind { opacity: 1.0; }
#globeMaskCircleMoon.behind { opacity: 1.0; }
</style>

<body>

<script src="js/d3.7.9.0.min.js"></script>
<script src="js/d3-geo-projection.4.0.0.min.js"></script>
<script src="js/topojson.3.0.2.min.js"></script>
<div class="solarTerminator"></div>
<script>

// refresh the script every 60 seconds
window.setInterval('refresh()', 60000);     
    function refresh() {
        window.location.reload();
  }


  var π = Math.PI;

function toDegrees(x) {
  return x * (180.0 / π);
}

function toRadians(x) {
  return x * (π / 180.0);
}

  var moonDec = <?php echo $alm["moon_declination"];?>;
  var ecliptic = <?php echo $alm["ecliptic_angle"];?>;
  var lunarTerminator = <?php echo $alm["moon_ecliptic_angle"];?>; 
  var lat = <?php echo $lat;?>;
  var long = <?php echo $lon;?>;

  var w = 790;
  var h = 500;

if (lat > 0.0) {
  // northern hemispehre tilt globe forwards
  var earth = d3.geoOrthographic()
    .scale(200)
    .translate([w/2, h/2])
    .clipAngle(90)
    .rotate([-long, -ecliptic, -ecliptic])
    .precision(0.1);

  var skynet = d3.geoOrthographic()
    .scale(235)
    .rotate([-long, -ecliptic, -ecliptic])
    .translate(earth.translate());

  var lunar = d3.geoOrthographic()
    .scale(217)
    .rotate([-long, -ecliptic, -ecliptic])
    .translate(earth.translate());
} else {
  // southern hemisphere titlt globe backwards
  var earth = d3.geoOrthographic()
    .scale(200)
    .translate([w/2, h/2])
    .clipAngle(90)
    .rotate([-long, ecliptic, -ecliptic])
    .precision(0.1);

  var skynet = d3.geoOrthographic()
    .scale(235)
    .rotate([-long, ecliptic, -ecliptic])
    .translate(earth.translate());

  var lunar = d3.geoOrthographic()
    .scale(217)
    .rotate([-long, ecliptic, -ecliptic])
    .translate(earth.translate());
}

  var circle0 = d3.geoCircle().radius(90); // night time (sunrise sunset)
  var circle1 = d3.geoCircle().radius(84); // civil twilight (dawn dusk) -6°
  var circle2 = d3.geoCircle().radius(78); // nautical twilight (dawn dusk) -12°
  var circle3 = d3.geoCircle().radius(72); // astronomical twilight (dawn dusk) -18°

  var path = d3.geoPath().projection(earth);
  var graticule = d3.geoGraticule();

  var center = [w/2, h/2];

  var svg = d3.select(".solarTerminator")
      .append("svg")
      //.style("background", "red") 
      .attr("width", w)
      .attr("height", h);

  // create a random map of stars
  var dots = 75;  
  var stars = (function() {
  var result = [];
  for(var i = 0; i < 1 * dots; i++) {
      result.push({
          id: 1,
          position: [Math.random() * w, Math.random() * h],
          color: d3.rgb(0,150,250),
          radius: 0.5,
          fillOpacity: 1,
          stroke: d3.rgb(0,150,250)
      });
      result.push({
          id: 1,
          position: [Math.random() * w, Math.random() * h],
          color: d3.rgb(0,150,250),
          radius: 0.5,
          fillOpacity: 1,
          stroke: d3.rgb(0,150,250)
        });
      }
      return result;
    });

  svg.selectAll("stars")
      .data(stars).enter()
      .append("circle")
      .attr("class", "stars")
      .attr("id", (d, i) => { return "stars" + i; })
      .attr("r", d => { return d.radius; })
      .attr("cx", d => { return d.position[0]; })
      .attr("cy", d => { return d.position[1]; })
      .style("fill", d => { return d.color; })
      .style("fill-opacity", d => { return d.fillOpacity; })
      .style("stroke", d => { return d.stroke; });

  var defs = svg.append("defs");
    
  // sun and moon scale factor for a 3D effect
  var sunScale = d3.scaleLinear().domain([0,1]).range([5,25]);
  var moonScale = d3.scaleLinear().domain([0,1]).range([2,8]);

  d3.json("jsondata/worldmap.json").then(function(world) {

  buildDefs();
  buildGlobe();

  function buildDefs() {

  defs.append("path")
      .datum({type: "Sphere"})
      .attr("id", "sphere")
      .attr("d", path);

  var globeMaskSun = defs.append('mask')
      .attr('id','globeMaskSun')
      .attr('x',0).attr('y',0)
      .attr('width', w)
      .attr('height', h);
  
  globeMaskSun.append('rect')
      .attr('x',0).attr('y',0)
      .attr('width', w)
      .attr('height', h);

  globeMaskSun.append('use')
      .attr('id','globeMaskCircleSun')
      .attr("xlink:href", "#sphere");

  var globeMaskMoon = defs.append('mask')
      .attr('id','globeMaskMoon')
      .attr('x',0).attr('y',0)
      .attr('width', w)
      .attr('height', h);
  
  globeMaskMoon.append('rect')
      .attr('x',0).attr('y',0)
      .attr('width', w)
      .attr('height', h);

  globeMaskMoon.append('use')
      .attr('id','globeMaskCircleMoon')
      .attr("xlink:href", "#sphere");

  var sunGradient = defs.append("radialGradient")
      .attr("id", "sunGradient")
      .attr("cx", "75%")
      .attr("cy", "25%");
  
  sunGradient.append("stop").attr("offset", "5%").attr("stop-color", "#ffcfc7");
  sunGradient.append("stop").attr("offset", "150%").attr("stop-color", "#ff6347");

  var moonGradient = defs.append("radialGradient")
      .attr("id", "moonGradient")
      .attr("cx", "75%")
      .attr("cy", "25%");
  
  moonGradient.append("stop").attr("offset", "5%").attr("stop-color", "#f5f5f5");
  moonGradient.append("stop").attr("offset", "150%").attr("stop-color", "#999999");

  var oceanGradient = defs.append("radialGradient")
      .attr("id", "oceanGradient")
      .attr("cx", "75%")
      .attr("cy", "25%");

  oceanGradient.append("stop").attr("offset", "5%").attr("stop-color", "#d4e2ff");
  oceanGradient.append("stop").attr("offset", "150%").attr("stop-color", "#99bbff");

  var landGradient = defs.append("radialGradient")
      .attr("id", "landGradient")
      .attr("cx", "75%")
      .attr("cy", "25%");

  landGradient.append("stop").attr("offset", "5%").attr("stop-color", "#44c17b");
  landGradient.append("stop").attr("offset", "150%").attr("stop-color", "#2e8b57");

}

  function buildGlobe() {

  var land = topojson.feature(world, world.objects.land);
  var borders = topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; });

  svg.append("use")
      .attr("class", "ocean")
      .attr("xlink:href", "#sphere");

  svg.append("path")
      .datum(land)
      .attr("class", "land");

  svg.append("path")
      .datum(borders)
      .attr("class", "borders");

  svg.append("path")
      .datum(graticule)
      .attr("class", "graticule");  

  var meridian180 = d3.geoGraticule()
      .step([179.9999, 0])
      .extentMajor([[179.9999, -90 + 1e-6], [180, 90 - 1e-6]])
      .extentMinor([[179.9999, -90 - 1e-6], [180, 90 + 1e-6]]);

  svg.append("path")
      .datum(meridian180)
      .attr("class", "meridian180");

  var gmt_meridian = d3.geoGraticule()
      .step([90, 0])
      .extent([[-0.1, -90 -1e-6], [0.1, 90 +1e-6]]);

  svg.append("path")
      .datum(gmt_meridian)
      .attr("class", "gmt_meridian");

  var equator = d3.geoGraticule()
      .step([0, 90])
      .extent([[-180, -1], [180, 1]]);

  svg.append("path")
      .datum(equator)
      .attr("class", "equator");

  var capricorn = d3.geoGraticule()
      .step([0, 23.436556])
      .extentMajor([[-180, -23.536556 + 1e-6], [180, 0]])
      .extentMinor([[-180, -23.536556 - 1e-6], [180, -23.536556 + 1e-6]]);

  svg.append("path")
      .datum(capricorn) // Tropic
      .attr("class", "capricorn");

  var cancer = d3.geoGraticule()
      .step([0, 23.436556])
      .extentMajor([[-180,  23.436556 + 1e-6], [180, 23.436556 - 1e-6]])
      .extentMinor([[-180, -23.436556 - 1e-6], [180, 23.436556 + 1e-6]]);

  svg.append("path")
      .datum(cancer) // Tropic
      .attr("class", "cancer");

  var arctic = d3.geoGraticule()
      .step([0, 66.563444])
      .extentMajor([[-180, 66.563444 + 1e-6], [180, 66.563444 - 1e-6]])
      .extentMinor([[-180, 0], [180, 0]]);

  svg.append("path")
      .datum(arctic)
      .attr("class", "arctic");

  var antarctic = d3.geoGraticule()
      .step([0, 66.563444])
      .extentMajor([[-180, -66.563444], [180, 0]])
      .extentMinor([[-180, -66.563444 - 1e-6], [180, -66.563444 + 1e-6]]);
     
  svg.append("path")
      .datum(antarctic)
      .attr("class", "antarctic");

  svg.append("path")
      .datum({type: "LineString", coordinates: [[-180, 0], [-90, -ecliptic], [0, 0], [90, ecliptic], [180, 0]]})
      .attr("class", "ecliptic");

// location
setInterval(function() {
  svg.append("circle")
      .attr("class", "dot")
      .attr("transform", "translate(" + earth([long, lat]) + ")")
      .attr("r", 1);  
  svg.append("circle")
      .attr("class", "ring")
      .attr("transform", "translate(" + earth([long, lat]) + ")")
      .attr("r", 2)
      .transition()
      .ease(d3.easeLinear)
      .duration(6000)
      .style("stroke-opacity", 1e-6)
      .attr("r", 20)
      .remove();
 }, 1000);

  svg.append("path").attr("class", "night");
  svg.append("path").attr("class", "civiltwilight");
  svg.append("path").attr("class", "nauticaltwilight");
  svg.append("path").attr("class", "astronomicaltwilight");

  svg.append('circle').datum([])
      .attr('class','moon');

  svg.append('circle').datum([])
      .attr('class','sun');

  redraw();
  
}

function redraw(now) {
    var now = now || new Date(Date.now());
    var night = d3.selectAll('.night');
    var civil_twilight = d3.selectAll('.civiltwilight');
    var nautical_twilight = d3.selectAll('.nauticaltwilight');
    var astronomical_twilight = d3.selectAll('.astronomicaltwilight');

    var sunshine = d3.selectAll('.sun'); 
    var moonshine = d3.selectAll('.moon');

    var globeMaskCircleSun = d3.selectAll('#globeMaskCircleSun');
    var globeMaskCircleMoon = d3.selectAll('#globeMaskCircleMoon');

    var sunPos = solarPosition(now),
      antiSunPos = antipode(sunPos);

    var moonPos = [sunPos[0] + lunarTerminator, moonDec],
      antiSunPos = antipode(sunPos);

    var noonSun = {
      earth: earth(sunPos),
      skynet: skynet(sunPos)
    };

    var noonMoon = {
      earth: earth(moonPos),
      lunar: lunar(moonPos)
    };

    // sunrise sunset
    night.datum(circle0.center(antiSunPos)).attr("d", path);
    // -6°
    civil_twilight.datum(circle1.center(antiSunPos)).attr("d", path);
    // -12°
    nautical_twilight.datum(circle2.center(antiSunPos)).attr("d", path);
    // -18°
    astronomical_twilight.datum(circle3.center(antiSunPos)).attr("d", path);

    // sunshine refelction across the ocean (middle point is sun position) 
    d3.select('#oceanGradient')
    .attr('cx', ((noonSun.earth[0] - (w - h) / 2) / h * 100) + '%')
    .attr('cy', (noonSun.earth[1] / h * 100) + '%');

    // sunshine refelction across land (middle point is sun position)
    d3.select('#landGradient')
    .attr('cx', ((noonSun.earth[0] - (w - h) / 2) / h * 100) + '%')
    .attr('cy', (noonSun.earth[1] / h * 100) + '%');
   
    // 3D effect starting at sunrise small --> midday largest --> sunset small (displays a distance effect)
    var sunSizeFactor = 1 - (degrees_from_center_sun(sunPos) / 180);

    // 3D effect starting at moonrise small --> midday largest --> moonset small (displays a distance effect)
    var moonSizeFactor = 1 - (degrees_from_center_moon(moonPos) / 180);

    sunshine
      .attr("cx", noonSun.skynet[0])
      .attr("cy", noonSun.skynet[1])
      .attr('r', sunScale(sunSizeFactor));

    moonshine
      .attr("cx", noonMoon.lunar[0])
      .attr("cy", noonMoon.lunar[1])
      .attr('r', moonScale(moonSizeFactor));

    d3.selectAll('.land').attr('d',path);
    d3.selectAll('.borders').attr('d',path);
    d3.selectAll('.graticule').attr("d", path);
    d3.selectAll('.meridian180').attr("d", path);
    d3.selectAll('.gmt_meridian').attr("d", path);
    d3.selectAll('.equator').attr("d", path);
    d3.selectAll('.capricorn').attr("d", path);
    d3.selectAll('.cancer').attr("d", path);
    d3.selectAll('.arctic').attr("d", path);
    d3.selectAll('.antarctic').attr("d", path);
    d3.selectAll('.ecliptic').attr("d", path);

    globeMaskCircleSun // (sun mask) naturally rising an setting of the sun behind the globe
      .classed('behind', d => { return degrees_from_center_sun(sunPos) > 90 ? true : false; });

    globeMaskCircleMoon // (moon mask) naturally rising and setting of the moon behind the globe
      .classed('behind', d => { return degrees_from_center_moon(moonPos) > 90 ? true : false; });           
   }
});

function degrees_from_center_sun(d) {
    var distanceBetweenSun = d3.geoDistance
    centerPos = skynet.invert(center);   
  return toDegrees(distanceBetweenSun(d,centerPos));
}

function degrees_from_center_moon(d) {
    var distanceBetweenMoon = d3.geoDistance
    centerPos = lunar.invert(center); 
  return toDegrees(distanceBetweenMoon(d,centerPos));
}

function fade_at_edge_sun(d) {
  var sunPos = d;
  var sunDistance = degrees_from_center_sun(sunPos);  
  var fadeRangeSun = 6; // 6°
  var sunFade = d3.scaleLinear()
    .domain([90 + fadeRangeSun, 90 - fadeRangeSun])
    .range([0.1, 1]) // opacity range
    .clamp(true); 
  
  return sunFade(sunDistance);
}

// this function is not used but might be useful at some point
function fade_at_edge_moon(d) {
  var moonPos = d;
  var moonDistance = degrees_from_center_moon(moonPos);  
  var fadeRangeMoon = 6; // 6°
  var moonFade = d3.scaleLinear()
    .domain([90 + fadeRangeMoon, 90 - fadeRangeMoon])
    .range([0.1, 1]) // opacity range
    .clamp(true); 
  
  return moonFade(moonDistance);  
}

// Establish solar Geometric position
function antipode(position) {
  return [position[0] + 180.0, - position[1]];
}

function solarPosition(time) { 
      var T = (time - Date.UTC(2000, 0, 1, 12)) / 864e5 / 36525, // since J2000
        longitude = (d3.utcDay.floor(time) - time) / 864e5 * 360 - 180.0;
      return [
        longitude - toDegrees(equationOfTime(T)),
        toDegrees(solarDeclination(T))
    ];
 }

// Kepler's equation of time
function equationOfTime(T) { 
    var e = eccentricityEarthOrbit(T),
        m = solarGeometricMeanAnomaly(T),
        l = solarGeometricMeanLongitude(T),
        y = Math.tan(obliquityCorrection(T) / 2);
    y *= y;
    return y * Math.sin(2 * l) - 2 * e * Math.sin(m) + 4 * e * y * Math.sin(m) * Math.cos(2 * l) - 0.5 * y * y * Math.sin(4 * l) - 1.25 * e * e * Math.sin(2 * m);
 }

function solarDeclination(T) { 
    return Math.asin(Math.sin(obliquityCorrection(T)) * Math.sin(solarApparentLongitude(T)));
 }

function solarApparentLongitude(T) { 
    return solarTrueLongitude(T) - toRadians((0.00569 + 0.00478 * Math.sin((125.04 - 1934.136 * T) * π / 180.0)));
 }

function solarTrueLongitude(T) { 
    return solarGeometricMeanLongitude(T) + solarEquationOfCenter(T);
 }

function solarGeometricMeanAnomaly(T) { 
    return toRadians((357.52911 + T * (35999.05029 - 0.0001537 * T)));
 }

function solarGeometricMeanLongitude(T) { 
    var l = (280.46646 + T * (36000.76983 + T * 0.0003032)) % 360;
    return (l < 0 ? l + 360 : l) / 180.0 * π;
 }

function solarEquationOfCenter(T) { 
    var m = solarGeometricMeanAnomaly(T);
    return toRadians((Math.sin(m) * (1.914602 - T * (0.004817 + 0.000014 * T)) + Math.sin(m + m) * (0.019993 - 0.000101 * T) + Math.sin(m + m + m) * 0.000289));
 }

function obliquityCorrection(T) { 
    return meanObliquityOfEcliptic(T) + 0.00256 * toRadians(Math.cos((125.04 - 1934.136 * T) * π / 180.0));
 }

function meanObliquityOfEcliptic(T) { 
    return toRadians((23 + (26 + (21.448 - T * (46.8150 + T * (0.00059 - T * 0.001813))) / 60) / 60));
 }

function eccentricityEarthOrbit(T) { 
    return 0.016708634 - T * (0.000042037 + 0.0000001267 * T);
 }
 
</script>
</body>
</html>