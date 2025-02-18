<?php
include('dvmCombinedData.php');
date_default_timezone_set($TZ);
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
$light = $alm["daylight"]; 
$daylight = ltrim($light, '0'); 
$dark = 24 - str_replace(':', '.', $alm["daylight"]);
$lighthours = substr($alm["daylight"], 0, 2); 
$lightmins = substr($alm["daylight"], - 2);
$darkhours = 23 - $lighthours; 
$darkminutes = 60 - $lightmins;
$darkminutes = ($darkminutes < 10) ? '0' .$darkminutes : $darkminutes;
if (round($sun_alt,2) >= 0) { 
$sun_elevation = round($sun_alt,2)."°<div class=sunabovedivumwx> </div>";
} else if (round($sun_alt,2) < 0) { 
$sun_elevation = round($sun_alt,2)."°<div class=sunbelowdivumwx> </div>"; 
}?>
<!DOCTYPE html>
<html lang="en">
<head> 
<meta charset="utf-8">
<title>Earth Daylight Module</title>
</head>
<body>
<div class="chartforecast">
<span class="yearpopup"><a alt="World Daylight Map" title="World Daylight Map" href="dvmDaylightMapPopup.php" data-lity><?php echo $chartinfo;?> World Daylight Map</a></span>
<!--span class="yearpopup"><a alt="Projection" title="Projection" href="dvmProjection.php" data-lity><?php echo $chartinfo;?> Projected World Maps</a></span-->
<span class="yearpopup"><a alt="Solar Terminator" title="Solar Terminator" href="dvmSolarTerminatorPopup.php" data-lity><?php echo $chartinfo;?> Solar Terminator</a></span>
</div>
<span class='moduletitle'><?php echo $lang['earthDaylightModule'];?></span>   
<div class="updatedtime1"><span>
<?php if(file_exists($livedata)&&time() - filemtime($livedata)>300) echo $offline. '<offline> Offline </offline>'; else echo $online." ".$divum["time"];?></span>
</div>
<div class="daylightmoduleposition"> 
<?php echo 
'<div class="divumwxsunlightday"><divumwxdaylightdaycircle></divumwxdaylightdaycircle> '.$alm["daylight"].' hrs<br>'.$lang['TotalDaylight'].'</div>
<div class="divumwxsundarkday">'.$darkhours.':'.$darkminutes.' hrs <divumwxdarkdaycircle></divumwxdarkdaycircle><br>'.$lang['TotalDarkness'].'</div>
<div class="divumwxsunriseday">'.$sunuphalf.''.$lang['Sunrise'].'<br>Today: '.$alm["sunrise"].'<br>First Light: (<blueu>'.$alm["civil_twilight_begin"] .'</blueu>)</div>
<div class="divumwxsunsetday">'.$sundownhalf.''.$lang['Sunset'].'<br>Tonight: '.$alm["sunset"].'<br>Last Light: (<blueu>'.$alm["civil_twilight_end"].'</blueu>)</div>
<div class="sundialcontainerdiv2" ><div id="sundialcontainer" class=sundialcontainer><div class="suncanvasstyle"></div></div>';?></div>
<?php echo'<div class="divumwxsundistance">Distance<maxred> '.number_format(($alm['sun_distance']),2).' </maxred>km</div>';?>
<?php echo'<div class="divumwxeclipticangle">Ecliptic Angle <maxred>'.number_format($alm["ecliptic_angle"],5).'&deg;</maxred></div>';?>
<?php echo'<div class="divumwxequinox">Next Equinox<br><blueu>'.$alm["next_equinox"].'</blueu></div>';?>
<?php echo'<div class="divumwxsolstice">Next Solstice<br><blueu>'.$alm["next_solstice"].'</blueu></div>';?>

<style>
.solar-oscillator svg { margin-Top: -2.5px; margin-left: -10px; }
.ocean { fill: url(#oceanGradient); }
.graticule { fill: none; stroke: #444; stroke-width: 0.2px; stroke-opacity: 1.0; }
.land { fill: url(#landGradient); }
.borders { fill: none; stroke: #292E35; stroke-width: 0.1px; }
.night { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.2; }
.civiltwilight { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.35; }
.nauticaltwilight { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.5; }
.astronomicaltwilight { stroke-width: 0.1px; stroke: #292e35; fill: #292e35; fill-opacity: 0.6; }
.ecliptic { stroke: yellow; stroke-width: 0.5px; fill: none;}

.sun { mask: url(#globeMaskSun); }
.sun { fill: url(#sunGradientMini); }
.moon { mask: url(#globeMaskMoon); }
.moon { fill: url(#moonGradientMini); }

#globeMaskSun rect { fill: #fff; }
#globeMaskMoon rect { fill: #fff; }
#globeMaskCircleSun { fill: #000; opacity: 0.0; }
#globeMaskCircleMoon { fill: #000; opacity: 0.0; }
#globeMaskCircleSun.behind { opacity: 1.0; }
#globeMaskCircleMoon.behind { opacity: 1.0; }

</style>

<script src="js/d3.7.9.0.min.js"></script>
<script src="js/d3-geo-projection.4.0.0.min.js"></script>
<script src="js/topojson.3.0.2.min.js"></script>

<figure class="earth">
<div id="Globe" class="solar-oscillator"></div>
</figure>
<script>

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

  	var w = 310;
  	var h = 150;

  	var circle0 = d3.geoCircle().radius(90); // night time (sunrise sunset)
    var circle1 = d3.geoCircle().radius(84); // civil twilight (dawn dusk) -6°
    var circle2 = d3.geoCircle().radius(78); // nautical twilight (dawn dusk) -12°
    var circle3 = d3.geoCircle().radius(72); // astronomical twilight (dawn dusk) -18°

if (lat > 0.0) {
    var projection = d3.geoOrthographic()
        .scale(50)
        .translate([w/2, h/2])
        .rotate([-long, -ecliptic, -ecliptic])
        .precision(0.1)
        .clipAngle(90);

    var sunProjection = d3.geoOrthographic()
        .scale(61)
        .rotate([-long, -ecliptic, -ecliptic])
        .precision(0.1)
        .translate(projection.translate());

    var moonProjection = d3.geoOrthographic()
        .scale(53.5)
        .rotate([-long, -ecliptic, -ecliptic])
        .precision(0.1)
        .translate(projection.translate());
} else {
    var projection = d3.geoOrthographic()
        .scale(50)
        .translate([w/2, h/2])
        .rotate([-long, ecliptic, -ecliptic])
        .precision(0.1)
        .clipAngle(90);

    var sunProjection = d3.geoOrthographic()
        .scale(61)
        .rotate([-long, ecliptic, -ecliptic])
        .precision(0.1)
        .translate(projection.translate());

    var moonProjection = d3.geoOrthographic()
        .scale(53.5)
        .rotate([-long, ecliptic, -ecliptic])
        .precision(0.1)
        .translate(projection.translate());
}

  	var path = d3.geoPath()
      	.projection(projection);

    var center = [w/2, h/2];

  	var graticule = d3.geoGraticule();

  	var svg = d3.select("#Globe")
      	.append("svg")
      	//.style("background", "red")      	  
      	.attr("width", w)      	
      	.attr("height", h);

    var defs = svg.append("defs");

    // sun and moon scale factor for a 3D effect
    var sunScale = d3.scaleLinear().domain([0,1]).range([1.5,9]);
    var moonScale = d3.scaleLinear().domain([0,1]).range([0.6,3.5]);

  	svg.append("defs")
      	.append("path")
      	.datum({ type: "Sphere" })
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

    var sunGradientMini = defs.append("radialGradient")
      .attr("id", "sunGradientMini")
      .attr("cx", "75%")
      .attr("cy", "25%");

  sunGradientMini.append("stop").attr("offset", "5%").attr("stop-color", "#ffcfc7");
  sunGradientMini.append("stop").attr("offset", "150%").attr("stop-color", "#ff6347");

  var moonGradientMini = defs.append("radialGradient")
      .attr("id", "moonGradientMini")
      .attr("cx", "75%")
      .attr("cy", "25%");    
  
  moonGradientMini.append("stop").attr("offset", "5%").attr("stop-color", "#f5f5f5");
  moonGradientMini.append("stop").attr("offset", "150%").attr("stop-color", "#999999");

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
 
  	var earth = svg.append("g")
      	.attr("id", "earth");

    earth.append("use")
      	.attr("class", "ocean")
      	.attr("xlink:href", "#sphere");

  	var g = earth.append("g");

  	g.append("path")
      	.datum(graticule)
      	.attr("class", "graticule")
      	.attr("d", path);

    svg.append("circle")
        .attr("r",1)
        .style("fill", "red")
        .attr("transform", d => { return "translate(" + projection([long, lat]) + ")"; }); // location

    var meridian180 = d3.geoGraticule()
        .step([179.9999, 0])
        .extentMajor([[179.9999, -90 + 1e-6], [180, 90 - 1e-6]])
        .extentMinor([[179.9999, -90 - 1e-6], [180, 90 + 1e-6]]);

    g.append("path")
        .datum(meridian180)
        .style("stroke", "red")
        .style("fill", "none")  
        .style("stroke-width", 0.5)
        .attr("d", path);

    var gmt_meridian = d3.geoGraticule()
        .step([90, 0])
        .extent([[-0.1, -90 -1e-6], [0.1, 90 +1e-6]]);

    g.append("path")
        .datum(gmt_meridian)
        .style("stroke", "red")
        .style("fill", "none") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var equator = d3.geoGraticule()
        .step([0, 90])
        .extent([[-180, -1], [180, 1]]);

    g.append("path")
        .datum(equator)
        .style("stroke", "red")
        .style("fill", "none") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var capricorn = d3.geoGraticule()
        .step([0, 23.436556])
        .extentMajor([[-180, -23.536556 + 1e-6], [180, 0]])
        .extentMinor([[-180, -23.536556 - 1e-6], [180, -23.536556 + 1e-6]]);

    g.append("path")
        .datum(capricorn)
        .style("stroke", "blue")
        .style("fill", "none")
        .style("stroke-dasharray", "2, 2") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var cancer = d3.geoGraticule()
        .step([0, 23.436556])
        .extentMajor([[-180,  23.436556 + 1e-6], [180, 23.436556 - 1e-6]])
        .extentMinor([[-180, -23.436556 - 1e-6], [180, 23.436556 + 1e-6]]);

    g.append("path")
        .datum(cancer)
        .style("stroke", "blue")
        .style("fill", "none")
        .style("stroke-dasharray", "2, 2") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var arctic = d3.geoGraticule()
        .step([0, 66.563444])
        .extentMajor([[-180, 66.563444 + 1e-6], [180, 66.563444 - 1e-6]])
        .extentMinor([[-180, 0], [180, 0]])();

    g.append("path")
        .datum(arctic)
        .style("stroke", "magenta")
        .style("fill", "none")
        .style("stroke-dasharray", "2, 2") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    var antarctic = d3.geoGraticule()
        .step([0, 66.563444])
        .extentMajor([[-180, -66.563444], [180, 0]])
        .extentMinor([[-180, -66.563444 - 1e-6], [180, -66.563444 + 1e-6]]);       

    g.append("path")
        .datum(antarctic)
        .style("stroke", "magenta")
        .style("fill", "none")
        .style("stroke-dasharray", "2, 2") 
        .style("stroke-width", 0.5)
        .attr("d", path);

    g.append("path")
        .datum({type: "LineString", coordinates: [[-180, 0], [-90, -ecliptic], [0, 0], [90, ecliptic], [180, 0]]})
        .attr("class", "ecliptic")
        .attr("d", path);

    d3.json("jsondata/worldmap.json").then(function(world) {

    g.insert("path", ".graticule")
        .datum(topojson.feature(world, world.objects.land))
        .attr("class", "land")
        .attr("d", path);

    g.insert("path", ".graticule")
        .datum(topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; }))
        .attr("class", "borders")
        .attr("d", path);             
 });

    var Δ = 0; // delta
    var solarTerminator = solarPosition(new Date(Date.now() + Δ));

    svg.append('circle').datum([])
      .attr('class','moon');

    svg.append('circle').datum([])
      .attr('class','sun');

    var sunshine = d3.selectAll('.sun'); 
    var moonshine = d3.selectAll('.moon');
        
    var globeMaskCircleMoon = d3.selectAll('#globeMaskCircleMoon');     
    var globeMaskCircleSun = d3.selectAll('#globeMaskCircleSun');
    
    var sunPos = solarTerminator,
      antiSunPos = antipode(sunPos);

    var moonPos = [sunPos[0]+lunarTerminator, moonDec],
      antiSunPos = antipode(sunPos);

    var noonSun = {
      projection: projection(sunPos),
      sunProjection: sunProjection(sunPos)
    };

    var noonMoon = {
      projection: projection(moonPos),
      moonProjection: moonProjection(moonPos)
    };

    // sunshine refelction across the ocean (middle point is sun position) 
    d3.select('#oceanGradient')
    .attr('cx', ((noonSun.projection[0] - (w - h) / 2) / h * 100) + '%')
    .attr('cy', (noonSun.projection[1] / h * 100) + '%');

    // sunshine refelction across land (middle point is sun position)
    d3.select('#landGradient')
    .attr('cx', ((noonSun.projection[0] - (w - h) / 2) / h * 100) + '%')
    .attr('cy', (noonSun.projection[1] / h * 100) + '%');
 
    // 3D effect starting at sunrise small --> midday largest --> sunset small (displays a distance effect)
    var sunSizeFactor = 1 - (degrees_from_center_sun(sunPos) / 180);

    // 3D effect starting at moonrise small --> midday largest --> moonset small (displays a distance effect)
    var moonSizeFactor = 1 - (degrees_from_center_moon(moonPos) / 180);

    sunshine
      .attr("cx", noonSun.sunProjection[0])
      .attr("cy", noonSun.sunProjection[1])
      .attr('r', sunScale(sunSizeFactor));

    moonshine
      .attr("cx", noonMoon.moonProjection[0])
      .attr("cy", noonMoon.moonProjection[1])
      .attr('r', moonScale(moonSizeFactor));

    d3.selectAll('.land').attr('d',path);
    d3.selectAll('.ocean').attr('d',path);

    globeMaskCircleSun // (sun mask) naturally rising an setting of the sun behind the globe
      .classed('behind', d => { return degrees_from_center_sun(sunPos) > 90 ? true : false; });

    globeMaskCircleMoon // (moon mask) naturally rising and setting of the moon behind the globe
      .classed('behind', d => { return degrees_from_center_moon(moonPos) > 90 ? true : false; });

    function degrees_from_center_sun(d) {
    var distanceBetweenSun = d3.geoDistance
    centerPos = sunProjection.invert(center);   
  return toDegrees(distanceBetweenSun(d,centerPos));
}

function degrees_from_center_moon(d) {
    var distanceBetweenMoon = d3.geoDistance
    centerPos = moonProjection.invert(center); 
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

    var night = g.append("path")
        .attr("class", "night");

    var civil_twilight = g.append("path")
        .attr("class", "civiltwilight");

    var nautical_twilight = g.append("path")
        .attr("class", "nauticaltwilight");

    var astronomical_twilight = g.append("path")
        .attr("class", "astronomicaltwilight");

    var now = new Date(Date.now() + Δ);

    night.datum(circle0.center(antipode(solarPosition(now)))).attr("d", path);

    civil_twilight.datum(circle1.center(antipode(solarPosition(now)))).attr("d", path);

    nautical_twilight.datum(circle2.center(antipode(solarPosition(now)))).attr("d", path);

    astronomical_twilight.datum(circle3.center(antipode(solarPosition(now)))).attr("d", path);

    Δ += 1;

function antipode(position) { 
    return [position[0] + 180.0, - position[1]];
 }

function solarPosition(time) { 
    var T = (time - Date.UTC(2000, 0, 1, 12)) / 864e5 / 36525.0, // J2000 noon
        longitude = (d3.utcDay.floor(time) - time) / 864e5 * 360.0 - 180.0;
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
    var l = (280.46646 + T * (36000.76983 + T * 0.0003032)) % 360.0;
    return (l < 0 ? l + 360.0 : l) / 180.0 * π;
 }

function solarEquationOfCenter(T) { 
    var m = solarGeometricMeanAnomaly(T);
    return toRadians((Math.sin(m) * (1.914602 - T * (0.004817 + 0.000014 * T)) + Math.sin(m + m) * (0.019993 - 0.000101 * T) + Math.sin(m + m + m) * 0.000289));
 }

function obliquityCorrection(T) { 
    return meanObliquityOfEcliptic(T) + 0.00256 * toRadians(Math.cos((125.04 - 1934.136 * T) * π / 180.0));
 }

function meanObliquityOfEcliptic(T) { 
    return toRadians((23 + (26 + (21.448 - T * (46.8150 + T * (0.00059 - T * 0.001813))) / 60.0) / 60.0));
 }

function eccentricityEarthOrbit(T) { 
    return 0.016708634 - T * (0.000042037 + 0.0000001267 * T);
 }

</script>
</body>
</html>